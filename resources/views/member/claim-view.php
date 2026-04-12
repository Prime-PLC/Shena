<?php
$page = 'claims';
include __DIR__ . '/../layouts/member-header.php';

$claim = $claim ?? [];
$documents = $documents ?? [];
$beneficiary = $beneficiary ?? null;
$serviceChecklist = $serviceChecklist ?? null;

$status = $claim['status'] ?? 'submitted';
$statusLabel = ucfirst(str_replace('_', ' ', $status));
$serviceType = $claim['service_delivery_type'] ?? 'standard_services';
$cashAlternativeRequested = !empty($claim['cash_alternative_reason']);

// Status information
$statusConfig = [
    'submitted' => ['icon' => 'fa-clock', 'color' => '#3B82F6', 'bg' => '#DBEAFE', 'message' => 'Your claim has been submitted and is awaiting review.'],
    'under_review' => ['icon' => 'fa-search', 'color' => '#F59E0B', 'bg' => '#FEF3C7', 'message' => 'Your claim is currently being reviewed by our team.'],
    'approved' => ['icon' => 'fa-check-circle', 'color' => '#10B981', 'bg' => '#D1FAE5', 'message' => 'Your claim has been approved and services are being arranged.'],
    'paid' => ['icon' => 'fa-check-circle', 'color' => '#10B981', 'bg' => '#D1FAE5', 'message' => 'Your claim has been paid and completed.'],
    'completed' => ['icon' => 'fa-check-circle', 'color' => '#10B981', 'bg' => '#D1FAE5', 'message' => 'Your claim has been successfully completed.'],
    'rejected' => ['icon' => 'fa-times-circle', 'color' => '#EF4444', 'bg' => '#FEE2E2', 'message' => 'Your claim was not approved. Contact support for more information.']
];
$currentStatus = $statusConfig[$status] ?? $statusConfig['submitted'];
?>

<style>
main {
    padding: 0 !important;
    margin: 0 !important;
}

.claim-view-container {
    padding: 30px;
    background: #F8F9FC;
    max-width: 1200px;
    margin: 0 auto;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #7F20B0;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 20px;
    transition: gap 0.2s;
}

.back-link:hover {
    gap: 12px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #4A1468;
    margin: 0 0 8px 0;
}

.claim-id {
    color: #6B7280;
    font-size: 0.9rem;
    margin-bottom: 30px;
}

.status-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.status-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.status-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.status-info h3 {
    margin: 0 0 4px 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1F2937;
}

.status-message {
    color: #6B7280;
    margin: 0;
    line-height: 1.6;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.info-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-card h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6B7280;
    margin: 0 0 16px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-row {
    margin-bottom: 12px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-size: 0.875rem;
    color: #6B7280;
    margin-bottom: 4px;
}

.info-value {
    font-size: 1rem;
    color: #1F2937;
    font-weight: 500;
}

.documents-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.documents-card h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 16px 0;
}

.document-list {
    display: grid;
    gap: 12px;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #F9FAFB;
    border-radius: 8px;
    transition: background 0.2s;
}

.document-item:hover {
    background: #F3F4F6;
}

.document-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #7F20B0;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.document-info {
    flex: 1;
}

.document-name {
    font-weight: 600;
    color: #1F2937;
    margin-bottom: 2px;
}

.document-size {
    font-size: 0.875rem;
    color: #6B7280;
}

.cash-alt-card {
    background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
    border-left: 4px solid #F59E0B;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.cash-alt-card h3 {
    color: #92400E;
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0 0 12px 0;
}

.cash-alt-reason {
    color: #78350F;
    line-height: 1.6;
}

.timeline-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.timeline-card h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 20px 0;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: #E5E7EB;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -26px;
    top: 4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #7F20B0;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #E5E7EB;
}

.timeline-dot.current {
    background: #F59E0B;
    box-shadow: 0 0 0 2px #FEF3C7;
}

.timeline-content {
    padding-bottom: 20px;
}

.timeline-label {
    font-weight: 600;
    color: #1F2937;
    margin-bottom: 4px;
}

.timeline-date {
    font-size: 0.875rem;
    color: #6B7280;
}

@media (max-width: 768px) {
    .claim-info-grid, .claim-detail-grid {
        grid-template-columns: 1fr !important;
    }
    .claim-view-container { padding: 16px !important; }
}
</style>

<div class="claim-view-container">
    <a href="/claims" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Claims
    </a>
    
    <h1 class="page-title">Claim Details</h1>
    <p class="claim-id">Claim ID: #<?php echo htmlspecialchars($claim['id'] ?? 'N/A'); ?></p>
    
    <!-- Status Card -->
    <div class="status-card">
        <div class="status-header">
            <div class="status-icon" style="background: <?php echo $currentStatus['bg']; ?>; color: <?php echo $currentStatus['color']; ?>">
                <i class="fas <?php echo $currentStatus['icon']; ?>"></i>
            </div>
            <div class="status-info">
                <h3><?php echo htmlspecialchars($statusLabel); ?></h3>
            </div>
        </div>
        <p class="status-message"><?php echo $currentStatus['message']; ?></p>
    </div>
    
    <!-- Cash Alternative Notice -->
    <?php if ($cashAlternativeRequested): ?>
    <div class="cash-alt-card">
        <h3><i class="fas fa-info-circle"></i> Cash Alternative Requested</h3>
        <p class="cash-alt-reason">
            <strong>Your reason:</strong><br>
            <?php echo nl2br(htmlspecialchars($claim['cash_alternative_reason'])); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Information Grid -->
    <div class="info-grid">
        <!-- Deceased Information -->
        <div class="info-card">
            <h3>Deceased Information</h3>
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['deceased_name'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">ID Number</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['deceased_id_number'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date of Death</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['date_of_death'] ? date('F j, Y', strtotime($claim['date_of_death'])) : 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Place of Death</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['place_of_death'] ?? 'N/A'); ?></div>
            </div>
            <?php if (!empty($claim['cause_of_death'])): ?>
            <div class="info-row">
                <div class="info-label">Cause of Death</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['cause_of_death']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Beneficiary Information -->
        <?php if ($beneficiary): ?>
        <div class="info-card">
            <h3>Beneficiary Information</h3>
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value"><?php echo htmlspecialchars($beneficiary['name'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Relationship</div>
                <div class="info-value"><?php echo htmlspecialchars($beneficiary['relationship'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">ID Number</div>
                <div class="info-value"><?php echo htmlspecialchars($beneficiary['id_number'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value"><?php echo htmlspecialchars($beneficiary['phone'] ?? 'N/A'); ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mortuary Information -->
        <?php if (!empty($claim['mortuary_name'])): ?>
        <div class="info-card">
            <h3>Mortuary Information</h3>
            <div class="info-row">
                <div class="info-label">Mortuary Name</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['mortuary_name']); ?></div>
            </div>
            <?php if (!empty($claim['mortuary_bill_amount'])): ?>
            <div class="info-row">
                <div class="info-label">Bill Amount</div>
                <div class="info-value">KES <?php echo number_format($claim['mortuary_bill_amount'], 2); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($claim['mortuary_days_count'])): ?>
            <div class="info-row">
                <div class="info-label">Days in Mortuary</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['mortuary_days_count']); ?> days</div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Claim Details -->
        <div class="info-card">
            <h3>Claim Details</h3>
            <div class="info-row">
                <div class="info-label">Service Type</div>
                <div class="info-value"><?php echo $serviceType === 'cash_alternative' ? 'Cash Alternative' : 'Standard Services'; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Submitted On</div>
                <div class="info-value"><?php echo htmlspecialchars($claim['created_at'] ? date('F j, Y g:i A', strtotime($claim['created_at'])) : 'N/A'); ?></div>
            </div>
            <?php if (!empty($claim['approved_amount'])): ?>
            <div class="info-row">
                <div class="info-label">Approved Amount</div>
                <div class="info-value">KES <?php echo number_format($claim['approved_amount'], 2); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Documents -->
    <?php if (!empty($documents)): ?>
    <div class="documents-card">
        <h3><i class="fas fa-paperclip"></i> Uploaded Documents</h3>
        <div class="document-list">
            <?php 
            $documentLabels = [
                'id_copy' => 'ID/Birth Certificate Copy',
                'chief_letter' => "Chief's Letter",
                'mortuary_invoice' => 'Mortuary Invoice',
                'death_certificate' => 'Death Certificate'
            ];
            
            foreach ($documents as $doc): 
                $label = $documentLabels[$doc['document_type']] ?? ucwords(str_replace('_', ' ', $doc['document_type']));
                $fileSize = isset($doc['file_size']) ? round($doc['file_size'] / 1024, 1) . ' KB' : 'Unknown';
            ?>
            <div class="document-item">
                <div class="document-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="document-info">
                    <div class="document-name"><?php echo htmlspecialchars($label); ?></div>
                    <div class="document-size"><?php echo $fileSize; ?> • Uploaded <?php echo date('M d, Y', strtotime($doc['uploaded_at'] ?? 'now')); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Timeline -->
    <div class="timeline-card">
        <h3><i class="fas fa-history"></i> Claim Timeline</h3>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-label">Claim Submitted</div>
                    <div class="timeline-date"><?php echo htmlspecialchars($claim['created_at'] ? date('F j, Y g:i A', strtotime($claim['created_at'])) : 'N/A'); ?></div>
                </div>
            </div>
            
            <?php if ($status !== 'submitted'): ?>
            <div class="timeline-item">
                <div class="timeline-dot <?php echo in_array($status, ['under_review', 'approved', 'paid', 'completed']) ? '' : 'current'; ?>"></div>
                <div class="timeline-content">
                    <div class="timeline-label">Under Review</div>
                    <div class="timeline-date"><?php echo !empty($claim['reviewed_at']) ? date('F j, Y g:i A', strtotime($claim['reviewed_at'])) : 'In Progress'; ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($status, ['approved', 'paid', 'completed'])): ?>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-label">Approved</div>
                    <div class="timeline-date"><?php echo !empty($claim['approved_at']) ? date('F j, Y g:i A', strtotime($claim['approved_at'])) : date('F j, Y'); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($status, ['paid', 'completed'])): ?>
            <div class="timeline-item">
                <div class="timeline-dot current"></div>
                <div class="timeline-content">
                    <div class="timeline-label">Completed</div>
                    <div class="timeline-date"><?php echo !empty($claim['paid_date']) ? date('F j, Y g:i A', strtotime($claim['paid_date'])) : 'Completed'; ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($status === 'rejected'): ?>
            <div class="timeline-item">
                <div class="timeline-dot current" style="background: #EF4444;"></div>
                <div class="timeline-content">
                    <div class="timeline-label">Rejected</div>
                    <div class="timeline-date"><?php echo !empty($claim['rejected_at']) ? date('F j, Y g:i A', strtotime($claim['rejected_at'])) : 'Rejected'; ?></div>
                    <?php if (!empty($claim['rejection_reason'])): ?>
                    <div style="margin-top: 8px; color: #EF4444; font-size: 0.875rem;">
                        <?php echo nl2br(htmlspecialchars($claim['rejection_reason'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($claim['notes'])): ?>
    <div class="info-card" style="margin-top: 24px;">
        <h3>Additional Notes</h3>
        <p style="color: #6B7280; line-height: 1.6; margin: 0;">
            <?php echo nl2br(htmlspecialchars($claim['notes'])); ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
