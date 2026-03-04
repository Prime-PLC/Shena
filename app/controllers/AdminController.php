<?php
/**
 * Admin Controller - Handles administrative functions
 */
class AdminController extends BaseController 
{
    private $memberModel;
    private $paymentModel;
    private $claimModel;
    private $userModel;
    private $agentModel;
    private $payoutRequestModel;

    public function __construct()
    {
        parent::__construct();
        $this->memberModel = new Member();
        $this->paymentModel = new Payment();
        $this->claimModel = new Claim();
        $this->userModel = new User();
        $this->agentModel = new Agent();
        $this->payoutRequestModel = new PayoutRequest();
    }


    private function requireAdminAccess()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || 
            !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            header('Location: /admin-login');
            exit();
        }
    }

    /**
     * Admin Login Page
     */
    public function showLogin()
    {
        // Check if already logged in as admin
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && 
            in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            header('Location: /admin/dashboard');
            exit();
        }
        
        $data = [
            'title' => 'Admin Login - ' . APP_NAME,
            'error' => $_SESSION['error'] ?? null
        ];
        
        unset($_SESSION['error']);
        $this->view('admin.login', $data);
    }

    /**
     * Admin Login Process
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Please fill in all fields.';
                header('Location: /admin/login');
                exit();
            }

            // Check admin credentials in users table
            $admin = $this->userModel->findByEmail($username);
            
            if (!$admin) {
                // Also try to find by first_name as username for admin
                $db = Database::getInstance();
                $query = "SELECT * FROM users WHERE first_name = :first_name AND role IN ('super_admin', 'manager') LIMIT 1";
                $admin = $db->fetch($query, ['first_name' => $username]);
            }
            
            if ($admin && in_array($admin['role'], ['super_admin', 'manager']) && 
                password_verify($password, $admin['password'])) {
                
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_role'] = $admin['role'];
                $_SESSION['user_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['user_email'] = $admin['email'];
                
                // Update last login
                $db = Database::getInstance();
                $db->execute("UPDATE users SET last_login = NOW() WHERE id = :id", ['id' => $admin['id']]);
                
                header('Location: /admin/dashboard');
                exit();
            } else {
                $_SESSION['error'] = 'Invalid admin credentials';
            }
        }
        
        header('Location: /admin-login');
        exit();
    }

    /**
     * Admin Logout
     */
    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        $_SESSION['success'] = 'You have been logged out successfully.';
        header('Location: /');
        exit();
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $this->requireAdminAccess();
        
        // Gather dashboard statistics
        $data = [
            'title' => 'Admin Dashboard',
            'stats' => [
                'total_members' => $this->memberModel->getTotalMembers(),
                'active_members' => $this->memberModel->getActiveMembers(),
                'pending_members' => $this->memberModel->getPendingMembersCount(),
                'total_payments' => $this->paymentModel->getTotalPayments(),
                'monthly_revenue' => $this->paymentModel->getMonthlyRevenue(),
                'pending_claims' => $this->claimModel->getPendingClaimsCount(),
                'approved_claims' => $this->claimModel->getApprovedClaimsCount(),
                'total_commissions' => $this->agentModel->getTotalCommissions(),
                'active_agents' => $this->agentModel->getActiveAgentsCount(),
                'member_growth' => $this->memberModel->getMemberGrowth(),
                'contribution_count' => $this->paymentModel->getContributionCount()
            ],
            'recent_members' => $this->memberModel->getRecentMembers(5),
            'recent_payments' => $this->paymentModel->getRecentPayments(5),
            'recent_claims' => $this->claimModel->getRecentClaims(5),
            'recent_activities' => $this->getRecentActivities(5),
            'alerts' => $this->getDashboardAlerts()
        ];
        
        $this->view('admin.dashboard', $data);
    }

    /**
     * Member Management
     */
    public function members()
    {
        $this->requireAdminAccess();
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $package = $_GET['package'] ?? 'all';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50; // Show 50 members per page for better performance
        
        $members = $this->memberModel->getAllMembersWithDetails($search, $status, $package);
        
        // Calculate statistics
        $totalMembers = $this->memberModel->getTotalMembers();
        $activeMembers = $this->memberModel->getActiveMembers();
        $gracePeriodMembers = 0;
        $defaultRate = 0;
        
        // Count grace period members
        foreach ($members as $member) {
            if ($member['status'] === 'grace_period') {
                $gracePeriodMembers++;
            }
        }
        
        // Calculate default rate
        if ($totalMembers > 0) {
            $inactiveMembers = $this->memberModel->getInactiveMembers();
            $defaultRate = ($inactiveMembers / $totalMembers) * 100;
        }
        
        // Paginate members
        $totalItems = count($members);
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($page - 1) * $perPage;
        $members = array_slice($members, $offset, $perPage);
        
        // Get pending approvals (members with pending status)
        $pendingMembers = $this->memberModel->getPendingMembers();
        $pending_approvals = [];
        
        // Ensure $pendingMembers is an array before iterating
        if (is_array($pendingMembers)) {
            foreach ($pendingMembers as $pending) {
                $pending_approvals[] = [
                    'id' => $pending['id'],
                    'name' => $pending['first_name'] . ' ' . $pending['last_name'],
                    'package' => $pending['package'] ?? 'Standard',
                    'tag' => 'AWAITING ACTIVATION',
                    'tag_class' => 'awaiting',
                    'code' => $pending['transaction_id'] ?? '',
                    'action_text' => !empty($pending['transaction_id']) ? 'Verify & Activate' : 'Validate Code'
                ];
            }
        }
        
        // Get most recent pending claim for emergency alert
        $recentClaims = $this->claimModel->getPendingClaims();
        $recent_claim = null;
        
        if (!empty($recentClaims)) {
            // Get the first pending claim
            $claim = $recentClaims[0];
            $recent_claim = [
                'id' => $claim['id'],
                'member_name' => $claim['first_name'] . ' ' . $claim['last_name'],
                'member_number' => $claim['member_number'] ?? 'N/A',
                'deceased_name' => $claim['deceased_name'] ?? 'N/A',
                'date_of_death' => $claim['date_of_death'] ?? null
            ];
        }
        
        $data = [
            'title' => 'Members - Admin',
            'members' => $members,
            'total_members' => count($members),
            'stats' => [
                'total_members' => $totalMembers,
                'grace_period' => $gracePeriodMembers,
                'default_rate' => $defaultRate
            ],
            'pending_approvals' => $pending_approvals,
            'recent_claim' => $recent_claim,
            'search' => $search,
            'status' => $status,
            'package' => $package,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
                'total_items' => $totalItems
            ]
        ];
        
        $this->view('admin.members', $data);
    }

    /**
     * Export Members to CSV
     */
    public function exportMembersCSV()
    {
        $this->requireAdminAccess();
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $package = $_GET['package'] ?? 'all';
        
        $members = $this->memberModel->getAllMembersWithDetails($search, $status, $package);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="members_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'Member Number',
            'First Name',
            'Last Name',
            'National ID',
            'Email',
            'Phone',
            'Package',
            'Status',
            'Registration Date',
            'Last Payment Date',
            'Last Payment Amount'
        ], ',', '"', '\\', '');

        // Add member data
        foreach ($members as $member) {
            fputcsv($output, [
                $member['member_number'] ?? '',
                $member['first_name'] ?? '',
                $member['last_name'] ?? '',
                $member['national_id'] ?? $member['id_number'] ?? '',
                $member['email'] ?? '',
                $member['phone'] ?? '',
                $member['package'] ?? 'Standard',
                ucfirst($member['status'] ?? 'active'),
                $member['registration_date'] ?? $member['created_at'] ?? '',
                $member['last_payment_date'] ?? '',
                $member['last_payment_amount'] ?? ''
            ], ',', '"', '\\', '');
        }
        
        fclose($output);
        exit;
    }

    /**
     * Register New Member
     */
    public function registerMember()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Show registration form
            $data = [
                'title' => 'Register New Member - Admin'
            ];
            
            $this->view('admin.register-member', $data);
            return;
        }
        
        // Handle POST - process registration
        try {
            $this->validateCsrf();

            // Collect and sanitize inputs
            $firstName = $this->sanitizeInput($_POST['first_name'] ?? '');
            $lastName = $this->sanitizeInput($_POST['last_name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phoneRaw = $this->sanitizeInput($_POST['phone'] ?? '');
            $idNumber = $this->sanitizeInput($_POST['id_number'] ?? '');
            $dateOfBirth = $this->sanitizeInput($_POST['date_of_birth'] ?? '');
            $gender = $this->sanitizeInput($_POST['gender'] ?? 'male');
            $address = $this->sanitizeInput($_POST['address'] ?? '');
            $packageKey = $this->sanitizeInput($_POST['package'] ?? 'individual_below_70');
            $password = $_POST['password'] ?? null;

            // Basic validation
            if (empty($firstName) || empty($lastName) || empty($phoneRaw) || empty($idNumber) || empty($dateOfBirth)) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                $this->redirect('/admin/members/register');
                return;
            }

            // Normalize phone
            $phone = formatKenyanPhone($phoneRaw);
            if (!$phone) {
                $_SESSION['error'] = 'Invalid phone number format.';
                $this->redirect('/admin/members/register');
                return;
            }

            // Check uniqueness: phone and ID
            $exists = $this->userModel->findByPhone($phone);
            if ($exists) {
                $_SESSION['error'] = 'Phone number already registered.';
                $this->redirect('/admin/members/register');
                return;
            }

            if (!empty($email) && $this->userModel->findByEmail($email)) {
                $_SESSION['error'] = 'Email already registered.';
                $this->redirect('/admin/members/register');
                return;
            }

            if (!empty($idNumber)) {
                $existing = $this->memberModel->findByNationalId($idNumber);
                if ($existing) {
                    $_SESSION['error'] = 'National ID already registered.';
                    $this->redirect('/admin/members/register');
                    return;
                }
            }

            // Prepare password
            if (empty($password)) {
                $temp = bin2hex(random_bytes(6));
                $passwordHash = password_hash($temp, PASSWORD_DEFAULT);
            } else {
                if (strlen($password) < 8) {
                    $_SESSION['error'] = 'Password must be at least 8 characters.';
                    $this->redirect('/admin/members/register');
                    return;
                }
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            }

            // Start DB transaction
            $this->db->getConnection()->beginTransaction();

            // Create user
            $userId = $this->userModel->createUser([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email ?: null,
                'phone' => $phone,
                'password' => $passwordHash,
                'role' => 'member',
                'status' => 'pending'
            ]);

            // Generate member number using canonical formatter
            $memberNumber = MemberNumberHelper::generateCanonical();

            global $membership_packages;
            if (empty($membership_packages[$packageKey])) {
                $_SESSION['error'] = 'Invalid package selected.';
                $this->db->getConnection()->rollBack();
                $this->redirect('/admin/members/register');
                return;
            }
            $normalizedPackage = $this->memberModel->normalizePackageTier($packageKey, $membership_packages[$packageKey]);

            // Calculate monthly contribution centrally
            $memberForCalc = ['date_of_birth' => $dateOfBirth, 'package' => $packageKey];
            $monthlyContribution = $this->memberModel->calculateMonthlyContribution($memberForCalc, []);

            // Maturity ends default
            $maturityMonths = defined('MATURITY_PERIOD_UNDER_80') ? MATURITY_PERIOD_UNDER_80 : 4;
            $maturityEnds = date('Y-m-d', strtotime("+{$maturityMonths} months"));

            // Create member record
            $this->memberModel->create([
                'user_id' => $userId,
                'member_number' => $memberNumber,
                'id_number' => $idNumber,
                'date_of_birth' => $dateOfBirth,
                'gender' => $gender,
                'address' => $address,
                'package' => $normalizedPackage,
                'monthly_contribution' => $monthlyContribution,
                'maturity_ends' => $maturityEnds,
                'status' => 'inactive',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->getConnection()->commit();

            // Optionally notify user (email optional)
            try {
                $emailSvc = new EmailService();
                if (!empty($email)) {
                    $emailSvc->sendRegistrationConfirmation($email, [
                        'name' => $firstName . ' ' . $lastName,
                        'member_number' => $memberNumber,
                        'monthly_contribution' => $monthlyContribution
                    ]);
                }
            } catch (Exception $e) {
                error_log('Admin register email failed: ' . $e->getMessage());
            }

            $_SESSION['success'] = 'Member registered successfully.';
            $this->redirect('/admin/members');

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log('Admin register error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to register member: ' . $e->getMessage();
            $this->redirect('/admin/members/register');
        }
    }

    /**
     * Activate Member
     */
    public function activateMember($id = null)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $memberId = $id ?? ($_POST['member_id'] ?? 0);
            
            if ($memberId) {
                $member = $this->memberModel->find($memberId);
                
                if (!$member) {
                    $_SESSION['error'] = 'Member not found.';
                    $this->redirect('/admin/members');
                    return;
                }
                
                // Verify registration fee payment (KES 200) per policy Section 5
                $paymentModel = new Payment();
                $registrationFeeRequired = defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200;
                
                // Check if registration fee has been paid
                $registrationPayments = $paymentModel->findAll([
                    'member_id' => $memberId,
                    'payment_type' => 'registration',
                    'status' => 'completed'
                ]);
                
                $totalRegistrationPaid = 0;
                foreach ($registrationPayments as $payment) {
                    $totalRegistrationPaid += $payment['amount'];
                }
                
                if ($totalRegistrationPaid < $registrationFeeRequired) {
                    $outstanding = $registrationFeeRequired - $totalRegistrationPaid;
                    $_SESSION['error'] = "Cannot activate member. Registration fee of KES " . number_format($registrationFeeRequired) . " not paid. Outstanding: KES " . number_format($outstanding);
                    $this->redirect('/admin/members');
                    return;
                }
                
                // Update member status
                $this->memberModel->update($memberId, [
                    'status' => 'active',
                    'coverage_ends' => date('Y-m-d', strtotime('+1 year'))
                ]);
                
                // Update user status
                if ($member) {
                    $this->userModel->update($member['user_id'], ['status' => 'active']);
                }
                
                $_SESSION['success'] = 'Member activated successfully!';
            } else {
                $_SESSION['error'] = 'Invalid member ID.';
            }
        }
        
        $this->redirect('/admin/members');
    }

    /**
     * Deactivate Member
     */
    public function deactivateMember($id = null)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $memberId = $id ?? ($_POST['member_id'] ?? 0);
            
            if ($memberId) {
                // Update member status
                $this->memberModel->update($memberId, ['status' => 'inactive']);
                
                // Update user status
                $member = $this->memberModel->find($memberId);
                if ($member) {
                    $this->userModel->update($member['user_id'], ['status' => 'inactive']);
                }
                
                $_SESSION['success'] = 'Member deactivated successfully!';
            } else {
                $_SESSION['error'] = 'Invalid member ID.';
            }
        }
        
        $this->redirect('/admin/members');
    }

    /**
     * View Individual Member Details
     */
    public function viewMember($id)
    {
        $this->requireAdminAccess();
        
        // Get member details (include user email/phone)
        $member = $this->memberModel->getMemberById($id);
        
        if (!$member) {
            $_SESSION['error'] = 'Member not found.';
            $this->redirect('/admin/members');
            return;
        }
        
        // Get member statistics
        $payments = $this->paymentModel->getMemberPayments($id);
        $totalContributions = array_sum(array_column($payments, 'amount'));
        $lastPayment = !empty($payments) ? $payments[0] : null;
        
        // Calculate membership duration
        $membershipMonths = 0;
        if (!empty($member['registration_date'])) {
            $registrationDate = new DateTime($member['registration_date']);
            $now = new DateTime();
            $membershipMonths = $registrationDate->diff($now)->m + ($registrationDate->diff($now)->y * 12);
        } elseif (!empty($member['created_at'])) {
            $registrationDate = new DateTime($member['created_at']);
            $now = new DateTime();
            $membershipMonths = $registrationDate->diff($now)->m + ($registrationDate->diff($now)->y * 12);
        }
        
        // Get beneficiaries
        $beneficiaries = $this->memberModel->getMemberDependents($id);

        // Enrich member with agent contact details for display (agent number/phone/email)
        if (!empty($member['agent_id'])) {
            $agent = $this->agentModel->getAgentById($member['agent_id']);
            if ($agent) {
                $member['agent_number'] = $agent['agent_number'] ?? ($member['agent_number'] ?? null);
                $member['agent_phone'] = $agent['phone'] ?? null;
                // Agent email may be stored on agents.email or users.email (user_email alias)
                $member['agent_email'] = $agent['email'] ?? $agent['user_email'] ?? null;
            }
        }
        
        $data = [
            'title' => 'Member Details - Admin',
            'member' => $member,
            'payments' => $payments,
            'beneficiaries' => $beneficiaries,
            'stats' => [
                'total_contributions' => $totalContributions,
                'last_payment_date' => $lastPayment ? $lastPayment['payment_date'] : null,
                'membership_months' => $membershipMonths
            ]
        ];
        
        $this->view('admin.member-details', $data);
    }

    /**
     * Edit Member Page
     */
    public function editMember($id)
    {
        $this->requireAdminAccess();
        
        $member = $this->memberModel->find($id);
        
        if (!$member) {
            $_SESSION['error'] = 'Member not found.';
            $this->redirect('/admin/members');
            return;
        }
        
        $data = [
            'title' => 'Edit Member - Admin',
            'member' => $member
        ];
        
        $this->view('admin.member-edit', $data);
    }

    /**
     * Update Member
     */
    public function updateMember($id)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/members/edit/' . $id);
            return;
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'id_number' => $_POST['id_number'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'county' => $_POST['county'] ?? '',
            'sub_county' => $_POST['sub_county'] ?? '',
            'address' => $_POST['address'] ?? '',
            'package' => $_POST['package'] ?? 'basic',
            'status' => $_POST['status'] ?? 'active',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'nok_name' => $_POST['nok_name'] ?? '',
            'nok_relationship' => $_POST['nok_relationship'] ?? '',
            'nok_phone' => $_POST['nok_phone'] ?? '',
            'nok_id_number' => $_POST['nok_id_number'] ?? ''
        ];
        
        if ($this->memberModel->update($id, $data)) {
            $_SESSION['success_message'] = 'Member updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to update member.';
        }
        
        $this->redirect('/admin/members/view/' . $id);
    }

    /**
     * Suspend Member
     */
    public function suspendMember($id)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->memberModel->update($id, ['status' => 'suspended']);
            $_SESSION['success_message'] = 'Member suspended successfully!';
        }
        
        $this->redirect('/admin/members/view/' . $id);
    }

    /**
     * Payment Management
     */
    public function payments()
    {
        $this->requireAdminAccess();
        
        $status = $_GET['status'] ?? 'all';
        
        $conditions = [];
        if ($status !== 'all') {
            $conditions['status'] = $status;
        }
        
        $data = [
            'title' => 'Payments - Admin',
            'payments' => $this->paymentModel->getAllPaymentsWithDetails($conditions),
            'status' => $status
        ];
        
        $this->view('admin.payments', $data);
    }

    /**
     * Claims Management
     */
    public function claims()
    {
        $this->requireAdminAccess();
        
        $status = $_GET['status'] ?? 'all';
        
        $conditions = [];
        if ($status !== 'all') {
            $conditions['status'] = $status;
        }
        
        // Get all claims
        $allClaims = $this->claimModel->getAllClaimsWithDetails($conditions);
        
        // Check for cash alternative requests
        $cashAlternativeRequests = [];
        foreach ($allClaims as $claim) {
            if (!empty($claim['cash_alternative_reason']) && $claim['status'] === 'submitted') {
                $cashAlternativeRequests[] = $claim;
            }
        }
        
        // Calculate statistics and format data
        $pendingClaims = 0;
        $approvedClaims = 0;
        $rejectedClaims = 0;
        $totalClaimAmount = 0;
        $pending_claims = [];
        $completed_claims = [];
        
        // Format claims data for the view
        foreach ($allClaims as &$claim) {
            // Generate claim number if not exists
            $claim['claim_number'] = 'CLM-' . date('Y') . '-' . str_pad($claim['id'], 4, '0', STR_PAD_LEFT);
            
            // Format member name
            $claim['member_name'] = $claim['first_name'] . ' ' . $claim['last_name'];
            
            // Use claim_amount as amount
            $claim['amount'] = $claim['claim_amount'] ?? 0;
            
            // Format submitted date
            $claim['submitted_date'] = $claim['created_at'];
            
            // Get package/plan info
            $claim['plan'] = $claim['settlement_type'] ?? 'services';
            
            // Calculate totals
            $totalClaimAmount += $claim['amount'];
            
            // Categorize claims
            if ($claim['status'] === 'submitted' || $claim['status'] === 'under_review') {
                $pendingClaims++;
                $pending_claims[] = $claim;
            } elseif ($claim['status'] === 'approved' || $claim['status'] === 'paid') {
                $approvedClaims++;
                $claim['amount_paid'] = $claim['approved_amount'] ?? $claim['amount'];
                $claim['completed_date'] = $claim['approved_at'] ?? $claim['processed_at'];
                $completed_claims[] = $claim;
            } elseif ($claim['status'] === 'rejected') {
                $rejectedClaims++;
            }
        }
        
        $data = [
            'title' => 'Claims - Admin',
            'claims' => $allClaims,
            'all_claims' => $allClaims,
            'pending_claims' => $pending_claims,
            'completed_claims' => $completed_claims,
            'cash_alternative_requests' => $cashAlternativeRequests,
            'pendingClaims' => $pendingClaims,
            'approvedClaims' => $approvedClaims,
            'rejectedClaims' => $rejectedClaims,
            'totalClaimAmount' => $totalClaimAmount,
            'status' => $status
        ];
        
        $this->view('admin.claims', $data);
    }

    /**
     * View Claim Details
     */
    public function viewClaim($id)
    {
        $this->requireAdminAccess();

        $claimId = (int)$id;
        if ($claimId <= 0) {
            $_SESSION['error'] = 'Invalid claim ID.';
            $this->redirect('/admin/claims');
            return;
        }

        $claim = $this->claimModel->getClaimDetails($claimId);
        if (!$claim) {
            $_SESSION['error'] = 'Claim not found.';
            $this->redirect('/admin/claims');
            return;
        }

        $documentModel = new ClaimDocument();
        $documents = $documentModel->getClaimDocuments($claimId);
        $documentStatus = $documentModel->checkClaimDocumentCompleteness($claimId);
        $requiredDocuments = $documentModel->getRequiredDocuments();

        $data = [
            'title' => 'Claim Details - ' . $claimId,
            'claim' => $claim,
            'documents' => $documents,
            'document_status' => $documentStatus,
            'required_documents' => $requiredDocuments
        ];

        $this->view('admin.claim-view', $data);
    }
    
    /**
     * View Completed Claims
     */
    public function viewCompletedClaims()
    {
        $this->requireAdminAccess();
        
        $data = [
            'title' => 'Completed Claims - Admin',
            'claims' => $this->claimModel->getAllClaimsWithDetails(['status' => 'completed'])
        ];
        
        $this->view('admin.claims-completed', $data);
    }

    /**
     * Approve Claim for Standard Services (Default)
     */
    public function approveClaim()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $claimId = $_POST['claim_id'] ?? 0;
            $deliveryDate = $_POST['services_delivery_date'] ?? date('Y-m-d');
            $notes = $_POST['notes'] ?? '';
            
            if ($claimId) {
                try {
                    $claim = $this->claimModel->find($claimId);
                    if (!$claim) {
                        throw new Exception('Claim not found.');
                    }

                    $member = $this->memberModel->find($claim['member_id']);
                    if (!$member) {
                        throw new Exception('Associated member not found.');
                    }

                    // Validate claim eligibility per policy Section 9
                    if ($member['status'] === 'defaulted') {
                        throw new Exception('Cannot approve claim. Member is in default status.');
                    }
                    
                    if ($member['status'] !== 'active') {
                        throw new Exception('Cannot approve claim. Member must be active.');
                    }
                    
                    // Check maturity period completion
                    if (!empty($member['maturity_ends'])) {
                        $maturityDate = new DateTime($member['maturity_ends']);
                        $today = new DateTime();
                        
                        if ($today < $maturityDate) {
                            throw new Exception('Cannot approve claim. Maturity period not completed.');
                        }
                    }
                    
                    // Check required documents per policy Section 8
                    $claimDocumentModel = new ClaimDocument();
                    $documents = $claimDocumentModel->getClaimDocuments($claimId);
                    $requiredDocs = ['id_copy', 'chief_letter', 'mortuary_invoice'];
                    $uploadedTypes = array_column($documents, 'document_type');
                    
                    foreach ($requiredDocs as $docType) {
                        if (!in_array($docType, $uploadedTypes)) {
                            throw new Exception("Required document missing: {$docType}");
                        }
                    }

                    // Approve for standard service delivery
                    $this->claimModel->approveClaimForServices($claimId, $deliveryDate, $notes);
                    
                    // Send notification to member
                    if (class_exists('EmailService')) {
                        try {
                            $emailService = new EmailService();
                            $emailService->sendClaimApprovalEmail($member, $claim);
                        } catch (Exception $e) {
                            error_log('Email notification failed: ' . $e->getMessage());
                        }
                    }
                    
                    $message = 'Claim approved for standard service delivery. Proceed to arrange services.';
                    
                    // Check if AJAX request
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $message]);
                        exit();
                    }
                    
                    $_SESSION['success'] = $message;
                } catch (Exception $e) {
                    error_log('Claim approval error: ' . $e->getMessage());
                    $errorMessage = 'Failed to approve claim: ' . $e->getMessage();
                    
                    // Check if AJAX request
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $errorMessage]);
                        exit();
                    }
                    
                    $_SESSION['error'] = $errorMessage;
                }
            }
        }
        
        $this->redirect('/admin/claims');
    }
    
    /**
     * Approve Claim for Cash Alternative (KSH 20,000)
     * Per Policy Section 12: Only in exceptional circumstances
     */
    public function approveClaimCashAlternative()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $claimId = $_POST['claim_id'] ?? 0;
            $reason = $_POST['cash_alternative_reason'] ?? '';
            $requestedBy = $_POST['requested_by'] ?? 'company';
            
            if ($claimId) {
                try {
                    $claim = $this->claimModel->find($claimId);
                    if (!$claim) {
                        throw new Exception('Claim not found.');
                    }
                    
                    if (strlen($reason) < 20) {
                        throw new Exception('Detailed reason required (minimum 20 characters).');
                    }
                    
                    // Approve for cash alternative
                    $this->claimModel->approveClaimForCashAlternative(
                        $claimId,
                        $reason,
                        $requestedBy,
                        $_SESSION['user_id']
                    );
                    
                    $message = 'Claim approved for KSH 20,000 cash alternative. Agreement must be signed before payment.';
                    
                    // Check if AJAX request
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $message]);
                        exit();
                    }
                    
                    $_SESSION['success'] = $message;
                    
                } catch (Exception $e) {
                    error_log('Cash alternative approval error: ' . $e->getMessage());
                    $errorMessage = 'Failed to approve cash alternative: ' . $e->getMessage();
                    
                    // Check if AJAX request
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $errorMessage]);
                        exit();
                    }
                    
                    $_SESSION['error'] = $errorMessage;
                }
            }
        }
        
        $this->redirect('/admin/claims');
    }
    
    /**
     * Track Service Delivery for Approved Claims
     */
    public function trackServiceDelivery($claimId = null)
    {
        $this->requireAdminAccess();
        
        if (!$claimId && isset($_GET['claim_id'])) {
            $claimId = (int)$_GET['claim_id'];
        }
        
        if (!$claimId) {
            $_SESSION['error'] = 'Invalid claim ID.';
            $this->redirect('/admin/claims');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update service completion status
            $serviceType = $_POST['service_type'] ?? '';
            $completed = isset($_POST['completed']) ? (bool)$_POST['completed'] : true;
            $serviceNotes = $_POST['service_notes'] ?? '';
            
            try {
                $checklistModel = new ClaimServiceChecklist();
                $checklistModel->markServiceCompleted($claimId, $serviceType, $_SESSION['user_id'], $serviceNotes);

                // Update main claim table
                $fieldMap = [
                    'mortuary_bill' => 'mortuary_bill_settled',
                    'body_dressing' => 'body_dressing_completed',
                    'coffin' => 'coffin_delivered',
                    'transportation' => 'transportation_arranged',
                    'equipment' => 'equipment_delivered'
                ];

                if (isset($fieldMap[$serviceType])) {
                    $this->claimModel->updateServiceDeliveryStatus($claimId, $fieldMap[$serviceType], $completed);
                }

                $_SESSION['success'] = 'Service delivery status updated.';

                // Send notification to member (email + SMS) about checklist update
                try {
                    $claimDetails = $this->claimModel->getClaimDetails($claimId);

                    $memberEmail = $claimDetails['email'] ?? null;
                    $memberPhone = $claimDetails['phone'] ?? null;
                    $memberName = trim(($claimDetails['first_name'] ?? '') . ' ' . ($claimDetails['last_name'] ?? '')) ?: ($claimDetails['member_name'] ?? 'Member');

                    $serviceLabels = [
                        'mortuary_bill' => 'Mortuary bill settlement',
                        'body_dressing' => 'Body dressing',
                        'coffin' => 'Coffin delivery',
                        'transportation' => 'Transportation arrangement',
                        'equipment' => 'Equipment delivery'
                    ];

                    $serviceLabel = $serviceLabels[$serviceType] ?? $serviceType;

                    $adminName = $_SESSION['user_name'] ?? 'Admin';
                    $claimRef = 'CLM-' . date('Y') . '-' . str_pad($claimId, 4, '0', STR_PAD_LEFT);

                    $emailData = [
                        'status' => 'service completed',
                        'name' => $memberName,
                        'claim_id' => $claimId,
                        'notes' => "{$serviceLabel} completed by {$adminName}. {$serviceNotes}",
                    ];

                    if ($memberEmail && class_exists('EmailService')) {
                        try {
                            $emailService = new EmailService();
                            $emailService->sendClaimStatusUpdateEmail($memberEmail, $emailData);
                        } catch (Exception $e) {
                            error_log('Failed sending claim checklist email: ' . $e->getMessage());
                        }
                    }

                    if ($memberPhone && class_exists('SmsService')) {
                        try {
                            $smsService = new SmsService();
                            $smsMessage = "Claim Update: {$serviceLabel} completed for {$claimRef}. Notes: {$serviceNotes}. - Shena Companion";
                            $smsService->sendCustomMessage($memberPhone, $smsMessage, $claimDetails['member_number'] ?? null);
                        } catch (Exception $e) {
                            error_log('Failed sending claim checklist SMS: ' . $e->getMessage());
                        }
                    }

                    // Create in-app notification for the member
                    try {
                        $memberId = $claimDetails['member_id'] ?? null;
                        $userId = null;

                        if ($memberId && isset($this->memberModel)) {
                            $member = $this->memberModel->find($memberId);
                            if ($member && isset($member['user_id'])) {
                                $userId = (int)$member['user_id'];
                            }
                        }

                        if ($userId && class_exists('InAppNotificationService')) {
                            $inApp = new InAppNotificationService();
                            $payload = [
                                'subject' => "{$serviceLabel} completed",
                                'message' => "{$serviceLabel} has been completed for claim {$claimRef}. {$serviceNotes}",
                                'action_url' => '/claims/view/' . $claimId,
                                'action_text' => 'View claim'
                            ];

                            try {
                                $inApp->notifyUser($userId, $payload, $_SESSION['user_id'] ?? null);
                            } catch (Exception $e) {
                                error_log('Failed to create in-app notification: ' . $e->getMessage());
                            }
                        }
                    } catch (Exception $e) {
                        error_log('In-app notification error: ' . $e->getMessage());
                    }
                } catch (Exception $e) {
                    error_log('Notification error after checklist update: ' . $e->getMessage());
                }

            } catch (Exception $e) {
                error_log('Service tracking error: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to update service status: ' . $e->getMessage();
            }
            
            $this->redirect('/admin/claims/track/' . $claimId);
            return;
        }
        
        // Load claim and service checklist
        $claim = $this->claimModel->getClaimDetails($claimId);
        if (!$claim) {
            $_SESSION['error'] = 'Claim not found.';
            $this->redirect('/admin/claims');
            return;
        }
        
        $checklistModel = new ClaimServiceChecklist();
        $checklist = $checklistModel->getClaimChecklist($claimId);
        $completionPercentage = $checklistModel->getCompletionPercentage($claimId);
        
        $data = [
            'title' => 'Track Service Delivery - Claim #' . $claimId,
            'claim' => $claim,
            'checklist' => $checklist,
            'completion_percentage' => $completionPercentage
        ];
        
        $this->view('admin.claims-track-services', $data);
    }
    
    /**
     * Complete Claim After All Services Delivered
     */
    public function completeClaim()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $claimId = $_POST['claim_id'] ?? 0;
            $completionNotes = $_POST['completion_notes'] ?? '';
            
            if ($claimId) {
                try {
                    $this->claimModel->completeClaim($claimId, $completionNotes);
                    $_SESSION['success'] = 'Claim marked as completed successfully.';
                } catch (Exception $e) {
                    error_log('Complete claim error: ' . $e->getMessage());
                    $_SESSION['error'] = 'Failed to complete claim: ' . $e->getMessage();
                }
            }
        }
        
        $this->redirect('/admin/claims');
    }

    /**
     * Reject Claim
     */
    public function rejectClaim()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $claimId = $_POST['claim_id'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            
            if ($claimId && $this->claimModel->rejectClaim($claimId, $reason)) {
                $_SESSION['success'] = 'Claim rejected successfully!';
            } else {
                $_SESSION['error'] = 'Failed to reject claim.';
            }
        }
        
        header('Location: /admin/claims');
        exit();
    }

    /**
     * Reports & Analytics
     */
    public function reports()
    {
        $this->requireAdminAccess();
        
        $reportType = $_GET['report_type'] ?? 'overview';
        $startDate = $_GET['date_from'] ?? date('Y-m-01');
        $endDate = $_GET['date_to'] ?? date('Y-m-d');
        
        $data = [
            'title' => 'Reports - Admin',
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalMembers' => $this->memberModel->getTotalMembers(),
            'activeMembers' => $this->memberModel->getActiveMembers(),
            'inactiveMembers' => $this->memberModel->getInactiveMembers(),
            'pendingMembers' => $this->memberModel->getPendingMembersCount(),
            'totalRevenue' => $this->paymentModel->getTotalRevenue($startDate, $endDate),
            'monthlyRevenue' => $this->paymentModel->getMonthlyRevenue(),
            'totalClaimsPaid' => $this->claimModel->getTotalClaimsValue(),
            'newMembersThisMonth' => count($this->memberModel->getNewRegistrations(date('Y-m-01'), date('Y-m-d'))),
            'renewalDue' => count($this->paymentModel->getMembersWithOverduePayments()),
            'pendingPayments' => count($this->paymentModel->getPendingPayments()),
            'failedPayments' => count($this->paymentModel->getFailedPayments())
        ];
        
        // Add specific report data based on type
        if ($reportType === 'members') {
            $data['memberReports'] = $this->memberModel->getNewRegistrations($startDate, $endDate);
        } elseif ($reportType === 'payments') {
            $data['paymentReports'] = $this->paymentModel->getPaymentReport($startDate, $endDate);
        } elseif ($reportType === 'claims') {
            $data['claimReports'] = $this->claimModel->getClaimReport($startDate, $endDate);
        }
        
        $this->view('admin.reports', $data);
    }

    /**
     * Export reports (PDF)
     */
    public function exportReport()
    {
        $this->requireAdminAccess();

        $type = $_GET['type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        set_time_limit(120);

        if ($type === 'payments') {
            $payments = $this->paymentModel->getPaymentReport($dateFrom, $dateTo);
            $html = $this->renderPdfView('admin/reports-payments-pdf', [
                'payments' => $payments,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'generatedAt' => date('Y-m-d H:i')
            ]);
            $this->streamPdf($html, 'monthly-payments-report-' . date('Ymd_His') . '.pdf');
            return;
        }
        if ($type === 'financial') {
            $summary = $this->paymentModel->getPaymentStatistics();
            $methods = $this->paymentModel->getPaymentsByMethod($dateFrom, $dateTo);
            $types = $this->paymentModel->getPaymentsByType($dateFrom, $dateTo);
            $html = $this->renderPdfView('admin/reports-financial-pdf', [
                'summary' => $summary,
                'methods' => $methods,
                'types' => $types,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'generatedAt' => date('Y-m-d H:i')
            ]);
            $this->streamPdf($html, 'financial-report-' . date('Ymd_His') . '.pdf');
            return;
        }
        if ($type === 'member_payments') {
            $memberId = $_GET['member_id'] ?? null;
            if (!$memberId) {
                http_response_code(400);
                echo 'Missing member_id.';
                return;
            }
            $member = $this->memberModel->getMemberById($memberId);
            $payments = $this->paymentModel->getMemberPayments($memberId);
            $html = $this->renderPdfView('admin/reports-member-payments-pdf', [
                'member' => $member,
                'payments' => $payments,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'generatedAt' => date('Y-m-d H:i')
            ]);
            $this->streamPdf($html, 'member-' . ($member['member_number'] ?? 'report') . '-' . date('Ymd_His') . '.pdf');
            return;
        }
        if ($type === 'members') {
            $members = $this->memberModel->getAllMembersWithDetails('', 'all', 'all');
            $html = $this->renderPdfView('admin/reports-members-pdf', [
                'members' => $members,
                'generatedAt' => date('Y-m-d H:i')
            ]);
            $this->streamPdf($html, 'members-report-' . date('Ymd_His') . '.pdf');
            return;
        }
        http_response_code(400);
        echo 'Unsupported report type.';
        return;
    }

    private function renderPdfView($template, $data = [])
    {
        $templatePath = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($templatePath)) {
            throw new Exception("Template {$template} not found");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    private function streamPdf($html, $filename)
    {
        $autoloadPath = ROOT_PATH . '/vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            http_response_code(500);
            echo 'PDF library not installed.';
            return;
        }

        require_once $autoloadPath;

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Communications Center with SMS Campaigns
     */
    public function communications()
    {
        $this->requireAdminAccess();
        
        // Load BulkSmsService for campaign management
        require_once __DIR__ . '/../services/BulkSmsService.php';
        $bulkSmsService = new BulkSmsService();
        
        $type = $_GET['type'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        
        // Get campaigns
        $filters = [
            'status' => $_GET['campaign_status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        $campaigns = $bulkSmsService->getAllCampaigns($filters);
        
        // Get queue items
        $queue_items = $bulkSmsService->getQueueItems(50);
        
        // Get templates
        $templates = $bulkSmsService->getTemplates();
        
        // Get statistics
        $stats = [
            'active_campaigns' => $bulkSmsService->getActiveCampaignCount(),
            'sent_today' => $bulkSmsService->getSentCountToday(),
            'queue_pending' => $bulkSmsService->getQueuePendingCount(),
            'sms_credits' => $bulkSmsService->getSmsCredits()
        ];
        
        $data = [
            'title' => 'Communications - Admin',
            'type' => $type,
            'status' => $status,
            'communications' => $this->getRecentCommunications($type, $status),
            'campaigns' => $campaigns,
            'queue_items' => $queue_items,
            'templates' => $templates,
            'stats' => $stats
        ];
        
        $this->view('admin.sms-campaigns', $data);
    }

    /**
     * System Notifications Page
     */
    public function notifications()
    {
        $this->requireAdminAccess();

        $notifications = $this->getAdminNotifications($_SESSION['user_id'], [
            'category' => $_GET['category'] ?? 'all',
            'type' => $_GET['type'] ?? 'all',
            'date' => $_GET['date'] ?? 'all'
        ]);
        
        $data = [
            'title' => 'System Notifications - Admin',
            'notifications' => $notifications,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('admin.notifications', $data);
    }

    private function getAdminNotifications($userId, array $filters = [])
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT cr.id AS notification_id,
                   cr.status,
                   cr.read_at,
                   cr.sent_at,
                   c.subject,
                   c.message,
                   c.type,
                   c.created_at,
                   u.first_name,
                   u.last_name
            FROM communication_recipients cr
            INNER JOIN communications c ON c.id = cr.communication_id
            LEFT JOIN users u ON c.sender_id = u.id
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
            $meta = $this->inferAdminNotificationCategory($title, $message);
            $timestamp = $row['sent_at'] ?? $row['created_at'] ?? 'now';
            $senderName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $isRead = !empty($row['read_at']) || ($row['status'] ?? '') === 'read';

            $notifications[] = [
                'id' => (int)$row['notification_id'],
                'title' => $title,
                'message' => $message,
                'type' => $meta['type'],
                'icon' => $meta['icon'],
                'time' => $this->formatTimeAgo($timestamp),
                'recipient' => $senderName !== '' ? $senderName : 'System',
                'category' => $meta['category_label'],
                'category_key' => $meta['category_key'],
                'created_at' => $timestamp,
                'read' => $isRead,
                'action_url' => $meta['action_url'],
                'action_text' => $meta['action_text']
            ];
        }

        return $this->filterAdminNotifications($notifications, $filters);
    }

    private function filterAdminNotifications(array $notifications, array $filters)
    {
        $category = strtolower($filters['category'] ?? 'all');
        $type = strtolower($filters['type'] ?? 'all');
        $date = strtolower($filters['date'] ?? 'all');

        return array_values(array_filter($notifications, function ($notification) use ($category, $type, $date) {
            if ($category !== 'all' && strtolower($notification['category_key'] ?? '') !== $category) {
                return false;
            }

            if ($type !== 'all' && strtolower($notification['type'] ?? '') !== $type) {
                return false;
            }

            if ($date === 'all') {
                return true;
            }

            $timestamp = strtotime($notification['created_at'] ?? 'now');
            $now = time();

            if ($date === 'today') {
                return date('Y-m-d', $timestamp) === date('Y-m-d', $now);
            }

            if ($date === 'week') {
                return $timestamp >= strtotime('-7 days', $now);
            }

            if ($date === 'month') {
                return $timestamp >= strtotime('-30 days', $now);
            }

            return true;
        }));
    }

    private function inferAdminNotificationCategory($title, $message)
    {
        $haystack = strtolower($title . ' ' . $message);

        if (preg_match('/claim|burial|mortuary/', $haystack)) {
            return [
                'category_key' => 'claims',
                'category_label' => 'Claims',
                'type' => 'warning',
                'icon' => 'file-medical',
                'action_url' => '/admin/claims',
                'action_text' => 'Review claims'
            ];
        }

        if (preg_match('/upgrade|plan change/', $haystack)) {
            return [
                'category_key' => 'upgrades',
                'category_label' => 'Upgrades',
                'type' => 'info',
                'icon' => 'level-up-alt',
                'action_url' => '/admin/plan-upgrades',
                'action_text' => 'Review upgrades'
            ];
        }

        if (preg_match('/commission|payout|cashout/', $haystack)) {
            return [
                'category_key' => 'commissions',
                'category_label' => 'Commissions',
                'type' => 'warning',
                'icon' => 'money-bill-wave',
                'action_url' => '/admin/commissions',
                'action_text' => 'Review commissions'
            ];
        }

        if (preg_match('/payment|mpesa|contribution|invoice|receipt/', $haystack)) {
            return [
                'category_key' => 'payment',
                'category_label' => 'Payment',
                'type' => 'success',
                'icon' => 'money-bill-wave',
                'action_url' => '/admin/payments',
                'action_text' => 'Review payments'
            ];
        }

        if (preg_match('/member|registration|activation/', $haystack)) {
            return [
                'category_key' => 'membership',
                'category_label' => 'Membership',
                'type' => 'success',
                'icon' => 'user-plus',
                'action_url' => '/admin/members',
                'action_text' => 'View members'
            ];
        }

        if (preg_match('/sms/', $haystack)) {
            return [
                'category_key' => 'sms',
                'category_label' => 'SMS',
                'type' => 'info',
                'icon' => 'sms',
                'action_url' => '/admin/communications',
                'action_text' => 'Open communications'
            ];
        }

        if (preg_match('/email/', $haystack)) {
            return [
                'category_key' => 'email',
                'category_label' => 'Email',
                'type' => 'info',
                'icon' => 'envelope',
                'action_url' => '/admin/communications',
                'action_text' => 'Open communications'
            ];
        }

        return [
            'category_key' => 'system',
            'category_label' => 'System',
            'type' => 'info',
            'icon' => 'bell',
            'action_url' => '/admin/notifications',
            'action_text' => 'View details'
        ];
    }

    /**
     * Mark notification as read (admin)
     */
    public function markNotificationAsRead()
    {
        $this->requireAdminAccess();

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
     * Mark all notifications as read (admin)
     */
    public function markAllNotificationsAsRead()
    {
        $this->requireAdminAccess();

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

    /**
     * Send Email to Members
     */
    public function sendEmail()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recipients = $_POST['recipients'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            $sendCopy = isset($_POST['send_copy']);
            
            if ($subject && $message && $recipients) {
                // Get recipient list based on selection
                $memberList = $this->getRecipientList($recipients);
                
                if (!empty($memberList)) {
                    // Log communication attempt
                    $this->logCommunication('email', $recipients, $subject, $message, count($memberList));
                    
                    $_SESSION['success'] = 'Email sent to ' . count($memberList) . ' members successfully!';
                } else {
                    $_SESSION['error'] = 'No recipients found for the selected criteria.';
                }
            } else {
                $_SESSION['error'] = 'Please fill in all required fields.';
            }
        }
        
        header('Location: /admin/communications');
        exit();
    }

    /**
     * Send SMS to Members
     */
    public function sendSMS()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recipients = $_POST['recipients'] ?? '';
            $message = $_POST['message'] ?? '';
            
            if ($message && $recipients && strlen($message) <= 160) {
                // Get recipient list based on selection
                $memberList = $this->getRecipientList($recipients);
                
                if (!empty($memberList)) {
                    // Log communication attempt
                    $this->logCommunication('sms', $recipients, 'SMS Message', $message, count($memberList));
                    
                    $_SESSION['success'] = 'SMS sent to ' . count($memberList) . ' members successfully!';
                } else {
                    $_SESSION['error'] = 'No recipients found for the selected criteria.';
                }
            } else {
                $_SESSION['error'] = 'Please provide a valid message (max 160 characters).';
            }
        }
        
        header('Location: /admin/communications');
        exit();
    }

    /**
     * Get recipient list based on criteria
     */
    private function getRecipientList($criteria)
    {
        try {
            switch ($criteria) {
                case 'all':
                    return $this->memberModel->getAllMembers();
                case 'active':
                    return $this->memberModel->getActiveMembersList();
                case 'inactive':
                    return $this->memberModel->getInactiveMembersList();
                case 'recent':
                    return $this->memberModel->getRecentMembers(30);
                default:
                    return [];
            }
        } catch (Exception $e) {
            error_log('Failed to get recipient list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Log communication attempt
     */
    private function logCommunication($type, $recipients, $subject, $message, $count)
    {
        try {
            $db = Database::getInstance();
            // communications table schema:
            // sender_id, recipient_id (optional), recipient_type, recipient_criteria (JSON),
            // subject, message, type, status, sent_at
            $query = "INSERT INTO communications (sender_id, recipient_id, recipient_type, recipient_criteria, subject, message, type, status, sent_at) 
                      VALUES (:sender_id, NULL, :recipient_type, :recipient_criteria, :subject, :message, :type, 'sent', NOW())";

            $criteria = [
                'criteria' => $recipients,
                'estimated_recipient_count' => $count
            ];

            $db->execute($query, [
                'sender_id' => $_SESSION['user_id'] ?? null,
                'recipient_type' => $recipients,
                'recipient_criteria' => json_encode($criteria),
                'subject' => $subject,
                'message' => $message,
                'type' => $type
            ]);
        } catch (Exception $e) {
            error_log('Failed to log communication: ' . $e->getMessage());
        }
    }

    /**
     * Get recent communications
     */
    private function getRecentCommunications($type = 'all', $status = 'all')
    {
        try {
            $db = Database::getInstance();
            $query = "SELECT * FROM communications WHERE 1=1";
            $params = [];
            
            if ($type !== 'all') {
                $query .= " AND type = ?";
                $params[] = $type;
            }
            
            if ($status !== 'all') {
                $query .= " AND status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY sent_at DESC LIMIT 50";
            
            $result = $db->query($query, $params);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Failed to fetch communications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Settings Management
     */
    /**
     * Settings Management
     */
    public function settings()
    {
        $this->requireAdminAccess();
        
        $settingsService = new SettingsService();
        $dbSettings = $settingsService->getAll();
        
        // Merge DB settings with defaults or constants if not in DB
        $settings = array_merge([
            'app_name' => defined('APP_NAME') ? APP_NAME : 'Shena Companion',
            'registration_fee' => 200,
            'reactivation_fee' => 100,
            'grace_period_under_80' => 4,
            'grace_period_80_and_above' => 5,
            'maturation_period_under_80' => 4, // Align logic naming
            'maturation_period_80_and_above' => 5,
        ], $dbSettings);
        
        // Ensure values are present via lookup if keys differ
        // Note: system_settings keys should be lower_snake_case
        if (!isset($settings['registration_fee'])) $settings['registration_fee'] = get_system_setting('registration_fee', 200);
        if (!isset($settings['reactivation_fee'])) $settings['reactivation_fee'] = get_system_setting('reactivation_fee', 100);

        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $data = [
            'title' => 'Settings - Admin',
            'settings' => $settings
        ];
        
        $this->view('admin.settings', $data);
    }

    /**
     * Update Settings
     */
    public function updateSettings()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate CSRF token
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    throw new Exception("Invalid CSRF token");
                }

                $settingsService = new SettingsService();
                $editableSettings = [
                    'registration_fee', 
                    'reactivation_fee', 
                    'grace_period_under_80', 
                    'grace_period_80_and_above',
                    'app_name',
                    'admin_email'
                ];

                foreach ($editableSettings as $key) {
                    if (isset($_POST[$key])) {
                        $settingsService->set($key, $_POST[$key]);
                    }
                }
                
                $_SESSION['success'] = 'Settings updated successfully to database!';
                
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error updating settings: ' . $e->getMessage();
            }
        }
        
        $this->redirect('/admin/settings');
    }

    /**
     * M-Pesa Configuration Page
     */
    public function viewMpesaConfig()
    {
        $this->requireAdminAccess();
        
        $db = Database::getInstance()->getConnection();
        
        // Get current configuration
        $stmt = $db->query("SELECT * FROM mpesa_config ORDER BY id DESC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => 'M-Pesa Configuration - ' . APP_NAME,
            'config' => $config ?: []
        ];
        
        $this->view('admin.mpesa-config', $data);
    }

    /**
     * Update M-Pesa Configuration
     */
    public function updateMpesaConfig()
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mpesa-config');
            return;
        }
        
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid request';
            $this->redirect('/admin/mpesa-config');
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        $environment = $_POST['environment'] ?? 'sandbox';
        $consumerKey = $_POST['consumer_key'] ?? '';
        $consumerSecret = $_POST['consumer_secret'] ?? '';
        $shortCode = $_POST['short_code'] ?? '';
        $passKey = $_POST['pass_key'] ?? '';
        $callbackUrl = $_POST['callback_url'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            // Check if configuration exists
            $stmt = $db->query("SELECT id FROM mpesa_config LIMIT 1");
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($exists) {
                // Update existing
                $stmt = $db->prepare("
                    UPDATE mpesa_config SET
                        environment = ?,
                        consumer_key = ?,
                        consumer_secret = ?,
                        short_code = ?,
                        pass_key = ?,
                        callback_url = ?,
                        is_active = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $environment, $consumerKey, $consumerSecret, 
                    $shortCode, $passKey, $callbackUrl, $isActive, $exists['id']
                ]);
            } else {
                // Insert new
                $stmt = $db->prepare("
                    INSERT INTO mpesa_config (
                        environment, consumer_key, consumer_secret, 
                        short_code, pass_key, callback_url, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $environment, $consumerKey, $consumerSecret, 
                    $shortCode, $passKey, $callbackUrl, $isActive
                ]);
            }
            
            $_SESSION['success'] = 'M-Pesa configuration updated successfully';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error updating configuration: ' . $e->getMessage();
        }
        
        $this->redirect('/admin/mpesa-config');
    }

    /**
     * Plan Upgrades Management Page
     */
    public function viewPlanUpgrades()
    {
        $this->requireAdminAccess();
        
        $db = Database::getInstance()->getConnection();
        
        // Get statistics
        $stats = [
            'pending' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total_revenue' => 0
        ];
        
        $stmt = $db->query("
            SELECT 
                status,
                COUNT(*) as count,
                SUM(prorated_amount) as total
            FROM plan_upgrade_requests
            GROUP BY status
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
            if ($row['status'] === 'completed') {
                $stats['total_revenue'] = $row['total'];
            }
        }
        
        // Build filter query
        $where = ['1=1'];
        $params = [];
        
        if (!empty($_GET['status'])) {
            $where[] = 'pur.status = ?';
            $params[] = $_GET['status'];
        }
        
        if (!empty($_GET['from_date'])) {
            $where[] = 'DATE(pur.requested_at) >= ?';
            $params[] = $_GET['from_date'];
        }
        
        if (!empty($_GET['to_date'])) {
            $where[] = 'DATE(pur.requested_at) <= ?';
            $params[] = $_GET['to_date'];
        }
        
        // Get upgrade requests
        $sql = "
            SELECT 
                pur.*,
                u.first_name, u.last_name, m.member_number,
                CONCAT(u.first_name, ' ', u.last_name) as member_name
            FROM plan_upgrade_requests pur
            JOIN members m ON pur.member_id = m.id
            JOIN users u ON m.user_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY pur.requested_at DESC
            LIMIT 100
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $upgrades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => 'Plan Upgrade Management - ' . APP_NAME,
            'stats' => $stats,
            'upgrades' => $upgrades
        ];
        
        $this->view('admin.plan-upgrades', $data);
    }

    /**
     * Complete Plan Upgrade
     */
    public function completePlanUpgrade($id)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/plan-upgrades');
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Get upgrade request
            $stmt = $db->prepare("
                SELECT * FROM plan_upgrade_requests 
                WHERE id = ? AND status = 'pending' AND payment_status = 'completed'
            ");
            $stmt->execute([$id]);
            $upgrade = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$upgrade) {
                throw new Exception('Upgrade request not found or not eligible for completion');
            }
            
            // Update member package
            $stmt = $db->prepare("UPDATE members SET package = ? WHERE id = ?");
            $stmt->execute([$upgrade['to_package'], $upgrade['member_id']]);
            
            // Update upgrade status
            $stmt = $db->prepare("
                UPDATE plan_upgrade_requests 
                SET status = 'completed', processed_at = NOW(), processed_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $id]);
            
            // Insert into upgrade history
            $stmt = $db->prepare("
                INSERT INTO plan_upgrade_history (
                    member_id, from_package, to_package, 
                    amount, upgrade_date, processed_by
                ) VALUES (?, ?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([
                $upgrade['member_id'],
                $upgrade['from_package'],
                $upgrade['to_package'],
                $upgrade['prorated_amount'],
                $_SESSION['user_id']
            ]);
            
            $db->commit();
            $_SESSION['success'] = 'Upgrade completed successfully';
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Error completing upgrade: ' . $e->getMessage();
        }
        
        $this->redirect('/admin/plan-upgrades');
    }

    /**
     * Cancel Plan Upgrade and Refund
     */
    public function cancelPlanUpgrade($id)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/plan-upgrades');
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Get upgrade request
            $stmt = $db->prepare("SELECT * FROM plan_upgrade_requests WHERE id = ?");
            $stmt->execute([$id]);
            $upgrade = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$upgrade) {
                throw new Exception('Upgrade request not found');
            }
            
            // Update upgrade status
            $stmt = $db->prepare("
                UPDATE plan_upgrade_requests 
                SET status = 'cancelled', processed_at = NOW(), processed_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $id]);
            
            // Record refund transaction if payment was completed
            if ($upgrade['payment_status'] === 'completed') {
                $stmt = $db->prepare("
                    INSERT INTO financial_transactions (
                        transaction_type, amount, member_id, 
                        upgrade_request_id, status, description
                    ) VALUES (
                        'refund', ?, ?, ?, 'completed', 
                        'Refund for cancelled plan upgrade'
                    )
                ");
                $stmt->execute([
                    $upgrade['prorated_amount'],
                    $upgrade['member_id'],
                    $id
                ]);
            }
            
            $db->commit();
            $_SESSION['success'] = 'Upgrade cancelled and refund processed';
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Error cancelling upgrade: ' . $e->getMessage();
        }
        
        $this->redirect('/admin/plan-upgrades');
    }

    /**
     * Approve Plan Upgrade (admin)
     */
    public function approvePlanUpgrade($id)
    {
        $this->requireAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/plan-upgrades');
            return;
        }

        try {
            require_once 'app/services/PlanUpgradeService.php';
            $service = new PlanUpgradeService();
            $service->approveUpgrade($id, $_SESSION['user_id'] ?? null);
            $_SESSION['success'] = 'Upgrade approved. Payment initiation queued.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error approving upgrade: ' . $e->getMessage();
        }

        $this->redirect('/admin/plan-upgrades');
    }

    /**
     * Reject Plan Upgrade (admin)
     */
    public function rejectPlanUpgrade($id)
    {
        $this->requireAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/plan-upgrades');
            return;
        }

        $reason = $_POST['reason'] ?? 'Rejected by admin';

        try {
            require_once 'app/services/PlanUpgradeService.php';
            $service = new PlanUpgradeService();
            $service->rejectUpgrade($id, $reason, $_SESSION['user_id'] ?? null);
            $_SESSION['success'] = 'Upgrade request rejected';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error rejecting upgrade: ' . $e->getMessage();
        }

        $this->redirect('/admin/plan-upgrades');
    }

    /**
     * Financial Dashboard
     */
    public function viewFinancialDashboard()
    {
        $this->requireAdminAccess();
        
        $db = Database::getInstance()->getConnection();
        
        // Date range from filters or default to current month
        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate = $_GET['to_date'] ?? date('Y-m-d');
        
        // Get KPIs
        $stmt = $db->prepare("
            SELECT 
                SUM(CASE WHEN transaction_type = 'payment' THEN amount ELSE 0 END) as total_payments,
                SUM(CASE WHEN transaction_type = 'commission' THEN amount ELSE 0 END) as total_commissions,
                SUM(CASE WHEN transaction_type = 'upgrade' THEN amount ELSE 0 END) as total_upgrades,
                SUM(CASE WHEN transaction_type = 'refund' THEN amount ELSE 0 END) as total_refunds,
                COUNT(DISTINCT CASE WHEN transaction_type = 'payment' THEN member_id END) as paying_members,
                COUNT(DISTINCT CASE WHEN transaction_type = 'commission' THEN agent_id END) as earning_agents
            FROM financial_transactions
            WHERE DATE(transaction_date) BETWEEN ? AND ?
            AND status = 'completed'
        ");
        $stmt->execute([$fromDate, $toDate]);
        $kpis = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $kpis['net_revenue'] = ($kpis['total_payments'] + $kpis['total_upgrades']) - 
                               ($kpis['total_commissions'] + $kpis['total_refunds']);
        $kpis['revenue_change'] = 0; // Calculate vs previous period if needed
        $kpis['total_revenue'] = $kpis['total_payments'] + $kpis['total_upgrades'];
        
        // Get monthly summary
        $stmt = $db->query("
            SELECT * FROM vw_financial_summary
            ORDER BY month DESC
            LIMIT 12
        ");
        $monthlySummary = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get top agents
        $stmt = $db->query("
            SELECT * FROM vw_agent_leaderboard
            LIMIT 10
        ");
        $topAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent transactions
        $stmt = $db->prepare("
            SELECT 
                ft.*,
                m.member_number,
                m.package
            FROM financial_transactions ft
            LEFT JOIN members m ON ft.member_id = m.id
            WHERE DATE(ft.transaction_date) BETWEEN ? AND ?
            ORDER BY ft.transaction_date DESC
            LIMIT 20
        ");
        $stmt->execute([$fromDate, $toDate]);
        $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => 'Financial Dashboard - ' . APP_NAME,
            'kpis' => $kpis,
            'monthly_summary' => $monthlySummary,
            'top_agents' => $topAgents,
            'recent_transactions' => $recentTransactions
        ];
        
        $this->view('admin.financial-dashboard', $data);
    }

    /**
     * View all payout requests (admin)
     */
    public function payoutRequests()
    {
        $this->requireAdminAccess();
        
        $status = $_GET['status'] ?? 'all';
        $agentId = $_GET['agent_id'] ?? null;
        
        // Get payout requests based on filters
        if ($status !== 'all') {
            $payoutRequests = $this->payoutRequestModel->getAllPayouts($status);
        } else {
            $payoutRequests = $this->payoutRequestModel->getAllPayouts();
        }
        
        // Filter by agent if specified
        if ($agentId) {
            $payoutRequests = array_filter($payoutRequests, function($request) use ($agentId) {
                return $request['agent_id'] == $agentId;
            });
        }
        
        // Get statistics
        $stats = [
            'total' => 0,
            'requested' => 0,
            'processing' => 0,
            'paid' => 0,
            'rejected' => 0,
            'total_amount' => 0
        ];
        
        foreach ($payoutRequests as $request) {
            $stats['total']++;
            $stats[$request['status']]++;
            if ($request['status'] === 'paid') {
                $stats['total_amount'] += $request['amount'];
            }
        }
        
        $data = [
            'title' => 'Payout Requests - Admin',
            'payout_requests' => $payoutRequests,
            'stats' => $stats,
            'status_filter' => $status,
            'agent_id' => $agentId
        ];
        
        $this->view('admin.payout-requests', $data);
    }
    
    /**
     * Process (approve/reject) a payout request
     */
    public function processPayoutRequest($payoutId)
    {
        $this->requireAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            $this->redirect('/admin/payouts');
            return;
        }
        
        $action = $_POST['action'] ?? '';
        $paymentReference = $_POST['payment_reference'] ?? '';
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        $payout = $this->payoutRequestModel->getPayoutById($payoutId);
        
        if (!$payout) {
            $_SESSION['error'] = 'Payout request not found.';
            $this->redirect('/admin/payouts');
            return;
        }
        
        // Handle mark_paid action separately - requires status to be 'processing'
        if ($action === 'mark_paid') {
            // Verify payout is in 'processing' status for mark_paid action
            if ($payout['status'] !== 'processing') {
                $_SESSION['error'] = 'Payout request must be in processing status to mark as paid.';
                $this->redirect('/admin/payouts');
                return;
            }
            
            try {
                $result = $this->payoutRequestModel->markAsPaid($payoutId);
                
                if ($result) {
                    // Send notification to agent
                    $notification = new InAppNotificationService();
                    $notification->notifyUser(
                        $payout['user_id'],
                        [
                            'subject' => 'Payout Completed',
                            'message' => "Your payout of KES " . number_format($payout['amount'], 2) . " has been marked as paid.",
                            'action_url' => '/agent/payouts',
                            'action_text' => 'View Payouts'
                        ]
                    );
                    
                    $_SESSION['success'] = 'Payout marked as paid successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to mark payout as paid. The payout may have already been processed.';
                }
            } catch (Exception $e) {
                error_log('Mark as paid error: ' . $e->getMessage());
                $_SESSION['error'] = 'Error marking payout as paid: ' . $e->getMessage();
            }
            
            $this->redirect('/admin/payouts');
            return;
        }
        
        // For approve/reject actions, verify payout is in 'requested' status
        if ($payout['status'] !== 'requested') {
            $_SESSION['error'] = 'Payout request has already been processed.';
            $this->redirect('/admin/payouts');
            return;
        }
        
        try {
            if ($action === 'approve') {
                // Process the payout (mark as processing)
                $result = $this->payoutRequestModel->processPayout(
                    $payoutId,
                    $_SESSION['user_id'],
                    $paymentReference,
                    $adminNotes
                );
                
                if ($result) {
                    // Send notification to agent
                    $notification = new InAppNotificationService();
                    $notification->notifyUser(
                        $payout['user_id'],
                        [
                            'subject' => 'Payout Request Approved',
                            'message' => "Your payout request of KES " . number_format($payout['amount'], 2) . " has been approved and is being processed. Reference: " . ($paymentReference ?: 'N/A'),
                            'action_url' => '/agent/payouts',
                            'action_text' => 'View Payouts'
                        ]
                    );

                    $_SESSION['success'] = 'Payout request approved and marked as processing.';
                } else {
                    $_SESSION['error'] = 'Failed to process payout request.';
                }
            } elseif ($action === 'reject') {
                // Reject the payout
                $result = $this->payoutRequestModel->rejectPayout(
                    $payoutId,
                    $_SESSION['user_id'],
                    $adminNotes
                );
                
                if ($result) {
                    // Send notification to agent
                    $notification = new InAppNotificationService();
                    $notification->notifyUser(
                        $payout['user_id'],
                        [
                            'subject' => 'Payout Request Rejected',
                            'message' => "Your payout request of KES " . number_format($payout['amount'], 2) . " has been rejected. Reason: " . $adminNotes,
                            'action_url' => '/agent/payouts',
                            'action_text' => 'View Payouts'
                        ]
                    );
                    
                    $_SESSION['success'] = 'Payout request rejected.';
                } else {
                    $_SESSION['error'] = 'Failed to reject payout request.';
                }
            } else {
                $_SESSION['error'] = 'Invalid action specified.';
            }
        } catch (Exception $e) {
            error_log('Payout processing error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error processing payout: ' . $e->getMessage();
        }
        
        // Redirect back to agent details if agent_id is provided, otherwise to payouts list
        if (!empty($_POST['redirect_to_agent'])) {
            $this->redirect('/admin/agents/view/' . $payout['agent_id']);
        } else {
            $this->redirect('/admin/payouts');
        }
    }
    
    /**
     * View Agent Details with Payout Requests
     */
    public function viewAgent($id)
    {
        $this->requireAdminAccess();
        
        $agent = $this->agentModel->getAgentById($id);
        
        if (!$agent) {
            $_SESSION['error'] = 'Agent not found.';
            $this->redirect('/admin/agents');
            return;
        }
        
        // Get agent statistics
        $stats = $this->agentModel->getAgentDashboardStats($id);
        
        // Get commissions
        $commissions = $this->agentModel->getAgentCommissions($id);
        
        // Get payout requests for this agent
        $payoutRequests = $this->payoutRequestModel->getAgentPayouts($id);
        
        // Get available balance
        $availableBalance = $this->payoutRequestModel->getAvailableBalance($id);
        
        // Get recent members
        $recentMembers = $this->memberModel->getMembersByAgent($id);
        
        $data = [
            'title' => 'Agent Details - ' . $agent['agent_number'],
            'agent' => $agent,
            'stats' => $stats,
            'commissions' => $commissions,
            'payout_requests' => $payoutRequests,
            'available_balance' => $availableBalance,
            'recent_members' => $recentMembers
        ];
        
        $this->view('admin.agent-details', $data);
    }

    /**
     * Get dashboard alerts for urgent notifications
     */
    private function getDashboardAlerts()

    {
        $alerts = [];

        try {
            $db = Database::getInstance();

            // Check for new claims submitted in the last 24 hours
            $newClaims = $db->fetch("
                SELECT COUNT(*) as count
                FROM claims
                WHERE status = 'submitted'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");

            if ($newClaims && $newClaims['count'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-file-medical',
                    'title' => 'New Claims Submitted',
                    'message' => $newClaims['count'] . ' new claim(s) submitted in the last 24 hours requiring review.',
                    'action_url' => '/admin/claims',
                    'action_text' => 'Review Claims',
                    'priority' => 'high'
                ];
            }

            // Check for pending claims older than 7 days
            $oldPendingClaims = $db->fetch("
                SELECT COUNT(*) as count
                FROM claims
                WHERE status = 'submitted'
                AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");

            if ($oldPendingClaims && $oldPendingClaims['count'] > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'Overdue Claims',
                    'message' => $oldPendingClaims['count'] . ' claim(s) have been pending for more than 7 days.',
                    'action_url' => '/admin/claims?status=submitted',
                    'action_text' => 'Process Now',
                    'priority' => 'high'
                ];
            }

            // Check for members with overdue payments
            $overduePayments = $db->fetch("
                SELECT COUNT(*) as count
                FROM members m
                LEFT JOIN (
                    SELECT member_id, MAX(payment_date) as last_payment_date
                    FROM payments
                    WHERE status = 'completed'
                    GROUP BY member_id
                ) p ON m.id = p.member_id
                WHERE m.status = 'active'
                AND (
                    p.last_payment_date IS NULL
                    OR p.last_payment_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
                )
            ");

            if ($overduePayments && $overduePayments['count'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-clock',
                    'title' => 'Overdue Payments',
                    'message' => $overduePayments['count'] . ' active members have overdue payments.',
                    'action_url' => '/admin/members?status=active',
                    'action_text' => 'View Members',
                    'priority' => 'medium'
                ];
            }

            // Check for low agent commissions
            $lowCommissionAgents = $db->fetch("
                SELECT COUNT(*) as count
                FROM agents a
                LEFT JOIN (
                    SELECT agent_id, SUM(amount) as total_commission
                    FROM agent_commissions
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY agent_id
                ) ac ON a.id = ac.agent_id
                WHERE a.status = 'active'
                AND (ac.total_commission IS NULL OR ac.total_commission < 1000)
            ");

            if ($lowCommissionAgents && $lowCommissionAgents['count'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-chart-line',
                    'title' => 'Low Agent Performance',
                    'message' => $lowCommissionAgents['count'] . ' active agents have low commission earnings this month.',
                    'action_url' => '/admin/agents',
                    'action_text' => 'View Agents',
                    'priority' => 'low'
                ];
            }

            // Check for pending plan upgrade requests
            $pendingUpgrades = $db->fetch("
                SELECT COUNT(*) as count
                FROM plan_upgrade_requests
                WHERE status IN ('pending', 'payment_initiated')
            ");

            if ($pendingUpgrades && $pendingUpgrades['count'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-level-up-alt',
                    'title' => 'Plan Upgrade Requests',
                    'message' => $pendingUpgrades['count'] . ' plan upgrade request(s) awaiting review.',
                    'action_url' => '/admin/plan-upgrades',
                    'action_text' => 'Review Upgrades',
                    'priority' => 'medium'
                ];
            }

            // Check for commission payouts pending processing
            $pendingPayouts = $db->fetch("
                SELECT COUNT(*) as count
                FROM agent_commissions
                WHERE status IN ('pending', 'approved')
            ");

            if ($pendingPayouts && $pendingPayouts['count'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-money-bill-wave',
                    'title' => 'Commission Payout Requests',
                    'message' => $pendingPayouts['count'] . ' commission payout(s) awaiting processing.',
                    'action_url' => '/admin/commissions',
                    'action_text' => 'Review Payouts',
                    'priority' => 'high'
                ];
            }

        } catch (Exception $e) {
            error_log('Failed to fetch dashboard alerts: ' . $e->getMessage());
        }

        return $alerts;
    }

    /**
     * Get recent activities for dashboard
     */
    private function getRecentActivities($limit = 5)
    {
        try {
            $db = Database::getInstance();
            $query = "
                SELECT
                    'member_registration' as type,
                    CONCAT(u.first_name, ' ', u.last_name) as title,
                    'New member registered' as description,
                    u.created_at as activity_time,
                    m.id as reference_id
                FROM users u
                JOIN members m ON u.id = m.user_id
                WHERE u.role = 'member'
                UNION ALL
                SELECT
                    'payment' as type,
                    CONCAT('KES ', p.amount) as title,
                    CONCAT('Payment received from ', COALESCE(u.first_name, 'Unknown')) as description,
                    p.payment_date as activity_time,
                    p.id as reference_id
                FROM payments p
                LEFT JOIN members m ON p.member_id = m.id
                LEFT JOIN users u ON m.user_id = u.id
                WHERE p.status = 'completed'
                UNION ALL
                SELECT
                    'claim' as type,
                    CONCAT('Claim #', c.id) as title,
                    CONCAT('Claim submitted for ', COALESCE(c.deceased_name, 'Unknown')) as description,
                    c.created_at as activity_time,
                    c.id as reference_id
                FROM claims c
                ORDER BY activity_time DESC
                LIMIT ?
            ";

            $result = $db->query($query, [$limit]);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Failed to fetch recent activities: ' . $e->getMessage());
            return [];
        }
    }
}
?>
