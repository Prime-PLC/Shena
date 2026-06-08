<?php 
$member = $member ?? [];
$stats = $stats ?? [];
$payments = $payments ?? [];
$beneficiaries = $beneficiaries ?? [];
$corporateMembers = $corporate_members ?? [];
$packages = $packages ?? [];
$membershipPlanData = $membershipPlanData ?? [];
$dependantRelationshipOptions = $dependant_relationship_options ?? [];
$activationRestrictions = $activation_restrictions ?? [];
$dependantRestrictions = $dependant_restrictions ?? [];
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$formatRelation = static function ($value) {
    return ucwords(str_replace('_', ' ', (string)$value));
};
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .member-details-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        background: #F8F9FA;
    }

    .breadcrumb-row {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6B7280;
        font-size: 13px;
        margin-bottom: 14px;
    }

    .breadcrumb-row a {
        color: #7F3D9E;
        text-decoration: none;
        font-weight: 700;
    }

    .page-header {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: #1F2937;
        margin: 0 0 4px;
        letter-spacing: 0;
    }

    .page-kicker {
        font-size: 14px;
        color: #6B7280;
        margin: 0;
    }

    .header-actions,
    .inline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn {
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.2s, border-color 0.2s, color 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        min-height: 40px;
    }

    .btn-primary {
        background: #7F3D9E;
        color: #FFFFFF;
        border-color: #7F3D9E;
    }

    .btn-primary:hover {
        background: #6B2D8A;
        border-color: #6B2D8A;
        color: #FFFFFF;
    }

    .btn-secondary {
        background: #FFFFFF;
        color: #374151;
        border-color: #D1D5DB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
        color: #1F2937;
    }

    .btn-warning {
        background: #F59E0B;
        color: #111827;
        border-color: #F59E0B;
    }

    .btn-danger {
        background: #EF4444;
        color: #FFFFFF;
        border-color: #EF4444;
    }

    .btn-success {
        background: #10B981;
        color: #FFFFFF;
        border-color: #10B981;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card,
    .management-card,
    .member-info-card,
    .data-table-card {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        box-shadow: none;
    }

    .stat-card {
        padding: 18px;
        border-left: 4px solid #7F3D9E;
    }

    .stat-label {
        font-size: 12px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 26px;
        font-weight: 800;
        color: #1F2937;
        line-height: 1.2;
    }

    .management-card,
    .data-table-card {
        margin-bottom: 20px;
        overflow: hidden;
    }

    .management-body,
    .card-body {
        padding: 20px;
    }

    .card-header {
        padding: 14px 18px;
        background: #FFFFFF;
        color: #1F2937;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .card-title {
        font-size: 15px;
        font-weight: 800;
        margin: 0;
        color: #1F2937;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 20px;
    }

    .overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.75fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .overview-card {
        grid-row: span 2;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 18px;
    }

    .toggle-panel {
        scroll-margin-top: 90px;
    }

    .profile-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 18px;
        border-bottom: 1px solid #E5E7EB;
    }

    .profile-tab {
        appearance: none;
        border: 0;
        border-bottom: 3px solid transparent;
        background: transparent;
        color: #6B7280;
        font-size: 14px;
        font-weight: 800;
        padding: 12px 14px 10px;
        cursor: pointer;
    }

    .profile-tab.active {
        color: #7F3D9E;
        border-bottom-color: #7F3D9E;
    }

    .profile-tab-panel {
        display: none;
    }

    .profile-tab-panel.active {
        display: block;
    }

    .activation-alert {
        background: #FFF7ED;
        border: 1px solid #FED7AA;
        border-radius: 8px;
        color: #7C2D12;
        font-size: 13px;
        line-height: 1.5;
        margin: 12px 0;
        padding: 12px;
    }

    .activation-alert strong {
        display: block;
        margin-bottom: 6px;
    }

    .activation-alert ul {
        margin: 0;
        padding-left: 18px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        color: #4B5563;
        margin-bottom: 6px;
    }

    .form-input,
    .form-select,
    textarea.form-input,
    .form-control {
        width: 100%;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 10px 12px;
        color: #111827;
        background: #FFFFFF;
        min-height: 42px;
        box-shadow: none;
    }

    .form-input:focus,
    .form-select:focus,
    .form-control:focus {
        border-color: #7F3D9E;
        outline: none;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.12);
    }

    .section-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 22px 0 12px;
        padding-top: 18px;
        border-top: 1px solid #E5E7EB;
    }

    .section-title-row h3 {
        font-size: 16px;
        font-weight: 800;
        margin: 0;
        color: #1F2937;
    }

    .corporate-row {
        display: grid;
        grid-template-columns: 1fr 150px 1.4fr 115px 42px;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
    }

    .corporate-amount {
        font-weight: 800;
        color: #1F2937;
    }

    .relation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }

    .dependant-card,
    .corporate-card {
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 14px;
        background: #F9FAFB;
        color: #4B5563;
    }

    .dependant-card strong,
    .corporate-card strong {
        display: block;
        color: #111827;
        margin-bottom: 4px;
    }

    .card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .btn-sm {
        min-height: 32px;
        padding: 6px 10px;
        font-size: 12px;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #F3F4F6;
        color: #374151;
    }

    .status-badge.active {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-badge.suspended,
    .status-badge.grace_period {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-badge.defaulted,
    .status-badge.inactive {
        background: #FEE2E2;
        color: #991B1B;
    }

    .member-avatar {
        display: flex;
        align-items: center;
        gap: 14px;
        text-align: left;
        margin-bottom: 18px;
    }

    .avatar-circle {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        background: #7F3D9E;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 22px;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .member-name {
        font-size: 20px;
        font-weight: 800;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .member-number {
        color: #6B7280;
        font-size: 14px;
    }

    .info-item {
        margin-bottom: 14px;
    }

    .info-label {
        font-size: 12px;
        color: #6B7280;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
        word-break: break-word;
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

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #F9FAFB;
        padding: 12px 14px;
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .data-table td {
        padding: 14px;
        border-top: 1px solid #E5E7EB;
        font-size: 14px;
        color: #1F2937;
    }

    .empty-state {
        text-align: center;
        padding: 28px 20px;
        color: #6B7280;
    }

    .admin-pay-modal .modal-content {
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: none;
    }

    .admin-pay-modal .modal-header,
    .admin-pay-modal .modal-footer {
        background: #FFFFFF;
        border-color: #E5E7EB;
    }

    .admin-pay-modal .modal-title {
        color: #1F2937;
        font-weight: 800;
    }

    .admin-pay-modal .modal-body {
        padding: 22px;
    }

    @media (max-width: 768px) {
        .member-details-container {
            padding: 16px;
        }

        .page-header,
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .content-grid,
        .overview-grid,
        .form-grid,
        .info-grid,
        .corporate-row {
            grid-template-columns: 1fr;
        }

        .overview-card {
            grid-row: auto;
        }

        .data-table {
            min-width: 520px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="member-details-container">
    <div class="breadcrumb-row">
        <a href="/admin/members">Member Management</a>
        <span>/</span>
        <span><?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?></span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Member Profile</h1>
            <p class="page-kicker"><?= htmlspecialchars($member['member_number'] ?? 'N/A') ?> · <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?></p>
        </div>
        <div class="header-actions">
            <a href="/admin/members" class="btn btn-secondary">Back to Members</a>
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

    <div class="profile-tabs" role="tablist" aria-label="Member profile sections">
        <button type="button" class="profile-tab active" data-profile-tab="overview" onclick="switchProfileTab('overview')">Overview</button>
        <button type="button" class="profile-tab" data-profile-tab="dependants" onclick="switchProfileTab('dependants')">Dependants</button>
        <button type="button" class="profile-tab" data-profile-tab="payments" onclick="switchProfileTab('payments')">Payments</button>
    </div>

    <section class="profile-tab-panel active" data-profile-panel="overview">
    <div class="overview-grid">
        <div class="member-info-card overview-card">
            <div class="card-header">
                <span class="card-title">Member Overview</span>
                <span class="status-badge <?= $member['status'] ?? 'active' ?>">
                    <?= ucfirst(str_replace('_', ' ', $member['status'] ?? 'Active')) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="member-avatar">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($member['first_name'] ?? 'M', 0, 1) . substr($member['last_name'] ?? 'M', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="member-name"><?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?></div>
                        <div class="member-number">Member #<?= htmlspecialchars($member['member_number'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">National ID</div>
                        <div class="info-value"><?= htmlspecialchars($member['id_number'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?= htmlspecialchars($member['phone'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($member['email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">County</div>
                        <div class="info-value"><?= htmlspecialchars($member['county'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Package</div>
                        <div class="info-value"><?= htmlspecialchars(ucfirst($member['package'] ?? 'Basic')) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Monthly Contribution</div>
                        <div class="info-value">KES <?= number_format((float)($member['monthly_contribution'] ?? 0), 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Registered</div>
                        <div class="info-value">
                            <?php
                            $regDate = $member['registration_date'] ?? $member['created_at'] ?? null;
                            echo $regDate ? date('M j, Y', strtotime($regDate)) : 'N/A';
                            ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?= htmlspecialchars(ucfirst($member['gender'] ?? 'N/A')) ?></div>
                    </div>
                </div>
                <div class="inline-actions">
                    <button type="button" class="btn btn-primary" onclick="togglePanel('memberEditPanel')">Edit Member Details</button>
                </div>
            </div>
        </div>

        <div class="member-info-card">
            <div class="card-header">
                <span class="card-title">Account Status</span>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <div class="info-label">Current Status</div>
                    <div class="info-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $member['status'] ?? 'Active'))) ?></div>
                </div>
                <div class="action-buttons">
                    <?php if (($member['status'] ?? 'active') === 'active'): ?>
                        <form method="POST" action="/admin/members/suspend/<?= $member['id'] ?>" id="suspend-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <button type="button" onclick="confirmSuspend()" class="btn btn-warning" style="width: 100%;">Suspend Member</button>
                        </form>
                    <?php else: ?>
                        <?php if (!empty($activationRestrictions)): ?>
                            <div class="activation-alert">
                                <strong>System checks require attention</strong>
                                <ul>
                                    <?php foreach ($activationRestrictions as $restriction): ?>
                                        <li><?= htmlspecialchars($restriction) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="/admin/members/activate/<?= $member['id'] ?>" id="activate-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <input type="hidden" name="return_to" value="/admin/members/view/<?= (int)($member['id'] ?? 0) ?>">
                            <input type="hidden" name="activation_override" id="activationOverride" value="0">
                            <button type="button" onclick="confirmActivate()" class="btn btn-primary" style="width: 100%;">Activate Member</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="member-info-card">
            <div class="card-header">
                <span class="card-title">Plan & Corporate Members</span>
                <button type="button" class="btn btn-secondary" onclick="togglePanel('memberEditPanel')">Manage</button>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <div class="info-label">Primary Package</div>
                    <div class="info-value"><?= htmlspecialchars($member['package'] ?? 'N/A') ?></div>
                </div>
                <div class="relation-grid">
                    <?php if (empty($corporateMembers)): ?>
                        <div class="corporate-card">No corporate members attached.</div>
                    <?php else: ?>
                        <?php foreach ($corporateMembers as $corporateIndex => $corporate): ?>
                            <div class="corporate-card">
                                <strong><?= htmlspecialchars($corporate['label'] ?? 'Corporate member') ?></strong>
                                <div><?= htmlspecialchars($corporate['relationship'] ?? 'corporate') ?></div>
                                <div><?= htmlspecialchars($corporate['package_name'] ?? ($corporate['package_key'] ?? 'N/A')) ?></div>
                                <div>KES <?= number_format((float)($corporate['monthly_contribution'] ?? 0), 2) ?></div>
                                <div class="card-actions">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="editCorporateMember(<?= (int)$corporateIndex ?>)">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteCorporateMember(<?= (int)($corporate['id'] ?? 0) ?>, '<?= htmlspecialchars($corporate['label'] ?? 'Corporate member', ENT_QUOTES) ?>')">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="member-info-card">
            <div class="card-header">
                <span class="card-title">Agent & Next of Kin</span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Recruited By</div>
                        <div class="info-value">
                            <?php $agentNumber = htmlspecialchars($member['agent_number'] ?? 'N/A'); ?>
                            <?php if (!empty($member['agent_id'])): ?>
                                <a href="/admin/agents/view/<?= $member['agent_id'] ?>" style="color: #7F3D9E;">Agent #<?= $agentNumber ?></a>
                            <?php else: ?>
                                Agent #<?= $agentNumber ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Agent Phone</div>
                        <div class="info-value"><?= htmlspecialchars($member['agent_phone'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Next of Kin</div>
                        <div class="info-value"><?= htmlspecialchars($member['next_of_kin'] ?? $member['nok_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Next of Kin Phone</div>
                        <div class="info-value"><?= htmlspecialchars($member['next_of_kin_phone'] ?? $member['nok_phone'] ?? 'N/A') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="management-card toggle-panel" id="memberEditPanel" style="display:none;">
        <div class="card-header">
            <span class="card-title">Edit Member Details</span>
            <span><?= htmlspecialchars($member['member_number'] ?? 'N/A') ?></span>
        </div>
        <div class="management-body">
            <form method="POST" action="/admin/members/update/<?= (int)($member['id'] ?? 0) ?>" id="adminMemberProfileForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="return_to" value="/admin/members/view/<?= (int)($member['id'] ?? 0) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="memberFirstName">First Name</label>
                        <input class="form-input" id="memberFirstName" name="first_name" value="<?= htmlspecialchars($member['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberLastName">Last Name</label>
                        <input class="form-input" id="memberLastName" name="last_name" value="<?= htmlspecialchars($member['last_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberIdNumber">National ID</label>
                        <input class="form-input" id="memberIdNumber" name="id_number" value="<?= htmlspecialchars($member['id_number'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberPhone">Phone</label>
                        <input class="form-input" id="memberPhone" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberEmail">Email</label>
                        <input class="form-input" id="memberEmail" name="email" type="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberDateOfBirth">Date of Birth</label>
                        <input class="form-input" id="memberDateOfBirth" name="date_of_birth" type="date" value="<?= htmlspecialchars($member['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberGender">Gender</label>
                        <select class="form-select" id="memberGender" name="gender">
                            <option value="">Not set</option>
                            <option value="male" <?= (($member['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (($member['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberStatus">Status</label>
                        <select class="form-select" id="memberStatus" name="status">
                            <?php foreach (['active', 'inactive', 'suspended', 'grace_period', 'defaulted'] as $statusOption): ?>
                                <option value="<?= $statusOption ?>" <?= (($member['status'] ?? '') === $statusOption) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $statusOption))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberPackage">Package</label>
                        <select class="form-select" id="memberPackage" name="package_key" required>
                            <?php foreach ($packages as $packageKey => $packageOption): ?>
                                <option
                                    value="<?= htmlspecialchars($packageKey) ?>"
                                    data-monthly-contribution="<?= htmlspecialchars((string)($packageOption['monthly_contribution'] ?? 0)) ?>"
                                    <?= (($member['package_key'] ?? '') === $packageKey) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars(($packageOption['name'] ?? $packageKey) . ' - KES ' . number_format((float)($packageOption['monthly_contribution'] ?? 0), 0)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly Payable Amount</label>
                        <div class="form-input" id="memberMonthlyPreview" style="background:#F9FAFB;font-weight:700;">
                            KES <?= number_format((float)($member['monthly_contribution'] ?? 0), 2) ?>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label" for="memberAddress">Address</label>
                        <textarea class="form-input" id="memberAddress" name="address" rows="2"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberNokName">Next of Kin</label>
                        <input class="form-input" id="memberNokName" name="nok_name" value="<?= htmlspecialchars($member['next_of_kin'] ?? $member['nok_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="memberNokPhone">Next of Kin Phone</label>
                        <input class="form-input" id="memberNokPhone" name="nok_phone" value="<?= htmlspecialchars($member['next_of_kin_phone'] ?? $member['nok_phone'] ?? '') ?>">
                    </div>
                    <div class="form-group full">
                        <label class="form-label" for="memberNokRelationship">Next of Kin Relationship</label>
                        <input class="form-input" id="memberNokRelationship" name="nok_relationship" value="<?= htmlspecialchars($member['next_of_kin_relationship'] ?? $member['nok_relationship'] ?? '') ?>">
                    </div>
                </div>

                <div class="section-title-row">
                    <h3>Corporate Members</h3>
                    <button type="button" class="btn btn-secondary" onclick="addCorporateProfileRow()">
                        Add Corporate Member
                    </button>
                </div>
                <div id="corporateManagePanel">
                <div id="profileCorporateRows">
                    <?php foreach ($corporateMembers as $index => $corporate): ?>
                        <div class="corporate-row">
                            <input class="form-input corporate-label" name="corporate_members[<?= (int)$index ?>][label]" placeholder="Name / label" value="<?= htmlspecialchars($corporate['label'] ?? '') ?>">
                            <input class="form-input" name="corporate_members[<?= (int)$index ?>][relationship]" placeholder="Relationship" value="<?= htmlspecialchars($corporate['relationship'] ?? 'corporate') ?>">
                            <select class="form-select corporate-package" name="corporate_members[<?= (int)$index ?>][package_key]" onchange="updateProfileContributionPreview()">
                                <?php foreach ($packages as $packageKey => $packageOption): ?>
                                    <option value="<?= htmlspecialchars($packageKey) ?>" <?= (($corporate['package_key'] ?? '') === $packageKey) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(($packageOption['name'] ?? $packageKey) . ' - KES ' . number_format((float)($packageOption['monthly_contribution'] ?? 0), 0)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="corporate-amount">KES <?= number_format((float)($corporate['monthly_contribution'] ?? 0), 2) ?></div>
                            <button type="button" class="btn btn-danger" onclick="this.closest('.corporate-row').remove(); updateProfileContributionPreview();" aria-label="Remove corporate member">
                                Remove
                            </button>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($corporateMembers)): ?>
                        <div class="corporate-card" id="noCorporateMembers">No corporate members attached.</div>
                    <?php endif; ?>
                </div>
                </div>

                <div class="inline-actions">
                    <button type="submit" class="btn btn-primary">
                        Save Member Profile
                    </button>
                    <a href="/admin/members" class="btn btn-secondary">
                        Back to Member Management
                    </a>
                </div>
            </form>
        </div>
    </div>
    </section>

    <section class="profile-tab-panel" data-profile-panel="dependants">
    <div class="data-table-card">
        <div class="card-header">
            <span class="card-title">Dependants</span>
            <button type="button" class="btn btn-secondary" onclick="togglePanel('dependantAddPanel')">Add Dependant</button>
        </div>
        <div class="management-body">
            <div class="section-title-row">
                <h3>Covered Dependants</h3>
            </div>
            <div class="relation-grid">
                <?php if (empty($beneficiaries)): ?>
                    <div class="dependant-card">No dependants recorded under this member.</div>
                <?php else: ?>
                    <?php foreach ($beneficiaries as $beneficiary): ?>
                        <div class="dependant-card">
                            <strong><?= htmlspecialchars($beneficiary['full_name'] ?? 'N/A') ?></strong>
                            <div><?= htmlspecialchars($formatRelation($beneficiary['relationship'] ?? '')) ?></div>
                            <div>ID: <?= htmlspecialchars($beneficiary['id_number'] ?? 'N/A') ?></div>
                            <div>DOB: <?= htmlspecialchars($beneficiary['date_of_birth'] ?? 'N/A') ?></div>
                            <div>Phone: <?= htmlspecialchars($beneficiary['phone_number'] ?? 'N/A') ?></div>
                            <div class="card-actions">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openDependantEdit(<?= (int)($beneficiary['id'] ?? 0) ?>)">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteDependant(<?= (int)($beneficiary['id'] ?? 0) ?>, '<?= htmlspecialchars($beneficiary['full_name'] ?? 'Dependant', ENT_QUOTES) ?>')">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="/admin/members/<?= (int)($member['id'] ?? 0) ?>/dependants/add" id="dependantAddPanel" class="toggle-panel" style="display:none;margin-top:20px;" onsubmit="return confirmAddDependant(event);">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="return_to" value="/admin/members/view/<?= (int)($member['id'] ?? 0) ?>">
                <input type="hidden" name="dependant_override" id="dependantOverride" value="0">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="dependantFullName">Dependant Full Name</label>
                        <input class="form-input" id="dependantFullName" name="full_name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="dependantRelationship">Relationship</label>
                        <select class="form-select" id="dependantRelationship" name="relationship">
                            <?php if (empty($dependantRelationshipOptions)): ?>
                                <option value="">No dependant slots available</option>
                            <?php else: ?>
                                <?php foreach ($dependantRelationshipOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option['value'] ?? '') ?>"><?= htmlspecialchars($option['label'] ?? '') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="dependantIdNumber">ID / Birth Certificate</label>
                        <input class="form-input" id="dependantIdNumber" name="id_number">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="dependantDateOfBirth">Date of Birth</label>
                        <input class="form-input" id="dependantDateOfBirth" name="date_of_birth" type="date" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="dependantPhone">Phone</label>
                        <input class="form-input" id="dependantPhone" name="phone_number">
                    </div>
                    <div class="form-group" style="align-self:end;">
                        <button type="submit" class="btn btn-primary" <?= empty($dependantRelationshipOptions) ? 'disabled' : '' ?>>
                            Add Dependant
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </section>

    <section class="profile-tab-panel" data-profile-panel="payments">
    <div class="data-table-card">
        <div class="card-header">
            <span class="card-title">Payment History</span>
            <div class="header-actions">
                <button type="button" onclick="openAdminPayModal()" class="btn btn-success">Collect Payment</button>
                <a href="/admin/payments?member_id=<?= $member['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">View All</a>
            </div>
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

            <div class="data-table-card">
                <div class="card-header">
                    <span class="card-title">Registered Beneficiaries</span>
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
    </section>
</div>

<div class="modal fade" id="dependantEditModal" tabindex="-1" aria-labelledby="dependantEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/admin/members/<?= (int)($member['id'] ?? 0) ?>/dependants/update">
                <div class="modal-header">
                    <h5 class="modal-title" id="dependantEditModalLabel">Edit Dependant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="return_to" value="/admin/members/view/<?= (int)($member['id'] ?? 0) ?>">
                    <input type="hidden" name="dependant_id" id="editDependantId">
                    <div class="form-group">
                        <label class="form-label" for="editDependantFullName">Full Name</label>
                        <input class="form-input" id="editDependantFullName" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editDependantRelationship">Relationship</label>
                        <select class="form-select" id="editDependantRelationship" name="relationship" required>
                            <option value="spouse">Spouse</option>
                            <option value="child">Child</option>
                            <option value="parent">Parent</option>
                            <option value="father_in_law">Father-in-law</option>
                            <option value="mother_in_law">Mother-in-law</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editDependantIdNumber">ID / Birth Certificate</label>
                        <input class="form-input" id="editDependantIdNumber" name="id_number" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editDependantDateOfBirth">Date of Birth</label>
                        <input class="form-input" id="editDependantDateOfBirth" name="date_of_birth" type="date" max="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editDependantPhone">Phone</label>
                        <input class="form-input" id="editDependantPhone" name="phone_number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Dependant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="POST" action="/admin/members/<?= (int)($member['id'] ?? 0) ?>/dependants/delete" id="deleteDependantForm" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="return_to" value="/admin/members/view/<?= (int)($member['id'] ?? 0) ?>">
    <input type="hidden" name="dependant_id" id="deleteDependantId">
</form>

<form method="POST" action="/admin/members/<?= (int)($member['id'] ?? 0) ?>/corporate-members/delete" id="deleteCorporateMemberForm" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="return_to" value="/admin/members/view/<?= (int)($member['id'] ?? 0) ?>">
    <input type="hidden" name="corporate_member_id" id="deleteCorporateMemberId">
</form>

<!-- Collect Payment Modal -->
<div class="modal fade admin-pay-modal" id="adminPayModal" tabindex="-1" aria-labelledby="adminPayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="adminPayModalLabel">
                        Send M-Pesa Payment Request
                    </h5>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #6B7280;">
                        Member: <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>
                        &nbsp;(<?= htmlspecialchars($member['member_number'] ?? '') ?>)
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer" style="gap: 10px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="adminPayBtn" onclick="submitAdminPayment(<?= (int)($member['id'] ?? 0) ?>)"
                    class="btn btn-success">
                    Send M-Pesa Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const profileMembershipPlanData = <?php echo json_encode($membershipPlanData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const activationRestrictions = <?php echo json_encode(array_values($activationRestrictions), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const dependantRestrictions = <?php echo json_encode(array_values($dependantRestrictions), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const adminDependants = <?php echo json_encode(array_values($beneficiaries), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
let profileCorporateRowIndex = <?php echo count($corporateMembers); ?>;

function switchProfileTab(tabName) {
    document.querySelectorAll('[data-profile-tab]').forEach((tab) => {
        tab.classList.toggle('active', tab.dataset.profileTab === tabName);
    });
    document.querySelectorAll('[data-profile-panel]').forEach((panel) => {
        panel.classList.toggle('active', panel.dataset.profilePanel === tabName);
    });
}

function money(amount) {
    return 'KES ' + Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function profilePackageOptionsHtml(selectedKey) {
    return Object.entries(profileMembershipPlanData).map(([key, plan]) => {
        const selected = key === selectedKey ? 'selected' : '';
        const label = `${plan.name || key} - KES ${Number(plan.monthly_contribution || 0).toLocaleString()}`;
        return `<option value="${key}" ${selected}>${label}</option>`;
    }).join('');
}

function addCorporateProfileRow(item = {}) {
    const rows = document.getElementById('profileCorporateRows');
    const empty = document.getElementById('noCorporateMembers');
    if (!rows) return;
    if (empty) empty.remove();

    const index = profileCorporateRowIndex++;
    const row = document.createElement('div');
    row.className = 'corporate-row';
    row.innerHTML = `
        <input class="form-input corporate-label" name="corporate_members[${index}][label]" placeholder="Name / label" value="${String(item.label || '').replace(/"/g, '&quot;')}">
        <input class="form-input" name="corporate_members[${index}][relationship]" placeholder="Relationship" value="${String(item.relationship || 'corporate').replace(/"/g, '&quot;')}">
        <select class="form-select corporate-package" name="corporate_members[${index}][package_key]" onchange="updateProfileContributionPreview()">
            ${profilePackageOptionsHtml(item.package_key || '')}
        </select>
        <div class="corporate-amount">KES 0.00</div>
        <button type="button" class="btn btn-danger" onclick="this.closest('.corporate-row').remove(); updateProfileContributionPreview();" aria-label="Remove corporate member">
            Remove
        </button>
    `;
    rows.appendChild(row);
    updateProfileContributionPreview();
}

function updateProfileContributionPreview() {
    const packageSelect = document.getElementById('memberPackage');
    const mainKey = packageSelect ? packageSelect.value : '';
    let total = Number(profileMembershipPlanData[mainKey]?.monthly_contribution || 0);

    document.querySelectorAll('#profileCorporateRows .corporate-row').forEach(row => {
        const packageKey = row.querySelector('.corporate-package')?.value || '';
        const amount = Number(profileMembershipPlanData[packageKey]?.monthly_contribution || 0);
        total += amount;
        const amountEl = row.querySelector('.corporate-amount');
        if (amountEl) amountEl.textContent = money(amount);
    });

    const preview = document.getElementById('memberMonthlyPreview');
    if (preview) preview.textContent = money(total);
}

document.getElementById('memberPackage')?.addEventListener('change', updateProfileContributionPreview);
updateProfileContributionPreview();

function togglePanel(id) {
    const panel = document.getElementById(id);
    if (!panel) return;

    const isHidden = panel.style.display === 'none' || panel.hidden;
    panel.style.display = isHidden ? 'block' : 'none';
    panel.hidden = false;

    if (isHidden) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function openDependantEdit(dependantId) {
    const dependant = adminDependants.find((item) => Number(item.id || 0) === Number(dependantId));
    if (!dependant) return;

    document.getElementById('editDependantId').value = dependant.id || '';
    document.getElementById('editDependantFullName').value = dependant.full_name || '';
    document.getElementById('editDependantRelationship').value = dependant.relationship || '';
    document.getElementById('editDependantIdNumber').value = dependant.id_number || '';
    document.getElementById('editDependantDateOfBirth').value = dependant.date_of_birth || '';
    document.getElementById('editDependantPhone').value = dependant.phone_number || '';

    new bootstrap.Modal(document.getElementById('dependantEditModal')).show();
}

function deleteDependant(dependantId, dependantName) {
    ShenaApp.confirmAction(
        'Delete dependant "' + dependantName + '" from this member profile?',
        function() {
            document.getElementById('deleteDependantId').value = dependantId;
            document.getElementById('deleteDependantForm').submit();
        },
        null,
        { type: 'warning', title: 'Delete Dependant', confirmText: 'Delete' }
    );
}

function editCorporateMember(rowIndex) {
    switchProfileTab('overview');
    const panel = document.getElementById('memberEditPanel');
    if (panel && panel.style.display === 'none') {
        panel.style.display = 'block';
    }

    const row = document.querySelectorAll('#profileCorporateRows .corporate-row')[rowIndex];
    if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const input = row.querySelector('input, select');
        if (input) input.focus();
    }
}

function deleteCorporateMember(corporateId, corporateName) {
    ShenaApp.confirmAction(
        'Delete corporate member "' + corporateName + '" from this profile?',
        function() {
            document.getElementById('deleteCorporateMemberId').value = corporateId;
            document.getElementById('deleteCorporateMemberForm').submit();
        },
        null,
        { type: 'warning', title: 'Delete Corporate Member', confirmText: 'Delete' }
    );
}

function openAdminPayModal() {
    const modal = new bootstrap.Modal(document.getElementById('adminPayModal'));
    document.getElementById('adminPayStatus').style.display = 'none';
    document.getElementById('adminPayBtn').disabled = false;
    document.getElementById('adminPayBtn').textContent = 'Send M-Pesa Request';
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
    btn.textContent = 'Sending...';
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
            statusMsg.textContent = data.message;
            btn.textContent = 'Sent';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('adminPayModal'))?.hide();
                btn.disabled = false;
                btn.textContent = 'Send M-Pesa Request';
                statusDiv.style.display = 'none';
            }, 3500);
        } else {
            statusMsg.className = 'alert alert-danger';
            statusMsg.textContent = data.error || 'Failed to initiate payment.';
            btn.disabled = false;
            btn.textContent = 'Send M-Pesa Request';
        }
    } catch (err) {
        statusDiv.style.display = 'block';
        statusMsg.className = 'alert alert-danger';
        statusMsg.textContent = 'Network error. Please try again.';
        btn.disabled = false;
        btn.textContent = 'Send M-Pesa Request';
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
    const overrideInput = document.getElementById('activationOverride');
    if (overrideInput) {
        overrideInput.value = '0';
    }

    let message = 'Activate this member and restore their access?';
    let confirmText = 'Yes, Activate';
    if (activationRestrictions.length > 0) {
        message = 'System checks show this member has not cleared: ' + activationRestrictions.join(' ') + ' Override these restrictions and activate the member anyway?';
        confirmText = 'Override & Activate';
    }

    ShenaApp.confirmAction(
        message,
        function() {
            if (overrideInput && activationRestrictions.length > 0) {
                overrideInput.value = '1';
            }
            document.getElementById('activate-form').submit();
        },
        null,
        { type: activationRestrictions.length > 0 ? 'warning' : 'success', title: 'Activate Member', confirmText }
    );
}

function confirmAddDependant(event) {
    event.preventDefault();
    const form = event.target;
    const overrideInput = document.getElementById('dependantOverride');
    if (overrideInput) {
        overrideInput.value = '0';
    }

    if (dependantRestrictions.length === 0) {
        form.submit();
        return false;
    }

    const message = 'System checks show this member has not cleared: ' + dependantRestrictions.join(' ') + ' Override these restrictions and add the dependant anyway?';
    ShenaApp.confirmAction(
        message,
        function() {
            if (overrideInput) {
                overrideInput.value = '1';
            }
            form.submit();
        },
        null,
        { type: 'warning', title: 'Add Dependant', confirmText: 'Override & Add' }
    );
    return false;
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
