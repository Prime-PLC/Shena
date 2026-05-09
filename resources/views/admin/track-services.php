<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
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

    .btn-back {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: #F9FAFB;
        color: #1F2937;
        transform: translateY(-1px);
    }

    /* Alert */
    .alert-success {
        background: #D1FAE5;
        border: 1px solid #10B981;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        color: #065F46;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success i {
        font-size: 20px;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #F3F4F6;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-title i {
        color: #8B5CF6;
    }

    /* Tracking Table */
    .tracking-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .tracking-table thead th {
        background: #F9FAFB;
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #E5E7EB;
    }

    .tracking-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: 14px;
        color: #1F2937;
    }

    .tracking-table tbody tr:hover {
        background: #F9FAFB;
    }

    .claim-id {
        font-weight: 700;
        color: #8B5CF6;
    }

    .member-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .member-name {
        font-weight: 600;
        color: #1F2937;
    }

    .member-number {
        font-size: 12px;
        color: #9CA3AF;
    }

    .deceased-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .deceased-name {
        font-weight: 600;
        color: #1F2937;
    }

    .relationship {
        font-size: 12px;
        color: #9CA3AF;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge.approved {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .status-badge.in-progress {
        background: #FEF3C7;
        color: #D97706;
    }

    .status-badge.cash {
        background: #FEF3C7;
        color: #D97706;
    }

    .status-badge.services {
        background: #D1FAE5;
        color: #059669;
    }

    .btn-track {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: #8B5CF6;
        color: white;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-track:hover {
        background: #7C3AED;
        transform: translateY(-1px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #E5E7EB;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        font-size: 18px;
        font-weight: 700;
        color: #6B7280;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        color: #9CA3AF;
    }

    /* Progress Bar */
    .progress-bar {
        height: 8px;
        background: #F3F4F6;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #8B5CF6, #7C3AED);
        transition: width 0.3s;
    }

    .progress-text {
        font-size: 11px;
        color: #6B7280;
        margin-top: 4px;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Track Services</h1>
        <p class="page-subtitle">Monitor service delivery progress for approved claims</p>
    </div>
    <a href="/admin/claims" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Back to Claims
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = <?php echo json_encode($_SESSION['success']); ?>;
            if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                ShenaApp.alert(message, 'success');
                return;
            }
            if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                ShenaApp.showNotification(message, 'success', 5000);
                return;
            }
            console.warn(message);
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Tracking Table -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-map-marker-alt"></i>
            Service Tracking Overview
        </div>
    </div>
    
    <?php if (!empty($claims)): ?>
    <div style="overflow-x: auto;">
        <table class="tracking-table">
            <thead>
                <tr>
                    <th>Claim ID</th>
                    <th>Member</th>
                    <th>Deceased</th>
                    <th>Service Type</th>
                    <th>Approved Date</th>
                    <th>Progress</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($claims as $claim): ?>
                <tr>
                    <td>
                        <span class="claim-id">#<?= str_pad($claim['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </td>
                    <td>
                        <div class="member-info">
                            <span class="member-name"><?= htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']) ?></span>
                            <span class="member-number"><?= htmlspecialchars($claim['member_number']) ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="deceased-info">
                            <span class="deceased-name"><?= htmlspecialchars($claim['deceased_name']) ?></span>
                            <span class="relationship"><?= ucfirst($claim['relationship_to_deceased']) ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if ($claim['service_delivery_type'] === 'cash_alternative'): ?>
                            <span class="status-badge cash">
                                <i class="fas fa-money-bill"></i>
                                Cash KES 20,000
                            </span>
                        <?php else: ?>
                            <span class="status-badge services">
                                <i class="fas fa-hands-helping"></i>
                                Full Services
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= date('M j, Y', strtotime($claim['approved_at'] ?? $claim['updated_at'])) ?>
                    </td>
                    <td>
                        <div style="min-width: 120px;">
                            <?php 
                            // Calculate progress based on days since approval
                            $approvedDate = new DateTime($claim['approved_at'] ?? $claim['updated_at']);
                            $today = new DateTime();
                            $daysSinceApproval = $approvedDate->diff($today)->days;
                            $progress = min(100, ($daysSinceApproval / 7) * 100); // Assume 7 days target
                            ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                            </div>
                            <div class="progress-text">
                                <?= $daysSinceApproval ?> days in progress
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="/admin/claims/track/<?= $claim['id'] ?>" class="btn-track">
                            <i class="fas fa-route"></i>
                            Track Details
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-map-marker-alt"></i>
        <h5>No claims being tracked</h5>
        <p>Approved claims will appear here for service tracking</p>
    </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
