<?php 
$agent = $agent ?? [];
$stats = $stats ?? ['total_members' => 0];
$commissions = $commissions ?? [];
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .agent-details-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
        min-width: 0;
        overflow-x: hidden;
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

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-secondary {
        background: white;
        color: #374151;
        border: 1px solid #D1D5DB;
    }

    .btn-warning {
        background: #F59E0B;
        color: white;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        font-size: 32px;
        font-weight: 700;
        background: linear-gradient(135deg, #7F3D9E 0%, #B91C1C 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 24px;
    }

    .agent-info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 16px 20px;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        display: flex;
        justify-content: space-between;
align-items: center;
    }

    .card-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.active {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-badge.suspended {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-badge.inactive {
        background: #F3F4F6;
        color: #6B7280;
    }

    .card-body {
        padding: 20px;
    }

    .agent-avatar {
        text-align: center;
        margin-bottom: 20px;
    }

    .avatar-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 48px;
        margin-bottom: 12px;
    }

    .agent-name {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .agent-number {
        color: #9CA3AF;
        font-size: 14px;
    }

    .info-item {
        margin-bottom: 16px;
    }

    .info-label {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        color: #1F2937;
    }

    .divider {
        border: 0;
        border-top: 1px solid #E5E7EB;
        margin: 16px 0;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .data-table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
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

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9CA3AF;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.3;
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-wrap: wrap;
            gap: 12px;
        }
        .header-actions {
            flex-wrap: wrap;
            width: 100%;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
        .page-title { font-size: 20px; }
        .header-actions .btn { flex: 1 1 auto; justify-content: center; }
    }
</style>

<div class="agent-details-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user-tie"></i> Agent Details - <?= htmlspecialchars($agent['agent_number'] ?? 'N/A') ?>
        </h1>
        <div class="header-actions">
            <a href="/admin/agents" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Agents
            </a>
            <a href="/admin/agents/edit/<?= $agent['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Agent
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Members Recruited</div>
            <div class="stat-value"><?= $stats['total_members'] ?? 0 ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Commission</div>
            <div class="stat-value">KES <?= number_format($stats['paid_commission'] ?? 0, 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Commission</div>
            <div class="stat-value">KES <?= number_format($stats['pending_commission'] ?? 0, 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Members</div>
            <div class="stat-value"><?= $stats['active_members'] ?? 0 ?></div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Left: Agent Info -->
        <div>
            <div class="agent-info-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-info-circle"></i> Agent Information
                    </span>
                    <span class="status-badge <?= $agent['status'] ?? 'active' ?>">
                        <?= ucfirst($agent['status'] ?? 'Active') ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="agent-avatar">
                        <div class="avatar-circle">
                            <?= strtoupper(substr($agent['first_name'] ?? 'A', 0, 1) . substr($agent['last_name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="agent-name"><?= htmlspecialchars(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')) ?></div>
                        <div class="agent-number">Agent #<?= htmlspecialchars($agent['agent_number'] ?? 'N/A') ?></div>
                    </div>
                    
                    <hr class="divider">
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-id-card"></i> National ID</div>
                        <div class="info-value"><?= htmlspecialchars($agent['national_id'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone</div>
                        <div class="info-value"><?= htmlspecialchars($agent['phone'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                        <div class="info-value"><?= htmlspecialchars($agent['email'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> County</div>
                        <div class="info-value"><?= htmlspecialchars($agent['county'] ?? 'N/A') ?></div>
                    </div>
                    
                    <?php if (!empty($agent['address'])): ?>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-home"></i> Address</div>
                        <div class="info-value"><?= htmlspecialchars($agent['address']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-percentage"></i> Commission Rate</div>
                        <div class="info-value"><?= htmlspecialchars($agent['commission_rate'] ?? 0) ?>%</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Registered</div>
                        <div class="info-value"><?= date('M j, Y', strtotime($agent['registration_date'] ?? 'now')) ?></div>
                    </div>
                    
                    <hr class="divider">
                    
                    <div class="action-buttons">
                        <?php if (($agent['status'] ?? 'active') === 'active'): ?>
                            <form method="POST" action="/admin/agents/status/<?= $agent['id'] ?>" id="agent-suspend-form">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <input type="hidden" name="status" value="suspended">
                                <button type="button" onclick="confirmAgentSuspend()" class="btn btn-warning" style="width: 100%;">
                                    <i class="fas fa-ban"></i> Suspend Agent
                                </button>
                            </form>
                        <?php elseif (($agent['status'] ?? '') === 'suspended'): ?>
                            <form method="POST" action="/admin/agents/status/<?= $agent['id'] ?>" id="agent-activate-form">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <input type="hidden" name="status" value="active">
                                <button type="button" onclick="confirmAgentActivate()" class="btn btn-primary" style="width: 100%;">
                                    <i class="fas fa-check"></i> Activate Agent
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="/admin/agents/edit/<?= $agent['id'] ?>" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-edit"></i> Edit Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <?php if (!empty($agent['bank_account']) || !empty($agent['bank_name'])): ?>
            <div class="agent-info-card" style="margin-top: 24px;">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-university"></i> Bank Details
                    </span>
                </div>
                <div class="card-body">
                    <?php if (!empty($agent['bank_name'])): ?>
                    <div class="info-item">
                        <div class="info-label">Bank Name</div>
                        <div class="info-value"><?= htmlspecialchars($agent['bank_name']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($agent['bank_account'])): ?>
                    <div class="info-item">
                        <div class="info-label">Account Number</div>
                        <div class="info-value"><?= htmlspecialchars($agent['bank_account']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($agent['bank_branch'])): ?>
                    <div class="info-item">
                        <div class="info-label">Branch</div>
                        <div class="info-value"><?= htmlspecialchars($agent['bank_branch']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Tables -->
        <div>
            <!-- Payout Requests Section -->
            <div class="data-table-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-hand-holding-usd"></i> Payout Requests
                    </span>
                    <span style="background: #F3E8FF; color: #7F20B0; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Available: KES <?= number_format($available_balance ?? 0, 2) ?>
                    </span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date Requested</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payout_requests)): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p>No payout requests yet</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payout_requests as $request): ?>
                                <tr>
                                    <td><?= date('M j, Y H:i', strtotime($request['requested_at'])) ?></td>
                                    <td style="font-weight: 700; color: #1F2937;">KES <?= number_format($request['amount'], 2) ?></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $request['payment_method'])) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = match($request['status']) {
                                            'requested' => 'warning',
                                            'processing' => 'info',
                                            'paid' => 'success',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusLabel = match($request['status']) {
                                            'requested' => 'Requested',
                                            'processing' => 'Processing',
                                            'paid' => 'Paid',
                                            'rejected' => 'Rejected',
                                            default => ucfirst($request['status'])
                                        };
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'requested'): ?>
                                            <button onclick="showProcessModal(<?= $request['id'] ?>, <?= $request['amount'] ?>)" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                                <i class="fas fa-cog"></i> Process
                                            </button>
                                        <?php elseif ($request['status'] === 'processing'): ?>
                                            <form method="POST" action="/admin/payouts/<?= $request['id'] ?>/process" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                                <input type="hidden" name="action" value="mark_paid">
                                                <input type="hidden" name="redirect_to_agent" value="1">
                                                <button type="submit" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Mark this payout as paid?')">
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

            <!-- Commission History -->
            <div class="data-table-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-money-bill-wave"></i> Commission History
                    </span>
                    <a href="/admin/commissions?agent_id=<?= $agent['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                        View All
                    </a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($commissions)): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p>No commission records yet</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($commissions, 0, 10) as $commission): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($commission['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($commission['member_number'] ?? 'N/A') ?></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $commission['commission_type'])) ?></td>
                                    <td>KES <?= number_format($commission['amount'], 2) ?></td>
                                    <td style="color: #059669; font-weight: 700;">KES <?= number_format($commission['commission_amount'], 2) ?></td>
                                    <td>
                                        <span class="status-badge <?= $commission['status'] ?>">
                                            <?= ucfirst($commission['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- Recent Members -->
            <div class="data-table-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-users"></i> Recent Members Recruited
                    </span>
                    <a href="/admin/members?agent_id=<?= $agent['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                        View All
                    </a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Member #</th>
                                <th>Name</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['recent_members'])): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-user-slash"></i>
                                            <p>No members recruited yet</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stats['recent_members'] as $member): ?>
                                <tr>
                                    <td>
                                        <a href="/admin/members/view/<?= $member['id'] ?>" style="color: #7F3D9E; font-weight: 600;">
                                            <?= htmlspecialchars($member['member_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                    <td><span class="status-badge active"><?= ucfirst($member['package']) ?></span></td>
                                    <td>
                                        <span class="status-badge <?= $member['status'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $member['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($member['registration_date'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmAgentSuspend() {
    ShenaApp.confirmAction(
        'Are you sure you want to suspend this agent? They will lose access to their account and cannot recruit new members.',
        function() {
            document.getElementById('agent-suspend-form').submit();
        },
        null,
        { type: 'danger', title: 'Suspend Agent', confirmText: 'Yes, Suspend' }
    );
}

function confirmAgentActivate() {
    ShenaApp.confirmAction(
        'Activate this agent and restore their access?',
        function() {
            document.getElementById('agent-activate-form').submit();
        },
        null,
        { type: 'success', title: 'Activate Agent', confirmText: 'Yes, Activate' }
    );
}

// Payout Processing Modal
function showProcessModal(payoutId, amount) {
    const modalHtml = `
        <div id="payout-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <h3 style="margin: 0 0 16px 0; font-size: 18px; color: #1F2937;">
                    <i class="fas fa-hand-holding-usd" style="color: #7F20B0;"></i> Process Payout Request
                </h3>
                <p style="margin: 0 0 20px 0; color: #6B7280; font-size: 14px;">
                    Amount: <strong style="color: #1F2937; font-size: 16px;">KES ${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                </p>
                <form method="POST" action="/admin/payouts/${payoutId}/process" id="process-payout-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="redirect_to_agent" value="1">
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
</script>


<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
