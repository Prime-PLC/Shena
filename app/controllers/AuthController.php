<?php
/**
 * Authentication Controller - Handles user login, registration, and logout
 */
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Member.php';

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
            
            // Find user by email
            try {
                $user = $this->userModel->findByEmail($email);
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
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['login_time'] = time();
            
            // Update last login
            try {
                $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            } catch (Exception $e) {
                error_log('Failed to update last login: ' . $e->getMessage());
            }
            
            // Redirect based on role
            if (in_array($user['role'], ['super_admin', 'manager'])) {
                $this->redirect('/admin');
            } elseif ($user['role'] === 'agent') {
                $this->redirect('/agent/dashboard');
            } else {
                $this->redirect('/dashboard');
            }
            
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred during login. Please try again.';
            $this->redirect('/login');
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
                    $age = $this->memberModel->calculateAge($memberData['date_of_birth']);
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
                    $age = $this->memberModel->calculateAge($memberData['date_of_birth']);
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
                $packageKey = $memberData['package'] ?? null;
                
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
                    'date_of_birth' => $memberData['date_of_birth'] ?? null,
                    'gender' => $memberData['gender'] ?? 'male',
                    'address' => $memberData['address'] ?? '',
                    'next_of_kin' => $memberData['next_of_kin'] ?? '',
                    'next_of_kin_phone' => $memberData['next_of_kin_phone'] ?? '',
                    'package' => $packageCategory,
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
                    $registrationData['member_number'],
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
                    'account' => $registrationData['member_number']
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
        
        // Convert packages array to include IDs (using array keys as IDs)
        $packagesWithIds = [];
        foreach ($membership_packages as $key => $package) {
            $package['id'] = $key;
            $package['key'] = $key; // Keep the key for backward compatibility
            $packagesWithIds[] = $package;
        }
        
        $data = [
            'title' => 'Join Shena Companion - Public Registration',
            'packages' => $packagesWithIds,
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
            
            // Validate required fields (simple one-step registration)
            $required = ['first_name', 'last_name', 'national_id', 'date_of_birth', 'email', 'phone'];
            
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }
            
            // Sanitize inputs
            $packageId = $this->sanitizeInput($_POST['package_id'] ?? '');
            $firstName = $this->sanitizeInput($_POST['first_name']);
            $lastName = $this->sanitizeInput($_POST['last_name']);
            $nationalId = $this->sanitizeInput($_POST['national_id']);
            $dateOfBirth = $_POST['date_of_birth'];
            $email = $this->sanitizeInput($_POST['email']);
            $phone = $this->sanitizeInput($_POST['phone']);
            $address = $this->sanitizeInput($_POST['address']);
            $county = $this->sanitizeInput($_POST['county']);
            $subCounty = $this->sanitizeInput($_POST['sub_county'] ?? '');
            $postalCode = $this->sanitizeInput($_POST['postal_code'] ?? '');
            $paymentMethod = $this->sanitizeInput($_POST['payment_method'] ?? 'mpesa');
            
            // Normalize payment method - STK push is a type of M-Pesa payment
            if ($paymentMethod === 'stk_push') {
                $paymentMethod = 'mpesa';
            }
            if (empty($paymentMethod)) {
                $paymentMethod = 'mpesa';
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            
            // Validate age
            $age = floor((time() - strtotime($dateOfBirth)) / 31557600); // Seconds in a year
            if ($age < 18) {
                throw new Exception('You must be at least 18 years old to register');
            }
            
            // Get package details (auto-select individual package by age if not explicitly chosen)
            global $membership_packages;
            $package = null;

            if (!empty($packageId) && isset($membership_packages[$packageId])) {
                $package = $membership_packages[$packageId];
            } else {
                $autoPackageKey = $this->findAutoPackageByAge($age, $membership_packages);
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
            if (isset($package['age_max']) && $age > $package['age_max']) {
                echo json_encode([
                    'success' => false,
                    'message' => "This package is for members aged {$package['age_min']}-{$package['age_max']} years. You are {$age} years old. Please select an appropriate package for your age group.",
                    'field' => 'package',
                    'old_values' => $_POST
                ]);
                return;
            }
            
            if (isset($package['age_min']) && $age < $package['age_min']) {
                echo json_encode([
                    'success' => false,
                    'message' => "This package is for members aged {$package['age_min']}-{$package['age_max']} years. You are {$age} years old. Please select an appropriate package for your age group.",
                    'field' => 'package',
                    'old_values' => $_POST
                ]);
                return;
            }
            
            // Check if email or national ID already exists
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
            
            $this->db->getConnection()->beginTransaction();
            
            try {
                // Generate canonical member number
                $memberNumber = $this->generateMemberNumber();
                
                // Create user account (password will be sent via email)
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
                
                // Determine gender from national ID or default
                $gender = 'male'; // Default, or implement logic to determine from national ID
                
                // Map configured package to allowed members.package enum value
                $packageType = $this->memberModel->normalizePackageTier($packageId, $package);
                
                $memberData = [
                    'user_id' => $userId,
                    'member_number' => $memberNumber,
                    'id_number' => $nationalId,
                    'date_of_birth' => $dateOfBirth,
                    'gender' => $gender,
                    'address' => $address,
                    'package' => $packageType,
                    'package_key' => $packageId,
                    'monthly_contribution' => $package['monthly_contribution'],
                    'status' => 'inactive', // Awaiting registration fee payment
                    'maturity_ends' => $maturityEnds,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $memberId = $this->memberModel->create($memberData);
                
                // Handle payment based on method
                $paymentModel = new Payment();
                
                if ($paymentMethod === 'mpesa') {
                    // Check if checkout_request_id was provided from STK push
                    $checkoutRequestId = $_POST['checkout_request_id'] ?? null;
                    $paymentPhone = $_POST['payment_phone'] ?? $phone;
                    
                    $paymentData = [
                        'member_id' => $memberId,
                        'amount' => REGISTRATION_FEE,
                        'payment_type' => 'registration',
                        'payment_method' => 'mpesa',
                        'status' => 'pending',
                        'phone_number' => $paymentPhone,
                        'transaction_reference' => $checkoutRequestId ?? ('REG' . $phone . '_' . time()),
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // If checkout_request_id exists, it means STK push was initiated
                    if ($checkoutRequestId) {
                        $paymentData['notes'] = 'STK Push initiated - awaiting M-Pesa confirmation';
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
                
                $paymentModel->create($paymentData);
                
                $this->db->getConnection()->commit();
                
                // Send confirmation email
                try {
                    $emailService = new EmailService();
                    $emailService->sendRegistrationConfirmation($email, [
                        'name' => $firstName . ' ' . $lastName,
                        'member_number' => $memberNumber,
                        'package_name' => $package['name'],
                        'monthly_contribution' => $package['monthly_contribution'],
                        'maturity_date' => $maturityEnds,
                        'maturity_months' => $maturityMonths,
                        'payment_method' => $paymentMethod,
                        'temp_password' => $tempPassword,
                        'payment_deadline' => date('F j, Y', strtotime('+14 days'))
                    ]);
                    
                    $smsService = new SmsService();
                    $smsService->sendWelcomeSms($phone, [
                        'member_number' => $memberNumber
                    ]);
                } catch (Exception $e) {
                    error_log('Notification sending failed: ' . $e->getMessage());
                }
                
                // Clear any output buffers to prevent warnings from breaking JSON
                if (ob_get_length()) ob_clean();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! Check your email for login credentials.',
                    'member_number' => $memberNumber,
                    'payment_method' => $paymentMethod
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
     * Generate unique member number
     */
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

        if (strpos($packageId, 'couple') !== false) {
            return 'couple';
        }

        if (strpos($category, 'family') !== false || strpos($packageId, 'family') !== false) {
            return 'family';
        }

        return 'individual';
    }
}

