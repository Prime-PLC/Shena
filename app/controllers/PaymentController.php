<?php
/**
 * Payment Controller - Handles payment processing and M-Pesa callbacks
 */
class PaymentController extends BaseController 
{
    private $paymentService;
    private $memberModel;
    private $reconciliationService;
    
    public function __construct()
    {
        parent::__construct();
        $this->paymentService = new PaymentService();
        $this->memberModel = new Member();
        $this->reconciliationService = new PaymentReconciliationService();
    }
    
    public function initiatePayment()
    {
        try {
            // This endpoint expects JSON data
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON decode error: ' . json_last_error_msg());
                $this->json(['error' => 'Invalid JSON data'], 400);
                return;
            }
            
            if (!$input) {
                $this->json(['error' => 'Invalid request data'], 400);
                return;
            }
            
            $memberId = $input['member_id'] ?? null;
            $amount = $input['amount'] ?? null;
            $phoneNumber = $input['phone_number'] ?? null;
            $paymentType = $input['payment_type'] ?? 'monthly';
            
            // Validate input
            if (!$memberId || !$amount || !$phoneNumber) {
                $this->json(['error' => 'Missing required fields'], 400);
                return;
            }
            
            // Get member details
            $member = $this->memberModel->getMemberWithUser($memberId);
            if (!$member) {
                $this->json(['error' => 'Member not found'], 404);
                return;
            }

            if ($paymentType !== 'registration') {
                $paymentModel = new Payment();
                if (($member['status'] ?? '') !== 'active' && !$paymentModel->hasPaidRegistrationFee($memberId)) {
                    $this->json([
                        'error' => 'Please pay the KES ' . number_format(defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200) . ' registration fee first to become an active SHENA member before using this service.'
                    ], 403);
                    return;
                }
            }
            
            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            if (!$phoneNumber) {
                $this->json(['error' => 'Invalid phone number'], 400);
                return;
            }
            
            // Initiate M-Pesa STK Push
            $response = $this->paymentService->initiateSTKPush(
                $phoneNumber,
                $amount,
                $member['id_number'] ?: $member['member_number'],
                ucfirst($paymentType) . ' Contribution'
            );
            
            if ($response && isset($response['CheckoutRequestID'])) {
                // Record payment attempt
                $this->paymentService->recordPaymentAttempt(
                    $memberId,
                    $amount,
                    $phoneNumber,
                    $response['CheckoutRequestID'],
                    $paymentType
                );
                
                $this->json([
                    'success' => true,
                    'message' => 'Payment initiated successfully. Please check your phone for M-Pesa prompt.',
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);
            } else {
                $this->json(['error' => 'Failed to initiate payment'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Payment initiation error: ' . $e->getMessage());
            $this->json(['error' => 'Payment initiation failed'], 500);
        }
    }
    
    public function mpesaCallback()
    {
        try {
            // Get the callback data
            $callbackData = json_decode(file_get_contents('php://input'), true);
            
            if (!$callbackData) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid callback data']);
                return;
            }
            
            // Log the callback for debugging
            error_log('M-Pesa Callback: ' . json_encode($callbackData));
            
            // Process the callback
            $result = $this->paymentService->processCallback($callbackData);
            
            // Return appropriate response
            if ($result['status'] === 'success') {
                echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            } else {
                echo json_encode(['ResultCode' => 1, 'ResultDesc' => $result['message']]);
            }
            
        } catch (Exception $e) {
            error_log('M-Pesa callback error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Callback processing failed']);
        }
    }
    
    public function queryPaymentStatus()
    {
        try {
            $checkoutRequestId = $_GET['checkout_request_id'] ?? null;
            
            if (!$checkoutRequestId) {
                $this->json(['error' => 'Checkout request ID is required'], 400);
                return;
            }
            
            $response = $this->paymentService->queryTransactionStatus($checkoutRequestId);
            
            if ($response) {
                $this->json([
                    'success' => true,
                    'status' => $response
                ]);
            } else {
                $this->json(['error' => 'Failed to query payment status'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Payment status query error: ' . $e->getMessage());
            $this->json(['error' => 'Status query failed'], 500);
        }
    }

    /**
     * Serve a small QR URL for paybill/account so views can embed it dynamically.
     * Example: /qr/paybill?paybill=4163987&account=SC20260001
     */
    public function qrPaybill()
    {
        try {
            $paybill = $_GET['paybill'] ?? MPESA_BUSINESS_SHORTCODE ?? '';
            $account = $_GET['account'] ?? '';
            if (empty($paybill) || empty($account)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing paybill or account']);
                return;
            }

            require_once __DIR__ . '/../services/QrService.php';
            $dataUri = QrService::paybillQrDataUri($paybill, $account, 240);

            if (empty($dataUri)) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to generate QR']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['data_uri' => $dataUri]);
            return;
        } catch (Exception $e) {
            error_log('QR generation error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'QR generation failed']);
        }
    }
    
    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle different formats
        if (substr($phone, 0, 3) === '254') {
            return $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '254' . substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            return '254' . $phone;
        }
        
        return false; // Invalid format
    }

    /**
     * Resolve member ID from member number or ID number if provided
     */
    private function resolveMemberId($memberId, $memberNumber, $idNumber)
    {
        if (!empty($memberId)) {
            return (int)$memberId;
        }

        if (!empty($memberNumber)) {
            $member = $this->memberModel->findByMemberNumber($memberNumber);
            if ($member) {
                return (int)$member['id'];
            }
        }

        if (!empty($idNumber)) {
            $member = $this->memberModel->findByIdNumber($idNumber);
            if ($member) {
                return (int)$member['id'];
            }
        }

        return 0;
    }
    
    /**
     * View reconciliation page (admin only)
     */
    public function viewReconciliation()
    {
        $this->requireRole(['super_admin', 'manager']);

        $stats = $this->reconciliationService->getReconciliationStats();
        $unmatchedPayments = $this->reconciliationService->getUnmatchedPayments();

        // Today's collections
        $db = Database::getInstance();
        $todayStats = $db->fetch(
            "SELECT SUM(amount) as total FROM payments WHERE DATE(created_at)=CURDATE() AND status='completed'"
        );

        // Defaulters: members with no completed payment in last 60 days and status not active
        $defaultersRow = $db->fetch(
            "SELECT COUNT(*) as cnt FROM members m WHERE m.status IN ('inactive','grace_period','defaulted')
             AND NOT EXISTS (
                 SELECT 1 FROM payments p
                 WHERE p.member_id = m.id AND p.status = 'completed'
                 AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
             )"
        );

        $this->view('admin/payments-reconciliation', [
            'recon_stats'         => $stats ?? [],
            'unmatched_payments'  => $unmatchedPayments ?? [],
            'today_collections'   => (float)($todayStats['total'] ?? 0),
            'defaulters_count'    => (int)($defaultersRow['cnt'] ?? 0),
        ]);
    }
    
    /**
     * View unmatched payments for manual reconciliation
     * Admin only
     */
    public function viewUnmatchedPayments()
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }
        
        $unmatchedPayments = $this->reconciliationService->getUnmatchedPayments();
        
        $this->json($unmatchedPayments);
    }
    
    /**
     * Get potential member matches for unmatched payment
     * Admin only
     */
    public function getPotentialMatches($paymentId)
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }
        
        $matches = $this->reconciliationService->findPotentialMatches($paymentId);
        
        $this->json([
            'success' => true,
            'matches' => $matches,
            'count' => count($matches)
        ]);
    }
    
    /**
     * Manually reconcile payment with member
     * Admin only
     */
    public function manualReconcile()
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $paymentId = $input['payment_id'] ?? 0;
        $memberId = $input['member_id'] ?? 0;
        $notes = $input['notes'] ?? '';
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!$paymentId || !$memberId) {
            $this->json(['error' => 'Payment ID and Member ID required'], 400);
            return;
        }
        
        $success = $this->reconciliationService->manualReconciliation($paymentId, $memberId, $userId, $notes);
        
        if ($success) {
            $this->json([
                'success' => true,
                'message' => 'Payment successfully reconciled with member account'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to reconcile payment'
            ], 500);
        }
    }

    /**
     * Admin: Verify payment by Paybill receipt or STK Checkout Request ID
     */
    public function verifyAdminPayment()
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $method = strtolower(trim($input['method'] ?? ''));
        $checkoutRequestId = trim($input['checkout_request_id'] ?? '');
        $mpesaReceipt = trim($input['mpesa_receipt_number'] ?? '');
        $memberId = $this->resolveMemberId(
            $input['member_id'] ?? 0,
            trim($input['member_number'] ?? ''),
            trim($input['id_number'] ?? '')
        );
        $amount = $input['amount'] ?? null;
        $paymentType = trim($input['payment_type'] ?? 'monthly');
        $notes = trim($input['notes'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;

        if ($method === 'stk') {
            if (empty($checkoutRequestId)) {
                $this->json(['error' => 'Checkout Request ID is required for STK verification'], 400);
                return;
            }

            $status = $this->paymentService->queryTransactionStatus($checkoutRequestId);
            if (!$status) {
                $this->json(['error' => 'Failed to query STK transaction status'], 500);
                return;
            }

            $resultCode = $status['ResultCode'] ?? null;
            if (!in_array((string)$resultCode, ['0', '00'], true)) {
                $this->json([
                    'error' => 'STK verification failed',
                    'status' => $status
                ], 400);
                return;
            }

            $paymentModel = new Payment();
            $existing = $paymentModel->findAll(['transaction_reference' => $checkoutRequestId]);

            if (!empty($existing)) {
                $payment = $existing[0];
                if ($payment['status'] === 'completed') {
                    $this->json([
                        'success' => true,
                        'message' => 'Payment already completed',
                        'payment_id' => $payment['id']
                    ]);
                    return;
                }

                if ($memberId && empty($payment['member_id'])) {
                    $paymentModel->update($payment['id'], ['member_id' => $memberId]);
                }

                if (!empty($mpesaReceipt)) {
                    $paymentModel->update($payment['id'], ['transaction_id' => $mpesaReceipt]);
                }

                $paymentModel->confirmPayment($payment['id'], $mpesaReceipt ?: null);

                $this->json([
                    'success' => true,
                    'message' => 'STK payment verified and completed',
                    'payment_id' => $payment['id']
                ]);
                return;
            }

            if (!$memberId || !$amount) {
                $this->json(['error' => 'Member ID and amount are required to post this STK payment'], 400);
                return;
            }

            $paymentId = $paymentModel->recordPayment([
                'member_id' => $memberId,
                'amount' => $amount,
                'payment_type' => $paymentType,
                'payment_method' => 'mpesa',
                'status' => 'pending',
                'transaction_reference' => $checkoutRequestId,
                'transaction_id' => $mpesaReceipt ?: null,
                'notes' => $notes
            ]);

            $paymentModel->confirmPayment($paymentId, $mpesaReceipt ?: null);

            $this->json([
                'success' => true,
                'message' => 'STK payment verified and posted',
                'payment_id' => $paymentId
            ]);
            return;
        }

        if ($method === 'paybill') {
            if (empty($mpesaReceipt)) {
                $this->json(['error' => 'M-Pesa receipt number is required for Paybill verification'], 400);
                return;
            }

            $result = $this->reconciliationService->verifyPaybillReceipt(
                $mpesaReceipt,
                $memberId,
                $userId,
                $notes,
                $paymentType
            );

            if ($result['success']) {
                $this->json($result);
            } else {
                $this->json(['error' => $result['message'] ?? 'Verification failed'], 400);
            }
            return;
        }

        $this->json(['error' => 'Invalid verification method'], 400);
    }
    
    /**
     * Search members for autocomplete
     * Admin only - Returns member suggestions
     */
    public function searchMembers()
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }

        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            $this->json(['results' => []]);
            return;
        }

        try {
            $db = $this->db->getConnection();
            $stmt = $db->prepare("
                SELECT 
                    m.id,
                    m.member_number,
                    u.first_name,
                    u.last_name,
                    m.id_number,
                    u.phone
                FROM members m
                INNER JOIN users u ON m.user_id = u.id
                WHERE 
                    (
                        m.member_number LIKE :query
                        OR u.first_name LIKE :query
                        OR u.last_name LIKE :query
                        OR m.id_number LIKE :query
                        OR u.phone LIKE :query
                        OR CONCAT(u.first_name, ' ', u.last_name) LIKE :query
                    )
                LIMIT 15
            ");
            
            $searchTerm = '%' . $query . '%';
            $stmt->execute(['query' => $searchTerm]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = array_map(function($member) {
                return [
                    'id' => $member['id'],
                    'member_number' => $member['member_number'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'id_number' => $member['id_number'],
                    'phone' => $member['phone'],
                    'label' => sprintf(
                        '%s - %s (%s)',
                        $member['member_number'],
                        $member['first_name'] . ' ' . $member['last_name'],
                        $member['id_number']
                    )
                ];
            }, $members);

            $this->json(['results' => $results]);
        } catch (Exception $e) {
            error_log('Member search error: ' . $e->getMessage());
            $this->json(['results' => []]);
        }
    }

    /**
     * Manually confirm a payment
     * Admin only
     */
    public function confirmPayment($id)
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: /admin/payments');
            exit;
        }

        try {
            $paymentModel = new Payment();
            $payment = $paymentModel->find($id);
            
            if (!$payment) {
                $_SESSION['error'] = 'Payment not found';
                header('Location: /admin/payments');
                exit;
            }

            if ($payment['status'] === 'completed') {
                $_SESSION['info'] = 'Payment already confirmed';
                header('Location: /admin/payments');
                exit;
            }

            $paymentModel->confirmPayment($id);
            $_SESSION['success'] = 'Payment confirmed successfully';
            
        } catch (Exception $e) {
            error_log('Confirm payment error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to confirm payment';
        }

        header('Location: /admin/payments');
        exit;
    }

    /**
     * Mark a payment as failed
     * Admin only
     */
    public function failPayment($id)
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: /admin/payments');
            exit;
        }

        try {
            $reason = $_GET['reason'] ?? 'Manual failure by admin';
            
            $paymentModel = new Payment();
            $payment = $paymentModel->find($id);
            
            if (!$payment) {
                $_SESSION['error'] = 'Payment not found';
                header('Location: /admin/payments');
                exit;
            }

            if ($payment['status'] === 'failed') {
                $_SESSION['info'] = 'Payment already marked as failed';
                header('Location: /admin/payments');
                exit;
            }

            $paymentModel->update($id, [
                'status' => 'failed',
                'notes' => ($payment['notes'] ?? '') . "\nFailed: " . $reason
            ]);
            
            $_SESSION['success'] = 'Payment marked as failed';
            
        } catch (Exception $e) {
            error_log('Fail payment error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update payment status';
        }

        header('Location: /admin/payments');
        exit;
    }
    
    /**
     * Get reconciliation statistics
     * Admin only
     */
    public function getReconciliationStats()
    {
        // Require admin access
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }
        
        $stats = $this->reconciliationService->getReconciliationStats();
        
        $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
