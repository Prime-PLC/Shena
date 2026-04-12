<?php 
$commissions = $commissions ?? [];
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .commissions-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 24px;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .page-subtitle {
        font-size: 13px;
        color: #9CA3AF;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .stat-label {
        font-size: 13px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1F2937;
    }

    .stat-value.highlight {
        background: linear-gradient(135deg, #7F3D9E 0%, #B91C1C 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .commissions-table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header {
        padding: 20px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #E5E7EB;
        background: white;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        border-color: #7F3D9E;
    }

    .commissions-table {
        width: 100%;
        border-collapse: collapse;
    }

    .commissions-table th {
        background: #F9FAFB;
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .commissions-table td {
        padding: 16px;
        border-top: 1px solid #E5E7EB;
        font-size: 14px;
    }

    .agent-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agent-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 14px;
    }

    .agent-details {
        flex: 1;
    }

    .agent-name {
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 2px;
    }

    .agent-number {
        font-size: 12px;
        color: #6B7280;
    }

    .amount-value {
        font-size: 16px;
        font-weight: 700;
        color: #059669;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.pending {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-badge.approved {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-badge.paid {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-approve {
        background: #059669;
        color: white;
    }

    .btn-approve:hover {
        background: #047857;
    }

    .btn-pay {
        background: #7F3D9E;
        color: white;
    }

    .btn-pay:hover {
        background: #6D28D9;
    }

    .btn-view {
        background: #F3F4F6;
        color: #374151;
    }

    .btn-view:hover {
        background: #E5E7EB;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6B7280;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .empty-state h3 {
        font-size: 18px;
        margin-bottom: 8px;
        color: #1F2937;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
    }

    .modal-header {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .btn-cancel {
        padding: 10px 20px;
        background: #F3F4F6;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    .btn-submit {
        padding: 10px 20px;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .commissions-container { padding: 16px !important; }
        .filter-bar, .filters { flex-direction: column !important; gap: 8px; }
        .filter-bar > *, .filters > * { width: 100% !important; }

        /* Table scrolling — override overflow:hidden on card */
        .commissions-table-card { overflow: visible !important; }
        .commissions-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .commissions-table thead,
        .commissions-table tbody,
        .commissions-table tr { min-width: 480px; }
    }
</style>

<div class="commissions-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Commission Management</h1>
        <p class="page-subtitle">Review and process agent commission payments</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Pending</div>
            <div class="stat-value highlight">
                <?php 
                $pending = array_filter($commissions, fn($c) => $c['status'] === 'pending');
                echo count($pending);
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Amount</div>
            <div class="stat-value">
                KES <?php 
                $total = array_sum(array_column($pending, 'commission_amount'));
                echo number_format($total, 2);
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">This Month</div>
            <div class="stat-value">
                <?php 
                $thisMonth = array_filter($commissions, function($c) {
                    return date('Y-m', strtotime($c['created_at'])) === date('Y-m');
                });
                echo count($thisMonth);
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Processed</div>
            <div class="stat-value">
                <?php 
                $processed = array_filter($commissions, fn($c) => $c['status'] !== 'pending');
                echo count($processed);
                ?>
            </div>
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="commissions-table-card">
        <div class="table-header">
            <div class="table-title">Commission Requests</div>
            <div class="filter-buttons">
                <?php $activeStatus = $status ?? ''; ?>
                <a class="filter-btn <?php echo $activeStatus === '' ? 'active' : ''; ?>" href="/admin/commissions">All</a>
                <a class="filter-btn <?php echo $activeStatus === 'pending' ? 'active' : ''; ?>" href="/admin/commissions?status=pending">Pending</a>
                <a class="filter-btn <?php echo $activeStatus === 'approved' ? 'active' : ''; ?>" href="/admin/commissions?status=approved">Approved</a>
                <a class="filter-btn <?php echo $activeStatus === 'paid' ? 'active' : ''; ?>" href="/admin/commissions?status=paid">Paid</a>
                <a class="filter-btn" href="/admin/commissions/export<?php echo $activeStatus !== '' ? '?status=' . urlencode($activeStatus) : ''; ?>">Export CSV</a>
                <form method="POST" action="/admin/commissions/approve-all" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                    <button class="filter-btn" type="submit" onclick="return confirm('Approve all pending commissions?');">Approve All</button>
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="commissions-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Member Number</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($commissions)): ?>
                        <?php foreach ($commissions as $commission): ?>
                        <tr data-status="<?php echo $commission['status']; ?>">
                            <td>
                                <div class="agent-info">
                                    <div class="agent-avatar">
                                        <?php 
                                        $name = ($commission['first_name'] ?? 'A') . ' ' . ($commission['last_name'] ?? 'A');
                                        echo strtoupper(substr($commission['first_name'] ?? 'A', 0, 1) . substr($commission['last_name'] ?? 'A', 0, 1));
                                        ?>
                                    </div>
                                    <div class="agent-details">
                                        <div class="agent-name"><?php echo htmlspecialchars($name); ?></div>
                                        <div class="agent-number"><?php echo htmlspecialchars($commission['agent_number'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($commission['member_number'] ?? 'N/A'); ?></td>
                            <td><span class="amount-value">KES <?php echo number_format($commission['commission_amount'] ?? 0, 2); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($commission['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $commission['status']; ?>">
                                    <?php echo ucfirst($commission['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($commission['status'] === 'pending'): ?>
                                        <form method="POST" action="/admin/commissions/approve/<?php echo (int)$commission['id']; ?>" style="display: inline;" onsubmit="return confirm('Approve this commission?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                            <button class="btn-action btn-approve" type="submit">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                    <?php elseif ($commission['status'] === 'approved'): ?>
                                        <button class="btn-action btn-pay" type="button" onclick="openPayModal(<?php echo (int)$commission['id']; ?>)">
                                            <i class="fas fa-money-bill"></i> Mark Paid
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-action btn-view" onclick="window.location.href='/admin/agents/view/<?php echo $commission['agent_id']; ?>'">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-money-check-alt"></i>
                                    <h3>No Commissions Found</h3>
                                    <p>Commission requests will appear here when agents earn commissions</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-header">Mark Commission as Paid</h2>
        <form id="paymentForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <option value="">Select Method</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Reference</label>
                <input type="text" name="payment_reference" class="form-input" placeholder="Enter reference number" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closePayModal()">Cancel</button>
                <button type="submit" class="btn-submit">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayModal(id) {
    document.getElementById('paymentForm').action = `/admin/commissions/pay/${id}`;
    document.getElementById('paymentModal').classList.add('active');
}

function closePayModal() {
    document.getElementById('paymentModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('paymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePayModal();
    }
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
