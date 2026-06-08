<?php
/**
 * Agent Dashboard Controller
 * Handles agent dashboard and operations
 */
require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Beneficiary.php';
require_once __DIR__ . '/../models/MemberCorporateMember.php';
require_once __DIR__ . '/../models/PayoutRequest.php';
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../services/InAppNotificationService.php';
require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../services/ReportService.php';
require_once __DIR__ . '/../helpers/ReportDocumentTemplate.php';



class AgentDashboardController extends BaseController
{
    private $agentModel;
    private $userModel;
    private $memberModel;
    private $beneficiaryModel;
    private $corporateMemberModel;
    private $payoutRequestModel;
    private $paymentService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireAgent();

        $this->agentModel = new Agent();
        $this->userModel = new User();
        $this->memberModel = new Member();
        $this->beneficiaryModel = new Beneficiary();
        $this->corporateMemberModel = new MemberCorporateMember();
        $this->payoutRequestModel = new PayoutRequest();
        $this->paymentService = new PaymentService();
    }

    private function friendlyErrorMessage(Throwable $e, string $fallback): string
    {
        error_log($fallback . ': ' . $e->getMessage());
        return $fallback . ' Please review the information and try again.';
    }

    private function parseCorporateMembersFromPost(): array
    {
        global $membership_packages;

        $rows = $_POST['corporate_members'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $packageKey = $this->sanitizeInput($row['package_key'] ?? '');
            $label = $this->sanitizeInput($row['label'] ?? '');
            $relationship = $this->sanitizeInput($row['relationship'] ?? 'corporate');

            if ($packageKey === '' && $label === '') {
                continue;
            }

            if (!isset($membership_packages[$packageKey])) {
                throw new Exception('Invalid corporate member package selected.');
            }

            $items[] = [
                'label' => $label !== '' ? $label : 'Corporate member',
                'relationship' => $relationship !== '' ? $relationship : 'corporate',
                'package_key' => $packageKey,
            ];
        }

        return $items;
    }

    private function relationshipOptionsForMember(array $member, array $dependents): array
    {
        $coverage = $this->memberModel->getPlanCoverageSummary($member);
        $limits = $coverage['limits'] ?? [];
        $counts = [
            'spouse' => 0,
            'child' => 0,
            'parent' => 0,
            'father_in_law' => 0,
            'mother_in_law' => 0,
        ];

        foreach ($dependents as $dependent) {
            $relationship = $this->memberModel->normalizeDependentRelationship((string)($dependent['relationship'] ?? ''));
            if (isset($counts[$relationship])) {
                $counts[$relationship]++;
            }
        }

        $options = [];
        if ((int)($limits['spouse'] ?? 0) > $counts['spouse']) {
            $options[] = ['value' => 'spouse', 'label' => 'Spouse'];
        }
        if ((int)($limits['children'] ?? 0) > $counts['child']) {
            $options[] = ['value' => 'child', 'label' => 'Child'];
        }
        if ((int)($limits['parents'] ?? 0) > $counts['parent']) {
            $options[] = ['value' => 'parent', 'label' => 'Parent'];
        }
        if ((int)($limits['inlaws'] ?? 0) > ($counts['father_in_law'] + $counts['mother_in_law'])) {
            $options[] = ['value' => 'father_in_law', 'label' => 'Father-in-Law'];
            $options[] = ['value' => 'mother_in_law', 'label' => 'Mother-in-Law'];
        }

        return $options;
    }


    public function dashboard()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);

        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/login');
            return;
        }

        // Get agent statistics from database
        $dbStats = $this->agentModel->getAgentDashboardStats($agent['id']);
        
        // Calculate growth percentages (comparing last 30 days vs previous 30 days)
        $members_growth = 0;
        $policies_growth = 0;
        $commission_growth = 0;
        
        // You can enhance this later with actual growth calculations
        
        // Format stats for dashboard view
        $stats = [
            'total_members' => $dbStats['total_members'] ?? 0,
            'members_growth' => $members_growth,
            'active_policies' => $dbStats['active_members'] ?? 0,
            'policies_growth' => $policies_growth,
            'monthly_commission' => $dbStats['pending_commission'] ?? 0,
            'commission_growth' => $commission_growth,
            'agent_rank' => 0, // Can be calculated based on leaderboard later
            'rank_progress' => 0
        ];

        // Get recent members registered by this agent
        $members = $this->memberModel->getMembersByAgent($agent['id']);

        $data = [
            'title' => 'Agent Dashboard - Shena Companion Welfare Association',
            'agent' => $agent,
            'stats' => $stats,
            'members' => $members
        ];

        $this->view('agent/dashboard', $data);
    }

    public function profile()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);

        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        $data = [
            'title' => 'My Profile - Shena Companion Welfare Association',
            'agent' => $agent,
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('agent/profile', $data);
    }

    public function updateProfile()
    {
        try {
            $this->validateCsrf();

            $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
            if (!$agent) {
                $_SESSION['error'] = 'Agent profile not found.';
                $this->redirect('/agent/profile');
                return;
            }

            // Update user data
            $userData = [
                'first_name' => $this->sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => $this->sanitizeInput($_POST['last_name'] ?? ''),
                'phone' => $this->sanitizeInput($_POST['phone'] ?? '')
            ];

            // Update agent data
            $agentData = [
                'phone' => $this->sanitizeInput($_POST['phone'] ?? ''),
                'address' => $this->sanitizeInput($_POST['address'] ?? ''),
                'county' => $this->sanitizeInput($_POST['county'] ?? '')
            ];

            // Validate phone
            if (!empty($userData['phone']) && !$this->validatePhone($userData['phone'])) {
                $_SESSION['error'] = 'Please enter a valid phone number.';
                $this->redirect('/agent/profile');
                return;
            }

            // Update records
            try {
                $userResult = $this->userModel->update($_SESSION['user_id'], $userData);
                $agentResult = $this->agentModel->updateAgent($agent['id'], $agentData);

                if (!$userResult) {
                    $_SESSION['error'] = 'User profile update failed.';
                    error_log('User profile update failed for user_id: ' . $_SESSION['user_id']);
                }
                if (!$agentResult) {
                    $_SESSION['error'] = 'Agent profile update failed.';
                    error_log('Agent profile update failed for agent_id: ' . $agent['id']);
                }

                if ($userResult && $agentResult) {
                    $_SESSION['success'] = 'Profile updated successfully.';
                }
            } catch (Exception $e) {
                error_log('Database update error: ' . $e->getMessage());
                    $_SESSION['error'] = $this->friendlyErrorMessage($e, 'Failed to update profile.');
            }

        } catch (Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            $_SESSION['error'] = $this->friendlyErrorMessage($e, 'Failed to update profile.');
        }

        $this->redirect('/agent/profile');
    }

    public function members()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        $statusFilter = strtolower($this->sanitizeInput($_GET['status'] ?? 'all'));
        $searchQuery = trim($this->sanitizeInput($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        if (!in_array($statusFilter, ['all', 'pending'], true)) {
            $statusFilter = 'all';
        }

        $members = $this->memberModel->getMembersByAgent($agent['id']);

        if ($statusFilter === 'pending') {
            $members = array_filter($members, function ($member) {
                $status = strtolower($member['status'] ?? $member['display_status'] ?? '');
                return strpos($status, 'pending') !== false;
            });
        }

        if ($searchQuery !== '') {
            $needle = strtolower($searchQuery);
            $members = array_filter($members, function ($member) use ($needle) {
                $fullName = strtolower(trim(($member['full_name'] ?? '') . ' ' . ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')));
                $memberNumber = strtolower($member['member_number'] ?? '');
                $idNumber = strtolower($member['id_number'] ?? '');
                return strpos($fullName, $needle) !== false
                    || strpos($memberNumber, $needle) !== false
                    || strpos($idNumber, $needle) !== false;
            });
        }

        $members = array_values($members);
        $totalMembers = count($members);
        $totalPages = max(1, (int)ceil($totalMembers / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $pagedMembers = array_slice($members, $offset, $perPage);

        $startItem = $totalMembers > 0 ? $offset + 1 : 0;
        $endItem = $totalMembers > 0 ? min($offset + $perPage, $totalMembers) : 0;

        $data = [
            'title' => 'My Members - Shena Companion Welfare Association',
            'agent' => $agent,
            'members' => $pagedMembers,
            'filters' => [
                'status' => $statusFilter,
                'q' => $searchQuery
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalMembers,
                'total_pages' => $totalPages,
                'start_item' => $startItem,
                'end_item' => $endItem
            ]
        ];

        $this->view('agent/members', $data);
    }

    public function commissions()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        $commissions = $this->agentModel->getAgentCommissions($agent['id']);

        // Calculate totals
        $totalEarned = 0;
        $pendingAmount = 0;
        foreach ($commissions as $commission) {
            if ($commission['status'] === 'paid') {
                $totalEarned += $commission['commission_amount'];
            } elseif ($commission['status'] === 'pending') {
                $pendingAmount += $commission['commission_amount'];
            }
        }

        $data = [
            'title' => 'My Commissions - Shena Companion Welfare Association',
            'agent' => $agent,
            'commissions' => $commissions,
            'total_earned' => $totalEarned,
            'pending_amount' => $pendingAmount
        ];

        $this->view('agent/commissions', $data);
    }

    /**
     * Require agent role
     */
    private function requireAgent()
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'agent') {
            $this->redirect('/error/403');
        }
    }    
    /**
     * Show member registration form
     */
    public function registerMember()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        global $membership_packages;

        $data = [
            'title' => 'Register New Member - Shena Companion Welfare Association',
            'agent' => $agent,
            'packages' => $membership_packages,
            'tier_definitions' => MembershipPricingService::getTierDefinitions(),
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('agent/register-member', $data);
    }
    
    /**
     * Process member registration
     */
    public function storeRegisterMember()
    {
        try {
            $this->validateCsrf();

            $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
            if (!$agent) {
                $_SESSION['error'] = 'Agent profile not found.';
                $this->redirect('/agent/register-member');
                return;
            }

            // Store form data in session for repopulation on error
            $_SESSION['form_data'] = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'id_number' => $_POST['id_number'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'address' => $_POST['address'] ?? '',
                'next_of_kin' => $_POST['next_of_kin'] ?? '',
                'next_of_kin_phone' => $_POST['next_of_kin_phone'] ?? '',
                'package' => $_POST['package'] ?? '',
                'corporate_members' => $_POST['corporate_members'] ?? []
            ];

            // Validate email if provided
            $emailInput = $this->sanitizeInput($_POST['email'] ?? '');
            if (!empty($emailInput)) {
                $emailCheck = $this->db->fetch('SELECT id FROM users WHERE email = :email', ['email' => $emailInput]);
                if ($emailCheck) {
                    $_SESSION['error'] = 'This email address is already registered. Use a different email or leave it blank if the member has no email.';
                    $_SESSION['error_step'] = 2;
                    $this->redirect('/agent/register-member');
                    return;
                }
            }

            // Normalize phone and validate uniqueness
            $phoneInput = formatKenyanPhone($this->sanitizeInput($_POST['phone'] ?? ''));
            $phoneCheck = $this->db->fetch('SELECT id FROM users WHERE phone = :phone', ['phone' => $phoneInput]);
            if ($phoneCheck) {
                $_SESSION['error'] = 'This phone number is already registered. Use a different phone number or update the existing member.';
                $_SESSION['error_step'] = 2;
                $this->redirect('/agent/register-member');
                return;
            }

            // Validate ID number not already registered (prevent duplicate unique constraint errors)
            $idNumberInput = $this->sanitizeInput($_POST['id_number'] ?? '');
            if (!empty($idNumberInput)) {
                $existingMember = $this->db->fetch('SELECT id FROM members WHERE id_number = :id_number', ['id_number' => $idNumberInput]);
                if ($existingMember) {
                    $_SESSION['error'] = 'This National ID is already registered. Search your members list before creating a new record.';
                    $_SESSION['error_step'] = 1;
                    // Keep form data for repopulation
                    $_SESSION['form_data'] = $_SESSION['form_data'] ?? [];
                    $this->redirect('/agent/register-member');
                    return;
                }
            }

            $this->db->getConnection()->beginTransaction();

            // Create user record
            $firstName = $this->sanitizeInput($_POST['first_name']);
            $lastName = $this->sanitizeInput($_POST['last_name']);
            $email = $this->sanitizeInput($_POST['email']);
            $phone = $this->sanitizeInput($_POST['phone']);

            $userStmt = $this->db->getConnection()->prepare(
                'INSERT INTO users (first_name, last_name, email, phone, password, role, status, created_at) 
                 VALUES (:first_name, :last_name, :email, :phone, :password, :role, :status, NOW())'
            );
            $userStmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $emailInput ?: null,
                ':phone' => $phoneInput,
                ':password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                ':role' => 'member',
                ':status' => 'pending'
            ]);
            $userId = (int)$this->db->getConnection()->lastInsertId();

            // Create member record
            $memberNumber = MemberNumberHelper::generateCanonical();

            global $membership_packages;
            $packageKey = $_POST['package'] ?? '';
            $corporateMembers = $this->parseCorporateMembersFromPost();
            $accountContribution = MembershipPricingService::calculateAccountMonthlyContribution(
                $packageKey,
                $corporateMembers,
                $membership_packages ?? []
            );
            $corporateCoupleCount = count($accountContribution['line_items']);
            $normalizedPackage = $this->memberModel->normalizePackageTier($packageKey, $membership_packages[$packageKey] ?? []);
            
            $memberStmt = $this->db->getConnection()->prepare(
                'INSERT INTO members (
                    user_id, agent_id, member_number, id_number, date_of_birth, gender, 
                          address, next_of_kin, next_of_kin_phone, package, package_key, corporate_couple_count, monthly_contribution, status, created_at
                 ) VALUES (
                    :user_id, :agent_id, :member_number, :id_number, :date_of_birth, :gender,
                          :address, :next_of_kin, :next_of_kin_phone, :package, :package_key, :corporate_couple_count, :monthly_contribution, :status, NOW()
                 )'
            );
            // Calculate monthly contribution using central logic
            $age = 0;
            try {
                $age = $this->memberModel->calculateAge((string) ($_POST['date_of_birth'] ?? ''));
            } catch (Exception $e) {
                error_log('Agent reg age calc error: ' . $e->getMessage());
            }

            $monthlyContribution = (float)$accountContribution['total_amount'];

            $memberStmt->execute([
                ':user_id' => $userId,
                ':agent_id' => $agent['id'],
                ':member_number' => $memberNumber,
                ':id_number' => $this->sanitizeInput($_POST['id_number']),
                ':date_of_birth' => $_POST['date_of_birth'],
                ':gender' => $_POST['gender'],
                ':address' => $this->sanitizeInput($_POST['address'] ?? ''),
                ':next_of_kin' => $this->sanitizeInput($_POST['next_of_kin']),
                ':next_of_kin_phone' => formatKenyanPhone($this->sanitizeInput($_POST['next_of_kin_phone'] ?? '')),
                ':package' => $normalizedPackage,
                ':package_key' => $packageKey,
                ':corporate_couple_count' => $corporateCoupleCount,
                ':monthly_contribution' => $monthlyContribution,
                // New members registered by agents should start in 'inactive' status
                // so admins can review/activate them. User account remains 'pending'.
                ':status' => 'inactive'
            ]);
            // Grab member id for linking and notifications
            $memberId = (int)$this->db->getConnection()->lastInsertId();
            $this->corporateMemberModel->replaceForMember($memberId, $accountContribution['line_items']);

            $this->db->getConnection()->commit();

            // Send in-app notification to admins about the new member
            try {
                if (class_exists('InAppNotificationService')) {
                    $notif = new InAppNotificationService();
                    $payload = [
                        'subject' => 'New member registered',
                        'message' => trim(($firstName ?? '') . ' ' . ($lastName ?? '')) . " registered by agent {$agent['agent_number']}.",
                        'action_url' => '/admin/members/view/' . $memberId,
                        'action_text' => 'View member'
                    ];
                    $notif->notifyAdmins($payload, $_SESSION['user_id'] ?? null);
                }
            } catch (Exception $e) {
                error_log('Failed to send in-app notification: ' . $e->getMessage());
            }

            $_SESSION['flash_message'] = 'Member registered successfully! Commission will be processed upon payment.';
            $_SESSION['flash_type'] = 'success';
            // Clear form data on success
            unset($_SESSION['form_data']);

            // Send invite SMS with set-password link
            try {
                require_once __DIR__ . '/../services/SmsService.php';
                require_once __DIR__ . '/../controllers/AuthController.php';
                $inviteToken    = AuthController::generateInviteToken($userId);
                $inviteLink     = APP_URL . '/set-password?token=' . urlencode($inviteToken);
                $inviteFirstName = $firstName ?? 'Member';
                $inviteMemberNo  = $memberNumber ?? '';
                $invitePhone     = $phoneInput ?? '';
                $inviteAmount    = $monthlyContribution ?? '0';
                $inviteId        = $this->sanitizeInput($_POST['id_number'] ?? '');
                $smsMsg = "Hi {$inviteFirstName}! You've been registered with SHENA Companion. Member No: {$inviteMemberNo}. "
                        . "Set your account password here: {$inviteLink}  (valid 48 hrs). "
                        . "Monthly contribution: KES {$inviteAmount} via Paybill 4163987, Acct: {$inviteId}.";
                $smsService = new SmsService();
                $smsService->sendSms($invitePhone, $smsMsg);
            } catch (Exception $e) {
                error_log('Agent register invite SMS failed: ' . $e->getMessage());
            }

            $this->redirect('/agent/members');
            return;

        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log('Member registration error: ' . $e->getMessage());
            $msg = 'Failed to register member. Please try again.';
            // If this was a duplicate key error, give a clearer message
            $errText = $e->getMessage();
            if (stripos($errText, 'Duplicate entry') !== false || stripos($errText, 'SQLSTATE[23000]') !== false) {
                if (stripos($errText, 'id_number') !== false) {
                    $msg = 'This National ID is already registered. Search your members list before creating a new record.';
                    $_SESSION['error_step'] = 1;
                } elseif (stripos($errText, 'phone') !== false) {
                    $msg = 'This phone number is already registered. Use a different phone number or update the existing member.';
                    $_SESSION['error_step'] = 2;
                } elseif (stripos($errText, 'email') !== false) {
                    $msg = 'This email address is already registered. Use a different email or leave it blank.';
                    $_SESSION['error_step'] = 2;
                } else {
                    $msg = 'A unique field already exists. Please check National ID, phone, and email.';
                }
            }
            $_SESSION['error'] = $msg;
            // Keep form data for repopulation
            $_SESSION['form_data'] = $_SESSION['form_data'] ?? [];
            $this->redirect('/agent/register-member');
        }
    }
    
    /**
     * Update agent password
     */
    public function updatePassword()
    {
        try {
            $this->validateCsrf();

            $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
            if (!$agent) {
                $_SESSION['error'] = 'Agent profile not found.';
                $this->redirect('/agent/profile');
                return;
            }

            // Validate passwords
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                $_SESSION['error'] = 'New passwords do not match.';
                $this->redirect('/agent/profile');
                return;
            }

            // Verify current password
            $user = $this->userModel->getUserById($_SESSION['user_id']);
            if (!password_verify($_POST['current_password'], $user['password'])) {
                $_SESSION['error'] = 'Current password is incorrect.';
                $this->redirect('/agent/profile');
                return;
            }

            // Update password
            $result = $this->userModel->update($_SESSION['user_id'], [
                'password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT)
            ]);

            if ($result) {
                $_SESSION['success'] = 'Password updated successfully.';
            } else {
                $_SESSION['error'] = 'Password update failed.';
                error_log('Password update failed for user_id: ' . $_SESSION['user_id']);
            }

        } catch (Exception $e) {
            error_log('Password update error: ' . $e->getMessage());
            $_SESSION['error'] = $this->friendlyErrorMessage($e, 'Failed to update password.');
        }

        $this->redirect('/agent/profile');
    }

    /**
     * Display payouts/earnings page
     */
    public function payouts()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        // Get payout requests for this agent
        $payoutRequests = $this->payoutRequestModel->getAgentPayouts($agent['id']);
        
        // Get payout statistics
        $payoutStats = $this->payoutRequestModel->getAgentPayoutStats($agent['id']);
        
        // Get available balance (commissions earned minus payouts)
        $availableBalance = $this->payoutRequestModel->getAvailableBalance($agent['id']);
        
        // Get total earned from commissions
        $allCommissions = $this->agentModel->getAgentCommissions($agent['id']);
        $totalEarned = 0;
        foreach ($allCommissions as $commission) {
            if ($commission['status'] === 'paid') {
                $totalEarned += $commission['commission_amount'];
            }
        }

        $data = [
            'title' => 'Payouts & Earnings - Shena Companion Welfare Association',
            'agent' => $agent,
            'payout_requests' => $payoutRequests,
            'total_earned' => $totalEarned,
            'available_balance' => $availableBalance,
            'payout_stats' => $payoutStats,
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('agent/payouts', $data);
    }


    /**
     * Display resources and training materials
     */
    public function resources()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        // Fetch resources from database
        $resourceModel = new Resource();
        $groupedResources = $resourceModel->getGroupedByCategory(true);

        $data = [
            'title' => 'Resources - Shena Companion Welfare Association',
            'agent' => $agent,
            'resources' => $groupedResources,
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('agent/resources', $data);
    }
    
    /**
     * Download Resource (Agent)
     */
    public function downloadResource($resourceId)
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        $resourceModel = new Resource();
        $resource = $resourceModel->getById($resourceId);
        
        if (!$resource || !$resource['is_active']) {
            $_SESSION['error'] = 'Resource not found or inactive.';
            $this->redirect('/agent/resources');
            return;
        }
        
        if (!file_exists($resource['file_path'])) {
            $_SESSION['error'] = 'File not found on server.';
            $this->redirect('/agent/resources');
            return;
        }
        
        // Record download
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $resourceModel->recordDownload($resourceId, $_SESSION['user_id'], $ipAddress, $userAgent);
        $resourceModel->incrementDownloadCount($resourceId);
        
        // Set headers for download
        header('Content-Type: ' . $resource['mime_type']);
        header('Content-Disposition: attachment; filename="' . $resource['original_name'] . '"');
        header('Content-Length: ' . filesize($resource['file_path']));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($resource['file_path']);
        exit;
    }


    /**
     * Claims access denied for agents
     */
    public function claims()
    {
        $_SESSION['error'] = 'You do not have permission to access claims.';
        $this->redirect('/agent/dashboard');
    }

    /**
     * Display detailed member information
     */
    public function memberDetails($memberId)
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        // Get member details
        $member = $this->memberModel->getMemberById($memberId);
        
        if (!$member) {
            $_SESSION['error'] = 'Member not found.';
            $this->redirect('/agent/members');
            return;
        }

        // Verify this member belongs to this agent
        if ($member['agent_id'] != $agent['id']) {
            $_SESSION['error'] = 'You do not have access to view this member.';
            $this->redirect('/agent/members');
            return;
        }

        // Get member's dependents/beneficiaries
        $dependents = $this->memberModel->getMemberDependents($memberId);
        $coverageSummary = $this->memberModel->getPlanCoverageSummary($member);
        $dependantRelationshipOptions = $this->relationshipOptionsForMember($member, $dependents ?: []);
        
        // Get payment history
        $paymentHistory = $this->memberModel->getMemberPaymentHistory($memberId);

        $data = [
            'title' => 'Member Details - Shena Companion Welfare Association',
            'agent' => $agent,
            'member' => $member,
            'dependents' => $dependents,
            'coverage_summary' => $coverageSummary,
            'dependant_relationship_options' => $dependantRelationshipOptions,
            'payment_history' => $paymentHistory,
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('agent/member-details', $data);
    }

    public function requestClaimAssistance($memberId)
    {
        $this->validateCsrf();

        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/members');
            return;
        }

        $member = $this->memberModel->getMemberById($memberId);
        if (!$member || $member['agent_id'] != $agent['id']) {
            $_SESSION['error'] = 'You do not have access to this member.';
            $this->redirect('/agent/members');
            return;
        }

        $deceasedName = $this->sanitizeInput($_POST['deceased_name'] ?? '');
        $dateOfDeath = $this->sanitizeInput($_POST['date_of_death'] ?? '');
        $claimNotes = $this->sanitizeInput($_POST['claim_notes'] ?? '');

        if ($deceasedName === '' || $dateOfDeath === '') {
            $_SESSION['error'] = 'Please provide the deceased name and date of death.';
            $this->redirect('/agent/member-details/' . (int)$memberId);
            return;
        }

        $notification = new InAppNotificationService();
        $notification->notifyAdmins([
            'subject' => 'Claim assistance request',
            'message' => "Agent {$agent['first_name']} {$agent['last_name']} requested claim assistance for member #{$member['member_number']} ({$member['first_name']} {$member['last_name']}). Deceased: {$deceasedName}. Date of death: {$dateOfDeath}. Notes: {$claimNotes}",
            'action_url' => '/admin/members/view/' . (int)$memberId,
            'action_text' => 'View Member'
        ], $_SESSION['user_id'] ?? null);

        $_SESSION['success'] = 'Claim assistance request sent to administration.';
        $this->redirect('/agent/member-details/' . (int)$memberId);
    }

    public function requestPaymentAssistance($memberId)
    {
        header('Content-Type: application/json');

        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            echo json_encode(['success' => false, 'error' => 'Agent profile not found.']);
            return;
        }

        $member = $this->memberModel->getMemberById($memberId);
        if (!$member || $member['agent_id'] != $agent['id']) {
            echo json_encode(['success' => false, 'error' => 'You do not have access to this member.']);
            return;
        }

        // Accept JSON or POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        if (isset($input['csrf_token'])) {
            $_POST['csrf_token'] = $input['csrf_token'];
        }

        try {
            $this->validateCsrf();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Your session expired. Refresh the page and try again.']);
            return;
        }

        $amount = (float)($input['amount'] ?? 0);
        $phoneNumber = trim($input['phone_number'] ?? '');
        $paymentType = $input['payment_type'] ?? 'monthly';

        if ($amount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Please provide a valid amount.']);
            return;
        }
        if (empty($phoneNumber)) {
            echo json_encode(['success' => false, 'error' => 'Please provide a phone number.']);
            return;
        }

        // Format phone number (254XXXXXXXXX)
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        if (strlen($phoneNumber) === 9) {
            $phoneNumber = '254' . $phoneNumber;
        } elseif (strlen($phoneNumber) === 10 && $phoneNumber[0] === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }
        if (!preg_match('/^254[17]\d{8}$/', $phoneNumber)) {
            echo json_encode(['success' => false, 'error' => 'Invalid phone number. Use format 07XXXXXXXX or 254XXXXXXXXX.']);
            return;
        }

        try {
            $response = $this->paymentService->initiateSTKPush(
                $phoneNumber,
                $amount,
                $member['member_number'],
                ucfirst($paymentType) . ' Contribution'
            );

            if ($response && isset($response['CheckoutRequestID'])) {
                $this->paymentService->recordPaymentAttempt(
                    $member['id'],
                    $amount,
                    $phoneNumber,
                    $response['CheckoutRequestID'],
                    $paymentType,
                    $response['MerchantRequestID'] ?? null
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'M-Pesa request sent to ' . $phoneNumber . '. Please check your phone for the prompt.',
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to initiate M-Pesa payment. Please try again.']);
            }
        } catch (Exception $e) {
            error_log('Agent payment assistance error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Payment initiation failed. Please try again.']);
        }
    }

    public function addDependent($memberId)
    {
        $this->validateCsrf();

        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/members');
            return;
        }

        $member = $this->memberModel->getMemberById($memberId);
        if (!$member || $member['agent_id'] != $agent['id']) {
            $_SESSION['error'] = 'You do not have access to this member.';
            $this->redirect('/agent/members');
            return;
        }

        $dependentData = [
            'member_id' => (int)$memberId,
            'full_name' => $this->sanitizeInput($_POST['full_name'] ?? ''),
            'relationship' => $this->sanitizeInput($_POST['relationship'] ?? ''),
            'id_number' => $this->sanitizeInput($_POST['id_number'] ?? ''),
            'date_of_birth' => $this->sanitizeInput($_POST['date_of_birth'] ?? ''),
            'phone_number' => null,
            'percentage' => 0
        ];

        $dependentPhoneInput = $this->sanitizeInput($_POST['phone_number'] ?? '');
        if ($dependentPhoneInput !== '') {
            $dependentData['phone_number'] = formatKenyanPhone($dependentPhoneInput);
        }

        $currentDependents = $this->beneficiaryModel->getActiveBeneficiaries($memberId) ?: [];
        $policy = $this->memberModel->validateDependentAddition(
            $member,
            $currentDependents,
            $dependentData
        );

        if (empty($policy['allowed'])) {
            $coverageCheck = $policy['coverage'] ?? [];
            $requiredPackage = is_array($coverageCheck) ? ($coverageCheck['required_package'] ?? null) : null;
            if (!empty($requiredPackage)) {
                $_SESSION['error'] = (string)($policy['message'] ?? ('Dependent exceeds current plan coverage. Ask member to upgrade to ' . ucfirst($requiredPackage) . ' first.'));
                $this->redirect('/agent/member-details/' . (int)$memberId);
                return;
            }

            $_SESSION['error'] = (string)($policy['message'] ?? 'Dependent exceeds available coverage limits for this member.');
            $this->redirect('/agent/member-details/' . (int)$memberId);
            return;
        }
        $dependentData['relationship'] = (string)($policy['relationship'] ?? $dependentData['relationship']);

        try {
            $oldMonthly = (int)($member['monthly_contribution'] ?? 0);
            $this->beneficiaryModel->addBeneficiary($dependentData);

            $dependents = $this->beneficiaryModel->getActiveBeneficiaries($memberId);
            $memberForCalc = [
                'date_of_birth' => $member['date_of_birth'] ?? null,
                'package_key' => $member['package_key'] ?? null,
                'package' => $member['package'] ?? null
            ];
            $newMonthly = $this->memberModel->calculateMonthlyContribution($memberForCalc, $dependents ?: []);
            $this->memberModel->update($memberId, ['monthly_contribution' => $newMonthly]);

            if ($newMonthly > $oldMonthly) {
                $increase = $newMonthly - $oldMonthly;
                $_SESSION['success'] = 'Dependent added successfully. Monthly contribution increased by KES ' . number_format($increase) . ' (new total: KES ' . number_format($newMonthly) . ').';
            } else {
                $_SESSION['success'] = 'Dependent added successfully.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $this->friendlyErrorMessage($e, 'Failed to add dependent.');
        }

        $this->redirect('/agent/member-details/' . (int)$memberId);
    }

    public function downloadStatement($memberId)
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/members');
            return;
        }

        $member = $this->memberModel->getMemberById($memberId);
        if (!$member || $member['agent_id'] != $agent['id']) {
            $_SESSION['error'] = 'You do not have access to this member.';
            $this->redirect('/agent/members');
            return;
        }

        $reportService = new ReportService();
        $payload = $reportService->getMemberStatementPayload((int)$memberId, '', '', [
            'prepared_for' => trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')) ?: 'Agent Portal',
            'title' => 'Member Payment Statement',
        ]);
        $filenameBase = 'member-statement-' . (int)$memberId . '-' . date('Ymd');

        if ($this->queryParam('format') === 'csv') {
            $table = $payload['tables'][0] ?? ['headers' => [], 'rows' => []];
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, [$payload['title'] ?? 'Member Statement'], ',', '"', '\\', '');
            fputcsv($output, ['Generated', $payload['generated_at'] ?? date('Y-m-d H:i')], ',', '"', '\\', '');
            fputcsv($output, [], ',', '"', '\\', '');
            fputcsv($output, $table['headers'] ?? [], ',', '"', '\\', '');

            foreach (($table['rows'] ?? []) as $row) {
                fputcsv($output, $row, ',', '"', '\\', '');
            }

            fclose($output);
            exit;
        }

        $autoloadPath = ROOT_PATH . '/vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            $_SESSION['error'] = 'PDF library not installed.';
            $this->redirect('/agent/member-details/' . (int)$memberId);
            return;
        }

        require_once $autoloadPath;

        $html = ReportDocumentTemplate::render($payload);
        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($payload['pdf_filename'] ?? ($filenameBase . '.pdf'), ['Attachment' => true]);
        exit;
    }

    private function queryParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Display support/contact admin page
     */
    public function support()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/dashboard');
            return;
        }

        $data = [
            'title' => 'Support - Shena Companion Welfare Association',
            'agent' => $agent
        ];

        $this->view('agent/support', $data);
    }
    
    /**
     * Display notifications page
     */
    public function notifications()
    {
        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/login');
            return;
        }
        
        $notifications = $this->getAgentNotifications($_SESSION['user_id']);
        
        $data = [
            'title' => 'Notifications - Shena Companion Welfare Association',
            'agent' => $agent,
            'notifications' => $notifications,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('agent/notifications', $data);
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead()
    {
        try {
            $this->validateCsrf();
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $this->json(['success' => false, 'message' => 'Invalid notification.'], 400);
            }

            $stmt = $this->db->getConnection()->prepare('
                UPDATE communication_recipients
                SET status = "read", read_at = NOW()
                WHERE id = :id AND user_id = :user_id
            ');
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $_SESSION['user_id']
            ]);

            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to mark notification as read.'], 500);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        try {
            $this->validateCsrf();

            $stmt = $this->db->getConnection()->prepare('
                UPDATE communication_recipients
                SET status = "read", read_at = NOW()
                WHERE user_id = :user_id AND status <> "read"
            ');
            $stmt->execute([':user_id' => $_SESSION['user_id']]);

            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to mark notifications as read.'], 500);
        }
    }
    
    /**
     * Delete a notification
     */
    public function deleteNotification()
    {
        try {
            $this->validateCsrf();
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $this->json(['success' => false, 'message' => 'Invalid notification.'], 400);
            }

            $stmt = $this->db->getConnection()->prepare('
                DELETE FROM communication_recipients
                WHERE id = :id AND user_id = :user_id
            ');
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $_SESSION['user_id']
            ]);

            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete notification.'], 500);
        }
    }
    
    /**
     * Clear all notifications
     */
    public function clearAllNotifications()
    {
        try {
            $this->validateCsrf();

            $stmt = $this->db->getConnection()->prepare('
                DELETE FROM communication_recipients
                WHERE user_id = :user_id
            ');
            $stmt->execute([':user_id' => $_SESSION['user_id']]);

            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to clear notifications.'], 500);
        }
    }

    public function requestPayout()
    {
        $this->validateCsrf();

        $agent = $this->agentModel->getAgentByUserId($_SESSION['user_id']);
        if (!$agent) {
            $_SESSION['error'] = 'Agent profile not found.';
            $this->redirect('/agent/payouts');
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $this->sanitizeInput($_POST['payment_method'] ?? 'mpesa');
        $phoneNumber = trim($_POST['phone_number'] ?? ($agent['phone'] ?? ''));
        $payoutNotes = $this->sanitizeInput($_POST['payout_notes'] ?? '');

        // Validate payment method
        $validMethods = ['mpesa', 'bank_transfer', 'cash'];
        if (!in_array($paymentMethod, $validMethods)) {
            $_SESSION['error'] = 'Invalid payment method selected.';
            $this->redirect('/agent/payouts');
            return;
        }

        // Get payment method details
        $paymentDetails = '';
        if ($paymentMethod === 'mpesa') {
            if (empty($phoneNumber)) {
                $_SESSION['error'] = 'Please provide a phone number for M-Pesa transfer.';
                $this->redirect('/agent/payouts');
                return;
            }
            $paymentDetails = 'M-Pesa to ' . $phoneNumber;
        } elseif ($paymentMethod === 'bank_transfer') {
            $bankName = $this->sanitizeInput($_POST['bank_name'] ?? '');
            $accountNumber = $this->sanitizeInput($_POST['account_number'] ?? '');
            $accountName = $this->sanitizeInput($_POST['account_name'] ?? '');
            
            if (empty($bankName) || empty($accountNumber) || empty($accountName)) {
                $_SESSION['error'] = 'Please provide all bank transfer details.';
                $this->redirect('/agent/payouts');
                return;
            }
            $paymentDetails = "Bank: {$bankName}, Account: {$accountNumber}, Name: {$accountName}";
        } elseif ($paymentMethod === 'cash') {
            $paymentDetails = 'Cash pickup at office';
        }

        if ($amount <= 0) {
            $_SESSION['error'] = 'Please enter a valid payout amount.';
            $this->redirect('/agent/payouts');
            return;
        }

        // Get available balance
        $availableBalance = $this->payoutRequestModel->getAvailableBalance($agent['id']);

        if ($amount > $availableBalance) {
            $_SESSION['error'] = 'Requested amount exceeds your available balance. Available: KES ' . number_format($availableBalance, 2);
            $this->redirect('/agent/payouts');
            return;
        }

        // Create payout request
        $payoutData = [
            'agent_id' => $agent['id'],
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_details' => $paymentDetails,
            'notes' => $payoutNotes
        ];
        
        $payoutRequestId = $this->payoutRequestModel->createPayoutRequest($payoutData);

        if (!$payoutRequestId) {
            $_SESSION['error'] = 'Failed to create payout request. Please try again.';
            $this->redirect('/agent/payouts');
            return;
        }

        // Send notification to admins
        $notification = new InAppNotificationService();
        $notification->notifyAdmins([
            'subject' => 'New Payout Request from Agent',
            'message' => "Agent {$agent['first_name']} {$agent['last_name']} (ID: {$agent['id']}) has requested a payout of KES " . number_format($amount, 2) . " via {$paymentMethod}. {$paymentDetails}. Notes: " . ($payoutNotes ?: 'None'),
            'action_url' => '/admin/payouts',
            'action_text' => 'Process Payout'
        ], $_SESSION['user_id'] ?? null);

        $_SESSION['success'] = 'Payout request of KES ' . number_format($amount, 2) . ' submitted successfully! Status: Requested. You will be notified once processed.';
        $this->redirect('/agent/payouts');
    }



    private function getAgentNotifications($userId)
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT cr.id AS notification_id,
                   cr.status,
                   cr.read_at,
                   cr.sent_at,
                   c.subject,
                   c.message,
                   c.action_url,
                   c.action_text,
                   c.type,
                   c.created_at
            FROM communication_recipients cr
            INNER JOIN communications c ON c.id = cr.communication_id
            WHERE cr.user_id = :user_id
            ORDER BY COALESCE(cr.sent_at, c.sent_at, c.created_at) DESC
            LIMIT 100
        ');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notifications = [];
        foreach ($rows as $row) {
            $title = $row['subject'] ?: 'Notification';
            $message = $row['message'] ?: '';
            $category = $this->inferNotificationCategory($title, $message);
            $actionUrl = !empty($row['action_url']) ? $row['action_url'] : $category['action_url'];
            $actionText = !empty($row['action_text']) ? $row['action_text'] : $category['action_text'];

            $notifications[] = [
                'id' => (int)$row['notification_id'],
                'type' => $category['type'],
                'icon' => $category['icon'],
                'color' => $category['color'],
                'title' => $title,
                'message' => $message,
                'time' => $this->formatTimeAgo($row['sent_at'] ?? $row['created_at'] ?? 'now'),
                'read' => !empty($row['read_at']) || ($row['status'] ?? '') === 'read',
                'action_url' => $actionUrl,
                'action_text' => $actionText
            ];
        }

        return $notifications;
    }

    private function inferNotificationCategory($title, $message)
    {
        $haystack = strtolower($title . ' ' . $message);

        if (preg_match('/payout|commission|cashout/', $haystack)) {
            return [
                'type' => 'commission',
                'icon' => 'fa-money-bill-wave',
                'color' => '#10B981',
                'action_url' => '/agent/payouts',
                'action_text' => 'View Payouts'
            ];
        }

        if (preg_match('/payment|mpesa|contribution|invoice|receipt/', $haystack)) {
            return [
                'type' => 'payment',
                'icon' => 'fa-credit-card',
                'color' => '#8B5CF6',
                'action_url' => '/agent/members',
                'action_text' => 'View Members'
            ];
        }

        if (preg_match('/claim|burial|mortuary/', $haystack)) {
            return [
                'type' => 'claims',
                'icon' => 'fa-file-medical',
                'color' => '#3B82F6',
                'action_url' => '/agent/claims',
                'action_text' => 'View Claims'
            ];
        }

        if (preg_match('/member|registration/', $haystack)) {
            return [
                'type' => 'registration',
                'icon' => 'fa-user-plus',
                'color' => '#6366F1',
                'action_url' => '/agent/members',
                'action_text' => 'View Members'
            ];
        }

        return [
            'type' => 'alert',
            'icon' => 'fa-bell',
            'color' => '#F59E0B',
            'action_url' => '/agent/notifications',
            'action_text' => 'View Notifications'
        ];
    }

    private function formatTimeAgo($datetime)
    {
        try {
            $date = new DateTime($datetime);
            $now = new DateTime();
            $diff = $now->diff($date);

            if ($diff->y > 0) {
                return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
            }
            if ($diff->m > 0) {
                return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
            }
            if ($diff->d > 0) {
                return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
            }
            if ($diff->h > 0) {
                return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            }
            if ($diff->i > 0) {
                return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            }

            return 'Just now';
        } catch (Exception $e) {
            return 'Just now';
        }
    }
}
