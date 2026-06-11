<?php 
$paymentSummary = $paymentSummary ?? [];
$totalPayments = $paymentSummary['totalPayments'] ?? ($totalPayments ?? 0);
$monthlyPayments = $paymentSummary['monthlyPayments'] ?? ($monthlyPayments ?? 0);
$pendingReconciliation = $paymentSummary['pendingReconciliation'] ?? ($pendingReconciliation ?? 0);
$successRate = $successRate ?? 0;
$paymentFilters = $payment_filters ?? [];
$paymentPagination = $payment_pagination ?? [
    'current_page' => 1,
    'total_pages' => 1,
    'per_page' => 50,
    'total_items' => count($payments ?? []),
];
$paymentFilterValue = static function (string $key) use ($paymentFilters): string {
    return htmlspecialchars((string)($paymentFilters[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};
$paymentPageUrl = static function (int $page) use ($paymentFilters, $paymentPagination): string {
    $query = array_filter($paymentFilters, static function ($value) {
        return $value !== '' && $value !== null && $value !== 'all';
    });
    $query['page'] = max(1, $page);
    $query['per_page'] = (int)($paymentPagination['per_page'] ?? 50);
    return '/admin/payments?' . http_build_query($query);
};
$paymentExportUrl = '/admin/reports/export?' . http_build_query(array_filter([
    'type' => 'payments',
    'format' => 'csv',
    'status' => ($paymentFilters['status'] ?? '') !== 'all' ? ($paymentFilters['status'] ?? '') : '',
    'date_from' => $paymentFilters['date_from'] ?? '',
    'date_to' => $paymentFilters['date_to'] ?? '',
], static function ($value) {
    return $value !== '' && $value !== null;
}));
?>

<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>
<?php include_once __DIR__ . '/modals/member-report-modal.php'; ?>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .page-subtitle {
        font-size: 14px;
        color: #9CA3AF;
    }

    .quick-actions {
        display: flex;
        gap: 12px;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #7F3D9E;
        border: 1px solid #E5E7EB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #E5E7EB;
        transition: all 0.2s;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        color: #10B981;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
        color: #3B82F6;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        color: #F59E0B;
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
        color: #7F3D9E;
    }

    .stat-label {
        font-size: 13px;
        color: #9CA3AF;
        font-weight: 500;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .stat-subtext {
        font-size: 12px;
        color: #6B7280;
    }

    /* Tabs */
    .tabs-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .tabs-header {
        display: flex;
        border-bottom: 1px solid #E5E7EB;
        padding: 0;
        background: #F9FAFB;
        overflow-x: auto;
    }

    .tab-btn {
        padding: 16px 24px;
        border: none;
        background: transparent;
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: #7F3D9E;
        background: rgba(127, 61, 158, 0.05);
    }

    .tab-btn.active {
        color: #7F3D9E;
        background: white;
        border-bottom-color: #7F3D9E;
    }

    .tab-content {
        display: none;
        padding: 24px;
    }

    .tab-content.active {
        display: block;
    }

    /* Table Styles */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
        max-width: 400px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
    }

    .filter-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .payment-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.5fr) repeat(6, minmax(130px, 1fr)) auto auto;
        gap: 12px;
        align-items: center;
        width: 100%;
    }

    .payment-filter-form .search-box {
        max-width: none;
    }

    .payment-filter-form select,
    .payment-filter-form input[type="date"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        color: #374151;
    }

    .filter-btn {
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        white-space: nowrap;
    }

    .filter-btn:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    .filter-btn.primary {
        background: #7F3D9E;
        border-color: #7F3D9E;
        color: white;
    }

    .filter-btn.primary:hover {
        color: white;
        background: #6B2F87;
    }

    .payment-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-top: 16px;
        color: #6B7280;
        font-size: 13px;
    }

    .payment-pagination-controls {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .payment-pagination .disabled {
        opacity: .5;
        pointer-events: none;
    }

    @media (max-width: 1200px) {
        .payment-filter-form {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table thead {
        background: #F9FAFB;
    }

    .custom-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #E5E7EB;
    }

    .custom-table td {
        padding: 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: 14px;
        color: #1F2937;
    }

    .custom-table tbody tr {
        transition: background 0.2s;
    }

    .custom-table tbody tr:hover {
        background: #F9FAFB;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.success {
        background: #D1FAE5;
        color: #059669;
    }

    .status-badge.pending {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .status-badge.failed {
        background: #FEE2E2;
        color: #DC2626;
    }

    /* Reconciliation Section */
    .reconciliation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .reconciliation-card {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #E5E7EB;
    }

    .reconciliation-card h4 {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 12px;
    }

    .reconciliation-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #E5E7EB;
    }

    .reconciliation-item:last-child {
        border-bottom: none;
    }

    .reconciliation-label {
        font-size: 13px;
        color: #6B7280;
    }

    .reconciliation-value {
        font-size: 13px;
        font-weight: 600;
        color: #1F2937;
    }

    .reconciliation-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.8fr);
        gap: 18px;
        align-items: start;
    }

    .reconciliation-panel {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 18px;
    }

    .reconciliation-panel h4 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 16px;
        font-weight: 800;
    }

    .reconciliation-panel p {
        margin: 0 0 14px;
        color: #6B7280;
        font-size: 13px;
        line-height: 1.45;
    }

    .reconciliation-queue {
        display: grid;
        gap: 10px;
    }

    .reconciliation-queue-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #F3F4F6;
    }

    .reconciliation-queue-item:last-child {
        border-bottom: 0;
    }

    .reconciliation-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    /* Financial Dashboard Cards */
    .financial-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .chart-header {
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .chart-subtitle {
        font-size: 13px;
        color: #9CA3AF;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .financial-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .reconciliation-grid {
            grid-template-columns: 1fr;
        }

        .reconciliation-workspace {
            grid-template-columns: 1fr;
        }

        .filter-group {
            flex-wrap: wrap;
        }

        /* Allow table to scroll — override overflow:hidden on tabs-container */
        .tabs-container {
            overflow: visible !important;
        }

        .custom-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            min-width: 0;
        }

        .custom-table thead,
        .custom-table tbody,
        .custom-table tr {
            min-width: 560px;
        }

        .quick-actions {
            flex-wrap: wrap;
        }

        .btn-action {
            flex: 1 1 auto;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Payment Management</h1>
        <p class="page-subtitle">Track, reconcile, and manage all payment operations</p>
    </div>
    <div class="quick-actions">
        <button class="btn-action btn-primary" onclick="window.location.href='/admin/payments-reconciliation'">
            <i class="fas fa-sync-alt"></i>
            Reconcile Payments
        </button>
        <button class="btn-action btn-secondary">
            <i class="fas fa-download"></i>
            Export Report
        </button>
    </div>
</div>

<!-- Payment Analytics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-coins"></i>
            </div>
            <span class="stat-label">Total Payments</span>
        </div>
        <div class="stat-value">KSh <?php echo number_format($totalPayments); ?></div>
        <div class="stat-subtext">All-time collection</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <span class="stat-label">This Month</span>
        </div>
        <div class="stat-value">KSh <?php echo number_format($monthlyPayments); ?></div>
        <div class="stat-subtext">Current month collection</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon orange">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <span class="stat-label">Pending Reconciliation</span>
        </div>
        <div class="stat-value"><?php echo $pendingReconciliation; ?></div>
        <div class="stat-subtext">Transactions to review</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon purple">
                <i class="fas fa-check-circle"></i>
            </div>
            <span class="stat-label">Success Rate</span>
        </div>
        <div class="stat-value"><?php echo $successRate; ?>%</div>
        <div class="stat-subtext">Transaction success rate</div>
    </div>
</div>

<!-- Payment Tabs -->
<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-btn active" onclick="showTab('all')" id="tab-all">
            <i class="fas fa-list"></i>
            All Payments
        </button>
        <button class="tab-btn" onclick="showTab('mpesa')" id="tab-mpesa">
            <i class="fas fa-mobile-alt"></i>
            M-Pesa Payments
        </button>
        <button class="tab-btn" onclick="showTab('reconciliation')" id="tab-reconciliation">
            <i class="fas fa-sync-alt"></i>
            Reconciliation
        </button> 
        <button class="tab-btn" onclick="showTab('reports')" id="tab-reports">
            <i class="fas fa-file-alt"></i>
            Reports
        </button>
    </div>

    <!-- All Payments Tab -->
    <div class="tab-content active" id="content-all">
        <div class="table-header">
            <form class="payment-filter-form" id="paymentFiltersForm" method="GET" action="/admin/payments">
                <?php if (!empty($member_id)): ?>
                    <input type="hidden" name="member_id" value="<?php echo (int)$member_id; ?>">
                <?php endif; ?>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="search" id="paymentSearchInput" name="search" value="<?php echo $paymentFilterValue('search'); ?>" placeholder="Search payments, receipt, member, phone...">
                </div>
                <select name="status" aria-label="Payment status">
                    <?php foreach (['all' => 'All statuses', 'pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled'] as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo (($paymentFilters['status'] ?? 'all') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="payment_method" aria-label="Payment method">
                    <?php foreach (['all' => 'All methods', 'mpesa' => 'M-Pesa', 'bank' => 'Bank', 'cash' => 'Cash', 'cheque' => 'Cheque'] as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo (($paymentFilters['payment_method'] ?? 'all') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="payment_type" aria-label="Payment type">
                    <?php foreach (['all' => 'All types', 'registration' => 'Registration', 'monthly' => 'Monthly', 'reactivation' => 'Reactivation', 'penalty' => 'Penalty'] as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo (($paymentFilters['payment_type'] ?? 'all') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="reconciliation_status" aria-label="Reconciliation status">
                    <?php foreach (['all' => 'All reconciliation', 'pending' => 'Pending', 'matched' => 'Matched', 'unmatched' => 'Unmatched', 'manual' => 'Manual'] as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo (($paymentFilters['reconciliation_status'] ?? 'all') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date_from" value="<?php echo $paymentFilterValue('date_from'); ?>" aria-label="Date from">
                <input type="date" name="date_to" value="<?php echo $paymentFilterValue('date_to'); ?>" aria-label="Date to">
                <button type="submit" class="filter-btn primary">
                    <i class="fas fa-filter"></i>
                    Apply
                </button>
                <a class="filter-btn" href="/admin/payments">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>
                <a class="filter-btn" href="<?php echo htmlspecialchars($paymentExportUrl); ?>">
                    <i class="fas fa-download"></i>
                    Export
                </a>
            </form>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Member Name</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Reconciliation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($p['transaction_id'] ?? $p['mpesa_receipt_number'] ?? '—'); ?></strong></td>
                    <td><?php echo htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?></td>
                    <td><strong>KSh <?php echo number_format($p['amount'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $p['payment_method'] ?? '—'))); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($p['created_at'])); ?></td>
                    <td><span class="status-badge <?php echo $p['status'] === 'completed' ? 'success' : ($p['status'] === 'failed' ? 'danger' : 'pending'); ?>"><?php echo ucfirst($p['status']); ?></span></td>
                    <td>
                        <strong><?php echo htmlspecialchars(ucfirst($p['reconciliation_status'] ?? 'pending')); ?></strong>
                        <?php if (!empty($p['reconciliation_notes'])): ?>
                            <div style="color:#6B7280; font-size:12px; margin-top:4px;"><?php echo htmlspecialchars($p['reconciliation_notes']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($p['reconciled_at'])): ?>
                            <div style="color:#9CA3AF; font-size:12px; margin-top:4px;">Reconciled <?php echo date('M j, Y g:i A', strtotime($p['reconciled_at'])); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/admin/payments/view/<?php echo $p['id']; ?>" class="filter-btn" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="8" style="text-align:center; padding: 24px; color:#6B7280;">No payments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="payment-pagination" id="paymentPagination">
            <div>
                Viewing <?php echo count($payments ?? []); ?> of <?php echo (int)($paymentPagination['total_items'] ?? 0); ?> payments
            </div>
            <div class="payment-pagination-controls">
                <a class="filter-btn <?php echo ((int)($paymentPagination['current_page'] ?? 1) <= 1) ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($paymentPageUrl(max(1, (int)($paymentPagination['current_page'] ?? 1) - 1))); ?>">Previous</a>
                <span>Page <?php echo (int)($paymentPagination['current_page'] ?? 1); ?> of <?php echo max(1, (int)($paymentPagination['total_pages'] ?? 1)); ?></span>
                <a class="filter-btn <?php echo ((int)($paymentPagination['current_page'] ?? 1) >= (int)($paymentPagination['total_pages'] ?? 1)) ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($paymentPageUrl((int)($paymentPagination['current_page'] ?? 1) + 1)); ?>">Next</a>
            </div>
        </div>
    </div>

    <!-- M-Pesa Payments Tab -->
    <div class="tab-content" id="content-mpesa">
        <div class="table-header">
            <div class="filter-group">
                <a class="filter-btn primary" href="/admin/payments?payment_method=mpesa">
                    <i class="fas fa-mobile-alt"></i>
                    Show M-Pesa
                </a>
                <a class="filter-btn" href="<?php echo htmlspecialchars($paymentExportUrl); ?>">
                    <i class="fas fa-download"></i>
                    Export
                </a>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>M-Pesa Code</th>
                    <th>Member</th>
                    <th>Phone Number</th>
                    <th>Amount</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $mpesaPayments = array_filter($payments ?? [], fn($p) => ($p['payment_method'] ?? '') === 'mpesa' && !empty($p['mpesa_receipt_number'])); ?>
                <?php if (!empty($mpesaPayments)): ?>
                <?php foreach ($mpesaPayments as $p): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($p['mpesa_receipt_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($p['sender_phone'] ?? '—'); ?></td>
                    <td><strong>KSh <?php echo number_format($p['amount'], 2); ?></strong></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($p['created_at'])); ?></td>
                    <td><span class="status-badge <?php echo $p['status'] === 'completed' ? 'success' : 'pending'; ?>"><?php echo $p['status'] === 'completed' ? 'Confirmed' : ucfirst($p['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding: 24px; color:#6B7280;">No M-Pesa transactions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Reconciliation Tab -->
    <div class="tab-content" id="content-reconciliation">
        <?php
            $reconStats = $recon_stats ?? [];
            $unmatchedQueue = $unmatched_payments ?? [];
            $auditLogs = $audit_logs ?? [];
            $totalRecon = (int)($reconStats['total_payments'] ?? 0);
            $matchedRecon = (int)($reconStats['matched'] ?? 0);
            $manualRecon = (int)($reconStats['manual'] ?? 0);
            $unmatchedRecon = (int)($reconStats['unmatched'] ?? count($unmatchedQueue));
            $unmatchedAmount = (float)($reconStats['unmatched_amount'] ?? array_sum(array_column($unmatchedQueue, 'amount')));
            $matchedAmount = (float)($reconStats['matched_amount'] ?? 0);
            $matchRate = $totalRecon > 0 ? round((($matchedRecon + $manualRecon) / $totalRecon) * 100) : 0;
        ?>
        <div class="reconciliation-grid">
            <div class="reconciliation-card">
                <h4><i class="fas fa-link"></i> Matching Queue</h4>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Unmatched Payments</span>
                    <span class="reconciliation-value" style="color: <?php echo $unmatchedRecon > 0 ? '#DC2626' : '#059669'; ?>;"><?php echo number_format($unmatchedRecon); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Unmatched Amount</span>
                    <span class="reconciliation-value">KSh <?php echo number_format($unmatchedAmount, 2); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Needs Action</span>
                    <span class="reconciliation-value"><?php echo $unmatchedRecon > 0 ? 'Review queue' : 'None'; ?></span>
                </div>
            </div>

            <div class="reconciliation-card">
                <h4><i class="fas fa-check-circle"></i> Matched Payments</h4>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Auto Matched</span>
                    <span class="reconciliation-value"><?php echo number_format($matchedRecon); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Matched Amount</span>
                    <span class="reconciliation-value">KSh <?php echo number_format($matchedAmount, 2); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Manual Matches</span>
                    <span class="reconciliation-value"><?php echo number_format($manualRecon); ?></span>
                </div>
            </div>

            <div class="reconciliation-card">
                <h4><i class="fas fa-chart-pie"></i> Reconciliation Health</h4>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Total M-Pesa Records</span>
                    <span class="reconciliation-value"><?php echo number_format($totalRecon); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Resolved Rate</span>
                    <span class="reconciliation-value"><?php echo number_format($matchRate); ?>%</span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Status</span>
                    <span class="reconciliation-value"><?php echo $unmatchedRecon > 0 ? 'Action required' : 'Clear'; ?></span>
                </div>
            </div>
        </div>

        <div class="reconciliation-workspace">
            <div class="reconciliation-panel">
                <h4>What needs attention</h4>
                <p>Start here when payments do not automatically attach to a member. Review each transaction, find the correct member, add a note, and reconcile it.</p>
                <div class="reconciliation-queue">
                    <?php if (empty($unmatchedQueue)): ?>
                        <div style="color:#6B7280; padding:12px 0;">No unmatched payments are waiting for review.</div>
                    <?php else: ?>
                        <?php foreach (array_slice($unmatchedQueue, 0, 5) as $item): ?>
                            <div class="reconciliation-queue-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['mpesa_receipt_number'] ?? 'N/A'); ?></strong>
                                    <div style="color:#6B7280; font-size:12px; margin-top:4px;">
                                        <?php echo htmlspecialchars($item['paybill_account'] ?? 'No account'); ?> ·
                                        KSh <?php echo number_format((float)($item['amount'] ?? 0), 2); ?>
                                    </div>
                                </div>
                                <a class="filter-btn" href="/admin/payments/unmatched?search=<?php echo urlencode($item['mpesa_receipt_number'] ?? ''); ?>">Match</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="reconciliation-actions">
                    <a class="btn-action btn-primary" href="/admin/payments/unmatched">
                        <i class="fas fa-link"></i>
                        Review unmatched queue
                    </a>
                    <a class="filter-btn" href="/admin/payments-reconciliation">
                        Open reconciliation workspace
                    </a>
                </div>
            </div>

            <div class="reconciliation-panel">
                <h4>Recent manual activity</h4>
                <p>Latest reconciliations with admin notes for quick audit.</p>
                <?php if (empty($auditLogs)): ?>
                    <div style="color:#6B7280; padding:12px 0;">No manual reconciliation activity yet.</div>
                <?php else: ?>
                    <?php foreach (array_slice($auditLogs, 0, 4) as $log): ?>
                        <div class="reconciliation-queue-item">
                            <div>
                                <strong><?php echo htmlspecialchars($log['mpesa_receipt_number'] ?? ('Payment #' . ($log['payment_id'] ?? ''))); ?></strong>
                                <div style="color:#6B7280; font-size:12px; margin-top:4px;">
                                    <?php echo htmlspecialchars($log['notes'] ?? $log['reconciliation_notes'] ?? 'No note captured'); ?>
                                </div>
                            </div>
                            <span style="color:#9CA3AF; font-size:12px;"><?php echo htmlspecialchars(substr((string)($log['created_at'] ?? ''), 0, 10)); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Financial Dashboard Tab -->
    <!-- <div class="tab-content" id="content-financial">
        <div class="financial-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Payment Trends</h3>
                    <p class="chart-subtitle">Monthly collection patterns</p>
                </div>
                <canvas id="paymentTrendsChart" height="300"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Payment Methods</h3>
                    <p class="chart-subtitle">Distribution by method</p>
                </div>
                <canvas id="paymentMethodsChart" height="300"></canvas>
            </div>
        </div>
    </div> -->

    <!-- Reports Tab -->
    <div class="tab-content" id="content-reports">
        <h4 style="margin-bottom: 20px; color: #1F2937;">Generate Payment Reports</h4>
        
        <div class="reconciliation-grid">
            <div class="reconciliation-card" style="cursor: pointer;" onclick="generateReport('monthly')">
                <h4><i class="fas fa-calendar-alt"></i> Monthly Report</h4>
                <p style="font-size: 13px; color: #6B7280; margin-top: 8px;">Comprehensive monthly payment summary</p>
                <button class="btn-action btn-primary" style="margin-top: 12px; width: 100%;">
                    <i class="fas fa-download"></i>
                    Generate Report
                </button>
            </div>

            <div class="reconciliation-card" style="cursor: pointer;" onclick="generateReport('member')">
                <h4><i class="fas fa-user"></i> Member Report</h4>
                <p style="font-size: 13px; color: #6B7280; margin-top: 8px;">Individual member payment history</p>
                <button class="btn-action btn-primary" style="margin-top: 12px; width: 100%;">
                    <i class="fas fa-download"></i>
                    Generate Report
                </button>
            </div>

            <div class="reconciliation-card" style="cursor: pointer;" onclick="generateReport('financial')">
                <h4><i class="fas fa-chart-line"></i> Financial Report</h4>
                <p style="font-size: 13px; color: #6B7280; margin-top: 8px;">Detailed financial analysis and trends</p>
                <button class="btn-action btn-primary" style="margin-top: 12px; width: 100%;">
                    <i class="fas fa-download"></i>
                    Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const paymentFiltersForm = document.getElementById('paymentFiltersForm');
const paymentSearchInput = document.getElementById('paymentSearchInput');
let paymentSearchTimer = null;

paymentSearchInput?.addEventListener('input', function() {
    clearTimeout(paymentSearchTimer);
    paymentSearchTimer = setTimeout(() => {
        paymentFiltersForm?.submit();
    }, 450);
});

paymentFiltersForm?.querySelectorAll('select, input[type="date"]').forEach((field) => {
    field.addEventListener('change', () => paymentFiltersForm.submit());
});

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById('content-' + tabName).classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function generateReport(type) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    if (type === 'member') {
        // Show member selection modal
        const modal = new bootstrap.Modal(document.getElementById('memberReportModal'));
        document.getElementById('memberSearchInput').value = '';
        loadMemberList('');
        modal.show();
        return;
    }
    let url = '/admin/reports/export';
    let params = {};
    let filename = '';
    if (type === 'monthly') {
        params = {
            format: 'pdf',
            type: 'payments',
            date_from: yyyy + '-' + mm + '-01',
            date_to: yyyy + '-' + mm + '-' + dd
        };
        filename = 'monthly-payments-report-' + yyyy + '-' + mm + '-' + dd + '.pdf';
    } else if (type === 'financial') {
        params = {
            format: 'pdf',
            type: 'financial',
            date_from: yyyy + '-01-01',
            date_to: yyyy + '-' + mm + '-' + dd
        };
        filename = 'financial-report-' + yyyy + '-' + mm + '-' + dd + '.pdf';
    }
    if (!url) return;
    ShenaApp.showNotification('Generating report...', 'info', 2000);
    fetch(url + '?' + new URLSearchParams(params), {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to generate report');
        return response.blob();
    })
    .then(blob => {
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        ShenaApp.showNotification('Report downloaded!', 'success', 2000);
    })
    .catch(() => {
        ShenaApp.showNotification('Failed to generate report', 'error', 3000);
    });
}

// Member search and report logic
function loadMemberList(query) {
    const container = document.getElementById('memberListContainer');
    container.innerHTML = '<div class="text-center py-3">Loading...</div>';
    fetch('/admin/api/members?search=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(members => {
            if (!Array.isArray(members) || members.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted">No members found.</div>';
                return;
            }
            container.innerHTML = members.map(member => `
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="generateMemberReport(${member.id}, '${member.first_name} ${member.last_name}')">
                    <div>
                        <strong>${member.member_number}</strong> - ${member.first_name} ${member.last_name}<br>
                        <small>${member.email} | ${member.phone}</small>
                    </div>
                    <span class="badge bg-primary">Select</span>
                </div>
            `).join('');
        })
        .catch(() => {
            container.innerHTML = '<div class="text-center py-3 text-danger">Failed to load members.</div>';
        });
}

document.getElementById('memberSearchInput').addEventListener('input', function() {
    loadMemberList(this.value);
});

function generateMemberReport(memberId, memberName) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const url = '/admin/reports/export';
    const params = {
        format: 'pdf',
        type: 'member_payments',
        member_id: memberId,
        date_from: yyyy + '-01-01',
        date_to: yyyy + '-' + mm + '-' + dd
    };
    const filename = 'member-' + memberName.replace(/\s+/g, '-') + '-payments-report-' + yyyy + '-' + mm + '-' + dd + '.pdf';
    ShenaApp.showNotification('Generating report for ' + memberName + '...', 'info', 2000);
    fetch(url + '?' + new URLSearchParams(params), {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to generate report');
        return response.blob();
    })
    .then(blob => {
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        ShenaApp.showNotification('Report downloaded!', 'success', 2000);
        bootstrap.Modal.getInstance(document.getElementById('memberReportModal')).hide();
    })
    .catch(() => {
        ShenaApp.showNotification('Failed to generate report', 'error', 3000);
    });
}

// Payment Trends Chart
const ctx1 = document.getElementById('paymentTrendsChart');
if (ctx1) {
    new Chart(ctx1.getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Payments (KSh)',
                data: [380000, 420000, 445000, 410000, 480000, 450000],
                borderColor: '#7F3D9E',
                backgroundColor: 'rgba(127, 61, 158, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KSh ' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });
}

// Payment Methods Chart
const ctx2 = document.getElementById('paymentMethodsChart');
if (ctx2) {
    new Chart(ctx2.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['M-Pesa', 'Bank Transfer', 'Cash'],
            datasets: [{
                data: [85, 10, 5],
                backgroundColor: ['#7F3D9E', '#3B82F6', '#F59E0B']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
