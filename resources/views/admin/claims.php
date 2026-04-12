<?php 
// Real data from controller
$pendingClaims = $pendingClaims ?? 0;
$approvedClaims = $approvedClaims ?? 0;
$rejectedClaims = $rejectedClaims ?? 0;
$totalClaimAmount = $totalClaimAmount ?? 0;
$pending_claims = $pending_claims ?? [];
$all_claims = $all_claims ?? [];
$completed_claims = $completed_claims ?? [];

// Calculate real statistics
$submittedCount = count(array_filter($all_claims, fn($c) => $c['status'] === 'submitted'));
$underReviewCount = count(array_filter($all_claims, fn($c) => $c['status'] === 'under_review'));
$inServiceCount = count(array_filter($all_claims, fn($c) => $c['status'] === 'approved'));
$actionNeededCount = count(array_filter($all_claims, fn($c) => 
    ($c['status'] === 'submitted' && empty($c['documents'])) || 
    ($c['status'] === 'under_review')
));
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

 
<!-- Tab Content -->
<div id="claimsTabContent">

<style>
    /* Page Header */
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
        margin: 0;
    }

    /* Stats Grid */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        margin-bottom: 12px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-icon.blue {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .stat-icon.orange {
        background: #FED7AA;
        color: #F97316;
    }

    .stat-icon.green {
        background: #D1FAE5;
        color: #10B981;
    }

    .stat-icon.red {
        background: #FEE2E2;
        color: #EF4444;
    }

    .stat-label {
        font-size: 11px;
        color: #9CA3AF;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
    }

    /* Main Content Layout */
    .content-layout {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    /* Active Claims */
    .claims-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .claims-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
    }

    .claim-item {
        padding: 16px;
        background: #F9FAFB;
        border-radius: 10px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .claim-item:hover {
        background: #F3F4F6;
    }

    .claim-item.active {
        background: white;
        border: 2px solid #7F3D9E;
    }

    .claim-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .claim-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .claim-badge.doc-review {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .claim-badge.logistics {
        background: #FED7AA;
        color: #F97316;
    }

    .claim-badge.settled {
        background: #D1FAE5;
        color: #10B981;
    }

    .claim-number {
        font-size: 11px;
        color: #9CA3AF;
    }

    .claim-name {
        font-size: 15px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .claim-beneficiary {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 8px;
    }

    .claim-progress {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #7F3D9E;
    }

    .claim-progress i {
        font-size: 14px;
    }

    .claim-logistics-info {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .claim-pickup {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #F97316;
    }

    .claim-settled-info {
        font-size: 12px;
        color: #10B981;
    }

    /* Verification Panel */
    .verification-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .verification-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .verification-title {
        font-size: 22px;
        font-weight: 700;
        color: #1F2937;
    }

    .verification-subtitle {
        font-size: 13px;
        color: #9CA3AF;
        margin-bottom: 24px;
    }

    .claim-amount-label {
        font-size: 11px;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .claim-amount-value {
        font-size: 18px;
        font-weight: 700;
        color: #7F3D9E;
    }

    /* Document Upload Grid */
    .document-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 30px;
    }

    .document-slot {
        aspect-ratio: 1;
        border: 2px dashed #E5E7EB;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #F9FAFB;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        background-image: repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(139, 92, 246, 0.05) 10px,
            rgba(139, 92, 246, 0.05) 20px
        );
    }

    .document-slot:hover {
        border-color: #8B5CF6;
        background: rgba(139, 92, 246, 0.02);
    }

    .document-slot.verified {
        border-color: #7F3D9E;
        border-style: solid;
        background: #F0FDF4;
        background-image: none;
    }

    .document-slot.review {
        border-color: #7F3D9E;
        border-style: solid;
        background: #FAF5FF;
        background-image: none;
    }

    .document-icon {
        font-size: 36px;
        color: #D1D5DB;
        margin-bottom: 12px;
    }

    .document-slot.verified .document-icon {
        color: #7F3D9E;
    }

    .document-slot.review .document-icon {
        color: #7F3D9E;
    }

    .document-label {
        font-size: 13px;
        font-weight: 600;
        color: #6B7280;
        margin-bottom: 8px;
        text-align: center;
    }

    .document-status {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .document-status.verified {
        background: #7F3D9E;
        color: white;
    }

    .document-status.review {
        background: #7F3D9E;
        color: white;
    }

    /* Logistics Section */
    .logistics-section {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .logistics-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .logistics-icon {
        width: 40px;
        height: 40px;
        background: #7F3D9E;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .logistics-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .form-section {
        margin-bottom: 20px;
    }

    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .form-select {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
        color: #1F2937;
        background: white;
        cursor: pointer;
    }

    .form-select:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    /* Checklist */
    .checklist-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checklist-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #7F3D9E;
    }

    .checklist-label {
        font-size: 13px;
        color: #1F2937;
        cursor: pointer;
    }

    @media (max-width: 1200px) {
        .content-layout {
            grid-template-columns: 1fr;
        }

        .document-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .checklist-grid {
            grid-template-columns: 1fr;
        }
        
        .tabs-nav {
            overflow-x: auto;
        }

        /* Allow tables to scroll — override overflow:hidden on tabs-container */
        .tabs-container {
            overflow: visible !important;
        }

        .claims-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .claims-table thead,
        .claims-table tbody,
        .claims-table tr {
            min-width: 520px;
        }
    }

    /* Tabs Styles */
    .tabs-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .tabs-nav {
        display: flex;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        padding: 4px;
        gap: 4px;
        overflow-x: auto;
    }

    .tab-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        background: transparent;
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .tab-item:hover {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .tab-item.active {
        background: white;
        color: #7F3D9E;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        background: #7F3D9E;
        color: white;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .tab-content {
        padding: 24px;
        overflow-x: auto;
    }

    .claims-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .claims-table thead th {
        text-align: left;
        padding: 12px;
        font-size: 11px;
        font-weight: 700;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #E5E7EB;
    }

    .claims-table tbody td {
        padding: 16px 12px;
        border-bottom: 1px solid #F3F4F6;
    }

    .claims-table tbody tr:hover {
        background: #F9FAFB;
    }

    .claim-member-info {
        display: flex;
        flex-direction: column;
    }

    .claim-member-name {
        font-weight: 600;
        color: #1F2937;
        font-size: 14px;
    }

    .claim-deceased-name {
        font-size: 12px;
        color: #6B7280;
    }

    .claims-table .actions-cell {
        text-align: right;
        white-space: nowrap;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn-view {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-view:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    .btn-approve {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        border: none;
        background: #10B981;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-approve:hover {
        background: #059669;
    }

    .btn-reject {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        border: none;
        background: #EF4444;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-reject:hover {
        background: #DC2626;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Claims & Logistics Hub</h1>
    <p class="page-subtitle">Verification and funeral coordination management</p>
</div>

<!-- Cash Alternative Requests Alert -->
<?php if (!empty($cash_alternative_requests)): ?>
<div class="alert alert-warning" style="margin-bottom: 24px; border-left: 4px solid #F59E0B;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #F59E0B;"></i>
        <div style="flex: 1;">
            <h5 style="margin: 0 0 8px 0; font-weight: 600;">Cash Alternative Requests Pending</h5>
            <p style="margin: 0; font-size: 14px;"><?= count($cash_alternative_requests) ?> member(s) have requested cash alternative (KSH 20,000) instead of service delivery.</p>
        </div>
    </div>
    <div style="margin-top: 12px;">
        <?php foreach ($cash_alternative_requests as $caRequest): ?>
        <div style="background: white; padding: 12px; border-radius: 8px; margin-top: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Claim #<?= 'CLM-' . date('Y') . '-' . str_pad($caRequest['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                    <span style="color: #6B7280; margin: 0 8px;">•</span>
                    <span><?= htmlspecialchars($caRequest['first_name'] . ' ' . $caRequest['last_name']) ?></span>
                    <span style="color: #6B7280; margin: 0 8px;">•</span>
                    <span>Member #<?= htmlspecialchars($caRequest['member_number']) ?></span>
                </div>
                <a href="/admin/claims/view/<?= $caRequest['id'] ?>" class="btn btn-sm btn-primary">Review Request</a>
            </div>
            <?php if (!empty($caRequest['cash_alternative_reason'])): ?>
            <div style="margin-top: 8px; padding:8px; background: #F9FAFB; border-radius: 4px; font-size: 13px; color: #374151;">
                <strong>Reason:</strong> <?= htmlspecialchars($caRequest['cash_alternative_reason']) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-row">
    <!-- Submitted Claims -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
        <div class="stat-label">Submitted</div>
        <div class="stat-value"><?= str_pad($submittedCount, 2, '0', STR_PAD_LEFT) ?> Claims</div>
    </div>

    <!-- Under Review -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon orange">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="stat-label">Under Review</div>
        <div class="stat-value"><?= str_pad($underReviewCount, 2, '0', STR_PAD_LEFT) ?> Claims</div>
    </div>

    <!-- Approved/In Service -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-label">In Service</div>
        <div class="stat-value"><?= str_pad($inServiceCount, 2, '0', STR_PAD_LEFT) ?> Active</div>
    </div>

    <!-- Completed -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
        <div class="stat-label">Completed</div>
        <div class="stat-value"><?= str_pad(count($completed_claims), 2, '0', STR_PAD_LEFT) ?> Total</div>
    </div>
</div>

<!-- Tabbed Interface -->
<div class="tabs-container">
    <div class="tabs-nav">
        <button class="tab-item active" onclick="switchClaimTab('all')">
            <i class="fas fa-list"></i>
            All Claims
            <span class="tab-badge"><?= count($all_claims) ?></span>
        </button>
        <button class="tab-item" onclick="switchClaimTab('submitted')">
            <i class="fas fa-file-alt"></i>
            Submitted
            <span class="tab-badge"><?= $submittedCount ?></span>
        </button>
        <button class="tab-item" onclick="switchClaimTab('review')">
            <i class="fas fa-search"></i>
            Under Review
            <span class="tab-badge"><?= $underReviewCount ?></span>
        </button>
        <button class="tab-item" onclick="switchClaimTab('approved')">
            <i class="fas fa-truck"></i>
            Approved/Service
            <span class="tab-badge"><?= $inServiceCount ?></span>
        </button>
        <button class="tab-item" onclick="switchClaimTab('completed')">
            <i class="fas fa-check-circle"></i>
            Completed
            <span class="tab-badge"><?= count($completed_claims) ?></span>
        </button>
        <button class="tab-item" onclick="switchClaimTab('rejected')">
            <i class="fas fa-times-circle"></i>
            Rejected
            <span class="tab-badge"><?= $rejectedClaims ?></span>
        </button>
    </div>

    <!-- All Claims Tab -->
    <div id="tab-all" class="tab-content">
        <?php if (!empty($all_claims)): ?>
            <table class="claims-table">
                <thead>
                    <tr>
                        <th>Claim #</th>
                        <th>Member</th>
                        <th>Deceased</th>
                        <th>Date of Death</th>
                        <th>Service Type</th>
                        <th>Status</th>
                        <th class="actions-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_claims as $claim): ?>
                        <tr>
                            <td><?= 'CLM-' . date('Y') . '-' . str_pad($claim['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td class="actions-cell">
                                <div class="claim-member-info">
                                    <span class="claim-member-name"><?= htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']) ?></span>
                                    <span class="claim-deceased-name">Member #<?= htmlspecialchars($claim['member_number'] ?? 'N/A') ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($claim['deceased_name'] ?? 'N/A') ?></td>
                            <td><?= $claim['date_of_death'] ? date('M d, Y', strtotime($claim['date_of_death'])) : 'N/A' ?></td>
                            <td>
                                <?php if (($claim['settlement_type'] ?? 'services') === 'cash_alternative'): ?>
                                    <span class="claim-badge" style="background: #FED7AA; color: #92400E;">Cash (KES 20K)</span>
                                <?php else: ?>
                                    <span class="claim-badge" style="background: #D1FAE5; color: #065F46;">Standard Services</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $claim['status'] ?? 'submitted';
                                $statusColors = [
                                    'submitted' => 'background: #DBEAFE; color: #1E40AF;',
                                    'under_review' => 'background: #FED7AA; color: #92400E;',
                                    'approved' => 'background: #D1FAE5; color: #065F46;',
                                    'paid' => 'background: #D1FAE5; color: #065F46;',
                                    'rejected' => 'background: #FEE2E2; color: #991B1B;',
                                    'completed' => 'background: #D1FAE5; color: #065F46;'
                                ];
                                ?>
                                <span class="claim-badge" style="<?= $statusColors[$status] ?? '' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-view" onclick="window.location.href='/admin/claims/view/<?= $claim['id'] ?>'">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($status === 'submitted' || $status === 'under_review'): ?>
                                        <button class="btn-approve" onclick="reviewClaim(<?= $claim['id'] ?>)" title="Review & Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($status === 'approved'): ?>
                                        <button class="btn-view" onclick="window.location.href='/admin/claims/track/<?= $claim['id'] ?>'" title="Track Services">
                                            <i class="fas fa-truck"></i> Track
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; color: #9CA3AF;">
                <i class="fas fa-inbox" style="font-size: 64px; opacity: 0.3;"></i>
                <div style="font-size: 18px; font-weight: 600; margin-top: 16px;">No Claims Found</div>
                <p style="font-size: 14px; margin-top: 8px;">Claims will appear here when members submit them</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Other tabs content (filtered dynamically) -->
    <div id="tab-submitted" class="tab-content" style="display:none;"></div>
    <div id="tab-review" class="tab-content" style="display:none;"></div>
    <div id="tab-approved" class="tab-content" style="display:none;"></div>
    <div id="tab-completed" class="tab-content" style="display:none;"></div>
    <div id="tab-rejected" class="tab-content" style="display:none;"></div>
</div>

<script>
function switchClaimTab(tabName, evt) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });

    // Remove active class
    document.querySelectorAll('.tab-item').forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Add active class to the clicked tab. If event is not provided (inline onclick), try to find the button by its onclick attribute.
    try {
        if (evt && evt.target) {
            const btn = evt.target.closest('.tab-item');
            if (btn) btn.classList.add('active');
        } else {
            // fallback: find tab button with matching onclick
            const buttons = document.querySelectorAll('.tab-item');
            buttons.forEach(b => {
                const attr = b.getAttribute('onclick') || '';
                if (attr.indexOf("switchClaimTab('" + tabName + "')") !== -1) {
                    b.classList.add('active');
                }
            });
        }
    } catch (e) {
        console.warn('switchClaimTab: could not set active tab', e);
    }

    // Filter and display data (for other tabs)
    if (tabName !== 'all') {
        filterClaimsByStatus(tabName);
    }
}

function filterClaimsByStatus(status) {
    const allClaims = <?= json_encode($all_claims) ?>;
    const statusMap = {
        'submitted': 'submitted',
        'review': 'under_review',
        'approved': 'approved',
        'completed': ['completed', 'paid'],
        'rejected': 'rejected'
    };

    const filtered = allClaims.filter(claim => {
        if (Array.isArray(statusMap[status])) {
            return statusMap[status].includes(claim.status);
        }
        return claim.status === statusMap[status];
    });

    const container = document.getElementById('tab-' + status);
    container.innerHTML = renderClaimsTable(filtered, status);
}

function renderClaimsTable(claims, status) {
    if (claims.length === 0) {
        return `
            <div style="text-align: center; padding: 60px; color: #9CA3AF;">
                <i class="fas fa-inbox" style="font-size: 64px; opacity: 0.3;"></i>
                <div style="font-size: 18px; font-weight: 600; margin-top: 16px;">No ${status} claims</div>
            </div>
        `;
    }

    let html = '<table class="claims-table"><thead><tr><th>Claim #</th><th>Member</th><th>Deceased</th><th>Date of Death</th><th>Service Type</th><th>Status</th><th class="actions-cell">Actions</th></tr></thead><tbody>';

    claims.forEach(claim => {
        const claimNumber = 'CLM-' + new Date().getFullYear() + '-' + String(claim.id).padStart(4, '0');
        const memberName = claim.first_name + ' ' + claim.last_name;
        const serviceType = (claim.settlement_type === 'cash_alternative') ? 
            '<span class="claim-badge" style="background: #FED7AA; color: #92400E;">Cash (KES 20K)</span>' :
            '<span class="claim-badge" style="background: #D1FAE5; color: #065F46;">Standard Services</span>';

        html += `
            <tr>
                <td>${claimNumber}</td>
                <td class="actions-cell">
                    <div class="claim-member-info">
                        <span class="claim-member-name">${memberName}</span>
                        <span class="claim-deceased-name">Member #${claim.member_number || 'N/A'}</span>
                    </div>
                </td>
                <td>${claim.deceased_name || 'N/A'}</td>
                <td>${claim.date_of_death ? new Date(claim.date_of_death).toLocaleDateString() : 'N/A'}</td>
                <td>${serviceType}</td>
                <td><span class="claim-badge">${claim.status.replace('_', ' ')}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-view" onclick="window.location.href='/admin/claims/view/${claim.id}'">
                            <i class="fas fa-eye"></i> View
                        </button>
                        ${(claim.status === 'submitted' || claim.status === 'under_review') ? 
                            `<button class="btn-approve" onclick="reviewClaim(${claim.id})"><i class="fas fa-check"></i></button>` : ''}
                        ${claim.status === 'approved' ? 
                            `<button class="btn-view" onclick="window.location.href='/admin/claims/track/${claim.id}'"><i class="fas fa-truck"></i> Track</button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    return html;
}

function reviewClaim(claimId) {
    window.location.href = '/admin/claims/view/' + claimId;
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
