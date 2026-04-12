<?php
$claim = $claim ?? [];
$documents = $documents ?? [];
$documentStatus = $document_status ?? ['complete' => false, 'missing' => [], 'uploaded' => []];
$requiredDocuments = $required_documents ?? [];
$documentsByType = [];
foreach ($documents as $document) {
    $documentsByType[$document['document_type']][] = $document;
}

$status = $claim['status'] ?? 'submitted';
$statusLabel = ucfirst(str_replace('_', ' ', $status));
$serviceType = $claim['service_delivery_type'] ?? ($claim['settlement_type'] ?? 'standard_services');
$serviceLabel = $serviceType === 'cash_alternative' ? 'Cash Alternative' : 'Standard Services';

$claimAmount = $claim['claim_amount'] ?? 0;
$approvedAmount = $claim['approved_amount'] ?? null;
$cashAmount = $claim['cash_alternative_amount'] ?? null;

$statusClasses = [
    'submitted' => 'status-blue',
    'under_review' => 'status-amber',
    'approved' => 'status-green',
    'paid' => 'status-green',
    'completed' => 'status-green',
    'rejected' => 'status-red'
];
$statusClass = $statusClasses[$status] ?? 'status-gray';
$submittedAt = !empty($claim['created_at']) ? date('M d, Y H:i', strtotime($claim['created_at'])) : 'N/A';
$dateOfDeath = !empty($claim['date_of_death']) ? date('M d, Y', strtotime($claim['date_of_death'])) : 'N/A';
$claimId = (int)($claim['id'] ?? 0);
?>

<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .page-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
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

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-blue {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .status-amber {
        background: #FED7AA;
        color: #92400E;
    }

    .status-green {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-red {
        background: #FEE2E2;
        color: #991B1B;
    }

    .status-gray {
        background: #E5E7EB;
        color: #374151;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 16px;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border-color: #7F3D9E;
        transform: translateY(-2px);
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    }

    .stat-label {
        font-size: 12px;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
    }

    .stat-meta {
        font-size: 12px;
        color: #6B7280;
        margin-top: 6px;
    }

    .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr);
        gap: 20px;
    }

    .info-card {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .info-title {
        font-size: 12px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table th {
        text-align: left;
        font-size: 12px;
        color: #6B7280;
        padding: 6px 0;
        width: 40%;
    }

    .info-table td {
        font-size: 13px;
        color: #111827;
        padding: 6px 0;
    }

    .doc-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 10px;
    }

    .doc-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        border-radius: 8px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
    }

    .doc-name {
        font-size: 13px;
        color: #1F2937;
        font-weight: 600;
    }

    .doc-status {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 4px 8px;
        border-radius: 999px;
    }

    .doc-status.uploaded {
        background: #D1FAE5;
        color: #065F46;
    }

    .doc-status.missing {
        background: #FEE2E2;
        color: #991B1B;
    }

    .doc-files {
        font-size: 11px;
        color: #6B7280;
        margin-top: 4px;
    }

    .action-list {
        display: grid;
        gap: 10px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .action-btn.primary {
        background: #7F3D9E;
        color: white;
        border-color: #7F3D9E;
    }

    .action-btn.primary:hover {
        background: #6B2D8A;
        border-color: #6B2D8A;
    }

    .action-btn.success {
        background: #10B981;
        color: white;
        border-color: #10B981;
    }

    .action-btn.success:hover {
        background: #059669;
        border-color: #059669;
    }

    .action-btn.warning {
        background: #F59E0B;
        color: white;
        border-color: #F59E0B;
    }

    .action-btn.warning:hover {
        background: #D97706;
        border-color: #D97706;
    }

    .action-btn.danger {
        background: #EF4444;
        color: white;
        border-color: #EF4444;
    }

    .action-btn.danger:hover {
        background: #DC2626;
        border-color: #DC2626;
    }

    .action-btn.ghost {
        background: white;
        color: #6B7280;
        border-color: #E5E7EB;
    }

    .action-btn.ghost:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Claim Details</h1>
        <p class="page-subtitle">Claim #<?= htmlspecialchars($claim['id'] ?? '') ?> • <?= htmlspecialchars(($claim['first_name'] ?? '') . ' ' . ($claim['last_name'] ?? '')) ?></p>
    </div>
    <div class="header-actions">
        <span class="status-badge <?= $statusClass; ?>"><?= htmlspecialchars($statusLabel) ?></span>
        <a href="/admin/claims" class="action-btn ghost">
            <i class="fas fa-arrow-left"></i> Back to Claims
        </a>
        <?php if (($claim['status'] ?? '') === 'approved'): ?>
            <a href="/admin/claims/track/<?= $claimId ?>" class="action-btn primary">
                <i class="fas fa-truck"></i> Track Services
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Cash Alternative Request Alert -->
<?php if (!empty($claim['cash_alternative_reason']) && $status === 'submitted'): ?>
<div class="alert alert-warning" style="margin-bottom: 24px; padding: 16px; border-radius: 12px; background: #FEF3C7; border-left: 4px solid #F59E0B;">
    <div style="display: flex; align-items: start; gap: 12px;">
        <i class="fas fa-exclamation-circle" style="color: #F59E0B; font-size: 20px; margin-top: 2px;"></i>
        <div style="flex: 1;">
            <h4 style="margin: 0 0 8px 0; color: #92400E; font-size: 16px; font-weight: 600;">
                <i class="fas fa-coins"></i> Cash Alternative Requested (KSH 20,000)
            </h4>
            <p style="margin: 0 0 12px 0; color: #78350F; font-size: 14px;">
                The member has requested a cash alternative payment instead of standard service delivery. Per Policy Section 12, this requires mutual agreement and exceptional circumstances.
            </p>
            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #FDE68A;">
                <strong style="color: #78350F; font-size: 13px;">Member's Reason:</strong>
                <p style="margin: 8px 0 0 0; color: #1F2937; font-size: 14px; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($claim['cash_alternative_reason'])) ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon purple"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="stat-label">Status</div>
                <div class="stat-value"><?= htmlspecialchars($statusLabel) ?></div>
            </div>
        </div>
        <div class="stat-meta">Last updated: <?= !empty($claim['updated_at']) ? date('M d, Y', strtotime($claim['updated_at'])) : 'N/A' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue"><i class="fas fa-handshake"></i></div>
            <div>
                <div class="stat-label">Service Type</div>
                <div class="stat-value"><?= htmlspecialchars($serviceLabel) ?></div>
            </div>
        </div>
        <div class="stat-meta">Delivery date: <?= !empty($claim['services_delivery_date']) ? date('M d, Y', strtotime($claim['services_delivery_date'])) : 'Not set' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green"><i class="fas fa-coins"></i></div>
            <div>
                <div class="stat-label">Claim Amount</div>
                <div class="stat-value">KES <?= number_format((float)$claimAmount, 2) ?></div>
            </div>
        </div>
        <div class="stat-meta">Approved: <?= $approvedAmount !== null ? 'KES ' . number_format((float)$approvedAmount, 2) : 'Pending' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon orange"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="stat-label">Submitted</div>
                <div class="stat-value"><?= $submittedAt ?></div>
            </div>
        </div>
        <div class="stat-meta">Date of death: <?= $dateOfDeath ?></div>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="info-card">
            <div class="info-title">Claim Information</div>
            <table class="info-table">
                <tr>
                    <th>Claim ID</th>
                    <td><?= htmlspecialchars($claim['id'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Submitted</th>
                    <td><?= $submittedAt ?></td>
                </tr>
                <tr>
                    <th>Service Type</th>
                    <td><?= htmlspecialchars($serviceLabel) ?></td>
                </tr>
                <tr>
                    <th>Claim Amount</th>
                    <td>KES <?= number_format((float)$claimAmount, 2) ?></td>
                </tr>
                <?php if ($approvedAmount !== null): ?>
                <tr>
                    <th>Approved Amount</th>
                    <td>KES <?= number_format((float)$approvedAmount, 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($cashAmount !== null): ?>
                <tr>
                    <th>Cash Amount</th>
                    <td>KES <?= number_format((float)$cashAmount, 2) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="info-card">
            <div class="info-title">Member Information</div>
            <table class="info-table">
                <tr>
                    <th>Member</th>
                    <td><?= htmlspecialchars(($claim['first_name'] ?? '') . ' ' . ($claim['last_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Member Number</th>
                    <td><?= htmlspecialchars($claim['member_number'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($claim['email'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?= htmlspecialchars($claim['phone'] ?? 'N/A') ?></td>
                </tr>
            </table>
        </div>

        <div class="info-card">
            <div class="info-title">Deceased Information</div>
            <table class="info-table">
                <tr>
                    <th>Name</th>
                    <td><?= htmlspecialchars($claim['deceased_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Date of Death</th>
                    <td><?= $dateOfDeath ?></td>
                </tr>
                <tr>
                    <th>Place of Death</th>
                    <td><?= htmlspecialchars($claim['place_of_death'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Cause of Death</th>
                    <td><?= htmlspecialchars($claim['cause_of_death'] ?? 'N/A') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div>
        <div class="info-card">
            <div class="info-title">Documents</div>
            <ul class="doc-list">
                <?php foreach ($requiredDocuments as $docType => $label): ?>
                    <?php
                    $uploadedDocs = $documentsByType[$docType] ?? [];
                    $isUploaded = !empty($uploadedDocs);
                    $fileNames = [];
                    foreach ($uploadedDocs as $doc) {
                        $fileNames[] = $doc['original_name'] ?? $doc['file_name'] ?? $label;
                    }
                    ?>
                    <li class="doc-item">
                        <div>
                            <div class="doc-name"><?= htmlspecialchars($label) ?></div>
                            <?php if (!empty($fileNames)): ?>
                                <div class="doc-files"><?= htmlspecialchars(implode(', ', $fileNames)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="doc-status <?= $isUploaded ? 'uploaded' : 'missing' ?>">
                            <?= $isUploaded ? 'Uploaded' : 'Missing' ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($documentStatus['complete'] ?? false): ?>
                <div class="mt-3 text-success" style="font-size: 12px; font-weight: 700;">
                    All required documents received.
                </div>
            <?php else: ?>
                <div class="mt-3 text-muted" style="font-size: 12px;">
                    Missing documents: <?= htmlspecialchars(implode(', ', $documentStatus['missing'] ?? [])) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="info-card">
            <div class="info-title">Actions</div>
            <div class="action-list">
                <?php if ($status === 'submitted' || $status === 'under_review'): ?>
                    <button type="button" class="action-btn success" data-bs-toggle="modal" data-bs-target="#approveClaimModal">
                        <i class="fas fa-check"></i> Approve Services
                    </button>
                    <button type="button" class="action-btn warning" data-bs-toggle="modal" data-bs-target="#approveCashModal">
                        <i class="fas fa-money-bill-wave"></i> Approve Cash Alternative
                    </button>
                    <button type="button" class="action-btn danger" data-bs-toggle="modal" data-bs-target="#rejectClaimModal">
                        <i class="fas fa-times"></i> Reject Claim
                    </button>
                <?php elseif ($status === 'approved'): ?>
                    <a href="/admin/claims/track/<?= $claimId ?>" class="action-btn primary">
                        <i class="fas fa-truck"></i> Track Services
                    </a>
                <?php else: ?>
                    <div class="text-muted" style="font-size: 12px;">
                        No actions available for this status.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($status === 'submitted' || $status === 'under_review'): ?>
<div class="modal fade" id="approveClaimModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/claims/<?= $claimId ?>/approve">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Claim for Services</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="claim_id" value="<?= $claimId ?>">
                    <div class="mb-3">
                        <label class="form-label">Service Delivery Date</label>
                        <input type="date" name="services_delivery_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any processing notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Services</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="approveCashModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/claims/<?= $claimId ?>/approve-cash">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Cash Alternative</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="claim_id" value="<?= $claimId ?>">
                    <div class="mb-3">
                        <label class="form-label">Requested By</label>
                        <select name="requested_by" class="form-select">
                            <option value="company">Company</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (min 20 characters)</label>
                        <textarea name="cash_alternative_reason" class="form-control" rows="4" placeholder="Explain why cash alternative is required..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Approve Cash Alternative</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectClaimModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/claims/<?= $claimId ?>/reject">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="claim_id" value="<?= $claimId ?>">
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Provide a clear reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
