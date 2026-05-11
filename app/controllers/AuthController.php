<?php
/**
 * Authentication Controller - Handles user login, registration, and logout
 */
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../services/SmsService.php';

class AuthController extends BaseController 
{
    private $userModel;
    private $memberModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->memberModel = new Member();
    }
    
    public function showLogin()
    {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
            return;
        }
        
        $data = [
            'title' => 'Login - Shena Companion Welfare Association',
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('auth.login', $data);
    }
    
    public function login()
    {
        try {
            $this->validateCsrf();
            
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Rate limiting - prevent brute force attacks
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rateLimitKey = 'login_attempts_' . md5($ipAddress . $email);
            
            if (!isset($_SESSION[$rateLimitKey])) {
                $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
            }
            
            // Reset counter after 15 minutes
            if (time() - $_SESSION[$rateLimitKey]['time'] > 900) {
                $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
            }
            
            // Block after 5 failed attempts
            if ($_SESSION[$rateLimitKey]['count'] >= 5) {
                $waitTime = 900 - (time() - $_SESSION[$rateLimitKey]['time']);
                $_SESSION['error'] = 'Too many login attempts. Please try again in ' . ceil($waitTime / 60) . ' minutes.';
                $this->redirect('/login');
                return;
            }
            
            // Validate inputs
            if (empty($email) || empty($password)) {
                $_SESSION[$rateLimitKey]['count']++;
                $_SESSION['error'] = 'Please enter both email and password.';
                $this->redirect('/login');
                return;
            }
            
            // Find user by email, member number, or national ID
            try {
                $user = $this->userModel->findByAnyCredential($email);
            } catch (Exception $e) {
                error_log('Database error during login: ' . $e->getMessage());
                $_SESSION['error'] = 'An error occurred. Please try again.';
                $this->redirect('/login');
                return;
            }
            
            if (!$user) {
                $_SESSION[$rateLimitKey]['count']++;
                $_SESSION['error'] = 'Invalid credentials.';
                $this->redirect('/login');
                return;
            }
            
            // Verify password
            if (!$this->userModel->verifyPassword($password, $user['password'])) {
                $_SESSION[$rateLimitKey]['count']++;
                $_SESSION['error'] = 'Invalid credentials.';
                $this->redirect('/login');
                return;
            }

            // If this is a member, preserve status messaging but do not block login
            if (($user['role'] ?? '') === 'member') {
                try {
                    $member = $this->memberModel->getMemberByUserId($user['id']);
                } catch (Exception $e) {
                    $member = null;
                }

                if ($member && (($member['status'] ?? '') !== 'active')) {
                    $status = $member['status'] ?? 'inactive';
                    $_SESSION['info'] = 'Your membership status is currently ' . strtoupper($status) . '. Please complete required payments to activate full benefits.';
                }
            }
            
            // Check if user is active (members are allowed to login even when pending/inactive)
            if ($user['status'] !== 'active') {
                if (($user['role'] ?? '') !== 'member') {
                    $_SESSION['error'] = 'Your account is not active. Please contact support.';
                    $this->redirect('/login');
                    return;
                }
            }
            
            // Reset rate limit on successful login
            unset($_SESSION[$rateLimitKey]);
            $this->establishUserSession($user);
            $this->redirect($this->resolveUserRedirect($user['role'] ?? 'member'));
            
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred during login. Please try again.';
            $this->redirect('/login');
        }
    }

    /**
     * Step 1 of unified login: verify credential + password, then send OTP.
     * POST /login/verify
     */
    public function verifyPasswordAndSendOtp()
    {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();

            $credential = $this->sanitizeInput($_POST['credential'] ?? '');
            $password   = $_POST['password'] ?? '';

            // Rate limiting
            $ipAddress    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rlKey        = 'login_attempts_' . md5($ipAddress . $credential);
            if (!isset($_SESSION[$rlKey])) {
                $_SESSION[$rlKey] = ['count' => 0, 'time' => time()];
            }
            if (time() - $_SESSION[$rlKey]['time'] > 900) {
                $_SESSION[$rlKey] = ['count' => 0, 'time' => time()];
            }
            if ((int)$_SESSION[$rlKey]['count'] >= 5) {
                $wait = ceil((900 - (time() - $_SESSION[$rlKey]['time'])) / 60);
                $this->json(['success' => false, 'message' => 'Too many attempts. Try again in ' . $wait . ' minutes.'], 429);
                return;
            }

            if (empty($credential) || empty($password)) {
                $this->json(['success' => false, 'message' => 'Please enter your ID/Member Number and password.'], 422);
                return;
            }

            $user = $this->userModel->findByAnyCredential($credential);

            if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
                $_SESSION[$rlKey]['count']++;
                $this->json(['success' => false, 'message' => 'Invalid credentials. Please check and try again.'], 422);
                return;
            }

            if (($user['status'] ?? '') !== 'active' && ($user['role'] ?? '') !== 'member') {
                $this->json(['success' => false, 'message' => 'Your account is not active. Please contact support.'], 403);
                return;
            }

            unset($_SESSION[$rlKey]);

            $phone = $user['phone'] ?? null;
            if (empty($phone)) {
                // No phone on file — log in directly
                $this->establishUserSession($user);
                $this->json(['success' => true, 'otp_required' => false, 'redirect' => $this->resolveUserRedirect($user['role'] ?? 'member')]);
                return;
            }

            // Generate OTP and store session
            $otpCode = $this->generateOtpCode();
            $_SESSION['login_otp'] = [
                'user_id'      => (int) $user['id'],
                'code_hash'    => password_hash($otpCode, PASSWORD_DEFAULT),
                'phone'        => $phone,
                'expires_at'   => time() + 300,
                'attempts'     => 0,
                'last_sent_at' => time()
            ];

            $maskedPhone = strlen((string)$phone) >= 4
                ? str_repeat('*', max(0, strlen((string)$phone) - 4)) . substr((string)$phone, -4)
                : '****';

            $smsService = new SmsService();
            $smsResult  = $smsService->sendSms($phone, 'Your SHENA login code is ' . $otpCode . '. It expires in 5 minutes.');

            if (empty($smsResult['success']) && $this->isLocalOrDebugEnvironment()) {
                $this->json(['success' => true, 'otp_required' => true, 'masked_phone' => $maskedPhone,
                    'message' => 'SMS unavailable locally. Test OTP: ' . $otpCode]);
                return;
            }

            $this->json(['success' => true, 'otp_required' => true, 'masked_phone' => $maskedPhone,
                'message' => 'Code sent to ' . $maskedPhone . '.']);

        } catch (Exception $e) {
            error_log('verifyPasswordAndSendOtp error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Send login OTP to a registered phone number.
     * Accepts either a phone number (legacy) or a National ID / Member Number.
     */
    public function sendLoginOtp()
    {
        header('Content-Type: application/json');

        try {
            $this->validateCsrf();

            // Support lookup by National ID / Agent Number / Member Number (primary) or phone (legacy)
            $idInput    = $this->sanitizeInput($_POST['id_number'] ?? '');
            $phoneInput = $this->sanitizeInput($_POST['phone'] ?? '');
            $user = null;
            $phone = null;

            if (!empty($idInput)) {
                $user = $this->userModel->findByAnyCredential($idInput);
                $phone = $user['phone'] ?? null;
                if (!$user || !$phone) {
                    $this->json(['success' => false, 'message' => 'No account found for that identifier. Please check and try again.'], 404);
                    return;
                }
            } else {
                if (empty($phoneInput) || !$this->validatePhone($phoneInput)) {
                    $this->json(['success' => false, 'message' => 'Enter your National ID, Member Number, or a valid phone number.'], 422);
                    return;
                }
                $phone = formatKenyanPhone($phoneInput);
                $user  = $this->userModel->findByPhone($phone);
                if (!$user) {
                    $this->json(['success' => false, 'message' => 'No account found for that identifier.'], 404);
                    return;
                }
            }

            if (($user['status'] ?? '') !== 'active' && ($user['role'] ?? '') !== 'member') {
                $this->json(['success' => false, 'message' => 'Your account is not active. Please contact support.'], 403);
                return;
            }

            // Rate limiting: 60 s cooldown + max 5 sends per 15-min window
            $rateLimitKey = 'otp_login_' . md5((string)$user['id']);
            if (!isset($_SESSION[$rateLimitKey])) {
                $_SESSION[$rateLimitKey] = ['count' => 0, 'window_start' => time(), 'last_sent' => 0];
            }
            if (time() - (int)$_SESSION[$rateLimitKey]['window_start'] > 900) {
                $_SESSION[$rateLimitKey] = ['count' => 0, 'window_start' => time(), 'last_sent' => 0];
            }
            $cooldown = 60 - (time() - (int)$_SESSION[$rateLimitKey]['last_sent']);
            if ($cooldown > 0) {
                $this->json(['success' => false, 'message' => 'Please wait ' . $cooldown . ' seconds before requesting another code.'], 429);
                return;
            }
            if ((int)$_SESSION[$rateLimitKey]['count'] >= 5) {
                $this->json(['success' => false, 'message' => 'Too many OTP requests. Please try again in 15 minutes or use password login.'], 429);
                return;
            }

            $otpCode = $this->generateOtpCode();
            $otpMessage = 'Your SHENA login verification code is ' . $otpCode . '. It expires in 5 minutes.';

            // Mask phone for display — never expose digits beyond last 4
            $maskedPhone = strlen((string)$phone) >= 4
                ? str_repeat('*', max(0, strlen((string)$phone) - 4)) . substr((string)$phone, -4)
                : '****';

            // Store session BEFORE sending SMS so verification works even if SMS
            // delivery reporting has a transient error but the SMS was actually sent.
            $_SESSION['login_otp'] = [
                'user_id' => (int) $user['id'],
                'code_hash' => password_hash($otpCode, PASSWORD_DEFAULT),
                'phone' => $phone,
                'expires_at' => time() + 300,
                'attempts' => 0,
                'last_sent_at' => time()
            ];
            $_SESSION[$rateLimitKey]['count']++;
            $_SESSION[$rateLimitKey]['last_sent'] = time();

            $smsService = new SmsService();
            $smsResult = $smsService->sendSms($phone, $otpMessage);

            if (empty($smsResult['success'])) {
                if ($this->isLocalOrDebugEnvironment()) {
                    $this->json(['success' => true, 'message' => 'SMS unavailable in this environment. Use test OTP: ' . $otpCode]);
                } else {
                    error_log('Login OTP SMS error for user ' . $user['id'] . ': ' . ($smsResult['error'] ?? 'unknown'));
                    // Session is already set — user may still receive the SMS.
                    $this->json(['success' => true, 'message' => 'Verification code sent to ' . $maskedPhone . '. Enter the code below.']);
                }
                return;
            }

            $this->json(['success' => true, 'message' => 'Verification code sent to ' . $maskedPhone . '. Enter the code below.']);
        } catch (Exception $e) {
            error_log('Send login OTP error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    /**
     * Verify OTP and sign in user.
     */
    public function verifyLoginOtp()
    {
        header('Content-Type: application/json');

        try {
            $this->validateCsrf();

            $otpSession = $_SESSION['login_otp'] ?? null;
            if (empty($otpSession)) {
                $this->json(['success' => false, 'message' => 'OTP session expired. Request a new code.'], 400);
                return;
            }

            if (time() > (int) ($otpSession['expires_at'] ?? 0)) {
                unset($_SESSION['login_otp']);
                $this->json(['success' => false, 'message' => 'OTP expired. Request a new code.'], 400);
                return;
            }

            if ((int) ($otpSession['attempts'] ?? 0) >= 5) {
                unset($_SESSION['login_otp']);
                $this->json(['success' => false, 'message' => 'Too many attempts. Request a new OTP.'], 429);
                return;
            }

            $otpCode = preg_replace('/\D+/', '', (string) ($_POST['otp_code'] ?? ''));
            if (strlen($otpCode) !== 6 || !password_verify($otpCode, $otpSession['code_hash'])) {
                $_SESSION['login_otp']['attempts'] = ((int) ($otpSession['attempts'] ?? 0)) + 1;
                $this->json(['success' => false, 'message' => 'Invalid OTP code.'], 422);
                return;
            }

            $user = $this->userModel->getUserById((int) $otpSession['user_id']);
            if (!$user) {
                unset($_SESSION['login_otp']);
                $this->json(['success' => false, 'message' => 'User account not found.'], 404);
                return;
            }

            unset($_SESSION['login_otp']);
            $this->establishUserSession($user);

            $this->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $this->resolveUserRedirect($user['role'] ?? 'member')
            ]);
        } catch (Exception $e) {
            error_log('Verify login OTP error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'OTP verification failed.'], 500);
        }
    }
    
    public function showRegister()
    {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
            return;
        }
        
        global $membership_packages;
        
        $data = [
            'title' => 'Register - Shena Companion Welfare Association',
            'csrf_token' => $this->generateCsrfToken(),
            'packages' => $membership_packages
        ];
        
        $this->view('auth.register', $data);
    }
    
    public function register()
    {
        try {
            $this->validateCsrf();
            
            // Sanitize inputs
            $userData = [
                'first_name' => $this->sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => $this->sanitizeInput($_POST['last_name'] ?? ''),
                'email' => $this->sanitizeInput($_POST['email'] ?? ''),
                'phone' => $this->sanitizeInput($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];
            
            $memberData = [
                'id_number' => $this->sanitizeInput($_POST['id_number'] ?? ''),
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'address' => $this->sanitizeInput($_POST['address'] ?? ''),
                'next_of_kin' => $this->sanitizeInput($_POST['next_of_kin'] ?? ''),
                'next_of_kin_phone' => $this->sanitizeInput($_POST['next_of_kin_phone'] ?? ''),
                // This is the specific configured package key (e.g. individual_below_70)
                'package_key' => $_POST['package'] ?? ''
            ];
            
            // Validate required fields
            $required = ['first_name', 'last_name', 'phone', 'password'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    $_SESSION['error'] = 'Please fill in all required fields.';
                    $_SESSION['old_input'] = array_merge($userData, $memberData);
                    unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                    $this->redirect('/register');
                    return;
                }
            }
            
            // Validate email if provided (email is optional)
            if (!empty($userData['email']) && !$this->validateEmail($userData['email'])) {
                $_SESSION['error'] = 'Please enter a valid email address.';
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                $_SESSION['error_field'] = 'email';
                $this->redirect('/register');
                return;
            }
            
            // Validate phone
            if (!$this->validatePhone($userData['phone'])) {
                $_SESSION['error'] = 'Please enter a valid Kenyan phone number.';
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                $_SESSION['error_field'] = 'phone';
                $this->redirect('/register');
                return;
            }
            
            // Validate password
            if (strlen($userData['password']) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters long.';
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                $_SESSION['error_field'] = 'password';
                $this->redirect('/register');
                return;
            }
            
            if ($userData['password'] !== $userData['confirm_password']) {
                $_SESSION['error'] = 'Passwords do not match.';
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                $_SESSION['error_field'] = 'confirm_password';
                $this->redirect('/register');
                return;
            }
            
            // Check if email already exists (only if provided)
            if (!empty($userData['email']) && $this->userModel->findByEmail($userData['email'])) {
                $_SESSION['error'] = 'Email address already registered.';
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                $_SESSION['error_field'] = 'email';
                $this->redirect('/register');
                return;
            }
            
            // Normalize phone and check uniqueness
            $userData['phone'] = formatKenyanPhone($userData['phone']);
            if ($this->userModel->findByPhone($userData['phone'])) {
                $_SESSION['error'] = 'Phone number already registered.';
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                $_SESSION['error_field'] = 'phone';
                $this->redirect('/register');
                return;
            }

            // Validate ID number not already registered to avoid duplicate key DB errors
            $idNumberInput = $this->sanitizeInput($memberData['id_number'] ?? '');
            if (!empty($idNumberInput)) {
                try {
                    if ($this->memberModel->findByIdNumber($idNumberInput)) {
                        $_SESSION['error'] = 'National ID number already registered.';
                        $_SESSION['old_input'] = array_merge($userData, $memberData);
                        unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                        $_SESSION['error_field'] = 'id_number';
                        $this->redirect('/register');
                        return;
                    }
                } catch (Exception $e) {
                    error_log('ID number lookup failed: ' . $e->getMessage());
                    // proceed but warn in logs; we'll still rely on DB constraints as fallback
                }
            }
            
            // Validate age (18-100) and capture for later calculations
            $age = null;
            if (!empty($memberData['date_of_birth'])) {
                try {
                    $age = $this->memberModel->calculateAge((string) ($memberData['date_of_birth'] ?? ''));
                    if ($age < 18 || $age > 100) {
                        $_SESSION['error'] = 'Members must be between 18 and 100 years old.';
                        $_SESSION['old_input'] = array_merge($userData, $memberData);
                        unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                        $_SESSION['error_field'] = 'date_of_birth';
                        $this->redirect('/register');
                        return;
                    }
                } catch (Exception $e) {
                    error_log('Age calculation error: ' . $e->getMessage());
                    $_SESSION['error'] = 'Invalid date of birth.';
                    $_SESSION['old_input'] = array_merge($userData, $memberData);
                    unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                    $_SESSION['error_field'] = 'date_of_birth';
                    $this->redirect('/register');
                    return;
                }
            }
            
            // Start transaction
            try {
                $this->db->getConnection()->beginTransaction();
            } catch (Exception $e) {
                error_log('Failed to start transaction: ' . $e->getMessage());
                $_SESSION['error'] = 'Registration failed. Please try again.';
                $this->redirect('/register');
                return;
            }
            
            try {
                // Create user (ensure phone normalized; email optional)
                unset($userData['confirm_password']);
                $userData['phone'] = formatKenyanPhone($userData['phone']);
                $userData['email'] = $userData['email'] ?: null;
                $userId = $this->userModel->createUser($userData);
                
                // Generate canonical member number
                $memberNumber = MemberNumberHelper::generateCanonical();
                
                // Ensure we have a valid age for contribution/maturity calculations
                if ($age === null && !empty($memberData['date_of_birth'])) {
                    $age = $this->memberModel->calculateAge((string) ($memberData['date_of_birth'] ?? ''));
                }
                if ($age === null) {
                    $_SESSION['error'] = 'Date of birth is required to determine eligibility and package.';
                    $_SESSION['old_input'] = array_merge($userData, $memberData);
                    unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                    $_SESSION['error_field'] = 'date_of_birth';
                    $this->db->getConnection()->rollback();
                    $this->redirect('/register');
                    return;
                }
                
                // Determine package from selected package key
                global $membership_packages;
                $packageKey = $memberData['package_key'] ?? null;
                
                if (empty($packageKey) || !isset($membership_packages[$packageKey])) {
                    $_SESSION['error'] = 'Please select a valid membership package.';
                    $_SESSION['old_input'] = array_merge($userData, $memberData);
                    unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                    $_SESSION['error_field'] = 'package';
                    $this->db->getConnection()->rollback();
                    $this->redirect('/register');
                    return;
                }
                
                $selectedPackage = $membership_packages[$packageKey];
                // Validate selected package age bracket
                if (isset($selectedPackage['age_min'], $selectedPackage['age_max']) && ($age < $selectedPackage['age_min'] || $age > $selectedPackage['age_max'])) {
                    $_SESSION['error'] = 'Selected package does not match your age. Please choose an appropriate package or update your date of birth.';
                    $_SESSION['old_input'] = array_merge($userData, $memberData);
                    unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                    $_SESSION['error_field'] = 'package';
                    $this->db->getConnection()->rollback();
                    $this->redirect('/register');
                    return;
                }

                $packageCategory = $this->memberModel->normalizePackageTier($packageKey, $selectedPackage);

                // Compute monthly contribution centrally using Member model (considers age and dependents)
                $memberForCalc = [
                    'date_of_birth' => $memberData['date_of_birth'] ?? null,
                    'package' => $packageKey
                ];
                $monthlyContribution = $this->memberModel->calculateMonthlyContribution($memberForCalc, []);
                
                // Calculate maturity period end date based on age and policy configuration
                $maturityMonths = isset($selectedPackage['maturity_months']) ? $selectedPackage['maturity_months'] : 
                                  ($age >= 81 ? MATURITY_PERIOD_80_AND_ABOVE : MATURITY_PERIOD_UNDER_80);
                $maturityEnds = date('Y-m-d', strtotime("+{$maturityMonths} months"));
                
                // Create member record with actual database columns
                $memberRecord = [
                    'user_id' => $userId,
                    'member_number' => $memberNumber,
                    'id_number' => $memberData['id_number'] ?? '',
                    'id_number' => $memberData['id_number'] ?? '',
                    'date_of_birth' => $memberData['date_of_birth'] ?? null,
                    'gender' => $memberData['gender'] ?? 'male',
                    'address' => $memberData['address'] ?? '',
                    'next_of_kin' => $memberData['next_of_kin'] ?? '',
                    'next_of_kin_phone' => $memberData['next_of_kin_phone'] ?? '',
                    'package' => $packageCategory,
                    'package_key' => $packageKey,
                    'corporate_couple_count' => 0,
                    'monthly_contribution' => $monthlyContribution,
                    'maturity_ends' => $maturityEnds,
                    'status' => 'inactive',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $memberId = $this->memberModel->create($memberRecord);
                
                // Commit transaction
                $this->db->getConnection()->commit();
                
                // Send welcome email (skip if mail server not configured)
                try {
                    $emailService = new EmailService();
                    @$emailService->sendWelcomeEmail($userData['email'], [
                        'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                        'member_number' => $memberNumber
                    ]);
                } catch (Exception $e) {
                    error_log('Email sending failed: ' . $e->getMessage());
                }
                
                unset($_SESSION['old_input'], $_SESSION['error_field']);
                $_SESSION['registration_complete'] = [
                    'member_id' => $memberId,
                    'member_number' => $memberNumber,
                    'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'],
                    'amount' => REGISTRATION_FEE
                ];
                $this->redirect('/registration/complete');
                
            } catch (Exception $e) {
                $this->db->getConnection()->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $msg = 'Registration failed. Please try again.';
            $errText = $e->getMessage();
            if (stripos($errText, 'Duplicate entry') !== false || stripos($errText, 'SQLSTATE[23000]') !== false) {
                $msg = 'National ID number already exists. Please verify the ID.';
            }
            $_SESSION['error'] = $msg;
            // Preserve form values even on unexpected errors
            if (isset($userData) && isset($memberData)) {
                $_SESSION['old_input'] = array_merge($userData, $memberData);
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
            }
            $this->redirect('/register');
        }
    }

    // -------------------------------------------------------------------------
    // Set-Password (invite link for admin/agent-registered members)
    // -------------------------------------------------------------------------

    /**
     * Generate a signed invite token for a newly-registered user.
     * Token = base64url(userId|expiresUnix) + '.' + HMAC-SHA256 signature
     */
    public static function generateInviteToken(int $userId): string
    {
        $expiresAt = time() + 48 * 3600; // 48 hours
        $payload   = base64_encode($userId . '|' . $expiresAt);
        $secret    = hash('sha256', DB_PASS . '|shena-invite-v1');
        $sig       = hash_hmac('sha256', $payload, $secret);
        return $payload . '.' . $sig;
    }

    /**
     * Verify invite token. Returns user_id on success, false on failure.
     */
    private function verifyInviteToken(string $token)
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) return false;
        [$payload, $sig] = $parts;
        $secret   = hash('sha256', DB_PASS . '|shena-invite-v1');
        $expected = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expected, $sig)) return false;
        $decoded = base64_decode($payload, true);
        if (!$decoded || substr_count($decoded, '|') < 1) return false;
        [$userId, $expiresAt] = explode('|', $decoded, 2);
        if (time() > (int)$expiresAt) return false;
        return (int)$userId;
    }

    public function showSetPassword()
    {
        $token  = $_GET['token'] ?? '';
        $userId = $this->verifyInviteToken($token);
        $error  = null;

        if (!$userId) {
            $error = 'This activation link is invalid or has expired. Please contact support.';
            $this->render('auth/set-password', ['error' => $error, 'csrf_token' => null, 'token' => null]);
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user || $user['status'] !== 'pending') {
            $error = 'This link has already been used or is no longer valid.';
            $this->render('auth/set-password', ['error' => $error, 'csrf_token' => null, 'token' => null]);
            return;
        }

        $this->render('auth/set-password', [
            'title'      => 'Set Your Password - Shena Companion',
            'csrf_token' => $this->generateCsrfToken(),
            'token'      => htmlspecialchars($token, ENT_QUOTES),
            'memberName' => trim($user['first_name'] . ' ' . $user['last_name']),
        ]);
    }

    public function processSetPassword()
    {
        try {
            $this->validateCsrf();
        } catch (Exception $e) {
            $this->render('auth/set-password', ['error' => 'Invalid request. Please use the link from your SMS.', 'csrf_token' => null, 'token' => null]);
            return;
        }

        $token    = $_POST['token'] ?? '';
        $userId   = $this->verifyInviteToken($token);

        if (!$userId) {
            $this->render('auth/set-password', [
                'error'      => 'This activation link is invalid or has expired.',
                'csrf_token' => $this->generateCsrfToken(),
                'token'      => $token,
            ]);
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user || $user['status'] !== 'pending') {
            $this->render('auth/set-password', [
                'error'      => 'This link has already been used. Please log in or use Forgot Password.',
                'csrf_token' => $this->generateCsrfToken(),
                'token'      => $token,
            ]);
            return;
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 8) {
            $this->render('auth/set-password', [
                'error'      => 'Password must be at least 8 characters.',
                'csrf_token' => $this->generateCsrfToken(),
                'token'      => $token,
                'memberName' => trim($user['first_name'] . ' ' . $user['last_name']),
            ]);
            return;
        }

        if ($password !== $confirm) {
            $this->render('auth/set-password', [
                'error'      => 'Passwords do not match. Please try again.',
                'csrf_token' => $this->generateCsrfToken(),
                'token'      => $token,
                'memberName' => trim($user['first_name'] . ' ' . $user['last_name']),
            ]);
            return;
        }

        // Set password and activate account
        $this->userModel->update($userId, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status'   => 'active',
        ]);

        $this->render('auth/set-password', [
            'success'    => 'Your password has been set and your account is now active. You can now log in.',
            'csrf_token' => null,
            'token'      => null,
        ]);
    }

    public function showForgotPassword()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Forgot Password - Shena Companion',
            'csrf_token' => $this->generateCsrfToken(),
        ];
        $this->view('auth.forgot-password', $data);
    }

    public function sendForgotPasswordOtp()
    {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();

            $phoneInput = $this->sanitizeInput($_POST['phone'] ?? '');
            if (empty($phoneInput) || !$this->validatePhone($phoneInput)) {
                $this->json(['success' => false, 'message' => 'Enter a valid Kenyan phone number.'], 422);
                return;
            }

            $phone = formatKenyanPhone($phoneInput);
            $user = $this->userModel->findByPhone($phone);

            if (!$user) {
                // Return success to avoid phone enumeration
                $this->json(['success' => true, 'message' => 'If that number is registered, you will receive a reset code shortly.']);
                return;
            }

            $rateLimitKey = 'fp_otp_' . md5($phone);
            $rl = $_SESSION[$rateLimitKey] ?? ['count' => 0, 'window_start' => time()];
            if ((time() - $rl['window_start']) > 900) {
                $rl = ['count' => 0, 'window_start' => time()];
            }
            if ($rl['count'] >= 5) {
                $this->json(['success' => false, 'message' => 'Too many requests. Try again in 15 minutes.'], 429);
                return;
            }

            $otpCode = $this->generateOtpCode();
            $_SESSION['fp_otp'] = [
                'user_id'    => (int) $user['id'],
                'phone'      => $phone,
                'code_hash'  => password_hash($otpCode, PASSWORD_DEFAULT),
                'expires_at' => time() + 600,
                'attempts'   => 0,
                'verified'   => false,
            ];
            $rl['count']++;
            $_SESSION[$rateLimitKey] = $rl;

            $smsService = new SmsService();
            $smsResult = $smsService->sendSms($phone, 'Your SHENA password reset code is ' . $otpCode . '. Valid for 10 minutes. Ignore if you did not request this.');

            if (empty($smsResult['success'])) {
                if ($this->isLocalOrDebugEnvironment()) {
                    $this->json(['success' => true, 'message' => 'SMS unavailable locally. Reset code: ' . $otpCode]);
                    return;
                }
                error_log('Forgot password OTP SMS error: ' . ($smsResult['error'] ?? 'unknown'));
            }

            $this->json(['success' => true, 'message' => 'Reset code sent. Check your phone.']);
        } catch (Exception $e) {
            error_log('Send forgot-password OTP error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to send code. Please try again.'], 500);
        }
    }

    public function verifyForgotPasswordOtp()
    {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();

            $otpSession = $_SESSION['fp_otp'] ?? null;
            if (empty($otpSession) || $otpSession['expires_at'] < time()) {
                $this->json(['success' => false, 'message' => 'Code expired. Request a new one.'], 400);
                return;
            }

            if ($otpSession['attempts'] >= 5) {
                unset($_SESSION['fp_otp']);
                $this->json(['success' => false, 'message' => 'Too many wrong attempts. Request a new code.'], 429);
                return;
            }

            $enteredCode = trim($_POST['otp'] ?? '');
            if (!password_verify($enteredCode, $otpSession['code_hash'])) {
                $_SESSION['fp_otp']['attempts']++;
                $this->json(['success' => false, 'message' => 'Incorrect code. Please try again.'], 422);
                return;
            }

            $_SESSION['fp_otp']['verified'] = true;
            $this->json(['success' => true, 'message' => 'Code verified. Set your new password.']);
        } catch (Exception $e) {
            error_log('Verify forgot-password OTP error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Verification failed. Please try again.'], 500);
        }
    }

    public function resetPassword()
    {
        try {
            $this->validateCsrf();

            $otpSession = $_SESSION['fp_otp'] ?? null;
            if (empty($otpSession) || empty($otpSession['verified']) || $otpSession['expires_at'] < time()) {
                $_SESSION['error'] = 'Reset session expired. Please start again.';
                $this->redirect('/forgot-password');
                return;
            }

            $password = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters.';
                $this->redirect('/forgot-password');
                return;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Passwords do not match.';
                $this->redirect('/forgot-password');
                return;
            }

            $userId = (int) $otpSession['user_id'];
            $this->userModel->updatePassword($userId, $password);
            unset($_SESSION['fp_otp']);

            $_SESSION['success'] = 'Password reset successfully. Please log in with your new password.';
            $this->redirect('/login');
        } catch (Exception $e) {
            error_log('Reset password error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reset password. Please try again.';
            $this->redirect('/forgot-password');
        }
    }

    public function logout()
    {
        session_destroy();
        session_start();
        $_SESSION['success'] = 'You have been logged out successfully.';

        $this->redirect('/');
    }
    
    /**
     * Show registration complete page with payment options
     */
    public function registrationComplete()
    {
        // Check if registration was just completed
        if (!isset($_SESSION['registration_complete'])) {
            $_SESSION['error'] = 'Registration session expired. Please login to make payment.';
            $this->redirect('/login');
            return;
        }
        
        $registrationData = $_SESSION['registration_complete'];
        
        $data = [
            'title' => 'Registration Complete - Payment Required',
            'registration' => $registrationData
        ];
        
        $this->view('auth.registration-complete', $data);
    }
    
    /**
     * Initiate registration payment via STK push
     */
    public function initiateRegistrationPayment()
    {
        header('Content-Type: application/json');
        
        try {
            // Check if registration data exists
            if (!isset($_SESSION['registration_complete'])) {
                $this->json(['error' => 'Registration session expired'], 400);
                return;
            }
            
            $registrationData = $_SESSION['registration_complete'];
            $input = json_decode(file_get_contents('php://input'), true);
            
            $phoneNumber = $input['phone_number'] ?? $registrationData['phone'];
            $paymentMethod = $input['payment_method'] ?? 'stk';
            
            if ($paymentMethod === 'stk') {
                // Initiate STK push
                require_once __DIR__ . '/../services/PaymentService.php';
                $paymentService = new PaymentService();
                
                $response = $paymentService->initiateSTKPush(
                    $phoneNumber,
                    $registrationData['amount'],
                    $registrationData['id_number'] ?? $registrationData['member_number'],
                    'Registration Fee'
                );
                
                if ($response && isset($response['CheckoutRequestID'])) {
                    // Record payment attempt
                    require_once __DIR__ . '/../models/Payment.php';
                    $paymentModel = new Payment();
                    
                    $paymentModel->recordPayment([
                        'member_id' => $registrationData['member_id'],
                        'amount' => $registrationData['amount'],
                        'payment_type' => 'registration',
                        'payment_method' => 'mpesa',
                        'phone_number' => $phoneNumber,
                        'status' => 'pending',
                        'transaction_reference' => $response['CheckoutRequestID']
                    ]);
                    
                    $this->json([
                        'success' => true,
                        'message' => 'Payment request sent. Check your phone for M-Pesa prompt.',
                        'checkout_request_id' => $response['CheckoutRequestID']
                    ]);
                } else {
                    $this->json(['error' => 'Failed to initiate payment. Please try again.'], 500);
                }
            } else {
                $this->json([
                    'success' => true,
                    'message' => 'Please complete payment via M-Pesa paybill.',
                    'paybill' => MPESA_BUSINESS_SHORTCODE,
                    'account' => $registrationData['id_number'] ?? $registrationData['member_number']
                ]);
            }
            
        } catch (Exception $e) {
            error_log('Registration payment error: ' . $e->getMessage());
            $this->json(['error' => 'Payment initiation failed'], 500);
        }
    }
    
    /**
     * Initiate STK push for public registration
     */
    public function initiatePublicRegistrationPayment()
    {
        header('Content-Type: application/json');
        
        try {
            // Get form data from POST
            $phoneNumber = $_POST['phone_number'] ?? '';
            $amount = REGISTRATION_FEE;
            
            // Validate phone number
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = '254' . substr($phoneNumber, 1);
            }
            
            if (!preg_match('/^254[17][0-9]{8}$/', $phoneNumber)) {
                $this->json(['success' => false, 'message' => 'Invalid phone number format'], 400);
                return;
            }
            
            // Generate temporary reference
            $reference = 'REG' . $phoneNumber . '_' . time();
            
            // Initiate STK push
            require_once __DIR__ . '/../services/PaymentService.php';
            $paymentService = new PaymentService();
            
            $response = $paymentService->initiateSTKPush(
                $phoneNumber,
                $amount,
                $reference,
                'Registration Fee'
            );
            
            if ($response && isset($response['CheckoutRequestID'])) {
                // Store in session for tracking
                $_SESSION['pending_registration_payment'] = [
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'phone_number' => $phoneNumber,
                    'amount' => $amount,
                    'reference' => $reference,
                    'timestamp' => time()
                ];
                
                $this->json([
                    'success' => true,
                    'message' => 'Payment request sent. Check your phone for M-Pesa prompt.',
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to initiate payment. Please try again.'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Public registration STK push error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Show transaction verification page
     */
    public function showTransactionVerification()
    {
        $data = [
            'title' => 'Verify Your Payment - Shena Companion'
        ];
        
        $this->view('auth.verify-transaction', $data);
    }
    
    /**
     * Verify M-Pesa transaction code and activate account
     */
    public function verifyTransaction()
    {
        header('Content-Type: application/json');
        
        try {
            $transactionCode = $_POST['transaction_code'] ?? '';
            $phoneNumber = $_POST['phone_number'] ?? '';
            
            // Validate inputs
            if (empty($transactionCode)) {
                $this->json(['success' => false, 'message' => 'Please enter M-Pesa transaction code'], 400);
                return;
            }
            
            if (empty($phoneNumber)) {
                $this->json(['success' => false, 'message' => 'Please enter your phone number'], 400);
                return;
            }
            
            // Format phone number
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = '254' . substr($phoneNumber, 1);
            }
            
            // Format transaction code (remove spaces, uppercase)
            $transactionCode = strtoupper(preg_replace('/\s+/', '', $transactionCode));
            
            // Search for payment record with this transaction code or phone number
            require_once __DIR__ . '/../models/Payment.php';
            require_once __DIR__ . '/../models/Member.php';
            $paymentModel = new Payment();
            $memberModel = new Member();
            
            // Try to find payment by transaction code in transaction_reference or mpesa_receipt_number
            $sql = "SELECT p.*, m.member_number, m.user_id, u.email, u.first_name, u.last_name 
                    FROM payments p 
                    JOIN members m ON p.member_id = m.id 
                    JOIN users u ON m.user_id = u.id
                    WHERE (p.mpesa_receipt_number = :code OR p.transaction_reference LIKE :code_pattern)
                    AND p.phone_number LIKE :phone
                    ORDER BY p.created_at DESC 
                    LIMIT 1";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                ':code' => $transactionCode,
                ':code_pattern' => '%' . $transactionCode . '%',
                ':phone' => '%' . substr($phoneNumber, -9) . '%'
            ]);
            
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$payment) {
                // Try alternative search - by phone and recent pending payments
                $sql = "SELECT p.*, m.member_number, m.user_id, u.email, u.first_name, u.last_name 
                        FROM payments p 
                        JOIN members m ON p.member_id = m.id 
                        JOIN users u ON m.user_id = u.id
                        WHERE p.phone_number LIKE :phone
                        AND p.status IN ('pending', 'initiated')
                        AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        ORDER BY p.created_at DESC 
                        LIMIT 1";
                
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([':phone' => '%' . substr($phoneNumber, -9) . '%']);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$payment) {
                $this->json([
                    'success' => false, 
                    'message' => 'No matching payment found. Please verify your transaction code and phone number.'
                ], 404);
                return;
            }
            
            // Check if already verified
            if ($payment['status'] === 'completed') {
                $this->json([
                    'success' => true, 
                    'message' => 'Payment already verified! Your account is active.',
                    'member_number' => $payment['member_number']
                ]);
                return;
            }
            
            // Update payment status
            $this->db->getConnection()->beginTransaction();
            
            try {
                // Update payment record
                $updatePayment = "UPDATE payments SET 
                                status = 'completed',
                                mpesa_receipt_number = :receipt,
                                transaction_date = NOW(),
                                verified_at = NOW(),
                                verified_by = 'manual_recovery'
                              WHERE id = :id";
                
                $stmt = $this->db->getConnection()->prepare($updatePayment);
                $stmt->execute([
                    ':receipt' => $transactionCode,
                    ':id' => $payment['id']
                ]);
                
                // Activate member account
                $updateMember = "UPDATE members SET 
                               status = 'active',
                               last_payment_date = NOW()
                             WHERE id = :id";
                
                $stmt = $this->db->getConnection()->prepare($updateMember);
                $stmt->execute([':id' => $payment['member_id']]);
                
                // Update user account status
                $updateUser = "UPDATE users SET status = 'active' WHERE id = :id";
                $stmt = $this->db->getConnection()->prepare($updateUser);
                $stmt->execute([':id' => $payment['user_id']]);
                
                $this->db->getConnection()->commit();
                
                // Send confirmation notifications
                try {
                    require_once __DIR__ . '/../services/EmailService.php';
                    require_once __DIR__ . '/../services/SmsService.php';
                    
                    $emailService = new EmailService();
                    $smsService = new SmsService();
                    
                    $emailService->sendPaymentConfirmation($payment['email'], [
                        'name' => $payment['first_name'] . ' ' . $payment['last_name'],
                        'member_number' => $payment['member_number'],
                        'amount' => $payment['amount'],
                        'receipt' => $transactionCode
                    ]);
                    
                    $smsService->sendPaymentConfirmation($phoneNumber, [
                        'amount' => $payment['amount'],
                        'member_number' => $payment['member_number']
                    ]);
                } catch (Exception $e) {
                    error_log('Notification error: ' . $e->getMessage());
                }
                
                $this->json([
                    'success' => true,
                    'message' => 'Payment verified successfully! Your account is now active.',
                    'member_number' => $payment['member_number'],
                    'redirect' => '/login'
                ]);
                
            } catch (Exception $e) {
                $this->db->getConnection()->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Transaction verification error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Verification failed. Please try again or contact support.'
            ], 500);
        }
    }
    
    /**
     * Show public registration page
     */
    public function showPublicRegistration()
    {
        global $membership_packages;

        // Validate and pass URL preselect params for the 2-step plan picker
        $allowedPlans = ['individual', 'family', 'extended_family_1', 'extended_family_2', 'executive'];
        $preselectPlan = $this->sanitizeInput($_GET['plan'] ?? '');
        if (!in_array($preselectPlan, $allowedPlans, true)) {
            $preselectPlan = '';
        }
        $allowedBrackets = ['below_70', '71_80', '81_90', '91_100', '70_80', '81_90', 'above_70'];
        $preselectBracket = $this->sanitizeInput($_GET['bracket'] ?? '');
        if (!in_array($preselectBracket, $allowedBrackets, true)) {
            $preselectBracket = '';
        }

        $data = [
            'title' => 'Join Shena Companion - Public Registration',
            'tier_definitions' => MembershipPricingService::getTierDefinitions(),
            'preselect_plan'    => $preselectPlan,
            'preselect_bracket' => $preselectBracket,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('public.register-public', $data);
    }
    
    /**
     * Process public registration with payment
     */
    public function processPublicRegistration()
    {
        // Start output buffering to catch any warnings/errors
        ob_start();
        
        header('Content-Type: application/json');
        
        try {
            $this->validateCsrf();
            
            // Validate required fields (minimal registration — package & ID collected on dashboard)
            $required = ['first_name', 'last_name', 'phone'];
            
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }
            
            // Sanitize inputs
            $packageId = $this->sanitizeInput($_POST['package_id'] ?? '');
            $firstName = $this->sanitizeInput($_POST['first_name']);
            $lastName = $this->sanitizeInput($_POST['last_name']);
            $nationalId = $this->sanitizeInput($_POST['national_id'] ?? '');
            $dateOfBirth = $_POST['date_of_birth'] ?? null;
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone']);
            $address = $this->sanitizeInput($_POST['address']);
            $county = $this->sanitizeInput($_POST['county']);
            $subCounty = $this->sanitizeInput($_POST['sub_county'] ?? '');
            $postalCode = $this->sanitizeInput($_POST['postal_code'] ?? '');
            $corporateCoupleCount = max(0, min(5, (int)($_POST['corporate_couple_count'] ?? 0)));
            $paymentMethod = $this->sanitizeInput($_POST['payment_method'] ?? 'mpesa');
            
            // Normalize payment method - STK push is a type of M-Pesa payment
            if ($paymentMethod === 'stk_push') {
                $paymentMethod = 'mpesa';
            }
            if (empty($paymentMethod)) {
                $paymentMethod = 'mpesa';
            }
            
            // Validate email only when provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            
            // Validate phone (Kenyan format)
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (!preg_match('/^(254|0)[17][0-9]{8}$/', $phone)) {
                throw new Exception('Invalid phone number. Use format: 0712345678 or 254712345678');
            }
            
            // Normalize phone to 254 format
            if (substr($phone, 0, 1) === '0') {
                $phone = '254' . substr($phone, 1);
            }
            
            // Validate age only when date of birth is provided
            $age = null;
            if (!empty($dateOfBirth)) {
                $age = floor((time() - strtotime($dateOfBirth)) / 31557600); // Seconds in a year
                if ($age < 18) {
                    throw new Exception('You must be at least 18 years old to register');
                }
            }
            
            // Get package details (auto-select individual package by age if not explicitly chosen)
            global $membership_packages;
            $package = null;

            if (!empty($packageId) && isset($membership_packages[$packageId])) {
                $package = $membership_packages[$packageId];
            } else {
                $autoPackageKey = null;
                if ($age !== null) {
                    $autoPackageKey = $this->findAutoPackageByAge($age, $membership_packages);
                }
                if (!$autoPackageKey && isset($membership_packages['individual_below_70'])) {
                    $autoPackageKey = 'individual_below_70';
                }
                if (!$autoPackageKey && !empty($membership_packages)) {
                    $keys = array_keys($membership_packages);
                    $autoPackageKey = $keys[0] ?? null;
                }
                if ($autoPackageKey && isset($membership_packages[$autoPackageKey])) {
                    $packageId = $autoPackageKey;
                    $package = $membership_packages[$autoPackageKey];
                }
            }
            
            if (!$package) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid package selected',
                    'old_values' => $_POST
                ]);
                return;
            }
            
            // Validate age against package limits
            if ($age !== null && isset($package['age_max']) && $age > $package['age_max']) {
                echo json_encode([
                    'success' => false,
                    'message' => "This package is for members aged {$package['age_min']}-{$package['age_max']} years. You are {$age} years old. Please select an appropriate package for your age group.",
                    'field' => 'package',
                    'old_values' => $_POST
                ]);
                return;
            }
            
            if ($age !== null && isset($package['age_min']) && $age < $package['age_min']) {
                echo json_encode([
                    'success' => false,
                    'message' => "This package is for members aged {$package['age_min']}-{$package['age_max']} years. You are {$age} years old. Please select an appropriate package for your age group.",
                    'field' => 'package',
                    'old_values' => $_POST
                ]);
                return;
            }
            
            // Check if email or national ID already exists
            if (!empty($email)) {
                $existingUser = $this->userModel->findByEmail($email);
                if ($existingUser) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email address already registered',
                        'field' => 'email',
                        'old_values' => $_POST
                    ]);
                    return;
                }
            }

            $existingPhone = $this->userModel->findByPhone($phone);
            if ($existingPhone) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Phone number already registered',
                    'field' => 'phone',
                    'old_values' => $_POST
                ]);
                return;
            }

            if (empty($email)) {
                $email = $this->generatePublicPlaceholderEmail($phone);
            }
            
            if (!empty($nationalId)) {
                $existingMember = $this->memberModel->findByNationalId($nationalId);
                if ($existingMember) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'National ID already registered',
                        'field' => 'national_id',
                        'old_values' => $_POST
                    ]);
                    return;
                }
            }
            
            $this->db->getConnection()->beginTransaction();
            
            try {
                // Generate canonical member number
                $memberNumber = $this->generateMemberNumber();
                
                // Create user account (temporary password until OTP verification/password setup)
                $tempPassword = bin2hex(random_bytes(8));
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                $userData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashedPassword,
                    'role' => 'member',
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $userId = $this->userModel->create($userData);
                
                // Create member record
                $maturityMonths = $package['maturity_months'] ?? 4;
                $maturityEnds = date('Y-m-d', strtotime("+{$maturityMonths} months"));
                
                // Profile details can be completed after first login
                $gender = 'male';
                $safeDateOfBirth = !empty($dateOfBirth) ? $dateOfBirth : null;
                $memberNationalId = !empty($nationalId) ? $nationalId : $this->generateTemporaryIdNumber();
                
                // Map configured package to allowed members.package enum value
                $packageType = $this->memberModel->normalizePackageTier($packageId, $package);
                $memberForCalc = [
                    'date_of_birth' => $safeDateOfBirth,
                    'package' => $packageId,
                    'package_key' => $packageId,
                    'corporate_couple_count' => $corporateCoupleCount
                ];
                $monthlyContribution = $this->memberModel->calculateMonthlyContribution($memberForCalc, []);
                
                $memberData = [
                    'user_id' => $userId,
                    'member_number' => $memberNumber,
                    'id_number' => $memberNationalId,
                    'date_of_birth' => $safeDateOfBirth,
                    'gender' => $gender,
                    'address' => $address,
                    'package' => $packageType,
                    'package_key' => $packageId,
                    'corporate_couple_count' => $corporateCoupleCount,
                    'monthly_contribution' => $monthlyContribution,
                    'status' => 'inactive', // Awaiting registration fee payment
                    'maturity_ends' => $maturityEnds,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $memberId = $this->memberModel->create($memberData);

                // Handle payment based on method
                $paymentModel = new Payment();
                $paymentData = null;

                if ($paymentMethod === 'mpesa') {
                    // Check if checkout_request_id was provided from STK push
                    $checkoutRequestId = $_POST['checkout_request_id'] ?? null;
                    $paymentPhone = $_POST['payment_phone'] ?? $phone;

                    // Do not create a placeholder registration payment here.
                    // Manual Paybill payments are posted by the C2B callback or receipt verification.
                    if ($checkoutRequestId) {
                        $paymentData = [
                            'member_id' => $memberId,
                            'amount' => REGISTRATION_FEE,
                            'payment_type' => 'registration',
                            'payment_method' => 'mpesa',
                            'status' => 'pending',
                            'phone_number' => $paymentPhone,
                            'transaction_reference' => $checkoutRequestId,
                            'checkout_request_id' => $checkoutRequestId,
                            'notes' => 'STK Push initiated - awaiting M-Pesa confirmation',
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                    }
                } else {
                    // Cash/office payment or manual M-Pesa
                    $paymentData = [
                        'member_id' => $memberId,
                        'amount' => REGISTRATION_FEE,
                        'payment_type' => 'registration',
                        'payment_method' => $paymentMethod,
                        'status' => 'pending',
                        'notes' => 'Awaiting payment confirmation within 14 days',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }

                if ($paymentData !== null) {
                    $paymentModel->create($paymentData);
                }

                $otpCode = $this->generateOtpCode();
                $otpMessage = 'Your SHENA registration verification code is ' . $otpCode . '. It expires in 10 minutes.';

                // Commit the transaction BEFORE attempting SMS so the registration
                // is never rolled back due to an SMS delivery failure.
                $this->db->getConnection()->commit();

                $_SESSION['signup_otp'] = [
                    'user_id' => (int) $userId,
                    'member_number' => $memberNumber,
                    'phone' => $phone,
                    'code_hash' => password_hash($otpCode, PASSWORD_DEFAULT),
                    'expires_at' => time() + 600,
                    'attempts' => 0,
                    'last_sent_at' => time()
                ];

                $smsService = new SmsService();
                $smsResult = $smsService->sendSms($phone, $otpMessage);

                $otpDeliveryMessage = 'Registration successful. Verify OTP sent to your phone, then create your password.';
                if (empty($smsResult['success'])) {
                    if ($this->isLocalOrDebugEnvironment()) {
                        $otpDeliveryMessage = 'Registration successful. SMS delivery is unavailable in this environment. Use test OTP: ' . $otpCode . ', then create your password.';
                    } else {
                        // SMS failed but registration succeeded — let the user proceed
                        // and use the resend OTP option on the verification page.
                        error_log('SMS delivery failed after registration for member ' . $memberNumber . ': ' . ($smsResult['error'] ?? 'unknown'));
                        $otpDeliveryMessage = 'Registration successful! We could not deliver the SMS right now. Use the "Resend Code" option on the next page to try again.';
                    }
                }
                
                // Clear any output buffers to prevent warnings from breaking JSON
                if (ob_get_length()) ob_clean();
                
                echo json_encode([
                    'success' => true,
                    'message' => $otpDeliveryMessage,
                    'payment_method' => $paymentMethod,
                    'otp_required' => true,
                    'redirect' => '/register/verify-otp'
                ]);
                exit;
                
            } catch (Exception $e) {
                $this->db->getConnection()->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Public registration error: ' . $e->getMessage());
            
            // Prepare old values without sensitive data
            $oldValues = $_POST ?? [];
            unset($oldValues['csrf_token']);
            
            // Clear any output buffers to prevent warnings from breaking JSON
            if (ob_get_length()) ob_clean();
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'old_values' => $oldValues
            ]);
            exit;
        }
    }

    /**
     * Show signup OTP verification form.
     */
    public function showSignupOtpVerification()
    {
        if (empty($_SESSION['signup_otp'])) {
            $_SESSION['error'] = 'Registration OTP session expired. Please register again.';
            $this->redirect('/register');
            return;
        }

        $phone = (string) ($_SESSION['signup_otp']['phone'] ?? '');
        $lastSentAt = (int) ($_SESSION['signup_otp']['last_sent_at'] ?? 0);
        $cooldownSeconds = 60;
        $resendRemainingSeconds = max(0, $cooldownSeconds - (time() - $lastSentAt));
        $maskedPhone = strlen($phone) >= 4
            ? str_repeat('*', max(0, strlen($phone) - 4)) . substr($phone, -4)
            : $phone;

        $this->view('auth.signup-otp-verify', [
            'title' => 'Verify OTP - Shena Companion',
            'csrf_token' => $this->generateCsrfToken(),
            'masked_phone' => $maskedPhone,
            'resend_remaining_seconds' => $resendRemainingSeconds
        ]);
    }

    /**
     * Verify signup OTP and continue to password creation.
     */
    public function verifySignupOtp()
    {
        try {
            $this->validateCsrf();

            $otpSession = $_SESSION['signup_otp'] ?? null;
            if (empty($otpSession)) {
                $_SESSION['error'] = 'OTP session expired. Please register again.';
                $this->redirect('/register');
                return;
            }

            if (time() > (int) ($otpSession['expires_at'] ?? 0)) {
                unset($_SESSION['signup_otp']);
                $_SESSION['error'] = 'OTP expired. Please register again to receive a new code.';
                $this->redirect('/register');
                return;
            }

            if ((int) ($otpSession['attempts'] ?? 0) >= 5) {
                unset($_SESSION['signup_otp']);
                $_SESSION['error'] = 'Too many OTP attempts. Please register again.';
                $this->redirect('/register');
                return;
            }

            $otpCode = preg_replace('/\D+/', '', (string) ($_POST['otp_code'] ?? ''));
            if (strlen($otpCode) !== 6 || !password_verify($otpCode, (string) $otpSession['code_hash'])) {
                $_SESSION['signup_otp']['attempts'] = ((int) ($otpSession['attempts'] ?? 0)) + 1;
                $_SESSION['error'] = 'Invalid OTP code. Please try again.';
                $this->redirect('/register/verify-otp');
                return;
            }

            $userId = (int) $otpSession['user_id'];
            $this->userModel->update($userId, ['email_verified_at' => date('Y-m-d H:i:s')]);

            // Send welcome SMS with member number
            $memberNumber = (string) ($otpSession['member_number'] ?? '');
            $phone = (string) ($otpSession['phone'] ?? '');

            $_SESSION['signup_password_setup'] = [
                'user_id'       => $userId,
                'phone'         => $phone,
                'member_number' => $memberNumber,
            ];
            unset($_SESSION['signup_otp']);

            $_SESSION['success'] = 'Phone verification successful. Create your password to continue.';
            $this->redirect('/register/create-password');
        } catch (Exception $e) {
            error_log('Verify signup OTP error: ' . $e->getMessage());
            $_SESSION['error'] = 'OTP verification failed. Please try again.';
            $this->redirect('/register/verify-otp');
        }
    }

    /**
     * Resend signup OTP code.
     */
    public function resendSignupOtp()
    {
        try {
            $this->validateCsrf();

            $otpSession = $_SESSION['signup_otp'] ?? null;
            if (empty($otpSession)) {
                $_SESSION['error'] = 'OTP session expired. Please register again.';
                $this->redirect('/register');
                return;
            }

            // Max 3 resends per registration — prevents exhausting SMS credits
            $resendCount = (int)($otpSession['resend_count'] ?? 0);
            if ($resendCount >= 3) {
                $_SESSION['error'] = 'Maximum resend attempts reached. Please start registration again or contact support at +254 748 585 067.';
                $this->redirect('/register/verify-otp');
                return;
            }

            $lastSentAt = (int)($otpSession['last_sent_at'] ?? 0);
            $cooldownSeconds = 60;
            $remaining = $cooldownSeconds - (time() - $lastSentAt);
            if ($remaining > 0) {
                $_SESSION['error'] = 'Please wait ' . $remaining . ' seconds before requesting another code.';
                $this->redirect('/register/verify-otp');
                return;
            }

            $phone = (string)($otpSession['phone'] ?? '');
            if (empty($phone)) {
                $_SESSION['error'] = 'Phone number not found in OTP session. Please register again.';
                $this->redirect('/register');
                return;
            }

            $otpCode = $this->generateOtpCode();
            $otpMessage = 'Your SHENA registration verification code is ' . $otpCode . '. It expires in 10 minutes.';

            $smsService = new SmsService();
            $smsResult = $smsService->sendSms($phone, $otpMessage);

            $_SESSION['signup_otp']['code_hash'] = password_hash($otpCode, PASSWORD_DEFAULT);
            $_SESSION['signup_otp']['expires_at'] = time() + 600;
            $_SESSION['signup_otp']['resend_count'] = $resendCount + 1;
            $_SESSION['signup_otp']['attempts'] = 0;
            $_SESSION['signup_otp']['last_sent_at'] = time();

            if (empty($smsResult['success'])) {
                if ($this->isLocalOrDebugEnvironment()) {
                    $_SESSION['success'] = 'SMS delivery is unavailable in this environment. Your test OTP is: ' . $otpCode;
                } else {
                    $_SESSION['error'] = 'Unable to resend code right now. Please try again shortly.';
                }
            } else {
                $_SESSION['success'] = 'Verification code sent successfully. Check your phone.';
            }

            $this->redirect('/register/verify-otp');
        } catch (Exception $e) {
            error_log('Resend signup OTP error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to resend verification code. Please try again.';
            $this->redirect('/register/verify-otp');
        }
    }

    /**
     * Show create password form after signup OTP verification.
     */
    public function showCreatePassword()
    {
        if (empty($_SESSION['signup_password_setup']['user_id'])) {
            $_SESSION['error'] = 'Password setup session expired. Please register again.';
            $this->redirect('/register');
            return;
        }

        $this->view('auth.create-password', [
            'title' => 'Create Password - Shena Companion',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Store new password and redirect to login.
     */
    public function storeCreatedPassword()
    {
        try {
            $this->validateCsrf();

            $userId = (int) ($_SESSION['signup_password_setup']['user_id'] ?? 0);
            if ($userId <= 0) {
                $_SESSION['error'] = 'Password setup session expired. Please register again.';
                $this->redirect('/register');
                return;
            }

            $password = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters long.';
                $this->redirect('/register/create-password');
                return;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Passwords do not match.';
                $this->redirect('/register/create-password');
                return;
            }

            $this->userModel->updatePassword($userId, $password);

            unset($_SESSION['signup_password_setup']);

            $_SESSION['success'] = 'Password created successfully. Please login.';
            $this->redirect('/login');
        } catch (Exception $e) {
            error_log('Create password error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to create password. Please try again.';
            $this->redirect('/register/create-password');
        }
    }
    
    /**
     * Generate unique member number
     */
    private function generatePublicPlaceholderEmail($phone)
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', (string) $phone);
        $baseLocalPart = 'public-' . ($normalizedPhone !== '' ? $normalizedPhone : time());

        $email = $baseLocalPart . '@noemail.shena.local';
        $attempt = 0;

        while ($this->userModel->findByEmail($email)) {
            $attempt++;
            $email = $baseLocalPart . '-' . $attempt . '@noemail.shena.local';
        }

        return $email;
    }

    private function generateMemberNumber()
    {
        return MemberNumberHelper::generateCanonical();
    }

    /**
     * Auto-select a package by age, preferring individual plans.
     *
     * @param int $age
     * @param array $membershipPackages
     * @return string|null
     */
    private function findAutoPackageByAge($age, array $membershipPackages)
    {
        foreach ($membershipPackages as $key => $package) {
            $isIndividual = ($package['category'] ?? '') === 'individual';
            $hasAgeRange = isset($package['age_min'], $package['age_max']);

            if ($isIndividual && $hasAgeRange && $age >= $package['age_min'] && $age <= $package['age_max']) {
                return $key;
            }
        }

        foreach ($membershipPackages as $key => $package) {
            $hasAgeRange = isset($package['age_min'], $package['age_max']);
            if ($hasAgeRange && $age >= $package['age_min'] && $age <= $package['age_max']) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Map package id/config to the allowed members.package enum values.
     *
     * @param string $packageId
     * @param array $package
     * @return string
     */
    private function mapPackageType($packageId, array $package)
    {
        $category = strtolower((string)($package['category'] ?? ''));

        if ($category === 'executive' || strpos($packageId, 'executive') !== false) {
            return 'executive';
        }

        if (strpos($packageId, 'inlaws') !== false || strpos($category, 'extended_family_2') !== false || strpos($packageId, 'maximum_family') !== false) {
            return 'extended_family_2';
        }

        if (strpos($packageId, 'parents') !== false || strpos($category, 'extended_family_1') !== false || strpos($packageId, 'extended_family') !== false) {
            return 'extended_family_1';
        }

        if (strpos($category, 'family') !== false || strpos($packageId, 'family') !== false || strpos($packageId, 'couple') !== false) {
            return 'family';
        }

        return 'individual';
    }

    /**
     * Generate temporary ID number placeholder for quick signup.
     *
     * @return string
     */
    private function generateTemporaryIdNumber()
    {
        $prefix = 'TMP';

        do {
            $candidate = $prefix . date('ymdHis') . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $exists = $this->memberModel->findByNationalId($candidate);
        } while ($exists);

        return $candidate;
    }

    /**
     * Generate a numeric OTP code.
     *
     * @return string
     */
    private function generateOtpCode()
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Establish authenticated session for user.
     *
     * @param array $user
     * @return void
     */
    private function establishUserSession(array $user)
    {
        $_SESSION['is_first_login'] = empty($user['last_login']);
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['login_time'] = time();

        try {
            $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            error_log('Failed to update last login: ' . $e->getMessage());
        }
    }

    /**
     * Resolve dashboard redirect path by role.
     *
     * @param string $role
     * @return string
     */
    private function resolveUserRedirect($role)
    {
        if (in_array($role, ['super_admin', 'manager'], true)) {
            return '/admin';
        }

        if ($role === 'agent') {
            return '/agent/dashboard';
        }

        return '/dashboard';
    }

    /**
     * Determine if current runtime is local or debug.
     *
     * @return bool
     */
    private function isLocalOrDebugEnvironment()
    {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            return true;
        }

        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        return preg_match('/^(localhost|127\.0\.0\.1)(:\\d+)?$/i', $host) === 1 || PHP_SAPI === 'cli-server';
    }
}

