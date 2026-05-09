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
        color: #10B981;
    }

    /* Claims Table */
    .claims-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .claims-table thead th {
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

    .claims-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: 14px;
        color: #1F2937;
    }

    .claims-table tbody tr:hover {
        background: #F9FAFB;
    }

    .claim-id {
        font-weight: 700;
        color: #10B981;
        background: #D1FAE5;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        display: inline-block;
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

    .status-badge.cash {
        background: #FEF3C7;
        color: #D97706;
    }

    .status-badge.services {
        background: #D1FAE5;
        color: #059669;
    }

    .duration-badge {
        background: #DBEAFE;
        color: #1E40AF;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-view {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: #8B5CF6;
        color: white;
        transition: all 0.2s;
    }

    .btn-view:hover {
        background: #7C3AED;
        transform: translateY(-1px);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-dialog {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        background: #10B981;
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .btn-close {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .btn-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #E5E7EB;
        display: flex;
        justify-content: flex-end;
    }

    .info-section {
        margin-bottom: 24px;
    }

    .info-title {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 12px;
    }

    .info-table {
        width: 100%;
    }

    .info-table tr {
        border-bottom: 1px solid #F3F4F6;
    }

    .info-table th {
        padding: 8px 0;
        font-size: 13px;
        font-weight: 600;
        color: #6B7280;
        width: 40%;
    }

    .info-table td {
        padding: 8px 0;
        font-size: 14px;
        color: #1F2937;
    }

    .alert-box {
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .alert-box.warning {
        background: #FEF3C7;
        border: 1px solid #F59E0B;
        color: #92400E;
    }

    .alert-box.info {
        background: #DBEAFE;
        border: 1px solid #3B82F6;
        color: #1E3A8A;
    }

    .alert-heading {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .service-checklist {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }

    .service-item {
        padding: 12px 16px;
        border-radius: 8px;
        background: #D1FAE5;
        border: 1px solid #10B981;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #065F46;
    }

    .service-item i {
        color: #10B981;
        font-size: 16px;
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
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Completed Claims</h1>
        <p class="page-subtitle">Claims where all services have been delivered</p>
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

<!-- Completed Claims Table -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-check-double"></i>
            Completed Claims Summary
        </div>
    </div>
    
    <?php if (!empty($claims)): ?>
    <div style="overflow-x: auto;">
        <table class="claims-table">
            <thead>
                <tr>
                    <th>Claim ID</th>
                    <th>Member</th>
                    <th>Deceased</th>
                    <th>Service Type</th>
                    <th>Submitted</th>
                    <th>Completed</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($claims as $claim): ?>
                    <?php
                    $submitted = new DateTime($claim['created_at']);
                    $completed = new DateTime($claim['completed_at'] ?? $claim['updated_at']);
                    $duration = $submitted->diff($completed);
                    ?>
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
                        <td><?= date('M j, Y', strtotime($claim['created_at'])) ?></td>
                        <td><?= date('M j, Y', strtotime($claim['completed_at'] ?? $claim['updated_at'])) ?></td>
                        <td>
                            <span class="duration-badge">
                                <?= $duration->days ?> day<?= $duration->days != 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn-view" onclick="openModal('modal<?= $claim['id'] ?>')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <h5>No completed claims yet</h5>
        <p>Completed claims will appear here once all services are delivered</p>
    </div>
    <?php endif; ?>
</div>

<!-- Modals for each claim -->
<?php if (!empty($claims)): ?>
    <?php foreach ($claims as $claim): ?>
        <?php
        $submitted = new DateTime($claim['created_at']);
        $completed = new DateTime($claim['completed_at'] ?? $claim['updated_at']);
        $duration = $submitted->diff($completed);
        ?>
        <div class="modal" id="modal<?= $claim['id'] ?>">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i>
                        Completed Claim Details
                    </h5>
                    <button type="button" class="btn-close" onclick="closeModal('modal<?= $claim['id'] ?>')">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 info-section">
                            <div class="info-title">Claim Information</div>
                            <table class="info-table">
                                <tr>
                                    <th>Claim ID:</th>
                                    <td>#<?= str_pad($claim['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td><span class="claim-id">Completed</span></td>
                                </tr>
                                <tr>
                                    <th>Submitted:</th>
                                    <td><?= date('M j, Y H:i', strtotime($claim['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Completed:</th>
                                    <td><?= date('M j, Y H:i', strtotime($claim['completed_at'] ?? $claim['updated_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Processing Time:</th>
                                    <td><?= $duration->days ?> days</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 info-section">
                            <div class="info-title">Deceased Information</div>
                            <table class="info-table">
                                <tr>
                                    <th>Name:</th>
                                    <td><?= htmlspecialchars($claim['deceased_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Relationship:</th>
                                    <td><?= ucfirst($claim['relationship_to_deceased']) ?></td>
                                </tr>
                                <tr>
                                    <th>Date of Death:</th>
                                    <td><?= date('M j, Y', strtotime($claim['date_of_death'])) ?></td>
                                </tr>
                                <?php if ($claim['place_of_death']): ?>
                                <tr>
                                    <th>Place of Death:</th>
                                    <td><?= htmlspecialchars($claim['place_of_death']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <?php if ($claim['service_delivery_type'] === 'cash_alternative'): ?>
                        <div class="alert-box warning">
                            <div class="alert-heading">
                                <i class="fas fa-money-bill"></i>
                                Cash Alternative Delivered
                            </div>
                            <p><strong>Amount:</strong> KES 20,000</p>
                            <?php if ($claim['cash_alternative_reason']): ?>
                                <p><strong>Reason:</strong> <?= htmlspecialchars($claim['cash_alternative_reason']) ?></p>
                            <?php endif; ?>
                            <?php if ($claim['cash_alternative_payment_date']): ?>
                                <p><strong>Payment Date:</strong> <?= date('M j, Y', strtotime($claim['cash_alternative_payment_date'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="info-title">Services Delivered:</div>
                        <div class="service-checklist">
                            <div class="service-item">
                                <i class="fas fa-check-circle"></i>
                                Mortuary Bill Payment (<?= $claim['mortuary_days_count'] ?? 14 ?> days)
                            </div>
                            <div class="service-item">
                                <i class="fas fa-check-circle"></i>
                                Body Dressing
                            </div>
                            <div class="service-item">
                                <i class="fas fa-check-circle"></i>
                                Executive Coffin
                            </div>
                            <div class="service-item">
                                <i class="fas fa-check-circle"></i>
                                Transportation
                            </div>
                            <div class="service-item">
                                <i class="fas fa-check-circle"></i>
                                Equipment (Lowering gear, trolley, gazebo, 100 chairs)
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($claim['admin_notes']): ?>
                        <div class="alert-box info">
                            <div class="alert-heading">
                                <i class="fas fa-sticky-note"></i>
                                Admin Notes
                            </div>
                            <p style="margin: 0;"><?= nl2br(htmlspecialchars($claim['admin_notes'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-back" onclick="closeModal('modal<?= $claim['id'] ?>')">Close</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            closeModal(modal.id);
        });
    }
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
