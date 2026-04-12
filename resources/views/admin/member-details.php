<?php 
$member = $member ?? [];
$stats = $stats ?? [];
$payments = $payments ?? [];
$beneficiaries = $beneficiaries ?? [];
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .member-details-container {
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

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #374151;
        border: 1px solid #D1D5DB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
    }

    .btn-warning {
        background: #F59E0B;
        color: white;
    }

    .btn-danger {
        background: #EF4444;
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

    .member-info-card {
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

    .status-badge.grace_period {
        background: #FEE2E2;
        color: #991B1B;
    }

    .card-body {
        padding: 20px;
    }

    .member-avatar {
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

    .member-name {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .member-number {
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

    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr !important;
        }

        /* Force min-width so .table-container overflow-x:auto triggers scrolling */
        .data-table {
            min-width: 480px;
        }
    }
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="member-details-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user"></i> Member Details - <?= htmlspecialchars($member['member_number'] ?? 'N/A') ?>
        </h1>
        <div class="header-actions">
            <a href="/admin/members" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Members
            </a>
            <a href="/admin/members/edit/<?= $member['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Member
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Contributions</div>
            <div class="stat-value">KES <?= number_format($stats['total_contributions'] ?? 0, 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Last Payment</div>
            <div class="stat-value"><?= !empty($stats['last_payment_date']) ? date('M j, Y', strtotime($stats['last_payment_date'])) : 'N/A' ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Membership Duration</div>
            <div class="stat-value"><?= $stats['membership_months'] ?? 0 ?> mon</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Beneficiaries</div>
            <div class="stat-value"><?= count($beneficiaries) ?></div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Left: Member Info -->
        <div>
            <div class="member-info-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-info-circle"></i> Member Information
                    </span>
                    <span class="status-badge <?= $member['status'] ?? 'active' ?>">
                        <?= ucfirst(str_replace('_', ' ', $member['status'] ?? 'Active')) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="member-avatar">
                        <div class="avatar-circle">
                            <?= strtoupper(substr($member['first_name'] ?? 'M', 0, 1) . substr($member['last_name'] ?? 'M', 0, 1)) ?>
                        </div>
                        <div class="member-name"><?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?></div>
                        <div class="member-number">Member #<?= htmlspecialchars($member['member_number'] ?? 'N/A') ?></div>
                    </div>
                    
                    <hr class="divider">
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-id-card"></i> National ID</div>
                        <div class="info-value"><?= htmlspecialchars($member['id_number'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone</div>
                        <div class="info-value"><?= htmlspecialchars($member['phone'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                        <div class="info-value"><?= htmlspecialchars($member['email'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> County</div>
                        <div class="info-value"><?= htmlspecialchars($member['county'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-box"></i> Package</div>
                        <div class="info-value"><?= ucfirst($member['package'] ?? 'Basic') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Registered</div>
                        <div class="info-value">
                            <?php 
                            $regDate = $member['registration_date'] ?? $member['created_at'] ?? null;
                            echo $regDate ? date('M j, Y', strtotime($regDate)) : 'N/A';
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user-tie"></i> Recruited By</div>
                        <div class="info-value">
                            <?php $agentNumber = htmlspecialchars($member['agent_number'] ?? 'N/A'); ?>
                            <?php $agentPhone = htmlspecialchars($member['agent_phone'] ?? 'N/A'); ?>
                            <?php $agentEmail = htmlspecialchars($member['agent_email'] ?? 'N/A'); ?>
                            <?php if (!empty($member['agent_id'])): ?>
                                <a href="/admin/agents/view/<?= $member['agent_id'] ?>" style="color: #7F3D9E;">
                                    Agent #<?= $agentNumber ?>
                                </a>
                            <?php else: ?>
                                Agent #<?= $agentNumber ?>
                            <?php endif; ?>
                            <div style="font-size:13px; color:#6B7280; margin-top:6px;">
                                Phone: <?= $agentPhone ?> &nbsp; | &nbsp; Email: <?= $agentEmail ?>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="divider">
                    
                    <div class="action-buttons">
                        <?php if (($member['status'] ?? 'active') === 'active'): ?>
                            <form method="POST" action="/admin/members/suspend/<?= $member['id'] ?>" id="suspend-form">
                                <button type="button" onclick="confirmSuspend()" class="btn btn-warning" style="width: 100%;">
                                    <i class="fas fa-ban"></i> Suspend Member
                                </button>
                            </form>
                        <?php elseif (($member['status'] ?? '') === 'suspended'): ?>
                            <form method="POST" action="/admin/members/activate/<?= $member['id'] ?>" id="activate-form">
                                <button type="button" onclick="confirmActivate()" class="btn btn-primary" style="width: 100%;">
                                    <i class="fas fa-check"></i> Activate Member
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="/admin/members/edit/<?= $member['id'] ?>" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-edit"></i> Edit Details
                        </a>
                        
                        <a href="/admin/payments?member_id=<?= $member['id'] ?>" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-money-bill-wave"></i> View Payments
                        </a>

                        <button type="button" onclick="openAdminPayModal()" class="btn btn-primary" style="width: 100%; background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                            <i class="fas fa-hand-holding-usd"></i> Collect Payment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Next of Kin -->
            <?php if (!empty($member['nok_name'])): ?>
            <div class="member-info-card" style="margin-top: 24px;">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-users"></i> Next of Kin
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Name</div>
                        <div class="info-value"><?= htmlspecialchars($member['nok_name']) ?></div>
                    </div>
                    
                    <?php if (!empty($member['nok_relationship'])): ?>
                    <div class="info-item">
                        <div class="info-label">Relationship</div>
                        <div class="info-value"><?= htmlspecialchars($member['nok_relationship']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($member['nok_phone'])): ?>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?= htmlspecialchars($member['nok_phone']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Tables -->
        <div>
            <!-- Payment History -->
            <div class="data-table-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-money-bill-wave"></i> Payment History
                    </span>
                    <a href="/admin/payments?member_id=<?= $member['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                        View All
                    </a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p>No payment records yet</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($payments, 0, 10) as $payment): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                    <td style="font-family: monospace;"><?= htmlspecialchars($payment['transaction_id'] ?? 'N/A') ?></td>
                                    <td style="color: #059669; font-weight: 700;">KES <?= number_format($payment['amount'], 2) ?></td>
                                    <td><?= ucfirst($payment['payment_method'] ?? 'M-Pesa') ?></td>
                                    <td>
                                        <span class="status-badge <?= $payment['status'] ?? 'completed' ?>">
                                            <?= ucfirst($payment['status'] ?? 'Completed') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Beneficiaries -->
            <div class="data-table-card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-heart"></i> Registered Beneficiaries
                    </span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>ID Number</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($beneficiaries)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="fas fa-user-slash"></i>
                                            <p>No beneficiaries registered</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($beneficiaries as $beneficiary): ?>
                                <tr>
                                    <td><?= htmlspecialchars($beneficiary['full_name'] ?? ($beneficiary['name'] ?? 'N/A')) ?></td>
                                    <td><?= htmlspecialchars($beneficiary['relationship'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($beneficiary['id_number'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($beneficiary['phone_number'] ?? ($beneficiary['phone'] ?? 'N/A')) ?></td>
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

<!-- Collect Payment Modal -->
<div class="modal fade" id="adminPayModal" tabindex="-1" aria-labelledby="adminPayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; border: none; padding: 20px 24px;">
                <div>
                    <h5 class="modal-title" id="adminPayModalLabel" style="font-weight: 700; margin: 0;">
                        <i class="fas fa-hand-holding-usd" style="margin-right: 8px;"></i>Send M-Pesa Payment Request
                    </h5>
                    <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.85;">
                        Member: <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>
                        &nbsp;(<?= htmlspecialchars($member['member_number'] ?? '') ?>)
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div id="adminPayStatus" style="display: none; margin-bottom: 16px;">
                    <div class="alert" id="adminPayStatusMsg" style="border-radius: 8px;"></div>
                </div>
                <div class="mb-3">
                    <label for="adminPayPhone" class="form-label" style="font-weight: 600; color: #374151;">M-Pesa Phone Number <span style="color: #EF4444;">*</span></label>
                    <input type="tel" id="adminPayPhone" class="form-control" placeholder="07XXXXXXXX"
                        value="<?= htmlspecialchars($member['phone'] ?? '') ?>"
                        style="border-radius: 8px; border: 1px solid #D1D5DB; padding: 10px 14px;">
                    <div style="font-size: 12px; color: #6B7280; margin-top: 4px;">Format: 07XXXXXXXX or 254XXXXXXXXX</div>
                </div>
                <div class="mb-3">
                    <label for="adminPayAmount" class="form-label" style="font-weight: 600; color: #374151;">Amount (KES) <span style="color: #EF4444;">*</span></label>
                    <input type="number" id="adminPayAmount" class="form-control" placeholder="0.00" min="1" step="1"
                        value="<?= (int)($member['monthly_contribution'] ?? 0) > 0 ? (int)$member['monthly_contribution'] : '' ?>"
                        style="border-radius: 8px; border: 1px solid #D1D5DB; padding: 10px 14px;">
                </div>
                <div class="mb-3">
                    <label for="adminPayType" class="form-label" style="font-weight: 600; color: #374151;">Payment Type</label>
                    <select id="adminPayType" class="form-select" style="border-radius: 8px; border: 1px solid #D1D5DB; padding: 10px 14px;">
                        <option value="monthly">Monthly Contribution</option>
                        <option value="reactivation">Reactivation Fee</option>
                        <option value="penalty">Penalty</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="border: none; padding: 16px 24px 24px; gap: 10px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" id="adminPayBtn" onclick="submitAdminPayment(<?= (int)($member['id'] ?? 0) ?>)"
                    style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-paper-plane"></i> Send M-Pesa Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openAdminPayModal() {
    const modal = new bootstrap.Modal(document.getElementById('adminPayModal'));
    document.getElementById('adminPayStatus').style.display = 'none';
    document.getElementById('adminPayBtn').disabled = false;
    document.getElementById('adminPayBtn').innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
    modal.show();
}

async function submitAdminPayment(memberId) {
    const btn = document.getElementById('adminPayBtn');
    const statusDiv = document.getElementById('adminPayStatus');
    const statusMsg = document.getElementById('adminPayStatusMsg');
    const phone = document.getElementById('adminPayPhone').value.trim();
    const amount = parseFloat(document.getElementById('adminPayAmount').value);
    const paymentType = document.getElementById('adminPayType').value;

    if (!phone || phone.length < 9) {
        ShenaApp.showNotification('Please enter a valid phone number.', 'warning');
        return;
    }
    if (!amount || amount <= 0) {
        ShenaApp.showNotification('Please enter a valid amount.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    statusDiv.style.display = 'none';

    try {
        const response = await fetch('/payment/initiate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ member_id: memberId, phone_number: phone, amount: amount, payment_type: paymentType })
        });

        const data = await response.json();

        statusDiv.style.display = 'block';
        if (data.success) {
            statusMsg.className = 'alert alert-success';
            statusMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            btn.innerHTML = '<i class="fas fa-check"></i> Sent';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('adminPayModal'))?.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
                statusDiv.style.display = 'none';
            }, 3500);
        } else {
            statusMsg.className = 'alert alert-danger';
            statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> ' + (data.error || 'Failed to initiate payment.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
        }
    } catch (err) {
        statusDiv.style.display = 'block';
        statusMsg.className = 'alert alert-danger';
        statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> Network error. Please try again.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
    }
}

function confirmSuspend() {
    ShenaApp.confirmAction(
        'Are you sure you want to suspend this member? They will lose access to their account.',
        function() {
            document.getElementById('suspend-form').submit();
        },
        null,
        { type: 'danger', title: 'Suspend Member', confirmText: 'Yes, Suspend' }
    );
}

function confirmActivate() {
    ShenaApp.confirmAction(
        'Activate this member and restore their access?',
        function() {
            document.getElementById('activate-form').submit();
        },
        null,
        { type: 'success', title: 'Activate Member', confirmText: 'Yes, Activate' }
    );
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
