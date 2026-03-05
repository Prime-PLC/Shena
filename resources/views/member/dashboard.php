<?php 
$page = 'dashboard';
include __DIR__ . '/../layouts/member-header.php';

$memberData = is_array($member ?? null) ? $member : (is_object($member ?? null) ? get_object_vars($member) : []); 
$totalPaid = $total_paid ?? 0;
$monthsCovered = $months_covered ?? 0;
$nextDueDate = $next_due_date ?? 'N/A';
$currentMonthStatus = $current_month_status ?? 'DUE';
$maturityProgress = $maturity_progress ?? 0;
$maturityMonthsCompleted = $maturity_months_completed ?? 0;
$maturityMonthsTotal = $maturity_months_total ?? 0;
$beneficiaryList = $beneficiaries ?? [];
$memberStatus = strtoupper($memberData['status'] ?? 'ACTIVE');
$statusLabel = ($memberData['status'] ?? '') === 'active' ? 'ACTIVE MEMBER' : $memberStatus;
$showProfileCompletionPopup = !empty($show_profile_completion_popup);
$missingFields = $missing_profile_fields ?? [];
?>

<style>
.dashboard-container {
    padding: 40px;
    background: #F8F9FC;
}

.hero-card {
    background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.hero-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    z-index: 0;
}

.hero-profile {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.hero-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #A855F7, #EC4899);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: white;
    border: 4px solid rgba(255, 255, 255, 0.2);
}

.hero-info h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #10B981;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.hero-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.95rem;
    margin: 0;
}

.pay-btn {
    position: absolute;
    top: 40px;
    right: 40px;
    background: white;
    color: #7F3D9E;
    border: none;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s;
    text-decoration: none;
    z-index: 2;
}

.pay-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.stat-card h3 {
    font-size: 0.85rem;
    color: #6B7280;
    font-weight: 600;
    margin-bottom: 25px;
    letter-spacing: 0.5px;
}

.stat-card p {
    font-size: 0.8rem;
    color: #9CA3AF;
    margin-bottom: 0;
}

.contribution-overview {
    display: flex;
    align-items: center;
    gap: 30px;
}

.contribution-item {
    text-align: center;
}

.contribution-item h4 {
    font-size: 0.7rem;
    color: #9CA3AF;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.contribution-item h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #D1FAE5;
    color: #059669;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
}

.next-due-card {
    background: #7F3D9E;
    color: white;
    padding: 25px;
    border-radius: 16px;
    text-align: center;
}

.next-due-card h4 {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.next-due-card h2 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.maturity-card h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.maturity-card h3 i {
    color: #F59E0B;
    font-size: 1.2rem;
}

.maturity-card p {
    font-size: 0.85rem;
    color: #6B7280;
    line-height: 1.6;
    margin-bottom: 20px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.progress-info span {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1F2937;
}

.progress-info strong {
    font-size: 1.1rem;
    color: #7F3D9E;
}

.progress-bar-container {
    width: 100%;
    height: 12px;
    background: #E5E7EB;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #7F3D9E 0%, #A855F7 100%);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.coverage-date {
    background: #FEF3C7;
    padding: 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.coverage-date i {
    color: #F59E0B;
    font-size: 1.3rem;
}

.coverage-date div h5 {
    font-size: 0.75rem;
    color: #92400E;
    margin: 0 0 4px 0;
    font-weight: 600;
}

.coverage-date div p {
    font-size: 0.95rem;
    font-weight: 700;
    color: #78350F;
    margin: 0;
}

.bottom-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
}

.payment-history-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.card-header-custom h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.export-btn {
    background: transparent;
    border: none;
    color: #7F3D9E;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.payment-table {
    width: 100%;
}

.payment-table thead {
    border-bottom: 1px solid #E5E7EB;
}

.payment-table th {
    padding: 12px 8px;
    text-align: left;
    font-size: 0.75rem;
    color: #6B7280;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.payment-table td {
    padding: 18px 8px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 0.9rem;
    color: #1F2937;
}

.ref-number {
    color: #7F3D9E;
    font-weight: 600;
}

.amount-cell {
    font-weight: 700;
    color: #1F2937;
}

.success-badge {
    background: #D1FAE5;
    color: #059669;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-block;
}

.receipt-icon {
    color: #7F3D9E;
    font-size: 1.1rem;
    cursor: pointer;
}

.dependents-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.dependent-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #F3F4F6;
}

.dependent-item:last-child {
    border-bottom: none;
}

.dependent-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #EC4899, #F472B6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}

.dependent-info h4 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.dependent-info p {
    font-size: 0.8rem;
    color: #6B7280;
    margin: 0;
}

.active-badge {
    background: #D1FAE5;
    color: #059669;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 700;
    margin-left: auto;
}

.add-dependent-btn {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
}

.add-dependent-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.add-dependent-btn i {
    font-size: 14px;
}

.support-card {
    background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    margin-top: 20px;
}

.support-card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 10px 0;
}

.support-card p {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    line-height: 1.5;
}

@media (max-width: 1024px) {
    .stats-grid, .bottom-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-container">
    <!-- Hero Card -->
    <div class="hero-card">
        <div class="hero-profile">
            <div class="hero-avatar">
                <?php echo strtoupper(substr($memberData['first_name'] ?? 'J', 0, 1)); ?>
            </div>
            <div class="hero-info">
                <h2>
                    <?php echo htmlspecialchars($memberData['first_name'] ?? 'John') . ' ' . htmlspecialchars($memberData['last_name'] ?? 'Doe'); ?>
                    <span class="status-badge">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($statusLabel); ?>
                    </span>
                </h2>
                <p class="hero-subtitle">
                    <?php echo ucfirst($memberData['package'] ?? 'Premium'); ?> Plan • Member since <?php echo isset($memberData['created_at']) ? date('M Y', strtotime($memberData['created_at'])) : 'Jan 2023'; ?>
                </p>
            </div>
        </div>
        <a class="pay-btn" href="/payments" role="button">
            <i class="fas fa-credit-card"></i> Pay Contribution
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <!-- Contribution Overview -->
        <div class="stat-card">
            <h3>Contribution Overview</h3>
            <p>Tracking your <?php echo date('Y'); ?> contribution history</p>
            <div class="contribution-overview" style="margin-top: 20px;">
                <div class="contribution-item">
                    <h4>TOTAL PAID</h4>
                    <h2>KES <?php echo number_format($totalPaid, 2); ?></h2>
                </div>
                <div class="contribution-item">
                    <h4>MONTHS COVERED</h4>
                    <h2><?php echo $monthsCovered; ?>/12</h2>
                </div>
                <div class="contribution-item">
                    <h4>CURRENT MONTH</h4>
                    <span class="status-indicator">
                        <i class="fas fa-check-circle"></i> <?php echo $currentMonthStatus; ?>
                    </span>
                </div>
                <div class="next-due-card">
                    <h4>NEXT DUE</h4>
                    <h2><?php echo $nextDueDate; ?></h2>
                </div>
            </div>
        </div>

        <!-- Maturity Progress -->
        <div class="stat-card maturity-card">
            <h3>
                <i class="fas fa-trophy"></i> Maturity Progress
            </h3>
            <p>Waiting period completion status for full funeral benefit coverage.</p>
            <div class="progress-info">
                <span>
                    <?php echo $maturityMonthsTotal > 0 ? $maturityMonthsCompleted . ' OF ' . $maturityMonthsTotal . ' MONTHS' : 'NOT STARTED'; ?>
                </span>
                <strong><?php echo $maturityProgress; ?>%</strong>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $maturityProgress; ?>%"></div>
            </div>
            <div class="coverage-date">
                <i class="fas fa-calendar-check"></i>
                <div>
                    <h5>Full Coverage Effective</h5>
                    <p><?php echo isset($memberData['maturity_ends']) ? date('M j, Y', strtotime($memberData['maturity_ends'])) : 'Pending'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="bottom-grid">
        <!-- Recent Payment History -->
        <div class="payment-history-card">
            <div class="card-header-custom">
                <h3>Recent Payment History</h3>
                <button class="export-btn" type="button" <?php echo !empty($recent_payments) ? 'onclick="window.location.href=\'/member/payments/export\'"' : 'disabled'; ?>>
                    <i class="fas fa-download"></i> Export History
                </button>
            </div>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>TRANSACTION DATE</th>
                        <th>REFERENCE</th>
                        <th>AMOUNT</th>
                        <th>STATUS</th>
                        <th>RECEIPT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_payments)): ?>
                        <?php foreach (array_slice($recent_payments, 0, 3) as $payment): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                            <td class="ref-number">TXN-<?php echo $payment['mpesa_receipt_number'] ?? 'RR2941'; ?></td>
                            <td class="amount-cell">KES <?php echo number_format($payment['amount'], 2); ?></td>
                            <td><span class="success-badge"><?php echo strtoupper($payment['status'] ?? 'SUCCESS'); ?></span></td>
                            <td><i class="fas fa-receipt receipt-icon"></i></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6B7280; padding: 30px;">
                                No payments recorded yet. Start by making your first contribution.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Dependents -->
        <div class="dependents-card">
            <div class="card-header-custom">
                <h3>Dependents</h3>
                <button class="add-dependent-btn" onclick="window.location.href='/beneficiaries'">
                    <i class="fas fa-plus"></i> Add Dependent
                </button>
            </div>
            <?php if (!empty($beneficiaryList)): ?>
                <?php foreach ($beneficiaryList as $dependent): ?>
                <div class="dependent-item">
                    <div class="dependent-avatar"><?php echo strtoupper(substr($dependent['full_name'] ?? 'B', 0, 1)); ?></div>
                    <div class="dependent-info">
                        <h4><?php echo htmlspecialchars($dependent['full_name'] ?? ''); ?></h4>
                        <p><?php echo htmlspecialchars($dependent['relationship'] ?? ''); ?></p>
                    </div>
                    <span class="active-badge"><?php echo !empty($dependent['is_active']) ? 'ACTIVE' : 'INACTIVE'; ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 20px; color: #6B7280;">
                    <i class="fas fa-users" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
                    <p style="margin: 0;">No beneficiaries registered yet.</p>
                    <p style="font-size: 0.85rem; margin: 8px 0 0 0;">Click "Add Dependent" to register your family members.</p>
                </div>
            <?php endif; ?>
            
            <!-- 24/7 Support Card -->
            <div class="support-card">
                <h3>24/7 Support</h3>
                <p>Immediate funeral assistance and claim reporting. We are with you.</p>
            </div>
        </div>
    </div>
</div>

<?php if ($showProfileCompletionPopup): ?>
<div class="modal fade" id="completeProfileModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%); color: #fff;">
                <h5 class="modal-title" style="font-weight: 700;">Complete Your Account Details</h5>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <p style="color:#4B5563; margin-bottom: 18px;">Welcome to SHENA. Please complete your profile before continuing.</p>
                <form method="POST" action="/member/profile/complete">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">
                    <?php $popupData = $profile_completion_form_data ?? []; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">National ID <span style="color:#DC2626">*</span></label>
                            <input type="text" name="national_id" class="form-control" value="<?php echo htmlspecialchars($popupData['national_id'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth <span style="color:#DC2626">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($popupData['date_of_birth'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address <span style="color:#DC2626">*</span></label>
                            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($popupData['address'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next of Kin Name <span style="color:#DC2626">*</span></label>
                            <input type="text" name="next_of_kin" class="form-control" value="<?php echo htmlspecialchars($popupData['next_of_kin'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Relationship</label>
                            <input type="text" name="next_of_kin_relationship" class="form-control" placeholder="e.g. Spouse" value="<?php echo htmlspecialchars($popupData['next_of_kin_relationship'] ?? '', ENT_QUOTES); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Next of Kin Phone <span style="color:#DC2626">*</span></label>
                            <input type="tel" name="next_of_kin_phone" class="form-control" placeholder="0712345678" value="<?php echo htmlspecialchars($popupData['next_of_kin_phone'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                    </div>

                    <div style="margin-top: 14px; color:#6B7280; font-size: 0.85rem;">
                        Missing fields: <?php echo htmlspecialchars(implode(', ', $missingFields), ENT_QUOTES); ?>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn" style="background:#7F3D9E;color:#fff;padding:10px 22px;">Save and Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($showProfileCompletionPopup): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('completeProfileModal');
    if (modalEl && window.bootstrap) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
