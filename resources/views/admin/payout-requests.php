<?php 
$payoutRequests = $payout_requests ?? [];
$stats = $stats ?? ['total' => 0, 'requested' => 0, 'processing' => 0, 'paid' => 0, 'rejected' => 0, 'total_amount' => 0];
$statusFilter = $status_filter ?? 'all';
?>

<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .payouts-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
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
        font-size: 28px;
        font-weight: 700;
        background: linear-gradient(135deg, #7F3D9E 0%, #B91C1C 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        background: white;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .filter-btn:hover, .filter-btn.active {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        border-color: transparent;
    }

    .data-table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        padding: 16px 20px;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 12px 12px 0 0;
    }

    .card-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #F9FAFB;
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table td {
        padding: 16px;
        border-top: 1px solid #E5E7EB;
        font-size: 14px;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.requested {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-badge.processing {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .status-badge.paid {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-badge.rejected {
        background: #FEE2E2;
        color: #991B1B;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-success {
        background: #10B981;
        color: white;
    }

    .btn-danger {
        background: #EF4444;
        color: white;
    }

    .btn-secondary {
        background: white;
        color: #374151;
        border: 1px solid #D1D5DB;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9CA3AF;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .agent-link {
        color: #7F3D9E;
        font-weight: 600;
        text-decoration: none;
    }

    .agent-link:hover {
        text-decoration: underline;
    }

    .amount {
        font-weight: 700;
        color: #1F2937;
    }

    @media (max-width: 768px) {
        .filters-bar, .filters-form { flex-direction: column !important; gap: 8px; }
        .filters-form > * { width: 100% !important; }
    }
</style>

<div class="payouts-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-hand-holding-usd"></i> Payout Requests
        </h1>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Requests</div>
            <div class="stat-value"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Requested</div>
            <div class="stat-value" style="color: #F59E0B;"><?= $stats['requested'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Processing</div>
            <div class="stat-value" style="color: #3B82F6;"><?= $stats['processing'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paid</div>
            <div class="stat-value" style="color: #10B981;"><?= $stats['paid'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Paid Amount</div>
            <div class="stat-value">KES <?= number_format($stats['total_amount'], 2) ?></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <span style="font-weight: 600; color: #374151; margin-right: 8px;">Filter:</span>
        <a href="/admin/payouts" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
        <a href="/admin/payouts?status=requested" class="filter-btn <?= $statusFilter === 'requested' ? 'active' : '' ?>">Requested</a>
        <a href="/admin/payouts?status=processing" class="filter-btn <?= $statusFilter === 'processing' ? 'active' : '' ?>">Processing</a>
        <a href="/admin/payouts?status=paid" class="filter-btn <?= $statusFilter === 'paid' ? 'active' : '' ?>">Paid</a>
        <a href="/admin/payouts?status=rejected" class="filter-btn <?= $statusFilter === 'rejected' ? 'active' : '' ?>">Rejected</a>
    </div>

    <!-- Payout Requests Table -->
    <div class="data-table-card">
        <div class="card-header">
            <span class="card-title">
                <i class="fas fa-list"></i> All Payout Requests
            </span>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agent</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payoutRequests)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No payout requests found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payoutRequests as $request): ?>
                        <tr>
                            <td>#<?= $request['id'] ?></td>
                            <td>
                                <a href="/admin/agents/view/<?= $request['agent_id'] ?>" class="agent-link">
                                    <?= htmlspecialchars(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '')) ?>
                                </a>
                                <br>
                                <small style="color: #9CA3AF;"><?= htmlspecialchars($request['agent_number'] ?? '') ?></small>
                            </td>

                            <td class="amount">KES <?= number_format($request['amount'], 2) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $request['payment_method'])) ?></td>
                            <td>
                                <span class="status-badge <?= $request['status'] ?>">
                                    <?= ucfirst($request['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y H:i', strtotime($request['requested_at'])) ?></td>
                            <td>
                                <?php if ($request['status'] === 'requested'): ?>
                                    <button onclick="showProcessModal(<?= $request['id'] ?>, <?= $request['amount'] ?>, '<?= htmlspecialchars(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '')) ?>')" class="btn btn-primary">

                                        <i class="fas fa-cog"></i> Process
                                    </button>
                                <?php elseif ($request['status'] === 'processing'): ?>
                                    <form method="POST" action="/admin/payouts/<?= $request['id'] ?>/process" style="display: inline;" id="mark-paid-form-<?= $request['id'] ?>">
                                        <input type="hidden" name="action" value="mark_paid">
                                        <button type="button" class="btn btn-success" onclick="confirmMarkPaid(<?= $request['id'] ?>, <?= $request['amount'] ?>, '<?= htmlspecialchars(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '')) ?>')">
                                            <i class="fas fa-check"></i> Mark Paid
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-size: 12px;"><?= ucfirst($request['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Process Modal -->
<script>
function showProcessModal(payoutId, amount, agentName) {
    const modalHtml = `
        <div id="payout-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <h3 style="margin: 0 0 16px 0; font-size: 18px; color: #1F2937;">
                    <i class="fas fa-hand-holding-usd" style="color: #7F20B0;"></i> Process Payout Request
                </h3>
                <p style="margin: 0 0 8px 0; color: #6B7280; font-size: 14px;">
                    Agent: <strong style="color: #1F2937;">${agentName}</strong>
                </p>
                <p style="margin: 0 0 20px 0; color: #6B7280; font-size: 14px;">
                    Amount: <strong style="color: #1F2937; font-size: 16px;">KES ${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                </p>
                <form method="POST" action="/admin/payouts/${payoutId}/process" id="process-payout-form">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #6B7280; margin-bottom: 6px; text-transform: uppercase;">Action</label>
                        <select name="action" id="payout-action" style="width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px;" onchange="togglePayoutFields()">
                            <option value="approve">Approve & Process</option>
                            <option value="reject">Reject Request</option>
                        </select>
                    </div>
                    <div id="payment-ref-field" style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #6B7280; margin-bottom: 6px; text-transform: uppercase;">Payment Reference</label>
                        <input type="text" name="payment_reference" placeholder="e.g., MPESA-123456789" style="width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #6B7280; margin-bottom: 6px; text-transform: uppercase;">Admin Notes</label>
                        <textarea name="admin_notes" rows="3" placeholder="Add notes about this payout..." style="width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                    </div>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" onclick="closePayoutModal()" style="padding: 10px 20px; border: 1px solid #D1D5DB; background: white; color: #374151; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancel</button>
                        <button type="submit" id="submit-btn" style="padding: 10px 20px; border: none; background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: white; border-radius: 6px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function togglePayoutFields() {
    const action = document.getElementById('payout-action').value;
    const refField = document.getElementById('payment-ref-field');
    const submitBtn = document.getElementById('submit-btn');
    
    if (action === 'reject') {
        refField.style.display = 'none';
        submitBtn.innerHTML = '<i class="fas fa-times"></i> Reject';
        submitBtn.style.background = '#DC2626';
    } else {
        refField.style.display = 'block';
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
        submitBtn.style.background = 'linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%)';
    }
}

function closePayoutModal() {
    const modal = document.getElementById('payout-modal');
    if (modal) modal.remove();
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('payout-modal');
    if (modal && e.target === modal) {
        closePayoutModal();
    }
});

// Confirm Mark Paid using ShenaApp standard design
function confirmMarkPaid(payoutId, amount, agentName) {
    const message = `Mark payout of KES ${amount.toLocaleString('en-US', {minimumFractionDigits: 2})} for ${agentName} as paid?`;
    ShenaApp.confirmAction(
        message,
        function() {
            document.getElementById('mark-paid-form-' + payoutId).submit();
        },
        null,
        { type: 'success', title: 'Confirm Payment', confirmText: 'Yes, Mark as Paid' }
    );
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
