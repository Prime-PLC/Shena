<?php
/**
 * Admin Service Delivery Tracking View
 * Track and manage service delivery for approved claims
 * UI matches admin/reports.php and admin/claims.php pattern
 */
require_once __DIR__ . '/../layouts/admin-header.php';
?>

<style>
    /* Page Header - Matching reports.php pattern */
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

    /* Card Styles - Matching reports.php pattern */
    .info-card, .progress-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
        margin-bottom: 24px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #F3F4F6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title i {
        color: #7F3D9E;
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

    .stat-icon.purple {
        background: #EDE9FE;
        color: #7F3D9E;
    }

    .stat-icon.green {
        background: #D1FAE5;
        color: #10B981;
    }

    .stat-icon.orange {
        background: #FED7AA;
        color: #F97316;
    }

    .stat-icon.blue {
        background: #DBEAFE;
        color: #3B82F6;
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

    /* Info Table */
    .info-table {
        width: 100%;
    }

    .info-table tr {
        border-bottom: 1px solid #F3F4F6;
    }

    .info-table tr:last-child {
        border-bottom: none;
    }

    .info-table th {
        padding: 12px 0;
        font-size: 13px;
        font-weight: 600;
        color: #6B7280;
        text-align: left;
        width: 40%;
    }

    .info-table td {
        padding: 12px 0;
        font-size: 14px;
        color: #1F2937;
    }

    /* Status Badge - Matching reports.php pattern */
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

    .status-badge.approved, .status-badge.completed {
        background: #D1FAE5;
        color: #059669;
    }

    .status-badge.pending {
        background: #FEF3C7;
        color: #D97706;
    }

    .status-badge.services {
        background: #D1FAE5;
        color: #059669;
    }

    .status-badge.cash {
        background: #FED7AA;
        color: #92400E;
    }

    /* Service List */
    .service-list {
        display: grid;
        gap: 12px;
    }

    .service-item {
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 16px;
        background: white;
        transition: all 0.2s;
    }

    .service-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .service-item.completed {
        background: #F0FDF4;
        border-color: #BBF7D0;
    }

    .service-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .service-item-title {
        font-size: 15px;
        font-weight: 700;
        color: #1F2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .service-item-title i {
        color: #7F3D9E;
    }

    .service-item-meta {
        font-size: 12px;
        color: #6B7280;
        margin-top: 4px;
    }

    .service-item-meta i {
        margin-right: 4px;
    }

    /* Buttons - Matching reports.php pattern */
    .btn-service {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-service.complete {
        background: #7F3D9E;
        color: white;
    }

    .btn-service.complete:hover {
        background: #6C2D8A;
        transform: translateY(-1px);
    }

    .btn-service.view {
        background: white;
        color: #6B7280;
        border: 1px solid #E5E7EB;
    }

    .btn-service.view:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    /* Progress Circle */
    .progress-circle-container {
        text-align: center;
        padding: 20px;
    }

    .progress-circle {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 20px;
    }

    .progress-circle svg {
        transform: rotate(-90deg);
    }

    .progress-circle-bg {
        fill: none;
        stroke: #E5E7EB;
        stroke-width: 10;
    }

    .progress-circle-fill {
        fill: none;
        stroke: #7F3D9E;
        stroke-width: 10;
        stroke-linecap: round;
        transition: stroke-dasharray 0.5s ease;
    }

    .progress-circle-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .progress-circle-value {
        font-size: 36px;
        font-weight: 700;
        color: #1F2937;
    }

    .progress-circle-label {
        font-size: 12px;
        color: #6B7280;
    }

    /* Complete Claim Button */
    .btn-complete-claim {
        background: #10B981;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-complete-claim:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    /* Alert */
    .alert-modern {
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        padding: 16px 20px;
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 20px;
    }

    .alert-modern.info {
        background: #DBEAFE;
        border-color: #3B82F6;
        color: #1E40AF;
    }

    .alert-modern.success {
        background: #D1FAE5;
        border-color: #10B981;
        color: #065F46;
    }

    .alert-modern.warning {
        background: #FEF3C7;
        border-color: #F59E0B;
        color: #92400E;
    }

    .alert-modern i {
        font-size: 20px;
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

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }

        .service-item-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }

    /* Modal Styles */
    .modal-header {
        background: #7F3D9E;
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Service Delivery Tracking</h1>
        <p class="page-subtitle">Claim #<?= str_pad($claim['id'] ?? 0, 4, '0', STR_PAD_LEFT) ?> — <?= htmlspecialchars($claim['deceased_name'] ?? 'N/A') ?></p>
    </div>
    <a href="/admin/claims" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Back to Claims
    </a>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success'])): ?>
                if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                    ShenaApp.showNotification(<?php echo json_encode($_SESSION['success']); ?>, 'success', 5000);
                } else {
                    alert(<?php echo json_encode($_SESSION['success']); ?>);
                }
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                    ShenaApp.showNotification(<?php echo json_encode($_SESSION['error']); ?>, 'error', 5000);
                } else {
                    alert(<?php echo json_encode($_SESSION['error']); ?>);
                }
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon purple">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="stat-label">Total Services</div>
        <div class="stat-value"><?= count($checklist) ?? 0 ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-label">Completed</div>
        <div class="stat-value"><?= count(array_filter($checklist ?? [], fn($c) => $c['completed'])) ?? 0 ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon orange">
                <i class="fas fa-spinner"></i>
            </div>
        </div>
        <div class="stat-label">In Progress</div>
        <div class="stat-value"><?= (count($checklist ?? [])) - (count(array_filter($checklist ?? [], fn($c) => $c['completed']))) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-percentage"></i>
            </div>
        </div>
        <div class="stat-label">Progress</div>
        <div class="stat-value"><?= $completion_percentage ?? 0 ?>%</div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Left Column - Claim Info & Progress -->
    <div class="col-lg-4">
        <!-- Claim Information Card -->
        <div class="info-card">
            <div class="card-title">
                <i class="fas fa-info-circle"></i>
                Claim Information
            </div>
            <table class="info-table">
                <tr>
                    <th>Claim ID</th>
                    <td>#<?= str_pad($claim['id'] ?? 0, 4, '0', STR_PAD_LEFT) ?></td>
                </tr>
                <tr>
                    <th>Member</th>
                    <td><?= htmlspecialchars(($claim['first_name'] ?? '') . ' ' . ($claim['last_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Member #</th>
                    <td><?= htmlspecialchars($claim['member_number'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Deceased</th>
                    <td><?= htmlspecialchars($claim['deceased_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Date of Death</th>
                    <td><?= !empty($claim['date_of_death']) ? date('M j, Y', strtotime($claim['date_of_death'])) : 'N/A' ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="status-badge <?= ($claim['status'] ?? '') === 'approved' ? 'approved' : 'pending' ?>">
                            <?= ucfirst(str_replace('_', ' ', $claim['status'] ?? 'pending')) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Service Type</th>
                    <td>
                        <?php if (($claim['service_delivery_type'] ?? '') === 'cash_alternative'): ?>
                            <span class="status-badge cash">
                                <i class="fas fa-money-bill"></i>
                                Cash Alternative
                            </span>
                        <?php else: ?>
                            <span class="status-badge services">
                                <i class="fas fa-hands-helping"></i>
                                Standard Services
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Delivery Date</th>
                    <td><?= !empty($claim['services_delivery_date']) ? date('M j, Y', strtotime($claim['services_delivery_date'])) : 'Not set' ?></td>
                </tr>
                <tr>
                    <th>Mortuary Days</th>
                    <td><?= (int)($claim['mortuary_days_count'] ?? 0) ?> days (max 14)</td>
                </tr>
            </table>
        </div>

        <!-- Progress Card -->
        <div class="progress-card">
            <div class="card-title">
                <i class="fas fa-chart-pie"></i>
                Overall Progress
            </div>
            <div class="progress-circle-container">
                <div class="progress-circle">
                    <svg width="150" height="150">
                        <circle class="progress-circle-bg" cx="75" cy="75" r="65"/>
                        <circle class="progress-circle-fill" cx="75" cy="75" r="65"
                                stroke-dasharray="<?= ($completion_percentage / 100) * 408.4 ?> 408.4"/>
                    </svg>
                    <div class="progress-circle-text">
                        <div class="progress-circle-value"><?= $completion_percentage ?? 0 ?>%</div>
                        <div class="progress-circle-label">Complete</div>
                    </div>
                </div>

                <?php if ($completion_percentage == 100): ?>
                    <div class="alert-modern success">
                        <i class="fas fa-check-circle"></i>
                        All services completed!
                    </div>
                    <form method="POST" action="/admin/claims/complete" id="complete-claim-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
                        <button type="button" onclick="confirmCompleteClaim()" class="btn-complete-claim">
                            <i class="fas fa-check-double"></i>
                            Complete Claim
                        </button>
                    </form>
                <?php else: ?>
                    <p style="font-size: 14px; color: #6B7280; margin: 0;">
                        <?= count(array_filter($checklist ?? [], fn($c) => $c['completed'])) ?> of <?= count($checklist ?? []) ?> services completed
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column - Service Checklist -->
    <div class="col-lg-8">
        <div class="info-card">
            <div class="card-title">
                <i class="fas fa-tasks"></i>
                Service Delivery Checklist
            </div>
            <small style="color: #9CA3AF; display: block; margin-bottom: 20px;">
                Per SHENA Companion Policy - Section 3
            </small>

            <?php if (empty($checklist)): ?>
                <div class="alert-modern warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No service checklist found. This may be a cash alternative claim.
                </div>
            <?php else: ?>
                <div class="service-list">
                    <?php 
                    $serviceIcons = [
                        'mortuary_bill' => 'fas fa-hospital',
                        'body_dressing' => 'fas fa-user-tie',
                        'coffin' => 'fas fa-box',
                        'transportation' => 'fas fa-truck',
                        'equipment' => 'fas fa-tools'
                    ];
                    $serviceLabels = [
                        'mortuary_bill' => 'Mortuary Bill Payment',
                        'body_dressing' => 'Body Dressing',
                        'coffin' => 'Executive Coffin Delivery',
                        'transportation' => 'Transportation Arranged',
                        'equipment' => 'Equipment Delivered (Lowering gear, trolley, gazebo, 100 chairs)'
                    ];
                    
                    foreach ($checklist as $item): 
                    ?>
                        <div class="service-item <?= $item['completed'] ? 'completed' : '' ?>">
                            <div class="service-item-header">
                                <div>
                                    <div class="service-item-title">
                                        <?php if ($item['completed']): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php else: ?>
                                            <i class="far fa-circle text-muted"></i>
                                        <?php endif; ?>
                                        <i class="<?= $serviceIcons[$item['service_type']] ?? 'fas fa-check' ?>"></i>
                                        <?= $serviceLabels[$item['service_type']] ?? $item['service_type'] ?>
                                    </div>
                                    <?php if ($item['completed']): ?>
                                        <div class="service-item-meta">
                                            <i class="fas fa-user"></i>
                                            Completed by <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                            on <?= date('M j, Y g:i A', strtotime($item['completed_at'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="service-item-meta">
                                            <i class="fas fa-clock"></i>
                                            Pending completion
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if (!$item['completed']): ?>
                                        <button type="button" class="btn-service complete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#completeServiceModal"
                                                data-service-type="<?= htmlspecialchars($item['service_type']) ?>"
                                                data-service-label="<?= htmlspecialchars($serviceLabels[$item['service_type']] ?? $item['service_type']) ?>">
                                            <i class="fas fa-check"></i>
                                            Mark Completed
                                        </button>
                                    <?php else: ?>
                                        <span class="status-badge completed">
                                            <i class="fas fa-check"></i>
                                            Completed
                                        </span>
                                        <?php if ($item['service_notes']): ?>
                                            <button type="button" class="btn-service view" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewNotesModal"
                                                    data-notes="<?= htmlspecialchars($item['service_notes']) ?>"
                                                    data-service-label="<?= htmlspecialchars($serviceLabels[$item['service_type']] ?? $item['service_type']) ?>">
                                                <i class="fas fa-eye"></i>
                                                Notes
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Complete Service Modal -->
<div class="modal fade" id="completeServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/claims/track/<?= htmlspecialchars($claim['id'] ?? '') ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i>
                        Complete Service
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="service_type" id="modal_service_type">
                    <input type="hidden" name="completed" value="1">
                    
                    <div class="alert-modern info" style="margin-bottom: 16px;">
                        <i class="fas fa-info-circle"></i>
                        <strong id="modal_service_label"></strong> will be marked as completed.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; display: block;">
                            Service Notes (Optional)
                        </label>
                        <textarea name="service_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this service delivery..."
                                  style="width: 100%; padding: 10px 16px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 14px;"></textarea>
                        <small style="color: #9CA3AF; font-size: 12px; margin-top: 4px; display: block;">
                            e.g., Invoice number, delivery details, special arrangements
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-service view" data-bs-dismiss="modal" style="border: 1px solid #E5E7EB; background: white;">
                        Cancel
                    </button>
                    <button type="submit" class="btn-service complete">
                        <i class="fas fa-check"></i>
                        Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notes Modal -->
<div class="modal fade" id="viewNotesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #3B82F6;">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note"></i>
                    Service Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="notes_service_label" style="font-size: 16px; font-weight: 600; color: #1F2937; margin-bottom: 12px;"></h6>
                <div class="alert-modern" style="background: #F9FAFB; border-color: #E5E7EB; color: #374151; margin: 0;">
                    <i class="fas fa-sticky-note" style="color: #6B7280;"></i>
                    <span id="notes_content"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-service view" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle Complete Service Modal
    document.getElementById('completeServiceModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const serviceType = button.getAttribute('data-service-type');
        const serviceLabel = button.getAttribute('data-service-label');
        
        document.getElementById('modal_service_type').value = serviceType;
        document.getElementById('modal_service_label').textContent = serviceLabel;
    });

    // Handle View Notes Modal
    document.getElementById('viewNotesModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const notes = button.getAttribute('data-notes');
        const serviceLabel = button.getAttribute('data-service-label');
        
        document.getElementById('notes_service_label').textContent = serviceLabel;
        document.getElementById('notes_content').textContent = notes;
    });

    // Confirm Complete Claim
    function confirmCompleteClaim() {
        if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
            ShenaApp.confirmAction(
                'Mark this claim as completed? This finalizes all service deliveries and closes the claim.',
                function() {
                    document.getElementById('complete-claim-form').submit();
                },
                null,
                { type: 'success', title: 'Complete Claim', confirmText: 'Yes, Complete' }
            );
        } else {
            if (confirm('Mark this claim as completed? This finalizes all service deliveries and closes the claim.')) {
                document.getElementById('complete-claim-form').submit();
            }
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
