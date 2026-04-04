<?php
/**
 * Member Controller - Handles member dashboard and operations
 */
class MemberController extends BaseController 
{
    private $userModel;
    private $memberModel;
    private $paymentModel;
    private $beneficiaryModel;
    private $claimModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->userModel = new User();
        $this->memberModel = new Member();
        $this->paymentModel = new Payment();
        $this->beneficiaryModel = new Beneficiary();
        $this->claimModel = new Claim();
    }
    
    public function dashboard()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/login');
            return;
        }
        
        // Get recent payments
        $recentPayments = $this->paymentModel->getMemberPayments($member['id'], 5);

        // Get all payments for stats
        $allPayments = $this->paymentModel->getMemberPayments($member['id']);

        // Get beneficiaries
        $beneficiaries = $this->beneficiaryModel->getActiveBeneficiaries($member['id']);

        // Get recent claims
        $recentClaims = $this->claimModel->getMemberClaims($member['id']);

        // Check payment status for current month
        $currentYear = date('Y');
        $currentMonth = date('n');
        $currentMonthPayment = $this->paymentModel->getMonthlyPaymentStatus($member['id'], $currentYear, $currentMonth);

        $completedPayments = array_filter($allPayments ?? [], fn($p) => ($p['status'] ?? '') === 'completed');
        $totalPaid = 0.0;
        $monthsCovered = 0;

        foreach ($completedPayments as $payment) {
            $paymentDate = $payment['payment_date'] ?? $payment['created_at'] ?? null;
            if ($paymentDate && date('Y', strtotime($paymentDate)) === (string)$currentYear) {
                $totalPaid += (float)($payment['amount'] ?? 0);
                $monthsCovered++;
            }
        }

        $lastCompletedPayment = null;
        foreach ($allPayments as $payment) {
            if (($payment['status'] ?? '') === 'completed') {
                $lastCompletedPayment = $payment;
                break;
            }
        }

        $nextDueDate = null;
        if ($lastCompletedPayment) {
            $lastDate = new DateTime($lastCompletedPayment['payment_date'] ?? $lastCompletedPayment['created_at']);
            $lastDate->modify('+1 month');
            $nextDueDate = $lastDate;
        } elseif (!empty($member['created_at'])) {
            $createdDate = new DateTime($member['created_at']);
            $createdDate->modify('+1 month');
            $nextDueDate = $createdDate;
        }

        $currentMonthStatus = (!empty($currentMonthPayment) && (int)($currentMonthPayment['completed_payments'] ?? 0) > 0)
            ? 'PAID'
            : 'DUE';

        if (($member['status'] ?? '') !== 'active') {
            $currentMonthStatus = strtoupper($member['status'] ?? 'INACTIVE');
        }

        $maturityProgress = 0;
        $maturityMonthsCompleted = 0;
        $maturityMonthsTotal = 0;

        $missingProfileFields = [];
        $memberIdNumber = (string) ($member['id_number'] ?? '');
        if ($memberIdNumber === '' || strpos($memberIdNumber, 'TMP') === 0) {
            $missingProfileFields[] = 'national_id';
        }
        if (empty($member['date_of_birth'])) {
            $missingProfileFields[] = 'date_of_birth';
        }
        if (empty($member['address'])) {
            $missingProfileFields[] = 'address';
        }
        if (empty($member['next_of_kin'])) {
            $missingProfileFields[] = 'next_of_kin';
        }
        if (empty($member['next_of_kin_phone'])) {
            $missingProfileFields[] = 'next_of_kin_phone';
        }

        $showProfileCompletionPopup = !empty($_SESSION['is_first_login']);

        $profileCompletionFormData = [
            'national_id' => (strpos($memberIdNumber, 'TMP') === 0) ? '' : $memberIdNumber,
            'date_of_birth' => (string) ($member['date_of_birth'] ?? ''),
            'address' => (string) ($member['address'] ?? ''),
            'next_of_kin' => (string) ($member['next_of_kin'] ?? ''),
            'next_of_kin_relationship' => (string) ($member['next_of_kin_relationship'] ?? ''),
            'next_of_kin_phone' => (string) ($member['next_of_kin_phone'] ?? '')
        ];

        if (!empty($_SESSION['profile_completion_form_data']) && is_array($_SESSION['profile_completion_form_data'])) {
            $profileCompletionFormData = array_merge($profileCompletionFormData, $_SESSION['profile_completion_form_data']);
        }

        if (!empty($member['maturity_ends']) && !empty($member['created_at'])) {
            $created = new DateTime($member['created_at']);
            $maturityEnds = new DateTime($member['maturity_ends']);
            $totalInterval = $created->diff($maturityEnds);
            $maturityMonthsTotal = max(1, ($totalInterval->y * 12) + $totalInterval->m);

            $elapsedInterval = $created->diff(new DateTime());
            $maturityMonthsCompleted = min($maturityMonthsTotal, ($elapsedInterval->y * 12) + $elapsedInterval->m);
            $maturityProgress = (int)round(($maturityMonthsCompleted / $maturityMonthsTotal) * 100);
        }

        // Dashboard stats
        $stats = [
            'total_payments' => is_array($allPayments) ? count(array_filter($allPayments, fn($p) => $p['status'] === 'completed')) : 0,
            'active_claims' => is_array($recentClaims) ? count(array_filter($recentClaims, fn($c) => isset($c['status']) && $c['status'] === 'active')) : 0
        ];

        $data = [
            'title' => 'Dashboard - Shena Companion Welfare Association',
            'member' => $member,
            'recent_payments' => $recentPayments,
            'beneficiaries' => $beneficiaries,
            'recent_claims' => $recentClaims,
            'current_month_paid' => !empty($currentMonthPayment),
            'current_month_payment' => $currentMonthPayment,
            'stats' => $stats,
            'total_paid' => $totalPaid,
            'months_covered' => $monthsCovered,
            'next_due_date' => $nextDueDate ? $nextDueDate->format('M d') : 'N/A',
            'current_month_status' => $currentMonthStatus,
            'maturity_progress' => $maturityProgress,
            'maturity_months_completed' => $maturityMonthsCompleted,
            'maturity_months_total' => $maturityMonthsTotal,
            'show_profile_completion_popup' => $showProfileCompletionPopup,
            'missing_profile_fields' => $missingProfileFields,
            'profile_completion_form_data' => $profileCompletionFormData,
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('member.dashboard', $data);
    }
    
    public function profile()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);

        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'My Profile - Shena Companion Welfare Association',
            'member' => $member,
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('member.profile', $data);
    }
    
    public function updateProfile()
    {
        try {
            $this->validateCsrf();
            
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/profile');
                return;
            }

            $fullName = $this->sanitizeInput($_POST['full_name'] ?? '');
            $nameParts = preg_split('/\s+/', trim($fullName));
            $firstName = !empty($nameParts[0]) ? $nameParts[0] : ($member['first_name'] ?? '');
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : ($member['last_name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $nationalId = $this->sanitizeInput($_POST['national_id'] ?? '');
            $address = $this->sanitizeInput($_POST['address'] ?? '');

            // Store form data in session before validation
            $_SESSION['form_data'] = [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'national_id' => $nationalId,
                'address' => $address
            ];

            if (!empty($email) && !$this->validateEmail($email)) {
                $_SESSION['error'] = 'Please enter a valid email address.';
                $this->redirect('/profile');
                return;
            }

            if (!empty($phone) && !$this->validatePhone($phone)) {
                $_SESSION['error'] = 'Please enter a valid phone number.';
                $this->redirect('/profile');
                return;
            }

            if (!empty($email) && $email !== ($member['email'] ?? '')) {
                $stmt = $this->db->getConnection()->prepare('SELECT id FROM users WHERE email = :email AND id <> :id');
                $stmt->execute([':email' => $email, ':id' => $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = 'This email address is already in use.';
                    $this->redirect('/profile');
                    return;
                }
            }

            // Update user data
            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'email' => $email
            ];
            
            // Update member data
            $memberData = [
                'address' => $address,
                'id_number' => $nationalId
            ];
            
            // Validate phone
            if (!empty($userData['phone']) && !$this->validatePhone($userData['phone'])) {
                $_SESSION['error'] = 'Please enter a valid phone number.';
                $this->redirect('/profile');
                return;
            }
            
            // Update records
            try {
                $this->userModel->update($_SESSION['user_id'], $userData);
                $this->memberModel->update($member['id'], $memberData);
            } catch (Exception $e) {
                error_log('Database update error: ' . $e->getMessage());
                throw new Exception('Failed to update profile in database.');
            }
            
            $_SESSION['success'] = 'Profile updated successfully.';
            unset($_SESSION['form_data']); // Clear form data on success
            
        } catch (Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
        }
        
        $this->redirect('/profile');
    }

    public function updateNextOfKin()
    {
        try {
            $this->validateCsrf();

            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/profile');
                return;
            }

            $nextOfKinName = $this->sanitizeInput($_POST['next_of_kin_name'] ?? '');
            $nextOfKinRelationship = $this->sanitizeInput($_POST['next_of_kin_relationship'] ?? '');
            $nextOfKinPhone = $this->sanitizeInput($_POST['next_of_kin_phone'] ?? '');

            // Store form data in session before validation
            $_SESSION['form_data'] = [
                'next_of_kin_name' => $nextOfKinName,
                'next_of_kin_relationship' => $nextOfKinRelationship,
                'next_of_kin_phone' => $nextOfKinPhone
            ];

            if (empty($nextOfKinName) || empty($nextOfKinRelationship)) {
                $_SESSION['error'] = 'Please provide next of kin name and relationship.';
                $this->redirect('/profile');
                return;
            }

            if (!empty($nextOfKinPhone) && !$this->validatePhone($nextOfKinPhone)) {
                $_SESSION['error'] = 'Please enter a valid next of kin phone number.';
                $this->redirect('/profile');
                return;
            }

            $this->memberModel->update($member['id'], [
                'next_of_kin' => $nextOfKinName,
                'next_of_kin_relationship' => $nextOfKinRelationship,
                'next_of_kin_phone' => $nextOfKinPhone
            ]);

            $_SESSION['success'] = 'Next of kin updated successfully.';
            unset($_SESSION['form_data']); // Clear form data on success
            unset($_SESSION['form_data']); // Clear form data on success
        } catch (Exception $e) {
            error_log('Next of kin update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update next of kin. Please try again.';
        }

        $this->redirect('/profile');
    }

    /**
     * Complete member profile from first-login dashboard popup.
     */
    public function completeProfileFromPopup()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            $this->validateCsrf();

            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Member profile not found.']);
                    return;
                }
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/dashboard');
                return;
            }

            $nationalId = $this->sanitizeInput($_POST['national_id'] ?? '');
            $dateOfBirth = $_POST['date_of_birth'] ?? '';
            $address = $this->sanitizeInput($_POST['address'] ?? '');
            $nextOfKin = $this->sanitizeInput($_POST['next_of_kin'] ?? '');
            $nextOfKinRelationship = $this->sanitizeInput($_POST['next_of_kin_relationship'] ?? '');
            $nextOfKinPhoneInput = $this->sanitizeInput($_POST['next_of_kin_phone'] ?? '');
            $nextOfKinPhone = preg_replace('/\s+/', '', $nextOfKinPhoneInput);

            $_SESSION['profile_completion_form_data'] = [
                'national_id' => $nationalId,
                'date_of_birth' => $dateOfBirth,
                'address' => $address,
                'next_of_kin' => $nextOfKin,
                'next_of_kin_relationship' => $nextOfKinRelationship,
                'next_of_kin_phone' => $nextOfKinPhoneInput
            ];

            if (empty($nationalId) || empty($dateOfBirth) || empty($address) || empty($nextOfKin) || empty($nextOfKinPhone)) {
                $msg = 'Please complete all required profile fields.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    return;
                }
                $_SESSION['error'] = $msg;
                $this->redirect('/dashboard');
                return;
            }

            if (!$this->validatePhone($nextOfKinPhone)) {
                $msg = 'Please provide a valid next of kin phone number.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    return;
                }
                $_SESSION['error'] = $msg;
                $this->redirect('/dashboard');
                return;
            }

            $stmt = $this->db->getConnection()->prepare('SELECT id FROM members WHERE id_number = :id_number AND id <> :id LIMIT 1');
            $stmt->execute([
                ':id_number' => $nationalId,
                ':id' => $member['id']
            ]);
            if ($stmt->fetch()) {
                $msg = 'The national ID number is already in use.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    return;
                }
                $_SESSION['error'] = $msg;
                $this->redirect('/dashboard');
                return;
            }

            $this->memberModel->update($member['id'], [
                'id_number' => $nationalId,
                'date_of_birth' => $dateOfBirth,
                'address' => $address,
                'next_of_kin' => $nextOfKin,
                'next_of_kin_relationship' => $nextOfKinRelationship,
                'next_of_kin_phone' => formatKenyanPhone($nextOfKinPhone)
            ]);

            unset($_SESSION['profile_completion_form_data']);
            // NOTE: is_first_login is intentionally NOT cleared here when AJAX —
            // it is cleared only via updatePackageFromOnboarding() or dismissOnboarding().
            if (!$isAjax) {
                unset($_SESSION['is_first_login']);
                $_SESSION['success'] = 'Profile updated successfully. Welcome to your dashboard.';
                $this->redirect('/dashboard');
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Personal details saved.']);

        } catch (Exception $e) {
            error_log('Complete profile popup update error: ' . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update profile details. Please try again.']);
                return;
            }
            $_SESSION['error'] = 'Failed to update profile details. Please try again.';
            $this->redirect('/dashboard');
        }
    }

    /**
     * AJAX: Update member package during onboarding wizard (Step 3).
     * Clears is_first_login so the wizard does not reappear.
     */
    public function updatePackageFromOnboarding()
    {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();

            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                echo json_encode(['success' => false, 'message' => 'Member not found.']);
                return;
            }

            $packageId = $this->sanitizeInput($_POST['package_id'] ?? '');
            global $membership_packages;
            if (empty($packageId) || !isset($membership_packages[$packageId])) {
                echo json_encode(['success' => false, 'message' => 'Invalid package selected.']);
                return;
            }

            $package = $membership_packages[$packageId];
            $packageType = $this->memberModel->normalizePackageTier($packageId, $package);
            $memberForCalc = [
                'date_of_birth'         => $member['date_of_birth'],
                'package'               => $packageId,
                'package_key'           => $packageId,
                'corporate_couple_count' => 0
            ];
            $monthlyContribution = $this->memberModel->calculateMonthlyContribution($memberForCalc, []);

            $this->memberModel->update($member['id'], [
                'package'             => $packageType,
                'package_key'         => $packageId,
                'monthly_contribution' => $monthlyContribution,
            ]);

            // Package is selected — clear the onboarding flag
            unset($_SESSION['is_first_login']);

            echo json_encode(['success' => true, 'message' => 'Membership plan updated successfully.']);

        } catch (Exception $e) {
            error_log('updatePackageFromOnboarding error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to update plan. Please try again.']);
        }
    }

    /**
     * AJAX: Dismiss the onboarding wizard (skip / finish).
     * Simply clears the is_first_login session flag.
     */
    public function dismissOnboarding()
    {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();
            unset($_SESSION['is_first_login']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('dismissOnboarding error: ' . $e->getMessage());
            echo json_encode(['success' => false]);
        }
    }
    
    public function payments()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }
        
        $payments = $this->paymentModel->getContributions($member['id']);
        $statusFilter = $this->sanitizeInput($_GET['status'] ?? '');
        $yearFilter = $this->sanitizeInput($_GET['year'] ?? '');
        $availableYears = $this->getPaymentYears($payments);
        $payments = $this->filterPayments($payments, $statusFilter, $yearFilter);

        // Calculate total paid and pending count
        $total_paid = 0;
        $pending_count = 0;
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                if ($payment['status'] === 'completed') {
                    $total_paid += (float)$payment['amount'];
                } elseif ($payment['status'] === 'pending') {
                    $pending_count++;
                }
            }
        }

        $data = [
            'title' => 'Payment History - Shena Companion Welfare Association',
            'member' => $member,
            'payments' => $payments,
            'total_paid' => $total_paid,
            'pending_count' => $pending_count,
            'available_years' => $availableYears,
            'selected_status' => $statusFilter,
            'selected_year' => $yearFilter
        ];

        // If redirected with intent to reactivate, set session error so the payments
        // page (or any layout that shows session errors) can display the instruction.
        if (!empty($_GET['intent']) && $_GET['intent'] === 'reactivate') {
            $memberId = (int)($_GET['member_id'] ?? 0);
            $status = 'inactive';
            $fee = defined('REACTIVATION_FEE') ? REACTIVATION_FEE : (defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200);
            $_SESSION['error'] = 'Your membership status is: ' . strtoupper($status) . ". Please pay KES " . $fee . " to activate/reactivate your membership.";
        }

        $this->view('member.payments', $data);
    }

    public function exportPaymentHistory()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }

        $payments = $this->paymentModel->getMemberPayments($member['id']);
        $statusFilter = $this->sanitizeInput($_GET['status'] ?? '');
        $yearFilter = $this->sanitizeInput($_GET['year'] ?? '');
        $payments = $this->filterPayments($payments, $statusFilter, $yearFilter);

        $filename = 'payment-history-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Reference Number', 'Amount (KES)', 'Period', 'Payment Method', 'Status'], ',', '"', '\\', '');

        foreach ($payments as $payment) {
            $reference = strip_tags(html_entity_decode($payment['transaction_id']
                ?? $payment['mpesa_receipt_number']
                ?? $payment['transaction_reference']
                ?? 'N/A', ENT_QUOTES | ENT_HTML5));
            fputcsv($output, [
                date('Y-m-d', strtotime($payment['payment_date'] ?? $payment['created_at'] ?? 'now')),
                $reference,
                number_format((float)($payment['amount'] ?? 0), 2, '.', ''),
                strip_tags(html_entity_decode($payment['period'] ?? 'N/A', ENT_QUOTES | ENT_HTML5)),
                strip_tags(html_entity_decode($payment['payment_method'] ?? 'M-Pesa', ENT_QUOTES | ENT_HTML5)),
                strip_tags(html_entity_decode($payment['status'] ?? 'pending', ENT_QUOTES | ENT_HTML5))
            ], ',', '"', '\\', '');
        }

        fclose($output);
        exit;
    }

    public function exportPaymentReceipt()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        if (!$paymentId) {
            $_SESSION['error'] = 'Invalid payment ID.';
            $this->redirect('/payments');
            return;
        }

        $payment = $this->paymentModel->find($paymentId);
        if (!$payment || $payment['member_id'] != $member['id']) {
            $_SESSION['error'] = 'Payment not found or access denied.';
            $this->redirect('/payments');
            return;
        }

        // Generate PDF receipt using Dompdf
        require_once 'vendor/autoload.php';
        $dompdf = new Dompdf\Dompdf();

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Payment Receipt - ' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                .receipt-container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
                .header { background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: white; padding: 30px; text-align: center; position: relative; }
                .header::before { content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMyIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIi8+Cjwvc3ZnPg==") repeat; opacity: 0.1; }
                .logo { font-size: 28px; font-weight: bold; margin-bottom: 10px; position: relative; z-index: 1; }
                .company-name { font-size: 16px; opacity: 0.9; position: relative; z-index: 1; }
                .receipt-title { font-size: 24px; font-weight: bold; margin: 30px 0 20px 0; color: #1F2937; text-align: center; }
                .receipt-details { padding: 30px; }
                .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
                .detail-row:last-child { border-bottom: none; }
                .detail-label { font-weight: 600; color: #6B7280; font-size: 14px; }
                .detail-value { font-size: 14px; color: #1F2937; }
                .amount-highlight { font-size: 18px; font-weight: bold; color: #059669; }
                .member-info { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #7F20B0; }
                .member-info h5 { margin: 0 0 10px 0; color: #1F2937; font-size: 16px; }
                .member-info p { margin: 5px 0; color: #6B7280; font-size: 14px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e9ecef; }
                .footer p { margin: 5px 0; font-size: 12px; color: #6B7280; }
                .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
                .status-completed { background: #D1FAE5; color: #059669; }
                .status-pending { background: #FEF3C7; color: #D97706; }
                .status-failed { background: #FEE2E2; color: #DC2626; }
                @media print { body { background: white; } .receipt-container { box-shadow: none; } }
            </style>
        </head>
        <body>
            <div class="receipt-container">
                <div class="header">
                    <div class="logo">SHENA</div>
                    <div class="company-name">Companion Welfare Association</div>
                </div>

                <div class="receipt-details">
                    <h2 class="receipt-title">Payment Receipt</h2>

                    <div class="detail-row">
                        <span class="detail-label">Receipt Number:</span>
                        <span class="detail-value">' . htmlspecialchars($payment['transaction_id'] ?? $payment['mpesa_receipt_number'] ?? 'N/A') . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Payment Date:</span>
                        <span class="detail-value">' . date('M d, Y', strtotime($payment['payment_date'] ?? $payment['created_at'] ?? 'now')) . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Amount Paid:</span>
                        <span class="detail-value amount-highlight">KES ' . number_format((float)($payment['amount'] ?? 0), 2) . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Period:</span>
                        <span class="detail-value">' . htmlspecialchars($payment['period'] ?? 'N/A') . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value">' . htmlspecialchars($payment['payment_method'] ?? 'M-Pesa') . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge status-' . ($payment['status'] ?? 'pending') . '">' . strtoupper($payment['status'] ?? 'pending') . '</span>
                        </span>
                    </div>';

        if (!empty($payment['mpesa_receipt_number']) && $payment['mpesa_receipt_number'] !== $payment['transaction_id']) {
            $html .= '<div class="detail-row">
                        <span class="detail-label">M-Pesa Code:</span>
                        <span class="detail-value">' . htmlspecialchars($payment['mpesa_receipt_number']) . '</span>
                    </div>';
        }

        $html .= '<div class="member-info">
                        <h5>Member Information</h5>
                        <p><strong>Name:</strong> ' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '</p>
                        <p><strong>Member ID:</strong> ' . htmlspecialchars($member['member_id'] ?? 'N/A') . '</p>
                        <p><strong>Phone:</strong> ' . htmlspecialchars($member['phone'] ?? 'N/A') . '</p>
                    </div>
                </div>

                <div class="footer">
                    <p><strong>SHENA Companion Welfare Association</strong></p>
                    <p>This is an official payment receipt. Generated on ' . date('F d, Y \a\t H:i') . '</p>
                    <p>For any inquiries, please contact our support team.</p>
                </div>
            </div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'receipt-' . ($payment['transaction_id'] ?? $payment['mpesa_receipt_number'] ?? 'payment') . '.pdf';
        $dompdf->stream($filename, array('Attachment' => true));
        exit;
    }

    public function exportPaymentHistoryPdf()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }

        $payments = $this->paymentModel->getMemberPayments($member['id']);
        $statusFilter = $this->sanitizeInput($_GET['status'] ?? '');
        $yearFilter = $this->sanitizeInput($_GET['year'] ?? '');
        $payments = $this->filterPayments($payments, $statusFilter, $yearFilter);

        // Generate PDF using Dompdf
        require_once 'vendor/autoload.php';
        $dompdf = new Dompdf\Dompdf();

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Payment History - ' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #7F3D9E; text-align: center; }
                .header-info { margin-bottom: 30px; }
                .header-info p { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .total-row { background-color: #e8f4fd; font-weight: bold; }
                .status-completed { color: #10B981; }
                .status-pending { color: #F59E0B; }
                .status-failed { color: #EF4444; }
            </style>
        </head>
        <body>
            <h1>Payment History Report</h1>
            <div class="header-info">
                <p><strong>Member:</strong> ' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '</p>
                <p><strong>Member ID:</strong> ' . htmlspecialchars($member['member_id'] ?? 'N/A') . '</p>
                <p><strong>Report Date:</strong> ' . date('F d, Y') . '</p>
                <p><strong>Period:</strong> ' . (!empty($yearFilter) ? $yearFilter : 'All Years') . ' ' . (!empty($statusFilter) ? ' - ' . ucfirst($statusFilter) . ' Payments' : '') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Amount (KES)</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        $totalAmount = 0;
        foreach ($payments as $payment) {
            $reference = $payment['transaction_id']
                ?? $payment['mpesa_receipt_number']
                ?? $payment['transaction_reference']
                ?? 'N/A';
            $amount = (float)($payment['amount'] ?? 0);
            $totalAmount += $amount;
            $statusClass = 'status-' . ($payment['status'] ?? 'pending');

            $html .= '<tr>
                <td>' . date('M d, Y', strtotime($payment['payment_date'] ?? $payment['created_at'] ?? 'now')) . '</td>
                <td>' . htmlspecialchars($reference) . '</td>
                <td>KES ' . number_format($amount, 2) . '</td>
                <td>' . htmlspecialchars($payment['payment_type'] ?? 'monthly') . '</td>
                <td class="' . $statusClass . '">' . htmlspecialchars(strtoupper($payment['status'] ?? 'pending')) . '</td>
            </tr>';
        }

        $html .= '<tr class="total-row">
                <td colspan="2"><strong>Total Amount</strong></td>
                <td><strong>KES ' . number_format($totalAmount, 2) . '</strong></td>
                <td colspan="2"></td>
            </tr>';

        $html .= '</tbody>
            </table>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'payment-history-' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, array('Attachment' => true));
        exit;
    }
    
    /**
     * Verify pending/failed M-Pesa transaction
     */
    public function verifyTransaction()
    {
        header('Content-Type: application/json');
        
        try {
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                $this->json(['success' => false, 'message' => 'Member not found'], 404);
                return;
            }
            
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
            
            // Search for payment record for this member
            $sql = "SELECT * FROM payments 
                    WHERE member_id = :member_id
                    AND (mpesa_receipt_number = :code OR transaction_reference LIKE :code_pattern)
                    AND phone_number LIKE :phone
                    AND status IN ('pending', 'failed', 'initiated')
                    ORDER BY created_at DESC 
                    LIMIT 1";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                ':member_id' => $member['id'],
                ':code' => $transactionCode,
                ':code_pattern' => '%' . $transactionCode . '%',
                ':phone' => '%' . substr($phoneNumber, -9) . '%'
            ]);
            
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$payment) {
                // Try alternative search - by phone and recent pending payments for this member
                $sql = "SELECT * FROM payments 
                        WHERE member_id = :member_id
                        AND phone_number LIKE :phone
                        AND status IN ('pending', 'failed', 'initiated')
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        ORDER BY created_at DESC 
                        LIMIT 1";
                
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([
                    ':member_id' => $member['id'],
                    ':phone' => '%' . substr($phoneNumber, -9) . '%'
                ]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$payment) {
                $this->json([
                    'success' => false, 
                    'message' => 'No matching pending payment found. Please verify your transaction code and phone number.'
                ], 404);
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
                                verified_by = 'manual_verification'
                              WHERE id = :id";
                
                $stmt = $this->db->getConnection()->prepare($updatePayment);
                $stmt->execute([
                    ':receipt' => $transactionCode,
                    ':id' => $payment['id']
                ]);
                
                // Update member last payment date
                $updateMember = "UPDATE members SET 
                               last_payment_date = NOW()
                             WHERE id = :id";
                
                $stmt = $this->db->getConnection()->prepare($updateMember);
                $stmt->execute([':id' => $member['id']]);
                
                $this->db->getConnection()->commit();
                
                $this->json([
                    'success' => true,
                    'message' => 'Payment verified successfully! Your account has been updated.',
                    'amount' => $payment['amount']
                ]);
                
            } catch (Exception $e) {
                $this->db->getConnection()->rollBack();
                error_log('Transaction verification error: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Failed to verify payment'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Verify transaction error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
    
    public function beneficiaries()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }
        
        $beneficiaries = $this->beneficiaryModel->getMemberBeneficiaries($member['id']);
        $coverageSummary = $this->memberModel->getPlanCoverageSummary($member);
        
        $data = [
            'title' => 'My Beneficiaries - Shena Companion Welfare Association',
            'member' => $member,
            'beneficiaries' => $beneficiaries,
            'coverage_summary' => $coverageSummary,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('member.beneficiaries', $data);
    }
    
    public function addBeneficiary()
    {
        error_log('addBeneficiary called');
        error_log('POST data: ' . print_r($_POST, true));
        
        try {
            $this->validateCsrf();
            error_log('CSRF validated');
            
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                error_log('Member not found');
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            error_log('Member found: ' . $member['id']);
            
            $beneficiaryData = [
                'member_id' => $member['id'],
                'full_name' => $this->sanitizeInput($_POST['full_name'] ?? ''),
                'relationship' => $this->sanitizeInput($_POST['relationship'] ?? ''),
                'id_number' => $this->sanitizeInput($_POST['id_number'] ?? ''),
                'date_of_birth' => $this->sanitizeInput($_POST['date_of_birth'] ?? ''),
                'phone_number' => null,
                'percentage' => (float)($_POST['percentage'] ?? 100)
            ];

            $beneficiaryPhoneInput = $this->sanitizeInput($_POST['phone_number'] ?? '');
            if ($beneficiaryPhoneInput !== '') {
                $beneficiaryData['phone_number'] = formatKenyanPhone($beneficiaryPhoneInput);
            }
            
            error_log('Beneficiary data: ' . print_r($beneficiaryData, true));
            
            // Validate required fields
            if (empty($beneficiaryData['full_name']) || empty($beneficiaryData['relationship']) || 
                empty($beneficiaryData['id_number']) || empty($beneficiaryData['date_of_birth'])) {
                error_log('Validation failed: missing required fields');
                $_SESSION['error'] = 'Please fill in all required fields including date of birth.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            // Validate percentage
            if ($beneficiaryData['percentage'] <= 0 || $beneficiaryData['percentage'] > 100) {
                error_log('Validation failed: invalid percentage');
                $_SESSION['error'] = 'Percentage must be between 1 and 100.';
                $this->redirect('/beneficiaries');
                return;
            }

            try {
                $beneficiaryDob = $beneficiaryData['date_of_birth'] ?? '';
                if (!is_string($beneficiaryDob)) {
                    $beneficiaryDob = '';
                }
                $benAge = $this->memberModel->calculateAge($beneficiaryDob);
                if ($benAge <= 0 || $benAge > 120) {
                    $_SESSION['error'] = 'Please enter a valid beneficiary date of birth.';
                    $this->redirect('/beneficiaries');
                    return;
                }
            } catch (Exception $e) {
                error_log('Beneficiary age calc error: ' . $e->getMessage());
                $_SESSION['error'] = 'Please enter a valid beneficiary date of birth.';
                $this->redirect('/beneficiaries');
                return;
            }

            $currentDependents = $this->beneficiaryModel->getActiveBeneficiaries($member['id']) ?: [];
            $coverageCheck = $this->memberModel->evaluateDependentCoverageForAddition(
                $member,
                $currentDependents,
                (string) ($beneficiaryData['relationship'] ?? '')
            );

            if (empty($coverageCheck['allowed'])) {
                $requiredPackage = $coverageCheck['required_package'] ?? null;
                if (!empty($requiredPackage)) {
                    $_SESSION['error'] = 'This beneficiary is outside your current plan coverage. Please upgrade to ' . ucfirst($requiredPackage) . ' to continue.';
                    $this->redirect('/member/upgrade');
                    return;
                }

                $_SESSION['error'] = 'This beneficiary is outside your current plan coverage. Please review your plan limits.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            // Check total percentage
            $currentTotal = $this->beneficiaryModel->validateBeneficiaryPercentages($member['id']);
            error_log('Current total percentage: ' . $currentTotal);
            
            if (($currentTotal + $beneficiaryData['percentage']) > 100) {
                error_log('Validation failed: percentage exceeds 100');
                $_SESSION['error'] = 'Total beneficiary percentage cannot exceed 100%.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            $beneficiaryId = $this->beneficiaryModel->addBeneficiary($beneficiaryData);
            error_log('Beneficiary added with ID: ' . $beneficiaryId);

            // Recalculate member's monthly contribution now that a beneficiary (dependent) was added
            try {
                $oldMonthly = (int)($member['monthly_contribution'] ?? 0);
                $beneficiaries = $this->beneficiaryModel->getActiveBeneficiaries($member['id']);
                $dependents = $beneficiaries ?: [];
                $memberForCalc = [
                    'date_of_birth' => $member['date_of_birth'] ?? null,
                    'package_key' => $member['package_key'] ?? null,
                    'package' => $member['package'] ?? null
                ];
                $newMonthly = $this->memberModel->calculateMonthlyContribution($memberForCalc, $dependents);
                $this->memberModel->update($member['id'], ['monthly_contribution' => $newMonthly]);
                error_log('Member monthly contribution updated to: ' . $newMonthly);

                if ($newMonthly > $oldMonthly) {
                    $increase = $newMonthly - $oldMonthly;
                    $_SESSION['success'] = 'Beneficiary added successfully. Monthly contribution increased by KES ' . number_format($increase) . ' (new total: KES ' . number_format($newMonthly) . ').';
                } else {
                    $_SESSION['success'] = 'Beneficiary added successfully.';
                }
            } catch (Exception $e) {
                error_log('Failed to recalc monthly contribution after adding beneficiary: ' . $e->getMessage());
                $_SESSION['success'] = 'Beneficiary added successfully.';
            }
            
        } catch (Exception $e) {
            error_log('Add beneficiary error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $_SESSION['error'] = 'Failed to add beneficiary: ' . $e->getMessage();
        }
        
        $this->redirect('/beneficiaries');
    }
    
    public function claims()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }
        
        $claims = $this->claimModel->getMemberClaims($member['id']);
        $beneficiaries = $this->beneficiaryModel->getActiveBeneficiaries($member['id']);
        
        $data = [
            'title' => 'Claims - Shena Companion Welfare Association',
            'member' => $member,
            'claims' => $claims,
            'beneficiaries' => $beneficiaries,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('member.claims', $data);
    }
    
    public function viewClaim($id)
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }
        
        // Get claim details
        $claim = $this->claimModel->find($id);
        
        if (!$claim) {
            $_SESSION['error'] = 'Claim not found.';
            $this->redirect('/claims');
            return;
        }
        
        // Verify this claim belongs to the logged-in member
        if ($claim['member_id'] != $member['id']) {
            $_SESSION['error'] = 'Unauthorized access to claim.';
            $this->redirect('/claims');
            return;
        }
        
        // Get claim documents
        $documentModel = new ClaimDocument();
        $documents = $documentModel->getClaimDocuments($id);
        
        // Get beneficiary details
        $beneficiary = null;
        if (!empty($claim['beneficiary_id'])) {
            $beneficiary = $this->beneficiaryModel->find($claim['beneficiary_id']);
        }
        
        // Get service checklist if exists
        $serviceChecklist = null;
        if (class_exists('ClaimServiceChecklist')) {
            try {
                $checklistModel = new ClaimServiceChecklist();
                if (method_exists($checklistModel, 'getByClaimId')) {
                    $serviceChecklist = $checklistModel->getByClaimId($id);
                } else {
                    error_log('ClaimServiceChecklist::getByClaimId() method not found');
                }
            } catch (Exception $e) {
                error_log('Could not load service checklist: ' . $e->getMessage());
            }
        }
        
        $data = [
            'title' => 'Claim Details - Shena Companion Welfare Association',
            'member' => $member,
            'claim' => $claim,
            'beneficiary' => $beneficiary,
            'documents' => $documents,
            'serviceChecklist' => $serviceChecklist
        ];
        
        $this->view('member.claim-view', $data);
    }
    
    public function submitClaim()
    {
        try {
            $this->validateCsrf();
            
            error_log('=== Claim Submission Started ===');
            
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                error_log('Claim submission failed: Member not found for user_id=' . $_SESSION['user_id']);
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/claims');
                return;
            }
            
            error_log('Member found: ID=' . $member['id'] . ', Status=' . $member['status']);
            
            // Check if member is requesting cash alternative
            $requestCashAlternative = isset($_POST['request_cash_alternative']) && $_POST['request_cash_alternative'] === '1';
            $cashAlternativeReason = $requestCashAlternative ? $this->sanitizeInput($_POST['cash_alternative_reason'] ?? '') : '';
            
            // Service-based claim data per SHENA Policy 2026
            $claimData = [
                'member_id' => $member['id'],
                'beneficiary_id' => (int)($_POST['beneficiary_id'] ?? 0),
                'deceased_name' => $this->sanitizeInput($_POST['deceased_name'] ?? ''),
                'deceased_id_number' => $this->sanitizeInput($_POST['deceased_id_number'] ?? ''),
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'date_of_death' => $_POST['date_of_death'] ?? '',
                'place_of_death' => $this->sanitizeInput($_POST['place_of_death'] ?? ''),
                'cause_of_death' => $this->sanitizeInput($_POST['cause_of_death'] ?? ''),
                'mortuary_name' => $this->sanitizeInput($_POST['mortuary_name'] ?? ''),
                'mortuary_bill_amount' => (float)($_POST['mortuary_bill_amount'] ?? 0),
                'mortuary_days_count' => (int)($_POST['mortuary_days_count'] ?? 0),
                'service_delivery_type' => 'standard_services', // Default to service delivery
                'cash_alternative_reason' => $cashAlternativeReason,
                'notes' => $this->sanitizeInput($_POST['notes'] ?? '')
            ];
            
            error_log('Claim data prepared: ' . json_encode($claimData));
            
            // Validate required fields (no claim_amount required for service-based)
            $required = ['beneficiary_id', 'deceased_name', 'deceased_id_number', 'date_of_death', 'place_of_death'];
            foreach ($required as $field) {
                if (empty($claimData[$field])) {
                    $_SESSION['error'] = 'Please fill in all required fields.';
                    $this->redirect('/claims');
                    return;
                }
            }
            
            // Validate mortuary days (max 14 per policy)
            if ($claimData['mortuary_days_count'] > 14) {
                $_SESSION['error'] = 'Mortuary preservation is covered for a maximum of 14 days per policy.';
                $this->redirect('/claims');
                return;
            }
            
            // Validate beneficiary belongs to member
            error_log('Validating beneficiary ID: ' . $claimData['beneficiary_id']);
            $beneficiary = $this->beneficiaryModel->find($claimData['beneficiary_id']);
            if (!$beneficiary) {
                error_log('Beneficiary not found: ID=' . $claimData['beneficiary_id']);
                $_SESSION['error'] = 'Beneficiary not found. Please add a beneficiary first.';
                $this->redirect('/claims');
                return;
            }
            if ($beneficiary['member_id'] != $member['id']) {
                error_log('Beneficiary belongs to different member: beneficiary.member_id=' . $beneficiary['member_id'] . ', current member.id=' . $member['id']);
                $_SESSION['error'] = 'The selected beneficiary does not belong to your account.';
                $this->redirect('/claims');
                return;
            }
            error_log('Beneficiary validated successfully');
            
            // Check member eligibility per policy Section 9
            // Must be active, maturity period completed, not in default
            error_log('Checking member eligibility - Status: ' . $member['status']);
            if ($member['status'] === 'defaulted') {
                error_log('CLAIM REJECTED: Member status is defaulted');
                $_SESSION['error'] = 'Cannot submit claim. Membership is in default status. Please clear outstanding contributions.';
                $this->redirect('/claims');
                return;
            }
            
            if ($member['status'] !== 'active') {
                error_log('CLAIM REJECTED: Member status is not active: ' . $member['status']);
                $_SESSION['error'] = 'Cannot submit claim. Membership must be active.';
                $this->redirect('/claims');
                return;
            }
            error_log('Member status check passed');
            
            // Check maturity period
            error_log('Checking maturity period - maturity_ends: ' . ($member['maturity_ends'] ?? 'NULL'));
            if (!empty($member['maturity_ends'])) {
                $maturityDate = new DateTime($member['maturity_ends']);
                $today = new DateTime();
                
                if ($today < $maturityDate) {
                    $daysRemaining = $today->diff($maturityDate)->days;
                    $maturityDateFormatted = $maturityDate->format('F j, Y');
                    error_log('CLAIM REJECTED: Maturity period not complete. Days remaining: ' . $daysRemaining);
                    
                    $_SESSION['error'] = "Your membership is still in the maturity period. Claims can be submitted after <strong>{$maturityDateFormatted}</strong> ({$daysRemaining} days remaining). "
                        . "The maturity period ensures your membership contributions are up to date before benefit claims can be processed. "
                        . "If you have an urgent situation, please contact SHENA administration for assistance.";
                    $_SESSION['error_type'] = 'maturity_pending';
                    
                    $this->redirect('/claims');
                    return;
                }
                error_log('Maturity period check passed');
            } else {
                error_log('No maturity period set - eligibility check passed');
            }
            
            // Submit claim
            error_log('All eligibility checks passed. Submitting claim to database...');
            $claimId = $this->claimModel->submitClaim($claimData);
            error_log('Claim submitted successfully with ID: ' . $claimId);

            // Handle required claim documents per policy Section 8
            // Required: ID copy, Chief letter, Mortuary invoice
            error_log('Processing file uploads for claim ID: ' . $claimId);
            $claimDocumentModel = new ClaimDocument();

            $documentFields = [
                'id_copy' => ['required' => true, 'label' => 'ID/Birth Certificate Copy'],
                'chief_letter' => ['required' => true, 'label' => 'Chief Letter'],
                'mortuary_invoice' => ['required' => true, 'label' => 'Mortuary Invoice'],
                'death_certificate' => ['required' => false, 'label' => 'Death Certificate']
            ];

            error_log('Checking uploaded files: ' . json_encode(array_keys($_FILES)));
            foreach ($documentFields as $inputName => $config) {
                if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
                    if ($config['required']) {
                        // Delete the claim if required document missing
                        error_log('REQUIRED FILE MISSING: ' . $config['label'] . ' (field: ' . $inputName . ')');
                        error_log('Rolling back claim ID: ' . $claimId);
                        $this->claimModel->delete($claimId);
                        $_SESSION['error'] = "Required document missing: {$config['label']}. Please upload all required documents.";
                        $this->redirect('/claims');
                        return;
                    }
                    error_log('Optional file not uploaded: ' . $inputName);
                    continue;
                }
                
                error_log('Processing file upload: ' . $inputName . ' (size: ' . $_FILES[$inputName]['size'] . ' bytes)');

                // Include the helper functions
                require_once 'app/helpers/functions.php';

                $uploadResult = uploadFile($_FILES[$inputName], 'claims/' . $claimId);
                if ($uploadResult === false) {
                    error_log('FILE UPLOAD FAILED: ' . $inputName . ' - uploadFile() returned false');
                    if ($config['required']) {
                        error_log('Rolling back claim ID: ' . $claimId);
                        $this->claimModel->delete($claimId);
                        $_SESSION['error'] = "Failed to upload required document: {$config['label']}. Please try again.";
                        $this->redirect('/claims');
                        return;
                    }
                    continue;
                }
                
                /** @var string $uploadFileName */
                $uploadFileName = $uploadResult['file_name'];
                /** @var string $uploadFilePath */
                $uploadFilePath = $uploadResult['file_path'];
                /** @var string $uploadMimeType */
                $uploadMimeType = $uploadResult['mime_type'];
                error_log('File uploaded successfully: ' . $uploadFilePath);
                $claimDocumentModel->addDocument([
                    'claim_id' => $claimId,
                    'document_type' => $inputName,
                    'file_name' => $uploadFileName,
                    'file_path' => $uploadFilePath,
                    'file_size' => $uploadResult['file_size'],
                    'mime_type' => $uploadMimeType,
                    'uploaded_by' => $_SESSION['user_id'] ?? null
                ]);
                error_log('Document record saved: claim_id=' . $claimId . ', type=' . $inputName);
            }
            
            error_log('All file uploads completed successfully for claim ID: ' . $claimId);
            
            // Send notification email to admin
            if (class_exists('EmailService')) {
                try {
                    $emailService = new EmailService();
                    $emailService->sendClaimNotificationEmail($member, $claimData);
                } catch (Exception $e) {
                    error_log('Email notification failed: ' . $e->getMessage());
                }
            }

            // Create in-app notifications
            require_once 'app/services/InAppNotificationService.php';
            $inAppNotificationService = new InAppNotificationService();
            try {
                /** @var string $deceasedName */
                $deceasedName = $claimData['deceased_name'];
                $inAppNotificationService->notifyAdmins([
                    'subject' => 'New claim submitted',
                    'message' => "Member {$member['member_number']} submitted Claim #{$claimId} for {$deceasedName}.",
                    'action_url' => "/admin/claims/view/{$claimId}",
                    'action_text' => 'Review Claim'
                ], $_SESSION['user_id'] ?? null);

                $inAppNotificationService->notifyUsers([
                    $_SESSION['user_id'] ?? null
                ], [
                    'subject' => 'Claim submitted successfully',
                    'message' => "Your claim #{$claimId} has been received and is under review.",
                    'action_url' => '/claims',
                    'action_text' => 'View Claims'
                ], $_SESSION['user_id'] ?? null);
            } catch (Exception $ne) {
                error_log('In-app notification failed: ' . $ne->getMessage());
            }
            
            // If cash alternative requested, notify admin
            if ($requestCashAlternative && !empty($cashAlternativeReason)) {
                error_log('Cash alternative requested for claim ID: ' . $claimId);
                try {
                    $inAppNotificationService->notifyAdmins([
                        'subject' => 'Cash alternative requested',
                        'message' => "Member {$member['member_number']} requested cash alternative for Claim #{$claimId}.",
                        'action_url' => "/admin/claims/view/{$claimId}",
                        'action_text' => 'Review Request'
                    ], $_SESSION['user_id'] ?? null);
                } catch (Exception $ne) {
                    error_log('Failed to create admin notification: ' . $ne->getMessage());
                }
            }
            
            $successMessage = 'Claim submitted successfully. SHENA Companion will review your claim and contact you within 1-3 business days.';
            if ($requestCashAlternative) {
                $successMessage .= ' Your cash alternative request has been noted and will be reviewed by administration.';
            }
            $_SESSION['success'] = $successMessage;
            error_log('=== Claim Submission Completed Successfully ===');
            
        } catch (Exception $e) {
            error_log('=== Submit claim error ===');
            error_log('Error message: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            if (DEBUG_MODE) {
                $_SESSION['error'] = 'Failed to submit claim: ' . $e->getMessage();
            } else {
                $_SESSION['error'] = 'An error occurred while submitting your claim. Please try again or contact support if the problem persists.';
            }
        }
        
        $this->redirect('/claims');
    }
    
    public function updateBeneficiary()
    {
        try {
            $this->validateCsrf();
            
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            $beneficiaryId = (int)($_POST['beneficiary_id'] ?? 0);
            
            // Verify beneficiary belongs to member
            $beneficiary = $this->beneficiaryModel->find($beneficiaryId);
            if (!$beneficiary || $beneficiary['member_id'] != $member['id']) {
                $_SESSION['error'] = 'Unauthorized action.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            $updateData = [
                'full_name' => $this->sanitizeInput($_POST['full_name'] ?? ''),
                'relationship' => $this->sanitizeInput($_POST['relationship'] ?? ''),
                'id_number' => $this->sanitizeInput($_POST['id_number'] ?? ''),
                'date_of_birth' => $this->sanitizeInput($_POST['date_of_birth'] ?? null),
                'phone_number' => $this->sanitizeInput($_POST['phone_number'] ?? ''),
                'percentage' => (float)($_POST['percentage'] ?? 0)
            ];
            
            // Validate percentage
            $currentTotal = $this->beneficiaryModel->validateBeneficiaryPercentages($member['id'], $beneficiaryId);
            if (($currentTotal + $updateData['percentage']) > 100) {
                $_SESSION['error'] = 'Total beneficiary percentage cannot exceed 100%.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            $this->beneficiaryModel->updateBeneficiary($beneficiaryId, $updateData);
            $_SESSION['success'] = 'Beneficiary updated successfully.';
            
        } catch (Exception $e) {
            error_log('Update beneficiary error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update beneficiary.';
        }
        
        $this->redirect('/beneficiaries');
    }
    
    public function deleteBeneficiary()
    {
        try {
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if (!$member) {
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            $beneficiaryId = (int)($_POST['beneficiary_id'] ?? 0);
            
            if (!$beneficiaryId) {
                $_SESSION['error'] = 'Invalid beneficiary.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            // Verify beneficiary belongs to member
            $beneficiary = $this->beneficiaryModel->find($beneficiaryId);
            if (!$beneficiary || $beneficiary['member_id'] != $member['id']) {
                $_SESSION['error'] = 'Unauthorized action.';
                $this->redirect('/beneficiaries');
                return;
            }
            
            $this->beneficiaryModel->delete($beneficiaryId);
            $_SESSION['success'] = 'Beneficiary deleted successfully.';
            
        } catch (Exception $e) {
            error_log('Delete beneficiary error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete beneficiary.';
        }
        
        $this->redirect('/beneficiaries');
    }
    
    /**
     * Search members by member number, ID number, or name
     * Used for reconciliation and admin searches
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            $this->json([]);
            return;
        }
        
        // Search by member number, ID number, or name
        $members = $this->memberModel->search($query);
        $this->json($members);
    }
    
    /**
     * View package upgrade page
     */
    public function viewUpgrade()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/member/dashboard');
            return;
        }

        $normalizedCurrentPackage = $this->memberModel->normalizePackageTier($member['package'] ?? 'individual');
        $member['package'] = $normalizedCurrentPackage;
        
        // Check if upgrade is possible
        if ($normalizedCurrentPackage === 'executive') {
            $_SESSION['info'] = 'You are already on the highest package.';
            $this->redirect('/member/dashboard');
            return;
        }
        
        require_once 'app/services/PlanUpgradeService.php';
        $upgradeService = new PlanUpgradeService();
        
        // Check for pending upgrades
        $pendingUpgrades = $upgradeService->getMemberPendingUpgrades($member['id']);
        
        $packageOrder = MembershipPricingService::getTierOrder();
        $currentPackage = strtolower($normalizedCurrentPackage);
        $currentIndex = array_search($currentPackage, $packageOrder, true);
        $defaultTargetPackage = $packageOrder[min(($currentIndex !== false ? $currentIndex + 1 : 1), count($packageOrder) - 1)];

        // Calculate upgrade cost
        try {
            $calculation = $upgradeService->calculateUpgradeCost($member['id'], $defaultTargetPackage);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/member/dashboard');
            return;
        }
        
        // Get upgrade history
        $upgradeHistory = $upgradeService->getMemberUpgradeHistory($member['id']);
        $upgradePreviews = $upgradeService->getMemberUpgradePreviews($member['id']);
        
        $this->view('member/upgrade', [
            'member' => $member,
            'calculation' => $calculation,
            'pendingUpgrades' => $pendingUpgrades,
            'upgradeHistory' => $upgradeHistory,
            'defaultTargetPackage' => $defaultTargetPackage,
            'tierDefinitions' => MembershipPricingService::getTierDefinitions(),
            'upgradePreviews' => $upgradePreviews
        ]);
    }
    
    /**
     * Request package upgrade
     */
    public function requestUpgrade()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method'], 405);
            return;
        }
        
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $this->json(['error' => 'Member not found'], 404);
            return;
        }
        
        require_once 'app/services/PlanUpgradeService.php';
        $upgradeService = new PlanUpgradeService();
        
        try {
            $rawInput = file_get_contents('php://input');
            $jsonInput = json_decode($rawInput, true);
            if (!is_array($jsonInput)) {
                $jsonInput = [];
            }

            $toPackage = $jsonInput['to_package'] ?? $_POST['to_package'] ?? 'family';
            $allowedPackages = MembershipPricingService::getTierOrder();
            if (!in_array($toPackage, $allowedPackages, true)) {
                $this->json(['error' => 'Invalid package selected'], 400);
                return;
            }

            // Check for existing pending upgrades
            $pendingUpgrades = $upgradeService->getMemberPendingUpgrades($member['id']);
            if (!empty($pendingUpgrades)) {
                $this->json(['error' => 'You already have a pending upgrade request'], 400);
                return;
            }
            
            // Create upgrade request
            $upgradeRequestId = $upgradeService->createUpgradeRequest($member['id'], $toPackage);

            // Create in-app notifications
            require_once 'app/services/InAppNotificationService.php';
            $inAppNotificationService = new InAppNotificationService();
            try {
                $toPackageLabel = ucfirst(str_replace('_', ' ', $toPackage));
                $inAppNotificationService->notifyAdmins([
                    'subject' => 'Plan upgrade requested',
                    'message' => "Member {$member['member_number']} requested an upgrade to {$toPackageLabel}.",
                    'action_url' => '/admin/plan-upgrades',
                    'action_text' => 'Review Upgrade'
                ], $_SESSION['user_id'] ?? null);

                $inAppNotificationService->notifyUsers([
                    $_SESSION['user_id'] ?? null
                ], [
                    'subject' => 'Plan upgrade initiated',
                    'message' => "Your upgrade request to {$toPackageLabel} has been submitted. Complete the payment to finish.",
                    'action_url' => '/member/upgrade',
                    'action_text' => 'View Upgrade'
                ], $_SESSION['user_id'] ?? null);
            } catch (Exception $ne) {
                error_log('In-app notification failed: ' . $ne->getMessage());
            }
            
            // Initiate M-Pesa payment
            $phoneNumber = $jsonInput['phone_number'] ?? $_POST['phone_number'] ?? $member['phone'];
            $paymentResponse = $upgradeService->initiateUpgradePayment($upgradeRequestId, $phoneNumber);
            
            $this->json([
                'success' => true,
                'message' => $paymentResponse['message'],
                'upgrade_request_id' => $upgradeRequestId,
                'checkout_request_id' => $paymentResponse['checkout_request_id'],
                'amount' => $paymentResponse['amount']
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Check upgrade payment status
     */
    public function checkUpgradeStatus()
    {
        $upgradeRequestId = $_GET['upgrade_request_id'] ?? null;
        
        if (!$upgradeRequestId) {
            $this->json(['error' => 'Upgrade request ID is required'], 400);
            return;
        }
        
        require_once 'app/services/PlanUpgradeService.php';
        $upgradeService = new PlanUpgradeService();
        
        try {
            $request = $upgradeService->getUpgradeRequest($upgradeRequestId);
            
            if (!$request) {
                $this->json(['error' => 'Upgrade request not found'], 404);
                return;
            }
            
            // Verify this upgrade belongs to the logged-in member
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if ($request['member_id'] != $member['id']) {
                $this->json(['error' => 'Unauthorized'], 403);
                return;
            }
            
            $this->json([
                'success' => true,
                'status' => $request['status'],
                'upgrade_request' => $request
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Cancel upgrade request
     */
    public function cancelUpgrade()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method'], 405);
            return;
        }
        
        $upgradeRequestId = $_POST['upgrade_request_id'] ?? null;
        
        if (!$upgradeRequestId) {
            $this->json(['error' => 'Upgrade request ID is required'], 400);
            return;
        }
        
        require_once 'app/services/PlanUpgradeService.php';
        $upgradeService = new PlanUpgradeService();
        
        try {
            $request = $upgradeService->getUpgradeRequest($upgradeRequestId);
            
            // Verify this upgrade belongs to the logged-in member
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            if ($request['member_id'] != $member['id']) {
                $this->json(['error' => 'Unauthorized'], 403);
                return;
            }
            
            $upgradeService->cancelUpgrade($upgradeRequestId, 'Cancelled by member');
            
            $this->json([
                'success' => true,
                'message' => 'Upgrade request cancelled successfully'
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * View notification settings page
     */
    public function viewNotificationSettings()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/dashboard');
            return;
        }
        
        // Get notification preferences
        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("
            SELECT * FROM notification_preferences WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Default preferences if none exist
        if (!$preferences) {
            $preferences = [
                'email_payment_reminders' => 1,
                'email_payment_confirmations' => 1,
                'email_claim_updates' => 1,
                'email_newsletters' => 1,
                'sms_payment_reminders' => 1,
                'sms_payment_confirmations' => 1,
                'sms_claim_updates' => 1,
                'sms_important_alerts' => 1,
                'notification_frequency' => 'immediate',
                'marketing_communications' => 0
            ];
        }
        
        $data = [
            'member' => $member,
            'preferences' => $preferences,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('member.notification-settings', $data);
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotificationSettings()
    {
        try {
            $this->validateCsrf();
            
            $member = $this->memberModel->findByUserId($_SESSION['user_id']);
            
            if (!$member) {
                $_SESSION['error'] = 'Member profile not found.';
                $this->redirect('/dashboard');
                return;
            }
            
            $db = Database::getInstance();
            
            // Prepare preferences data
            $preferences = [
                'email_payment_reminders' => isset($_POST['email_payment_reminders']) ? 1 : 0,
                'email_payment_confirmations' => isset($_POST['email_payment_confirmations']) ? 1 : 0,
                'email_claim_updates' => isset($_POST['email_claim_updates']) ? 1 : 0,
                'email_newsletters' => isset($_POST['email_newsletters']) ? 1 : 0,
                'sms_payment_reminders' => isset($_POST['sms_payment_reminders']) ? 1 : 0,
                'sms_payment_confirmations' => isset($_POST['sms_payment_confirmations']) ? 1 : 0,
                'sms_claim_updates' => isset($_POST['sms_claim_updates']) ? 1 : 0,
                'sms_important_alerts' => 1, // Always enabled
                'notification_frequency' => $_POST['notification_frequency'] ?? 'immediate',
                'marketing_communications' => isset($_POST['marketing_communications']) ? 1 : 0
            ];
            
            // Check if preferences exist
            $stmt = $db->getConnection()->prepare("
                SELECT id FROM notification_preferences WHERE user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Update existing preferences
                $stmt = $db->getConnection()->prepare("
                    UPDATE notification_preferences SET
                        email_payment_reminders = :email_payment_reminders,
                        email_payment_confirmations = :email_payment_confirmations,
                        email_claim_updates = :email_claim_updates,
                        email_newsletters = :email_newsletters,
                        sms_payment_reminders = :sms_payment_reminders,
                        sms_payment_confirmations = :sms_payment_confirmations,
                        sms_claim_updates = :sms_claim_updates,
                        sms_important_alerts = :sms_important_alerts,
                        notification_frequency = :notification_frequency,
                        marketing_communications = :marketing_communications,
                        updated_at = NOW()
                    WHERE user_id = :user_id
                ");
                $preferences['user_id'] = $_SESSION['user_id'];
                $stmt->execute($preferences);
            } else {
                // Insert new preferences
                $stmt = $db->getConnection()->prepare("
                    INSERT INTO notification_preferences (
                        user_id, email_payment_reminders, email_payment_confirmations,
                        email_claim_updates, email_newsletters, sms_payment_reminders,
                        sms_payment_confirmations, sms_claim_updates, sms_important_alerts,
                        notification_frequency, marketing_communications
                    ) VALUES (
                        :user_id, :email_payment_reminders, :email_payment_confirmations,
                        :email_claim_updates, :email_newsletters, :sms_payment_reminders,
                        :sms_payment_confirmations, :sms_claim_updates, :sms_important_alerts,
                        :notification_frequency, :marketing_communications
                    )
                ");
                $preferences['user_id'] = $_SESSION['user_id'];
                $stmt->execute($preferences);
            }
            
            $_SESSION['success'] = 'Notification preferences updated successfully!';
            
        } catch (Exception $e) {
            error_log('Notification settings update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update preferences. Please try again.';
        }
        
        $this->redirect('/member/notification-settings');
    }
    
    /**
     * Display notifications page
     */
    public function notifications()
    {
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/login');
            return;
        }

        $notifications = $this->getMemberNotifications($_SESSION['user_id']);
        
        $data = [
            'title' => 'Notifications - Shena Companion Welfare Association',
            'member' => $member,
            'notifications' => $notifications,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('member.notifications', $data);
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

    private function filterPayments($payments, $status, $year)
    {
        if (empty($payments)) {
            return [];
        }

        return array_values(array_filter($payments, function ($payment) use ($status, $year) {
            if (!empty($status) && ($payment['status'] ?? '') !== $status) {
                return false;
            }

            if (!empty($year)) {
                $paymentDate = $payment['payment_date'] ?? $payment['created_at'] ?? null;
                if (!$paymentDate || date('Y', strtotime($paymentDate)) !== $year) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function getPaymentYears($payments)
    {
        $years = [];
        foreach ($payments as $payment) {
            $paymentDate = $payment['payment_date'] ?? $payment['created_at'] ?? null;
            if ($paymentDate) {
                $years[] = date('Y', strtotime($paymentDate));
            }
        }

        $years = array_values(array_unique($years));
        rsort($years);
        return $years;
    }

    private function getMemberNotifications($userId)
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT cr.id AS notification_id,
                   cr.status,
                   cr.read_at,
                   cr.sent_at,
                   c.subject,
                   c.message,
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
            $actionUrl = $category['action_url'];
            $actionText = $category['action_text'];

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

        if (preg_match('/payment|mpesa|contribution|invoice|receipt/', $haystack)) {
            return [
                'type' => 'payments',
                'icon' => 'fa-credit-card',
                'color' => '#10B981',
                'action_url' => '/payments',
                'action_text' => 'View Payments'
            ];
        }

        if (preg_match('/claim|burial|mortuary/', $haystack)) {
            return [
                'type' => 'claims',
                'icon' => 'fa-file-medical',
                'color' => '#3B82F6',
                'action_url' => '/claims',
                'action_text' => 'View Claims'
            ];
        }

        return [
            'type' => 'system',
            'icon' => 'fa-bell',
            'color' => '#6B7280',
            'action_url' => '/dashboard',
            'action_text' => 'Go to Dashboard'
        ];
    }

    private function formatTimeAgo($datetime)
    {
        try {
            $time = new DateTime($datetime);
            $now = new DateTime();
            $diff = $now->diff($time);

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

            return 'just now';
        } catch (Exception $e) {
            return 'recently';
        }
    }
    
    /**
     * View contact support page
     */
    public function viewSupport()
    {  
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/member/dashboard');
            return;
        }
        
        $this->view('member/support', [
            'member' => $member
        ]);
    }
    
    /**
     * Submit support request
     */
    public function submitSupport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/member/support');
            return;
        }
        
        $member = $this->memberModel->findByUserId($_SESSION['user_id']);
        
        if (!$member) {
            $_SESSION['error'] = 'Member profile not found.';
            $this->redirect('/member/dashboard');
            return;
        }
        
        $subject = $this->sanitizeInput(trim($_POST['subject'] ?? ''));
        $message = $this->sanitizeInput(trim($_POST['message'] ?? ''));
        $allowedPriorities = ['low', 'normal', 'high', 'urgent'];
        $priority = in_array($_POST['priority'] ?? '', $allowedPriorities, true)
            ? $_POST['priority']
            : 'normal';

        if (empty($subject) || empty($message)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            $this->redirect('/member/support');
            return;
        }

        try {
            $db = \Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'INSERT INTO support_tickets (member_id, user_id, subject, message, priority)
                 VALUES (:member_id, :user_id, :subject, :message, :priority)'
            );
            $stmt->execute([
                ':member_id' => $member['id'],
                ':user_id'   => $_SESSION['user_id'],
                ':subject'   => $subject,
                ':message'   => $message,
                ':priority'  => $priority,
            ]);
        } catch (\Exception $e) {
            error_log('Support ticket insert failed: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to submit your request. Please try again.';
            $this->redirect('/member/support');
            return;
        }

        $_SESSION['success'] = 'Your support request has been submitted successfully. Our team will get back to you soon.';
        $this->redirect('/member/support');
    }
}
