<?php
/**
 * Agent Controller
 * Handles agent management, registration, and commission operations
 * 
 * @package Shena\Controllers
 */

require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Claim.php';
require_once __DIR__ . '/../models/PayoutRequest.php';
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/InAppNotificationService.php';


class AgentController extends BaseController
{
    private $agentModel;
    private $userModel;
    private $claimModel;
    private $emailService;
    
    public function __construct()
    {
        parent::__construct();
        $this->agentModel = new Agent();
        $this->userModel = new User();
        $this->claimModel = new Claim();
        $this->emailService = new EmailService();
    }
    
    /**
     * Display agent management dashboard (admin only)
     */
    public function index()
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $agents = $this->agentModel->getAllAgents($filters);
        
        // Calculate statistics
        $totalAgents = $this->agentModel->getActiveAgentsCount();
        $pendingCommissions = $this->agentModel->getTotalCommissions();
        
        // Get pending commissions and aggregate by agent
        $pendingCommissionsRaw = $this->agentModel->getPendingCommissions(100);
        $pendingCommissionsData = $this->aggregateCommissionsByAgent($pendingCommissionsRaw);
        
        // Calculate monthly accounts (members registered this month)
        $monthlyAccounts = 0;
        $totalPortfolios = 0;
        $newAgents = 0;
        
        foreach ($agents as $agent) {
            $monthlyAccounts += $agent['total_members'] ?? 0;
            $totalPortfolios += $agent['total_members'] ?? 0;
        }
        
        // Get top performers (agents with most members)
        $topPerformers = $this->getTopPerformers($agents, 3);
        
        // Get latest pending claim if exists
        $pendingClaims = $this->claimModel->getPendingClaims();
        $latestClaim = !empty($pendingClaims) ? $pendingClaims[0] : null;
        
        $this->render('admin/agents', [
            'agents' => $agents,
            'filters' => $filters,
            'stats' => [
                'total_agents' => $totalAgents,
                'pending_commissions' => $pendingCommissions,
                'monthly_accounts' => $monthlyAccounts,
                'total_portfolios' => $totalPortfolios,
                'new_agents' => $newAgents
            ],
            'pending_commissions_data' => array_slice($pendingCommissionsData, 0, 5), // Top 5 for display
            'top_performers' => $topPerformers,
            'latest_claim' => $latestClaim,
            'pageTitle' => 'Agent Management'
        ]);
    }
    
    /**
     * Aggregate pending commissions by agent
     */
    private function aggregateCommissionsByAgent($commissions)
    {
        $aggregated = [];
        
        foreach ($commissions as $commission) {
            $agentId = $commission['agent_id'];
            
            if (!isset($aggregated[$agentId])) {
                $aggregated[$agentId] = [
                    'agent_id' => $agentId,
                    'agent_number' => $commission['agent_number'] ?? 'N/A',
                    'agent_name' => trim(($commission['first_name'] ?? '') . ' ' . ($commission['last_name'] ?? '')),
                    'commission_amount' => 0,
                    'total_members' => 0
                ];
            }
            
            $aggregated[$agentId]['commission_amount'] += floatval($commission['commission_amount'] ?? 0);
            $aggregated[$agentId]['total_members']++;
        }
        
        // Sort by commission amount descending
        usort($aggregated, function($a, $b) {
            return $b['commission_amount'] <=> $a['commission_amount'];
        });
        
        return array_values($aggregated);
    }
    
    /**
     * Get top performing agents
     */
    private function getTopPerformers($agents, $limit = 3)
    {
        // Sort agents by total_members descending
        usort($agents, function($a, $b) {
            return ($b['total_members'] ?? 0) <=> ($a['total_members'] ?? 0);
        });
        
        return array_slice($agents, 0, $limit);
    }
    
    /**
     * Display agent details
     */
    public function show($agentId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $agent = $this->agentModel->getAgentById($agentId);
        
        if (!$agent) {
            $this->setFlashMessage('Agent not found', 'error');
            redirect('/admin/agents');
            return;
        }
        
        $stats = $this->agentModel->getAgentDashboardStats($agentId);
        $commissions = $this->agentModel->getAgentCommissions($agentId);
        
        // Get payout requests and available balance for this agent
        $payoutRequestModel = new PayoutRequest();
        $payoutRequests = $payoutRequestModel->getAgentPayouts($agentId);
        $availableBalance = $payoutRequestModel->getAvailableBalance($agentId);
        
        $this->render('admin/agent-details', [
            'agent' => $agent,
            'stats' => $stats,
            'commissions' => $commissions,
            'payout_requests' => $payoutRequests,
            'available_balance' => $availableBalance,
            'pageTitle' => 'Agent Details - ' . $agent['agent_number']
        ]);
    }
    
    /**
     * Display agent registration form
     */
    public function create()
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $this->render('admin/agent-create', [
            'pageTitle' => 'Register New Agent'
        ]);
    }
    
    /**
     * Process agent registration
     */
    public function store()
    {
        $this->requireRole(['admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/agents/create');
            return;
        }
        
        // Validate input
        $errors = $this->validateAgentData($_POST);
        
        if (!empty($errors)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Please correct the following errors: ' . implode(', ', $errors);
            if (isset($errors['national_id']) || isset($errors['first_name']) || isset($errors['last_name'])) {
                $_SESSION['error_step'] = 1;
            } elseif (isset($errors['phone']) || isset($errors['email'])) {
                $_SESSION['error_step'] = 2;
            } elseif (isset($errors['password'])) {
                $_SESSION['error_step'] = 3;
            } elseif (isset($errors['commission_rate'])) {
                $_SESSION['error_step'] = 4;
            }
            redirect('/admin/agents/create');
            return;
        }
        
        $nationalId = $this->sanitizeInput($_POST['national_id'] ?? '');
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $phoneRaw = $this->sanitizeInput($_POST['phone'] ?? '');

        // Check if agent already exists
        if ($this->agentModel->existsByNationalId($nationalId)) {
            $_SESSION['error'] = 'An agent with this National ID already exists in the system.';
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error_step'] = 1;
            redirect('/admin/agents/create');
            return;
        }
        
        // Check if email already exists
        if ($this->agentModel->existsByEmail($email)) {
            $_SESSION['error'] = 'An agent with this email address already exists in the system.';
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error_step'] = 2;
            redirect('/admin/agents/create');
            return;
        }

        // Check if phone already exists in users table
        $formattedPhone = formatKenyanPhone($phoneRaw);
        if ($this->userModel->findByPhone($formattedPhone)) {
            $_SESSION['error'] = 'A user with this phone number already exists in the system.';
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error_step'] = 2;
            redirect('/admin/agents/create');
            return;
        }

        try {
            $this->db->getConnection()->beginTransaction();

            // Create user account
            $userData = [
                'first_name' => $this->sanitizeInput($_POST['first_name'] ?? ''),
                'last_name'  => $this->sanitizeInput($_POST['last_name'] ?? ''),
                'email'      => $email,
                'phone'      => $formattedPhone,
                'password'   => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role'       => 'agent',
                'status'     => 'active',
            ];
            
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                throw new Exception('Failed to create user account.');
            }
            
            // Create agent profile
            $agentData = [
                'user_id' => $userId,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'national_id' => $nationalId,
                'phone' => $formattedPhone,
                'email' => $email,
                'address' => $this->sanitizeInput($_POST['address'] ?? ''),
                'county' => $this->sanitizeInput($_POST['county'] ?? ''),
                'commission_rate' => $_POST['commission_rate'] ?? 10.00
            ];
            
            $agentId = $this->agentModel->createAgent($agentData);
            if (!$agentId) {
                throw new Exception('Failed to create agent profile.');
            }

            $this->db->getConnection()->commit();
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log('Agent registration error: ' . $e->getMessage());
            $_SESSION['old_input'] = $_POST;
            $message = $e->getMessage();
            if (stripos($message, 'Duplicate entry') !== false || stripos($message, 'SQLSTATE[23000]') !== false) {
                if (stripos($message, 'national_id') !== false) {
                    $_SESSION['error'] = 'An agent with this National ID already exists in the system.';
                    $_SESSION['error_step'] = 1;
                } elseif (stripos($message, 'phone') !== false) {
                    $_SESSION['error'] = 'A user or agent with this phone number already exists in the system.';
                    $_SESSION['error_step'] = 2;
                } elseif (stripos($message, 'email') !== false) {
                    $_SESSION['error'] = 'An agent or user with this email address already exists in the system.';
                    $_SESSION['error_step'] = 2;
                } else {
                    $_SESSION['error'] = 'A unique field already exists. Please check National ID, phone, and email.';
                }
            } else {
                $_SESSION['error'] = 'Failed to create agent account. Please try again.';
            }
            redirect('/admin/agents/create');
            return;
        }
        
        if ($agentId) {
            // Get the complete agent details
            $agent = $this->agentModel->getAgentById($agentId);
            
            // Send welcome email with login credentials
            $emailSent = false;
            try {
                $emailSent = $this->emailService->sendAgentWelcomeEmail($agent, $_POST['password']);
            } catch (Exception $e) {
                error_log('Failed to send agent welcome email: ' . $e->getMessage());
            }
            
            // Set success message with email status
            if ($emailSent) {
                $_SESSION['success'] = 'Agent registered successfully! Welcome email sent to ' . $agent['email'] . ' with login credentials.';
            } else {
                $_SESSION['success'] = 'Agent registered successfully! However, the welcome email could not be sent. Please provide login credentials manually.';
            }
            
            redirect('/admin/agents');
        } else {
            $_SESSION['error'] = 'Failed to create agent profile. Please try again.';
            $_SESSION['old_input'] = $_POST;
            redirect('/admin/agents/create');
        }
    }
    
    /**
     * Display agent edit form
     */
    public function edit($agentId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $agent = $this->agentModel->getAgentById($agentId);
        
        if (!$agent) {
            $this->setFlashMessage('Agent not found', 'error');
            redirect('/admin/agents');
            return;
        }
        
        $this->render('admin/agent-edit', [
            'agent' => $agent,
            'pageTitle' => 'Edit Agent - ' . $agent['agent_number']
        ]);
    }
    
    /**
     * Process agent update
     */
    public function update($agentId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/agents/edit/' . $agentId);
            return;
        }
        
        $agent = $this->agentModel->getAgentById($agentId);
        
        if (!$agent) {
            $this->setFlashMessage('Agent not found', 'error');
            redirect('/admin/agents');
            return;
        }
        
        $formattedPhone = formatKenyanPhone($_POST['phone'] ?? '');
        if (!$formattedPhone) {
            $this->setFlashMessage('Invalid phone number format', 'error');
            redirect('/admin/agents/edit/' . $agentId);
            return;
        }

        $status = $_POST['status'] ?? ($agent['status'] ?? 'active');
        $previousStatus = $agent['status'] ?? '';
        $allowedStatuses = ['active', 'suspended', 'inactive'];
        if (!in_array($status, $allowedStatuses, true)) {
            $this->setFlashMessage('Invalid agent status', 'error');
            redirect('/admin/agents/edit/' . $agentId);
            return;
        }

        $userUpdateData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $formattedPhone,
            'email' => $_POST['email'] ?? '',
            'status' => $status,
        ];

        $agentUpdateData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $formattedPhone,
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? '',
            'county' => $_POST['county'] ?? '',
            'commission_rate' => $_POST['commission_rate'] ?? 10.00
        ];
        
        try {
            $this->db->getConnection()->beginTransaction();
            $updatedUser = $this->userModel->update($agent['user_id'], $userUpdateData);
            $updatedAgent = $this->agentModel->updateAgent($agentId, $agentUpdateData);
            $updatedStatus = $this->agentModel->updateStatus($agentId, $status);

            if ($updatedUser && $updatedAgent && $updatedStatus) {
                $this->db->getConnection()->commit();
                $this->setFlashMessage('Agent updated successfully', 'success');
                $this->notifyAgentManagementChange($agent, $status, $previousStatus);
            } else {
                $this->db->getConnection()->rollBack();
                $this->setFlashMessage('Failed to update agent', 'error');
            }
        } catch (Throwable $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log('Agent update error: ' . $e->getMessage());
            $this->setFlashMessage('Failed to update agent: ' . $e->getMessage(), 'error');
        }
        
        redirect('/admin/agents/view/' . $agentId);
    }
    
    /**
     * Update agent status (activate, suspend, deactivate)
     */
    public function updateStatus($agentId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/agents');
            return;
        }
        
        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['active', 'suspended', 'inactive'];
        
        if (!in_array($status, $allowedStatuses)) {
            $this->setFlashMessage('Invalid status', 'error');
            redirect('/admin/agents/view/' . $agentId);
            return;
        }
        
        $agent = $this->agentModel->getAgentById($agentId);
        if (!$agent) {
            $this->setFlashMessage('Agent not found', 'error');
            redirect('/admin/agents');
            return;
        }

        $previousStatus = $agent['status'] ?? '';
        try {
            $this->db->getConnection()->beginTransaction();
            $updatedAgent = $this->agentModel->updateStatus($agentId, $status);
            $updatedUser = $this->userModel->update($agent['user_id'], ['status' => $status]);

            if ($updatedAgent && $updatedUser) {
                $this->db->getConnection()->commit();
                $this->setFlashMessage('Agent status updated successfully', 'success');
                $this->notifyAgentManagementChange($agent, $status, $previousStatus);
            } else {
                $this->db->getConnection()->rollBack();
                $this->setFlashMessage('Failed to update agent status', 'error');
            }
        } catch (Throwable $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log('Agent status update error: ' . $e->getMessage());
            $this->setFlashMessage('Failed to update agent status: ' . $e->getMessage(), 'error');
        }
        
        redirect('/admin/agents/view/' . $agentId);
    }

    private function notifyAgentManagementChange(array $agent, string $newStatus, string $previousStatus): void
    {
        if (empty($agent['user_id'])) {
            return;
        }

        try {
            $subject = $newStatus !== $previousStatus ? 'Agent status updated' : 'Agent profile updated';
            $statusText = ucwords(str_replace('_', ' ', $newStatus));
            $message = $newStatus !== $previousStatus
                ? "Your SHENA agent account status has been updated to {$statusText}."
                : 'Your SHENA agent profile details have been updated by the admin team.';

            $notification = new InAppNotificationService();
            $notification->notifyUser((int)$agent['user_id'], [
                'subject' => $subject,
                'message' => $message,
                'action_url' => '/agent/dashboard',
                'action_text' => 'Open Dashboard',
            ], $_SESSION['user_id'] ?? null);
        } catch (Throwable $e) {
            error_log('Agent management notification failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Display commission management page
     */
    public function commissions()
    {
        $this->requireRole(['admin', 'super_admin']);

        $status = $this->sanitizeInput($_GET['status'] ?? '');
        $commissions = $this->agentModel->getCommissionsForExport($status);

        $this->render('admin/commissions', [
            'commissions' => $commissions,
            'pageTitle' => 'Commission Management',
            'status' => $status,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Approve commission payment
     */
    public function approveCommission($commissionId)
    {
        $this->requireRole(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/commissions');
            return;
        }

        $this->validateCsrf();

        // Debug logging for upload attempts
        $logDir = ROOT_PATH . '/storage/logs/';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $debugLog = $logDir . 'resource_upload_debug.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] Upload attempt by user: " . ($_SESSION['user_id'] ?? 'unknown') . "\n";
        $logEntry .= "POST: " . print_r($_POST, true) . "\n";
        $logEntry .= "FILES: " . print_r($_FILES, true) . "\n";
        @file_put_contents($debugLog, $logEntry, FILE_APPEND);
        
        $userId = $_SESSION['user_id'];
        $commission = $this->agentModel->getCommissionById($commissionId);

        if (!$commission) {
            $this->setFlashMessage('Commission not found', 'error');
            redirect('/admin/commissions');
            return;
        }

        if (($commission['status'] ?? '') !== 'pending') {
            $this->setFlashMessage('Only pending commissions can be approved', 'error');
            redirect('/admin/commissions');
            return;
        }

        if ($this->agentModel->approveCommission($commissionId, $userId)) {
            $this->setFlashMessage('Commission approved successfully', 'success');
        } else {
            $this->setFlashMessage('Failed to approve commission', 'error');
        }
        
        redirect('/admin/commissions');
    }
    
    /**
     * Mark commission as paid
     */
    public function markCommissionPaid($commissionId)
    {
        $this->requireRole(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/commissions');
            return;
        }

        $this->validateCsrf();
        
        $paymentMethod = $_POST['payment_method'] ?? '';
        $paymentReference = $_POST['payment_reference'] ?? '';
        
        if (empty($paymentMethod) || empty($paymentReference)) {
            $this->setFlashMessage('Payment method and reference are required', 'error');
            redirect('/admin/commissions');
            return;
        }

        $commission = $this->agentModel->getCommissionById($commissionId);
        if (!$commission) {
            $this->setFlashMessage('Commission not found', 'error');
            redirect('/admin/commissions');
            return;
        }

        if (($commission['status'] ?? '') !== 'approved') {
            $this->setFlashMessage('Only approved commissions can be marked as paid', 'error');
            redirect('/admin/commissions');
            return;
        }
        
        if ($this->agentModel->markCommissionPaid($commissionId, $paymentMethod, $paymentReference)) {
            $this->setFlashMessage('Commission marked as paid', 'success');
        } else {
            $this->setFlashMessage('Failed to update commission', 'error');
        }
        
        redirect('/admin/commissions');
    }
    
    /**
     * Export Agents to CSV
     */
    public function exportAgentsCSV()
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $statusFilter = $status === 'all' ? '' : $status;
        
        $agents = $this->agentModel->getAllAgents(['search' => $search, 'status' => $statusFilter], 10000, 0);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="agents_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'Agent Number',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'County',
            'Status',
            'Total Members',
            'Total Commission',
            'Pending Commission',
            'Registration Date'
        ]);
        
        // Add agent data
        foreach ($agents as $agent) {
            fputcsv($output, [
                $agent['agent_number'] ?? '',
                $agent['first_name'] ?? '',
                $agent['last_name'] ?? '',
                $agent['email'] ?? '',
                $agent['phone'] ?? '',
                $agent['county'] ?? '',
                ucfirst($agent['status'] ?? 'active'),
                $agent['total_members'] ?? 0,
                $agent['total_commission'] ?? 0,
                $agent['pending_commission'] ?? 0,
                $agent['created_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export commissions report to CSV
     */
    public function exportCommissions()
    {
        $this->requireRole(['admin', 'super_admin']);

        $status = $_GET['status'] ?? '';
        $commissions = $this->agentModel->getCommissionsForExport($status);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="agent_commissions_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Agent Number',
            'Agent Name',
            'Member Number',
            'Package',
            'Commission Type',
            'Amount',
            'Commission Rate',
            'Commission Amount',
            'Status',
            'Created At'
        ], ',', '"', '\\', '');

        foreach ($commissions as $commission) {
            fputcsv($output, [
                $commission['agent_number'] ?? '',
                trim(($commission['first_name'] ?? '') . ' ' . ($commission['last_name'] ?? '')),
                $commission['member_number'] ?? '',
                $commission['package'] ?? '',
                $commission['commission_type'] ?? '',
                $commission['amount'] ?? 0,
                $commission['commission_rate'] ?? 0,
                $commission['commission_amount'] ?? 0,
                $commission['status'] ?? 'pending',
                $commission['created_at'] ?? ''
            ], ',', '"', '\\', '');
        }

        fclose($output);
        exit;
    }

    /**
     * Approve all pending commissions
     */
    public function approveAllCommissions()
    {
        $this->requireRole(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $this->validateCsrf();

        $userId = $_SESSION['user_id'] ?? 0;
        $success = $this->agentModel->approveAllPendingCommissions($userId);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            $this->json([
                'success' => (bool)$success,
                'message' => $success ? 'All pending commissions approved.' : 'Failed to approve commissions.'
            ]);
            return;
        }

        $this->setFlashMessage(
            $success ? 'All pending commissions approved.' : 'Failed to approve commissions.',
            $success ? 'success' : 'error'
        );
        redirect('/admin/commissions');
    }

    /**
     * Reactivate all suspended agents
     */
    public function reactivateSuspendedAgents()
    {
        $this->requireRole(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $success = $this->agentModel->reactivateSuspendedAgents();

        $this->json([
            'success' => (bool)$success,
            'message' => $success ? 'Suspended agents reactivated.' : 'No suspended agents to reactivate.'
        ]);
    }

    /**
     * Agent performance report (PDF)
     */
    public function performanceReport()
    {
        $this->requireRole(['admin', 'super_admin']);

        $agents = $this->agentModel->getAllAgents([], 10000, 0);
        $html = $this->renderPdfView('admin/agents-performance-report-pdf', [
            'agents' => $agents,
            'generatedAt' => date('Y-m-d H:i')
        ]);

        $this->streamPdf($html, 'agent-performance-report-' . date('Ymd_His') . '.pdf');
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
     * Agent Resources Management
     */
    public function resources()
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $resourceModel = new Resource();
        $categoryFilter = $_GET['category'] ?? 'all';
        
        $filters = [];
        if ($categoryFilter !== 'all') {
            $filters['category'] = $categoryFilter;
        }
        $filters['is_active'] = true;
        
        // Get all resources (ungrouped) for the view
        $resources = $resourceModel->getAll($filters);
        
        // Calculate statistics
        $stats = [
            'total' => count($resources),
            'marketing' => 0,
            'training' => 0,
            'forms' => 0,
            'policy' => 0,
            'other' => 0
        ];
        
        foreach ($resources as $resource) {
            $category = $resource['category'] ?? 'other';
            if ($category === 'marketing_materials') {
                $stats['marketing']++;
            } elseif ($category === 'training_documents') {
                $stats['training']++;
            } elseif ($category === 'forms') {
                $stats['forms']++;
            } elseif ($category === 'policy_documents') {
                $stats['policy']++;
            } else {
                $stats['other']++;
            }
        }
        
        $this->render('admin/resources', [
            'resources' => $resources,
            'category' => $categoryFilter,
            'stats' => $stats,
            'pageTitle' => 'Agent Resources',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    
    /**
     * Upload Resource
     */
    public function uploadResource()
    {
        $this->requireRole(['admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/agents/resources');
            return;
        }
        
        $this->validateCsrf();
        
        // Validate required fields
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'other';
        
        if (empty($title)) {
            $this->setFlashMessage('Resource title is required', 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        // Validate category
        $allowedCategories = ['marketing_materials', 'training_documents', 'policy_documents', 'forms', 'other'];
        if (!in_array($category, $allowedCategories)) {
            $category = 'other';
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlashMessage('Please select a file to upload', 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        $file = $_FILES['resource_file'];
        
        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            $this->setFlashMessage('File size exceeds maximum limit of 10MB', 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        // Validate file type
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            $this->setFlashMessage('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes), 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = ROOT_PATH . '/storage/uploads/resources/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $uniqueName = uniqid() . '_' . time() . '.' . $fileExt;
        $filePath = $uploadDir . $uniqueName;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->setFlashMessage('Failed to upload file. Please try again.', 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        // Save to database
        $resourceModel = new Resource();
        $resourceId = $resourceModel->create([
            'title' => $title,
            'description' => $description,
            'file_name' => $uniqueName,
            'file_path' => $filePath,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'category' => $category,
            'uploaded_by' => $_SESSION['user_id'],
            'is_active' => true
        ]);
        
        if ($resourceId) {
            // Send notification to all agents except the uploader
            $notificationService = new InAppNotificationService();
            
            // Get all agent user IDs
            $agentModel = new Agent();
            $agents = $agentModel->getAllAgents();
            $agentUserIds = array_map(function($agent) {
                return $agent['user_id'];
            }, $agents);
            
            // Get current user ID (the uploader) to exclude from notifications
            $currentUserId = $_SESSION['user_id'] ?? null;
            
            // Filter out the uploader from receiving notification (they already know they uploaded it)
            if ($currentUserId) {
                $agentUserIds = array_filter($agentUserIds, function($userId) use ($currentUserId) {
                    return $userId != $currentUserId;
                });
                // Re-index the array after filtering
                $agentUserIds = array_values($agentUserIds);
            }

            
            if (!empty($agentUserIds)) {
                $categoryLabel = Resource::getCategoryLabel($category);
                $notificationService->notifyUsers(
                    $agentUserIds,
                    [
                        'subject' => 'New Resource Available: ' . $title,
                        'message' => "A new {$categoryLabel} has been uploaded: {$title}. " . ($description ? $description : ''),
                        'action_url' => '/agent/resources',
                        'action_text' => 'View Resources'
                    ],
                    $_SESSION['user_id']
                );
            }
            
            $this->setFlashMessage('Resource uploaded successfully and agents have been notified!', 'success');
        } else {
            // Delete the uploaded file if database insert failed
            unlink($filePath);
            $this->setFlashMessage('Failed to save resource. Please try again.', 'error');
        }
        
        redirect('/admin/agents/resources');
    }


    /**
     * Delete Resource
     */
    public function deleteResource($resourceId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/agents/resources');
            return;
        }
        
        $this->validateCsrf();
        
        $resourceModel = new Resource();
        $resource = $resourceModel->getById($resourceId);
        
        if (!$resource) {
            $this->setFlashMessage('Resource not found', 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        if ($resourceModel->delete($resourceId)) {
            $this->setFlashMessage('Resource deleted successfully', 'success');
        } else {
            $this->setFlashMessage('Failed to delete resource', 'error');
        }
        
        redirect('/admin/agents/resources');
    }
    
    /**
     * Download Resource (Admin)
     */
    public function downloadResource($resourceId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        $resourceModel = new Resource();
        $resource = $resourceModel->getById($resourceId);
        
        if (!$resource || !$resource['is_active']) {
            $this->setFlashMessage('Resource not found or inactive', 'error');
            redirect('/admin/agents/resources');
            return;
        }
        
        if (!file_exists($resource['file_path'])) {
            $this->setFlashMessage('File not found on server', 'error');
            redirect('/admin/agents/resources');
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
     * Export resource catalog to CSV
     */
    public function exportResources()
    {
        $this->requireRole(['admin', 'super_admin']);

        $resourceModel = new Resource();
        $resources = $resourceModel->getAll(['is_active' => true]);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="agent_resources_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Title', 'Category', 'File Size', 'Upload Date', 'Description', 'Download Count'], ',', '"', '\\', '');

        foreach ($resources as $resource) {
            fputcsv($output, [
                $resource['id'],
                $resource['title'],
                Resource::getCategoryLabel($resource['category']),
                Resource::formatFileSize($resource['file_size']),
                $resource['created_at'],
                $resource['description'],
                $resource['download_count']
            ], ',', '"', '\\', '');
        }

        fclose($output);
        exit;
    }

    
    /**
     * Validate agent registration data
     * 
     * @param array $data Form data
     * @return array Validation errors
     */
    private function validateAgentData($data)
    {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($data['national_id'])) {
            $errors['national_id'] = 'National ID is required';
        }
        
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^(\+254|0)[17]\d{8}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number format';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->userModel->findByEmail($data['email'])) {
            $errors['email'] = 'Email already exists';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        if (isset($data['commission_rate'])) {
            if (!is_numeric($data['commission_rate']) || $data['commission_rate'] < 0 || $data['commission_rate'] > 100) {
                $errors['commission_rate'] = 'Commission rate must be between 0 and 100';
            }
        }
        
        return $errors;
    }
}
