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

@media (max-width: 576px) {
    .dashboard-container { padding: 16px !important; }
    .hero-card { padding: 16px !important; }
    .stat-card { padding: 20px !important; }
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
                        <?php $pmtDate = $payment['created_at'] ?? $payment['payment_date'] ?? null; ?>
                        <tr>
                            <td><?php echo $pmtDate ? date('M d, Y', strtotime($pmtDate)) : '—'; ?></td>
                            <td class="ref-number"><?php echo htmlspecialchars($payment['mpesa_receipt_number'] ?? $payment['transaction_reference'] ?? '—'); ?></td>
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
<style>
/* ─── Onboarding Wizard ─────────────────────────────────────────────────── */
.onb-modal .modal-content { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(127,61,158,0.25); }
.onb-header { background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%); color: #fff; padding: 24px 28px 20px; }
.onb-title { font-family: 'Playfair Display', Georgia, serif; font-size: 1.3rem; font-weight: 700; margin: 0 0 2px; color: #fff; }
.onb-subtitle { color: rgba(255,255,255,0.75); font-size: 0.83rem; margin: 0; }
.onb-stepper { display: flex; align-items: center; margin-top: 16px; }
.onb-bubble { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.18); border: 2px solid rgba(255,255,255,0.35); color: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 700; flex-shrink: 0; transition: all 0.3s ease; cursor: default; }
.onb-bubble.active { background: #C9A659; border-color: #C9A659; color: #fff; box-shadow: 0 0 0 4px rgba(201,166,89,0.3); }
.onb-bubble.done { background: rgba(255,255,255,0.9); border-color: rgba(255,255,255,0.9); color: #7F3D9E; font-size: 0.9rem; }
.onb-connector { flex: 1; height: 2px; background: rgba(255,255,255,0.2); margin: 0 6px; }
.onb-step-pane { padding: 28px 32px; }
.onb-step-heading { font-size: 1rem; font-weight: 700; color: #7F3D9E; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.onb-req { color: #DC2626; }
.onb-footer { background: #F8F7FF; border-top: 1px solid #EDE8F5; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
.onb-btn-next { background: #7F3D9E; color: #fff; border: none; padding: 10px 22px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: background 0.2s; }
.onb-btn-next:hover { background: #6A2F87; }
.onb-btn-next:disabled { opacity: 0.55; cursor: not-allowed; }
/* Plan picker */
.onb-plan-select { font-size: 0.95rem; }
.onb-bracket-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
.onb-bracket-opt { flex: 1 1 calc(50% - 10px); border: 2px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; cursor: pointer; background: #FAFAFA; transition: border-color .18s, background .18s; user-select: none; }
.onb-bracket-opt:hover { border-color: #A78BFA; background: #FAF5FF; }
.onb-bracket-opt.selected { border-color: #7F3D9E; background: #F5F3FF; }
.onb-bracket-label { font-weight: 600; color: #1E1B4B; font-size: 0.88rem; }
.onb-bracket-price { color: #7F3D9E; font-weight: 700; font-size: 0.9rem; margin-top: 2px; }
.onb-plan-summary { background: #F5F3FF; border: 1.5px solid #C4B5FD; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-top: 12px; font-size: 0.93rem; }
.onb-plan-summary strong { color: #4C1D95; }
.onb-plan-summary-price { font-weight: 700; color: #7F3D9E; }
/* Step 4: Pay */
.onb-pay-box { background: #FAFAFA; border: 1px solid #E5E7EB; border-radius: 16px; padding: 28px 24px; text-align: center; }
.onb-pay-amount { font-size: 2.4rem; font-weight: 800; color: #7F3D9E; margin: 6px 0 4px; font-family: 'Playfair Display', Georgia, serif; }
.onb-pay-desc { color: #6B7280; font-size: 0.88rem; margin-bottom: 20px; }
.onb-mpesa-btn { background: #00A651; color: #fff; border: none; padding: 13px 32px; border-radius: 12px; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
.onb-mpesa-btn:hover { background: #008C44; }
.onb-mpesa-btn:disabled { opacity: .55; cursor: not-allowed; }
@media(max-width:576px){ .onb-bracket-opt{ flex: 1 1 100%; } .onb-step-pane{ padding:20px 18px; } }
</style>

<div class="modal fade" id="onboardingWizard" tabindex="-1" aria-labelledby="onbTitle" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered onb-modal">
        <div class="modal-content">

            <!-- ── Header ─────────────────────────────────────────── -->
            <div class="onb-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="onb-title" id="onbTitle">Complete Your SHENA Registration</h5>
                        <p class="onb-subtitle" id="onb-step-label">Step 1 of 4 — Personal Details</p>
                    </div>
                </div>
                <div class="onb-stepper mt-3">
                    <div class="onb-bubble active" id="onb-b1" title="Personal Details">1</div>
                    <div class="onb-connector"></div>
                    <div class="onb-bubble" id="onb-b2" title="Select Plan">2</div>
                    <div class="onb-connector"></div>
                    <div class="onb-bubble" id="onb-b3" title="Emergency Contact">3</div>
                    <div class="onb-connector"></div>
                    <div class="onb-bubble" id="onb-b4" title="Activate">4</div>
                </div>
            </div>

            <!-- ── Step 1: Personal Details ───────────────────────── -->
            <div id="onb-pane-1" class="onb-step-pane">
                <h6 class="onb-step-heading"><i class="fas fa-user-circle"></i> Personal Details</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">National ID <span class="onb-req">*</span></label>
                        <input type="text" class="form-control" id="onb-national-id" placeholder="e.g. 12345678"
                               value="<?php echo htmlspecialchars($profile_completion_form_data['national_id'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date of Birth <span class="onb-req">*</span></label>
                        <input type="date" class="form-control" id="onb-dob"
                               value="<?php echo htmlspecialchars($profile_completion_form_data['date_of_birth'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Physical Address <span class="onb-req">*</span></label>
                        <input type="text" class="form-control" id="onb-address" placeholder="e.g. Nairobi, Westlands"
                               value="<?php echo htmlspecialchars($profile_completion_form_data['address'] ?? '', ENT_QUOTES); ?>">
                    </div>
                </div>
            </div>

            <!-- ── Step 2: Select Membership Plan ──────────────────── -->
            <div id="onb-pane-3" class="onb-step-pane" style="display:none">
                <h6 class="onb-step-heading"><i class="fas fa-heart"></i> Emergency Contact (Next of Kin)</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="onb-req">*</span></label>
                        <input type="text" class="form-control" id="onb-kin-name" placeholder="Full name of next of kin"
                               value="<?php echo htmlspecialchars($profile_completion_form_data['next_of_kin'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Relationship</label>
                        <input type="text" class="form-control" id="onb-kin-rel" placeholder="e.g. Spouse"
                               value="<?php echo htmlspecialchars($profile_completion_form_data['next_of_kin_relationship'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Phone <span class="onb-req">*</span></label>
                        <input type="tel" class="form-control" id="onb-kin-phone" placeholder="0712345678"
                               value="<?php echo htmlspecialchars($profile_completion_form_data['next_of_kin_phone'] ?? '', ENT_QUOTES); ?>">
                    </div>
                </div>
            </div>

            <!-- ── Step 3: Emergency Contact ────────────────────────── -->
            <div id="onb-pane-2" class="onb-step-pane" style="display:none">
                <h6 class="onb-step-heading"><i class="fas fa-shield-alt"></i> Select Your Membership Plan</h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Plan Type <span class="onb-req">*</span></label>
                    <select class="form-select onb-plan-select" id="onb-plan-type">
                        <option value="">Choose a plan...</option>
                        <option value="individual">Individual &mdash; Principal member only (KES 100 &ndash; 650/month)</option>
                        <option value="family">Family &mdash; Principal + Spouse (KES 150 flat/month)</option>
                        <option value="extended_family_1">Extended Family 1 &mdash; Couple + Children + Parents (KES 250 &ndash; 650/month)</option>
                        <option value="extended_family_2">Extended Family 2 &mdash; Couple + Children + Parents + In-laws (KES 300 &ndash; 650/month)</option>
                        <option value="executive">Executive &mdash; Premium Individual (KES 300 or 500/month)</option>
                    </select>
                    <div style="text-align:center; margin-top:8px;">
                        <button type="button" class="btn btn-link btn-sm p-0" style="font-size:0.82rem; color:#7F20B0;" onclick="document.getElementById('onb-pkg-guide').style.display = document.getElementById('onb-pkg-guide').style.display === 'none' ? '' : 'none';">
                            <i class="fas fa-question-circle"></i> Don&rsquo;t know what to choose? Get a glimpse of how our packages are categorized
                        </button>
                    </div>
                    <div id="onb-pkg-guide" style="display:none; margin-top:8px; padding:10px 14px; background:#f5f0fb; border-radius:8px; font-size:0.82rem; color:#3d1060;">
                        <strong>Individual</strong> &mdash; Just you. Choose your age bracket to get a monthly rate.<br>
                        <strong>Family</strong> &mdash; You + spouse. Fixed monthly rate of KES 150.<br>
                        <strong>Extended Family 1</strong> &mdash; Couple + children + parents. Age-based rate.<br>
                        <strong>Extended Family 2</strong> &mdash; Extended Family 1 + in-laws. Best for large families.<br>
                        <strong>Executive</strong> &mdash; Premium individual cover with enhanced benefits.
                    </div>
                </div>
                <div id="onb-bracket-row" style="display:none">
                    <label class="form-label fw-semibold">Age Bracket <span class="onb-req">*</span> &mdash; <em id="onb-bracket-hint" class="text-muted" style="font-size:0.85rem;"></em></label>
                    <div class="onb-bracket-grid" id="onb-bracket-options"></div>
                </div>
                <div id="onb-plan-summary" class="onb-plan-summary" style="display:none">
                    <strong id="onb-plan-name"></strong>
                    <span class="onb-plan-summary-price" id="onb-plan-price"></span>
                </div>
            </div>

            <!-- ── Step 4: Activate Membership ────────────────────── -->
            <div id="onb-pane-4" class="onb-step-pane" style="display:none">
                <h6 class="onb-step-heading"><i class="fas fa-check-circle"></i> Activate Your Membership</h6>
                <div class="onb-pay-box">
                    <p class="text-muted mb-1" style="font-size:0.88rem;">One-time Registration Fee</p>
                    <div class="onb-pay-amount">KES <?php echo number_format(defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200); ?></div>
                    <p class="onb-pay-desc">Pay via M-Pesa to activate your SHENA membership and start coverage immediately after payment confirmation.</p>
                    <div class="mb-3" style="max-width:280px; margin:0 auto;">
                        <label class="form-label fw-semibold" style="font-size:0.88rem;">M-Pesa Phone Number</label>
                        <input type="tel" class="form-control form-control-sm" id="onb-pay-phone" placeholder="0712345678">
                    </div>
                    <button class="onb-mpesa-btn" id="onb-pay-btn" type="button">
                        <i class="fas fa-mobile-alt"></i> Pay with M-Pesa
                    </button>
                    <div id="onb-pay-status" style="margin-top:14px; font-size:0.88rem;"></div>
                </div>
            </div>

            <!-- ── Message area ────────────────────────────────────── -->
            <div id="onb-msg" style="margin:0 28px 6px; display:none"></div>

            <!-- ── Footer navigation ────────────────────────────────  -->
            <div class="onb-footer">
                <button type="button" id="onb-btn-skip" class="btn btn-link text-muted p-0" style="font-size:0.85rem;">
                    Skip for now
                </button>
                <div class="d-flex gap-2">
                    <button type="button" id="onb-btn-back" class="btn btn-outline-secondary btn-sm px-3" style="display:none; border-radius:8px;">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </button>
                    <button type="button" id="onb-btn-next" class="onb-btn-next">
                        Continue <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const CSRF       = <?php echo json_encode($csrf_token ?? ''); ?>;
    const MEMBER_ID  = <?php echo json_encode((int)($member['id'] ?? 0)); ?>;
    const MEMBER_PHONE = <?php echo json_encode((string)($member['phone'] ?? '')); ?>;
    const REG_FEE    = <?php echo json_encode(defined('REGISTRATION_FEE') ? (float)REGISTRATION_FEE : 200.0); ?>;
    const HAS_PAID_REGISTRATION = <?php echo json_encode(($member['status'] ?? 'inactive') === 'active'); ?>;

    const tierMap = {
        individual: {
            flat: false, hint: 'Your age',
            brackets: [
                { key: 'individual_below_70', label: 'Below 70 years', price: 100 },
                { key: 'individual_71_80',    label: '71–80 years',    price: 350 },
                { key: 'individual_81_90',    label: '81–90 years',    price: 450 },
                { key: 'individual_91_100',   label: '91–100 years',   price: 650 }
            ]
        },
        family: { flat: true, packageKey: 'couple_below_70', price: 150, label: 'Family Plan – Principal + Spouse' },
        extended_family_1: {
            flat: false, hint: 'Age of the oldest parent you are covering',
            brackets: [
                { key: 'couple_children_parents_below_70', label: 'Below 70 years', price: 250 },
                { key: 'couple_children_parents_70_80',    label: '70–80 years',    price: 350 },
                { key: 'couple_children_parents_81_90',    label: '81–90 years',    price: 450 },
                { key: 'couple_children_parents_91_100',   label: '91–100 years',   price: 650 }
            ]
        },
        extended_family_2: {
            flat: false, hint: 'Age of the oldest parent or in-law you are covering',
            brackets: [
                { key: 'couple_children_parents_inlaws_below_70', label: 'Below 70 years', price: 300 },
                { key: 'couple_children_parents_inlaws_71_80',    label: '71–80 years',    price: 400 },
                { key: 'couple_children_parents_inlaws_81_90',    label: '81–90 years',    price: 550 },
                { key: 'couple_children_parents_inlaws_91_100',   label: '91–100 years',   price: 650 }
            ]
        },
        executive: {
            flat: false, hint: 'Your age',
            brackets: [
                { key: 'executive_below_70', label: 'Below 70 years',  price: 300 },
                { key: 'executive_above_70', label: '70 years & above', price: 500 }
            ]
        }
    };

    const STEP_LABELS = [
        '',
        'Step 1 of 4 — Personal Details',
        'Step 2 of 4 — Select Your Plan',
        'Step 3 of 4 — Emergency Contact',
        'Step 4 of 4 — Activate Membership'
    ];

    let currentStep       = 1;
    let selectedPackageId = '';

    const panes    = [null, document.getElementById('onb-pane-1'), document.getElementById('onb-pane-2'), document.getElementById('onb-pane-3'), document.getElementById('onb-pane-4')];
    const bubbles  = [null, document.getElementById('onb-b1'), document.getElementById('onb-b2'), document.getElementById('onb-b3'), document.getElementById('onb-b4')];
    const stepLabel = document.getElementById('onb-step-label');
    const msgBox    = document.getElementById('onb-msg');
    const btnBack   = document.getElementById('onb-btn-back');
    const btnNext   = document.getElementById('onb-btn-next');
    const btnSkip   = document.getElementById('onb-btn-skip');

    function showStep(n) {
        for (var i = 1; i <= 4; i++) {
            panes[i].style.display = (i === n) ? '' : 'none';
            var b = bubbles[i];
            b.classList.remove('active', 'done');
            if (i < n)  b.classList.add('done'), b.innerHTML = '<i class="fas fa-check" style="font-size:0.7rem"></i>';
            if (i === n) b.classList.add('active'), b.textContent = i;
            if (i > n)  b.textContent = i;
        }
        stepLabel.textContent = STEP_LABELS[n];
        btnBack.style.display = n > 1 ? '' : 'none';
        if (n === 4) {
            btnNext.innerHTML = 'Finish <i class="fas fa-check ms-1"></i>';
        } else {
            btnNext.innerHTML = 'Continue <i class="fas fa-arrow-right ms-1"></i>';
        }
        currentStep = n;
        clearMsg();

        // Auto-select plan by age when entering Step 2
        if (n === 2) {
            autoSelectPlanByAge();
        }
    }

    function autoSelectPlanByAge() {
        var dobVal = document.getElementById('onb-dob').value;
        if (!dobVal) return;
        var dob = new Date(dobVal);
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        if (age < 18) return; // shouldn't happen but guard anyway

        // Determine the individual bracket key based on age
        var bracketKey, bracketLabel, bracketPrice;
        if (age < 70) {
            bracketKey = 'individual_below_70'; bracketLabel = 'Below 70 years'; bracketPrice = 100;
        } else if (age <= 80) {
            bracketKey = 'individual_71_80'; bracketLabel = '71–80 years'; bracketPrice = 350;
        } else if (age <= 90) {
            bracketKey = 'individual_81_90'; bracketLabel = '81–90 years'; bracketPrice = 450;
        } else {
            bracketKey = 'individual_91_100'; bracketLabel = '91–100 years'; bracketPrice = 650;
        }

        // If plan is already selected (user changed it), don't override
        if (selectedPackageId) return;

        // Set plan type select to "individual" and trigger bracket render
        planSelect.value = 'individual';
        clearPlan();
        var tier = tierMap.individual;
        bracketHint.textContent = tier.hint;
        tier.brackets.forEach(function (b) {
            var el = document.createElement('div');
            el.className = 'onb-bracket-opt';
            el.dataset.key = b.key;
            el.innerHTML = '<div class="onb-bracket-label">' + b.label + '</div>' +
                           '<div class="onb-bracket-price">KES ' + b.price.toLocaleString() + '/month</div>';
            el.addEventListener('click', function () {
                bracketGrid.querySelectorAll('.onb-bracket-opt').forEach(function (o) { o.classList.remove('selected'); });
                el.classList.add('selected');
                selectedPackageId = b.key;
                setPlanSummary('Individual · ' + b.label, b.price);
            });
            bracketGrid.appendChild(el);
        });
        bracketRow.style.display = '';

        // Auto-click the matching bracket
        var matchEl = bracketGrid.querySelector('[data-key="' + bracketKey + '"]');
        if (matchEl) matchEl.click();
    }

    function showMsg(text, type) {
        msgBox.innerHTML = '<div class="alert alert-' + type + ' py-2 mb-0 small">' + text + '</div>';
        msgBox.style.display = '';
    }
    function clearMsg() { msgBox.style.display = 'none'; msgBox.innerHTML = ''; }

    /* ── Plan picker ─────────────────────────────────────────────────── */
    var planSelect   = document.getElementById('onb-plan-type');
    var bracketRow   = document.getElementById('onb-bracket-row');
    var bracketHint  = document.getElementById('onb-bracket-hint');
    var bracketGrid  = document.getElementById('onb-bracket-options');
    var planSummary  = document.getElementById('onb-plan-summary');
    var planNameEl   = document.getElementById('onb-plan-name');
    var planPriceEl  = document.getElementById('onb-plan-price');

    function clearPlan() {
        bracketGrid.innerHTML = '';
        bracketRow.style.display  = 'none';
        planSummary.style.display = 'none';
        selectedPackageId = '';
    }

    function setPlanSummary(name, price) {
        planNameEl.textContent  = name;
        planPriceEl.textContent = 'KES ' + price.toLocaleString() + '/month';
        planSummary.style.display = '';
    }

    planSelect.addEventListener('change', function () {
        clearPlan();
        var val = planSelect.value;
        if (!val || !tierMap[val]) return;
        var tier = tierMap[val];
        if (tier.flat) {
            selectedPackageId = tier.packageKey;
            var label = planSelect.options[planSelect.selectedIndex].text.split('—')[0].trim();
            setPlanSummary(label + ' (Flat Rate)', tier.price);
        } else {
            bracketHint.textContent = tier.hint;
            tier.brackets.forEach(function (b) {
                var el = document.createElement('div');
                el.className = 'onb-bracket-opt';
                el.dataset.key = b.key;
                el.innerHTML = '<div class="onb-bracket-label">' + b.label + '</div>' +
                               '<div class="onb-bracket-price">KES ' + b.price.toLocaleString() + '/month</div>';
                el.addEventListener('click', function () {
                    bracketGrid.querySelectorAll('.onb-bracket-opt').forEach(function (o) { o.classList.remove('selected'); });
                    el.classList.add('selected');
                    selectedPackageId = b.key;
                    var pLabel = planSelect.options[planSelect.selectedIndex].text.split('—')[0].trim();
                    setPlanSummary(pLabel + ' · ' + b.label, b.price);
                });
                bracketGrid.appendChild(el);
            });
            bracketRow.style.display = '';
        }
    });

    /* ── Validation ──────────────────────────────────────────────────── */
    function validateStep(n) {
        if (n === 1) {
            if (!document.getElementById('onb-national-id').value.trim()) { showMsg('Please enter your National ID number.', 'warning'); return false; }
            if (!document.getElementById('onb-dob').value)                 { showMsg('Please enter your date of birth.', 'warning'); return false; }
            if (!document.getElementById('onb-address').value.trim())      { showMsg('Please enter your physical address.', 'warning'); return false; }
        }
        if (n === 2) {
            if (!selectedPackageId) { showMsg('Please select a plan' + (planSelect.value && !tierMap[planSelect.value]?.flat ? ' and an age bracket' : '') + '.', 'warning'); return false; }
        }
        if (n === 3) {
            if (!document.getElementById('onb-kin-name').value.trim())  { showMsg('Please enter the name of your next of kin.', 'warning'); return false; }
            if (!document.getElementById('onb-kin-phone').value.trim()) { showMsg('Please enter your next of kin phone number.', 'warning'); return false; }
        }
        return true;
    }

    /* ── Submit steps via AJAX ───────────────────────────────────────── */
    async function submitStep(n) {
        if (n === 2) {
            // Step 2 = Select Plan — save package
            btnNext.disabled = true;
            btnNext.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            var fd2 = new FormData();
            fd2.append('csrf_token', CSRF);
            fd2.append('package_id', selectedPackageId);
            try {
                var r2 = await fetch('/member/onboarding/package', { method: 'POST', body: fd2, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                var d2 = await r2.json();
                if (!d2.success) {
                    showMsg(d2.message || 'Could not update your plan. Please try again.', 'danger');
                    btnNext.disabled = false;
                    btnNext.innerHTML = 'Continue <i class="fas fa-arrow-right ms-1"></i>';
                    return false;
                }
            } catch (e) {
                showMsg('Network error. Please check your connection and try again.', 'danger');
                btnNext.disabled = false;
                btnNext.innerHTML = 'Continue <i class="fas fa-arrow-right ms-1"></i>';
                return false;
            }
            btnNext.disabled = false;
            return true;
        }
        if (n === 3) {
            // Step 3 = Emergency Contact — also save personal details from Step 1
            btnNext.disabled = true;
            btnNext.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            var fd = new FormData();
            fd.append('csrf_token', CSRF);
            fd.append('national_id',              document.getElementById('onb-national-id').value.trim());
            fd.append('date_of_birth',             document.getElementById('onb-dob').value);
            fd.append('address',                   document.getElementById('onb-address').value.trim());
            fd.append('next_of_kin',               document.getElementById('onb-kin-name').value.trim());
            fd.append('next_of_kin_relationship',  document.getElementById('onb-kin-rel').value.trim());
            fd.append('next_of_kin_phone',         document.getElementById('onb-kin-phone').value.trim());
            try {
                var r = await fetch('/member/profile/complete', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                var d = await r.json();
                if (!d.success) {
                    showMsg(d.message || 'Could not save your details. Please try again.', 'danger');
                    btnNext.disabled = false;
                    btnNext.innerHTML = 'Continue <i class="fas fa-arrow-right ms-1"></i>';
                    return false;
                }
            } catch (e) {
                showMsg('Network error. Please check your connection and try again.', 'danger');
                btnNext.disabled = false;
                btnNext.innerHTML = 'Continue <i class="fas fa-arrow-right ms-1"></i>';
                return false;
            }
            btnNext.disabled = false;
            return true;
        }
        return true;
    }

    /* ── Button events ───────────────────────────────────────────────── */
    btnNext.addEventListener('click', async function () {
        clearMsg();
        if (!validateStep(currentStep)) return;

        // Step 4 "Finish" — verify payment before closing
        if (currentStep === 4) {
            btnNext.disabled = true;
            btnNext.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verifying...';
            try {
                var pr = await fetch('/member/onboarding/payment-status', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                var pd = await pr.json();
                if (!pd.paid) {
                    showMsg(
                        '<i class="fas fa-exclamation-triangle me-1"></i>' +
                        '<strong>Payment required.</strong> Please complete the KES ' +
                        REG_FEE.toLocaleString() +
                        ' registration fee payment above before finishing. ' +
                        'Tap <em>Pay with M-Pesa</em> and approve the prompt on your phone.',
                        'warning'
                    );
                    btnNext.disabled = false;
                    btnNext.innerHTML = 'Finish <i class="fas fa-check ms-1"></i>';
                    return;
                }
            } catch (e) {
                showMsg('Could not verify payment. Please check your connection and try again.', 'danger');
                btnNext.disabled = false;
                btnNext.innerHTML = 'Finish <i class="fas fa-check ms-1"></i>';
                return;
            }
            dismissAndReload();
            return;
        }

        var submitted = await submitStep(currentStep);
        if (!submitted) return;
        showStep(currentStep + 1);
    });

    btnBack.addEventListener('click', function () { clearMsg(); if (currentStep > 1) showStep(currentStep - 1); });
    btnSkip.addEventListener('click', function () {
        if (!HAS_PAID_REGISTRATION) {
            showMsg('Please complete your registration payment before skipping. Go to Step 4 to pay.', 'warning');
            showStep(4);
            return;
        }
        dismissAndReload();
    });

    async function dismissAndReload() {
        try {
            var fd3 = new FormData();
            fd3.append('csrf_token', CSRF);
            await fetch('/member/onboarding/dismiss', { method: 'POST', body: fd3, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        } catch (_) {}
        var modal = bootstrap.Modal.getInstance(document.getElementById('onboardingWizard'));
        if (modal) modal.hide();
        window.location.reload();
    }

    /* ── M-Pesa payment (Step 4) ─────────────────────────────────────── */
    var payPhone  = document.getElementById('onb-pay-phone');
    var payBtn    = document.getElementById('onb-pay-btn');
    var payStatus = document.getElementById('onb-pay-status');

    if (MEMBER_PHONE) {
        var ph = MEMBER_PHONE.toString();
        if (ph.startsWith('254')) ph = '0' + ph.slice(3);
        payPhone.value = ph;
    }

    payBtn.addEventListener('click', async function () {
        var rawPhone = payPhone.value.trim();
        if (!rawPhone) { payStatus.innerHTML = '<span class="text-danger">Please enter a phone number.</span>'; return; }
        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Please wait...';
        try {
            var resp = await fetch('/payment/initiate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ member_id: MEMBER_ID, amount: REG_FEE, phone_number: rawPhone, payment_type: 'registration' })
            });
            var data = await resp.json();
            if (data.success) {
                payStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + (data.message || 'STK Push sent! Check your phone for the M-Pesa prompt.') + '</span>';
                payBtn.innerHTML = '<i class="fas fa-check me-1"></i> Prompt Sent';
            } else {
                payStatus.innerHTML = '<span class="text-danger">' + (data.error || data.message || 'Payment failed. Please try again.') + '</span>';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with M-Pesa';
            }
        } catch (e) {
            payStatus.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with M-Pesa';
        }
    });

    /* ── Init ────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        showStep(1);
        var modalEl = document.getElementById('onboardingWizard');
        if (modalEl && window.bootstrap) {
            new bootstrap.Modal(modalEl).show();
        }
    });

})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
