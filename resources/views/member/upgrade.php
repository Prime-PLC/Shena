<?php
$page = 'upgrade';
include __DIR__ . '/../layouts/member-header.php';

$memberData = $member ?? [];
$calculation = $calculation ?? [];
$pendingUpgrades = $pendingUpgrades ?? [];
$upgradeHistory = $upgradeHistory ?? [];
$upgradePreviews = $upgradePreviews ?? [];
$tierDefinitions = $tierDefinitions ?? [];
$defaultTargetPackage = $defaultTargetPackage ?? 'family';
$packageOrder = ['individual', 'family', 'extended_family_1', 'extended_family_2', 'executive'];
$currentPackage = strtolower($memberData['package'] ?? 'individual');
if (!in_array($currentPackage, $packageOrder, true)) {
    $currentPackage = 'individual';
}

$labelMap = [
    'individual' => 'Individual',
    'family' => 'Family',
    'extended_family_1' => 'Extended Family 1',
    'extended_family_2' => 'Extended Family 2',
    'executive' => 'Executive'
];

$feeMap = [
    'individual' => (float)($upgradePreviews['individual']['new_monthly_fee'] ?? ($memberData['monthly_contribution'] ?? 100)),
    'family' => (float)($upgradePreviews['family']['new_monthly_fee'] ?? 150),
    'extended_family_1' => (float)($upgradePreviews['extended_family_1']['new_monthly_fee'] ?? 250),
    'extended_family_2' => (float)($upgradePreviews['extended_family_2']['new_monthly_fee'] ?? 300),
    'executive' => (float)($upgradePreviews['executive']['new_monthly_fee'] ?? 300)
];

$currentMonthLabel = date('F');
$nextMonthStartLabel = date('F 1, Y', strtotime('first day of next month'));
$initialCalculationMethod = (string)($calculation['calculation_method'] ?? 'prorated');
?>

<style>
main {
    padding: 0 !important;
    margin: 0 !important;
}

.upgrade-container {
    padding: 30px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
    max-width: 100%;
    overflow-x: hidden;
}

.page-header {
    margin-bottom: 32px;
}

.page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.page-header p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.packages-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 32px;
}

@media (max-width: 968px) {
    .packages-grid {
        grid-template-columns: 1fr;
    }

    .upgrade-top-grid {
        grid-template-columns: 1fr;
    }
}

.package-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: transform 0.3s, box-shadow 0.3s;
}

.package-card.current {
    border: 2px solid #E5E7EB;
}

.package-card.premium {
    border: 2px solid #7F20B0;
    background: linear-gradient(135deg, rgba(127, 32, 176, 0.03) 0%, rgba(94, 43, 122, 0.05) 100%);
}

.package-card.premium:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(127, 32, 176, 0.2);
}

.package-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.package-badge.current-badge {
    background: #E5E7EB;
    color: #4B5563;
}

.package-badge.recommended {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    color: white;
}

.package-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    font-size: 28px;
}

.package-icon.basic {
    background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
    color: white;
}

.package-icon.premium {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
}

.package-name {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 8px 0;
    text-transform: uppercase;
}

.package-price {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 700;
    color: #7F20B0;
    margin: 0 0 4px 0;
}

.package-price span {
    font-size: 16px;
    color: #6B7280;
    font-family: 'Manrope', sans-serif;
    font-weight: 500;
}

.package-description {
    font-size: 14px;
    color: #6B7280;
    margin: 0 0 24px 0;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0 0 24px 0;
}

.benefits-list li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 14px;
    color: #4B5563;
}

.benefits-list li i {
    color: #10B981;
    font-size: 16px;
    margin-top: 2px;
}

.upgrade-btn {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.upgrade-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(127, 32, 176, 0.4);
}

.upgrade-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.upgrade-top-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
    gap: 24px;
    margin-bottom: 32px;
}

.selected-plan-line {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #4B5563;
    margin: -8px 0 16px 0;
}

.selected-plan-line strong {
    color: #111827;
    font-weight: 700;
}

.upgrade-guide-card {
    background: linear-gradient(145deg, #FFF7ED 0%, #FEF3C7 100%);
    border: 1px solid #FDE68A;
}

.guide-item {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}

.guide-item:last-child {
    margin-bottom: 0;
}

.guide-step {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #7F20B0;
    color: white;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.guide-item p {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #6B7280;
}

.plans-grid-new {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.plan-card-new {
    background: white;
    border-radius: 18px;
    padding: 26px;
    border: 1px solid #E5E7EB;
    position: relative;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.plan-card-new:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
    border-color: #C4B5FD;
}

.plan-card-new.selected {
    border-color: #7F20B0;
    box-shadow: 0 16px 36px rgba(127, 32, 176, 0.18);
}

.highlight-plan {
    border: 2px solid #F59E0B;
    background: linear-gradient(180deg, #FFF7ED 0%, #FFFFFF 100%);
}

.premium-plan {
    border: 2px solid #7F20B0;
    background: linear-gradient(180deg, #F5F3FF 0%, #FFFFFF 100%);
}

.plan-icon-new {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
    color: white;
}

.bronze-icon {
    background: linear-gradient(135deg, #1F2937 0%, #4B5563 100%);
}

.silver-icon {
    background: linear-gradient(135deg, #64748B 0%, #94A3B8 100%);
}

.gold-icon {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
}

.premium-icon {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
}

.plan-name-new {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 6px 0;
    color: #111827;
}

.plan-price-new {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 6px;
}

.price-currency {
    font-size: 14px;
    color: #6B7280;
    font-weight: 600;
}

.price-amount {
    font-size: 30px;
    font-weight: 700;
    color: #1F2937;
}

.price-period {
    font-size: 12px;
    color: #6B7280;
    font-weight: 600;
}

.plan-subtitle {
    font-size: 13px;
    color: #6B7280;
    margin-bottom: 16px;
}

.plan-features-new {
    display: grid;
    gap: 10px;
    margin-bottom: 18px;
}

.feature-item-new {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #374151;
}

.feature-item-new i {
    color: #10B981;
}

.plan-select-btn-new {
    width: 100%;
    border: none;
    padding: 12px;
    border-radius: 10px;
    background: #111827;
    color: white;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.plan-select-btn-new:hover {
    background: #7F20B0;
    transform: translateY(-2px);
}

.plan-select-btn-new.disabled-plan {
    background: #E5E7EB;
    color: #6B7280;
    cursor: not-allowed;
    transform: none;
}

.current-badge-new,
.popular-badge-new,
.premium-badge-new {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

.current-badge-new {
    background: #E5E7EB;
    color: #374151;
}

.popular-badge-new {
    background: #FEF3C7;
    color: #92400E;
}

.premium-badge-new {
    background: #7F20B0;
    color: white;
}

.calculation-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.calculation-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.calculation-card h3 i {
    color: #7F20B0;
}

.cost-breakdown {
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 24px;
}

.cost-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #E5E7EB;
}

.cost-row:last-child {
    border-bottom: none;
}

.cost-row.total {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
}

.cost-label {
    font-size: 14px;
    font-weight: 600;
    color: #4B5563;
}

.cost-row.total .cost-label {
    color: white;
    font-size: 16px;
}

.cost-value {
    font-size: 18px;
    font-weight: 700;
    color: #1F2937;
}

.cost-row.total .cost-value {
    color: white;
    font-size: 24px;
}

.info-alert {
    background: #EFF6FF;
    border-left: 4px solid #3B82F6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
}

.info-alert i {
    color: #3B82F6;
    font-size: 20px;
    flex-shrink: 0;
}

.info-alert-content {
    flex: 1;
}

.info-alert-content h6 {
    font-size: 14px;
    font-weight: 700;
    color: #1E40AF;
    margin: 0 0 4px 0;
}

.info-alert-content p {
    font-size: 13px;
    color: #1E40AF;
    margin: 0;
    line-height: 1.6;
}

.upgrade-form {
    margin-top: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #7F20B0;
    box-shadow: 0 0 0 3px rgba(127, 32, 176, 0.1);
}

.form-text {
    font-size: 12px;
    color: #6B7280;
    margin-top: 6px;
    display: block;
}

.form-check {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 20px;
}

.form-check-input {
    margin-top: 3px;
    cursor: pointer;
}

.form-check-label {
    font-size: 13px;
    color: #4B5563;
    line-height: 1.6;
}

.pending-alert {
    background: #FEF3C7;
    border-left: 4px solid #F59E0B;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 24px;
}

.pending-alert h5 {
    font-size: 16px;
    font-weight: 700;
    color: #92400E;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pending-info {
    font-size: 13px;
    color: #78350F;
    margin-bottom: 8px;
}

.cancel-btn {
    background: #EF4444;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 12px;
    transition: all 0.2s;
}

.cancel-btn:hover {
    background: #DC2626;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table thead th {
    background: #F9FAFB;
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #E5E7EB;
}

.history-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 14px;
    color: #4B5563;
}

.history-table tbody tr:last-child td {
    border-bottom: none;
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.modal-overlay.show {
    display: flex;
}

.modal-content-custom {
    background: white;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #E5E7EB;
    border-top-color: #7F20B0;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="upgrade-container">
    <div class="page-header">
        <h1>Choose Your Plan</h1>
        <p>Select the coverage level that best protects your family and see your exact upgrade cost instantly</p>
    </div>
    <?php $currentIndex = array_search($currentPackage, $packageOrder, true); ?>
    <div class="plans-comparison-section">
        <div class="plans-grid-new">
            <?php foreach ($packageOrder as $planKey): ?>
                <?php
                    $planIndex = array_search($planKey, $packageOrder, true);
                    $isCurrent = $currentPackage === $planKey;
                    $canSelect = empty($pendingUpgrades) && $planIndex > $currentIndex;
                    $isPremium = $planKey === 'executive';
                    $tierMeta = $tierDefinitions[$planKey] ?? [];
                    $priceAmount = (float)($feeMap[$planKey] ?? 0);
                ?>
                <div class="plan-card-new <?php echo $isPremium ? 'premium-plan' : ''; ?> <?php echo $planKey === 'extended_family_1' ? 'highlight-plan' : ''; ?>" data-plan="<?php echo htmlspecialchars($planKey); ?>" data-monthly="<?php echo (int)$priceAmount; ?>">
                    <?php if ($isCurrent): ?>
                        <div class="current-badge-new">YOUR PLAN</div>
                    <?php elseif ($isPremium): ?>
                        <div class="premium-badge-new"><i class="fas fa-crown"></i> PREMIUM</div>
                    <?php elseif ($planKey === 'extended_family_1'): ?>
                        <div class="popular-badge-new">BEST VALUE</div>
                    <?php endif; ?>

                    <div class="plan-icon-new <?php echo $isPremium ? 'premium-icon' : ($planKey === 'individual' ? 'bronze-icon' : ($planKey === 'family' ? 'silver-icon' : 'gold-icon')); ?>">
                        <i class="fas <?php echo $isPremium ? 'fa-crown' : ($planKey === 'individual' ? 'fa-user' : ($planKey === 'family' ? 'fa-user-friends' : 'fa-home')); ?>"></i>
                    </div>

                    <h3 class="plan-name-new"><?php echo htmlspecialchars($labelMap[$planKey] ?? ucwords(str_replace('_', ' ', $planKey))); ?></h3>
                    <div class="plan-price-new">
                        <span class="price-currency">KES</span>
                        <span class="price-amount"><?php echo number_format($priceAmount, 0); ?></span>
                        <span class="price-period">/month</span>
                    </div>
                    <p class="plan-subtitle"><?php echo htmlspecialchars($tierMeta['description'] ?? 'Coverage tier'); ?></p>

                    <div class="plan-features-new">
                        <?php foreach (array_slice(($tierMeta['coverage'] ?? []), 0, 3) as $coverageItem): ?>
                            <div class="feature-item-new"><i class="fas fa-check-circle"></i><span><?php echo htmlspecialchars($coverageItem); ?></span></div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isCurrent): ?>
                        <button class="plan-select-btn-new disabled-plan" disabled>Current Plan</button>
                    <?php elseif (!$canSelect): ?>
                        <button class="plan-select-btn-new disabled-plan" disabled>Not Available</button>
                    <?php else: ?>
                        <button class="plan-select-btn-new" onclick="selectPlan('<?php echo htmlspecialchars($planKey); ?>', true)">
                            <i class="fas fa-arrow-up"></i> Upgrade to <?php echo htmlspecialchars($labelMap[$planKey] ?? ucwords(str_replace('_', ' ', $planKey))); ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($pendingUpgrades)): ?>
        <div class="pending-alert">
            <h5><i class="fas fa-clock"></i>Pending Upgrade Request</h5>
            <?php foreach ($pendingUpgrades as $pending): ?>
                <p class="pending-info"><strong>Status:</strong> <?php echo ucwords(str_replace('_', ' ', $pending['status'])); ?></p>
                <p class="pending-info"><strong>Amount:</strong> KES <?php echo number_format($pending['prorated_amount'], 2); ?></p>
                <p class="pending-info"><strong>Requested:</strong> <?php echo date('M d, Y H:i', strtotime($pending['requested_at'])); ?></p>
                <?php if ($pending['status'] === 'pending'): ?>
                    <button class="cancel-btn" onclick="cancelUpgrade(<?php echo $pending['id']; ?>)">
                        <i class="fas fa-times"></i> Cancel Request
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="upgrade-top-grid">
            <div class="calculation-card calculation-card-primary" id="upgradeForm">
                <h3><i class="fas fa-calculator"></i>Upgrade Cost Breakdown</h3>
                <div class="selected-plan-line">
                    <span>Selected plan:</span>
                    <strong id="selectedPlanName"><?php echo ucfirst($defaultTargetPackage); ?></strong>
                </div>

                <div class="cost-breakdown">
                    <div class="cost-row">
                        <span class="cost-label">Current Plan (<?php echo htmlspecialchars($labelMap[$currentPackage] ?? ucwords(str_replace('_', ' ', $currentPackage))); ?>)</span>
                        <span class="cost-value" id="currentMonthlyFee">KES <?php echo number_format((float)($calculation['current_monthly_fee'] ?? ($memberData['monthly_contribution'] ?? 0)), 2); ?></span>
                    </div>
                    <div class="cost-row">
                        <span class="cost-label">Selected Plan Monthly Fee</span>
                        <span class="cost-value" id="selectedMonthlyFee">KES <?php echo number_format((float)($calculation['new_monthly_fee'] ?? 0), 2); ?></span>
                    </div>
                    <div class="cost-row">
                        <span class="cost-label">Monthly Difference</span>
                        <span class="cost-value" id="monthlyDifference">+KES <?php echo number_format(((float)($calculation['new_monthly_fee'] ?? 0)) - ((float)($calculation['current_monthly_fee'] ?? 0)), 2); ?></span>
                    </div>
                    <div class="cost-row">
                        <span class="cost-label" id="daysBasisLabel">Days Remaining in <?php echo $currentMonthLabel; ?></span>
                        <span class="cost-value" id="daysBasisValue"><?php echo $calculation['days_remaining'] ?? 15; ?> / <?php echo $calculation['total_days_in_month'] ?? 30; ?> days</span>
                    </div>
                    <div class="cost-row total">
                        <span class="cost-label" id="payTodayLabel">Pay Today (Prorated)</span>
                        <span class="cost-value" id="proratedAmount">KES <?php echo number_format($calculation['prorated_amount'] ?? 0, 2); ?></span>
                    </div>
                </div>

                <div class="info-alert" id="planNotice">
                    <i class="fas fa-info-circle"></i>
                    <div class="info-alert-content">
                        <h6>How It Works</h6>
                        <p id="planNoticeText">Upgrade charges are prorated by remaining days in <?php echo $currentMonthLabel; ?>. Starting <?php echo $nextMonthStartLabel; ?>, your monthly contribution will be <span id="nextMonthFee">KES <?php echo number_format((float)($calculation['new_monthly_fee'] ?? 0), 2); ?></span>.</p>
                    </div>
                </div>

                <form class="upgrade-form" id="upgradeFormSubmit">
                    <input type="hidden" name="to_package" id="to_package" value="<?php echo htmlspecialchars($defaultTargetPackage); ?>">
                    <div class="form-group">
                        <label class="form-label">M-Pesa Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                               value="<?php echo htmlspecialchars($memberData['phone'] ?? ''); ?>" 
                               placeholder="0712345678 or 254712345678" required>
                        <small class="form-text">You will receive an M-Pesa prompt to complete the payment</small>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">
                            I understand that by upgrading, my monthly contribution will increase to 
                            <span id="agreementNewFee">KES <?php echo number_format((float)($calculation['new_monthly_fee'] ?? 0), 2); ?></span> starting next month.
                        </label>
                    </div>
                    
                    <button type="submit" class="upgrade-btn" id="upgradeBtn">
                        <i class="fas fa-arrow-up"></i>
                        <span id="upgradeBtnLabel">Upgrade to <?php echo ucfirst($defaultTargetPackage); ?> (KES <?php echo number_format($calculation['prorated_amount'] ?? 0, 2); ?>)</span>
                    </button>
                </form>
            </div>
            <div class="calculation-card upgrade-guide-card">
                <h3><i class="fas fa-compass"></i>Upgrade Guide</h3>
                <div class="guide-item">
                    <span class="guide-step">1</span>
                    <div>
                        <strong>Pick your next plan</strong>
                        <p>Compare the packages below and select the coverage level that fits your family.</p>
                    </div>
                </div>
                <div class="guide-item">
                    <span class="guide-step">2</span>
                    <div>
                        <strong>Charges follow upgrade type</strong>
                        <p>All upgrades use prorated billing based on remaining days in the current month.</p>
                    </div>
                </div>
                <div class="guide-item">
                    <span class="guide-step">3</span>
                    <div>
                        <strong>Add dependents within plan coverage</strong>
                        <p>If beneficiaries exceed your current plan limits, upgrade to a higher coverage plan before adding more dependents.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upgrade History -->
    <?php if (!empty($upgradeHistory)): ?>
        <div class="calculation-card">
            <h3><i class="fas fa-history"></i>Upgrade History</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From Package</th>
                        <th>To Package</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upgradeHistory as $history): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($history['upgraded_at'])); ?></td>
                        <td><?php echo ucfirst($history['from_package']); ?></td>
                        <td><?php echo ucfirst($history['to_package']); ?></td>
                        <td>KES <?php echo number_format($history['amount_paid'], 2); ?></td>
                        <td><?php echo strtoupper($history['payment_method']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Processing Modal -->
<div class="modal-overlay" id="processingModal">
    <div class="modal-content-custom">
        <div class="spinner"></div>
        <h5 style="margin: 0 0 12px 0; font-size: 18px; color: #1F2937;">Processing Upgrade</h5>
        <p style="margin: 0; font-size: 14px; color: #6B7280;">Please check your phone for the M-Pesa prompt...</p>
        <p id="processingStatus" style="margin: 12px 0 0 0; font-size: 13px; color: #7F20B0;"></p>
    </div>
</div>

<script>
const upgradeConfig = {
    currentPackage: '<?php echo $currentPackage; ?>',
    packageOrder: <?php echo json_encode($packageOrder); ?>,
    fees: <?php echo json_encode($feeMap); ?>,
    labels: <?php echo json_encode($labelMap); ?>,
    daysRemaining: <?php echo (int)($calculation['days_remaining'] ?? 15); ?>,
    totalDays: <?php echo (int)($calculation['total_days_in_month'] ?? 30); ?>,
    currentMonth: '<?php echo $currentMonthLabel; ?>',
    nextMonthStart: '<?php echo $nextMonthStartLabel; ?>'
};

function formatCurrency(amount) {
    return 'KES ' + Number(amount).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function resolveUpgradeCalculation(currentPackage, targetPackage, currentFee, newFee) {
    const difference = Math.max(0, newFee - currentFee);

    const prorated = difference * (upgradeConfig.daysRemaining / upgradeConfig.totalDays);
    return {
        method: 'prorated',
        payableToday: prorated,
        payLabel: 'Pay Today (Prorated)',
        daysLabel: 'Days Remaining in ' + upgradeConfig.currentMonth,
        daysValue: upgradeConfig.daysRemaining + ' / ' + upgradeConfig.totalDays + ' days',
        notice: 'You only pay for the remaining days of ' + upgradeConfig.currentMonth + '. Starting ' + upgradeConfig.nextMonthStart + ', your monthly contribution will be ' + formatCurrency(newFee) + '.'
    };
}

function selectPlan(planKey, shouldScroll = false) {
    const form = document.getElementById('upgradeFormSubmit');
    if (!form) return;

    const currentIndex = upgradeConfig.packageOrder.indexOf(upgradeConfig.currentPackage);
    const targetIndex = upgradeConfig.packageOrder.indexOf(planKey);
    const selectedPlanName = upgradeConfig.labels[planKey] || planKey;

    document.querySelectorAll('.plan-card-new').forEach(card => {
        card.classList.toggle('selected', card.dataset.plan === planKey);
    });

    const notice = document.getElementById('planNotice');
    const upgradeBtn = document.getElementById('upgradeBtn');
    const upgradeBtnLabel = document.getElementById('upgradeBtnLabel');

    if (targetIndex <= currentIndex) {
        if (notice) {
            notice.querySelector('h6').textContent = 'Selection not available';
            notice.querySelector('p').textContent = 'You can only upgrade to a higher package.';
        }
        if (upgradeBtn) {
            upgradeBtn.disabled = true;
        }
        return;
    }

    const currentFee = upgradeConfig.fees[upgradeConfig.currentPackage] || 0;
    const newFee = upgradeConfig.fees[planKey] || 0;
    const pricing = resolveUpgradeCalculation(upgradeConfig.currentPackage, planKey, currentFee, newFee);
    const difference = Math.max(0, newFee - currentFee);

    document.getElementById('selectedPlanName').textContent = selectedPlanName;
    document.getElementById('selectedMonthlyFee').textContent = formatCurrency(newFee);
    document.getElementById('monthlyDifference').textContent = '+'.concat(formatCurrency(difference));
    document.getElementById('proratedAmount').textContent = formatCurrency(pricing.payableToday);
    document.getElementById('agreementNewFee').textContent = formatCurrency(newFee);
    document.getElementById('payTodayLabel').textContent = pricing.payLabel;
    document.getElementById('daysBasisLabel').textContent = pricing.daysLabel;
    document.getElementById('daysBasisValue').textContent = pricing.daysValue;
    document.getElementById('to_package').value = planKey;

    if (notice) {
        notice.querySelector('h6').textContent = 'How It Works';
        const planNoticeText = document.getElementById('planNoticeText');
        if (planNoticeText) {
            planNoticeText.innerHTML = 'This upgrade is prorated by remaining days in ' + upgradeConfig.currentMonth + '. Starting ' + upgradeConfig.nextMonthStart + ', your monthly contribution will be <span id="nextMonthFee">' + formatCurrency(newFee) + '</span>.';
        } else {
            notice.querySelector('p').textContent = pricing.notice;
        }
    }

    if (upgradeBtn && upgradeBtnLabel) {
        upgradeBtn.disabled = false;
        upgradeBtnLabel.textContent = 'Upgrade to ' + selectedPlanName + ' (' + formatCurrency(pricing.payableToday) + ')';
    }

    if (shouldScroll) {
        document.getElementById('upgradeForm')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    selectPlan('<?php echo $defaultTargetPackage; ?>');
});

document.getElementById('upgradeFormSubmit')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!document.getElementById('agree_terms').checked) {
        ShenaApp.showNotification('Please agree to the terms before proceeding', 'warning');
        return;
    }

    const phone = document.getElementById('phone_number').value;
    const toPackage = document.getElementById('to_package').value;
    const upgradeBtn = document.getElementById('upgradeBtn');
    const upgradeBtnLabel = document.getElementById('upgradeBtnLabel');

    upgradeBtn.disabled = true;
    upgradeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    try {
        const response = await fetch('/member/upgrade/request', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ phone_number: phone, to_package: toPackage })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('processingModal').classList.add('show');
            document.getElementById('processingStatus').textContent = 'Request ID: ' + data.upgrade_request_id;

            const checkInterval = setInterval(() => {
                checkUpgradeStatus(data.upgrade_request_id, checkInterval);
            }, 5000);

            setTimeout(() => {
                clearInterval(checkInterval);
                document.getElementById('processingModal').classList.remove('show');
                ShenaApp.showNotification('Payment timeout. Please check your payment status.', 'warning');
                location.reload();
            }, 120000);
        } else {
            ShenaApp.showNotification(data.message || data.error || 'Failed to initiate upgrade', 'error');
            upgradeBtn.disabled = false;
            upgradeBtn.innerHTML = '<i class="fas fa-arrow-up"></i> <span id="upgradeBtnLabel">Upgrade to ' + (upgradeConfig.labels[toPackage] || toPackage) + '</span>';
        }
    } catch (error) {
        ShenaApp.showNotification('Failed to process upgrade request', 'error');
        upgradeBtn.disabled = false;
        upgradeBtn.innerHTML = '<i class="fas fa-arrow-up"></i> <span id="upgradeBtnLabel">Upgrade to ' + (upgradeConfig.labels[toPackage] || toPackage) + '</span>';
    }
});

async function checkUpgradeStatus(upgradeRequestId, interval) {
    try {
        const response = await fetch('/member/upgrade/status?upgrade_request_id=' + upgradeRequestId);
        const data = await response.json();
        
        if (data.success) {
            if (data.status === 'completed') {
                clearInterval(interval);
                document.getElementById('processingModal').classList.remove('show');
                ShenaApp.showNotification('Upgrade completed successfully! Redirecting...', 'success');
                setTimeout(() => { window.location.href = '/member/dashboard'; }, 2000);
            } else if (data.status === 'failed') {
                clearInterval(interval);
                document.getElementById('processingModal').classList.remove('show');
                ShenaApp.showNotification('Payment failed. Please try again.', 'error');
                location.reload();
            }
        }
    } catch (error) {
        console.error('Status check error:', error);
    }
}

function cancelUpgrade(upgradeRequestId) {
    const proceed = () => {
        fetch('/member/upgrade/cancel', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ upgrade_request_id: upgradeRequestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Upgrade request cancelled', 'info');
                location.reload();
            } else {
                ShenaApp.showNotification(data.error || 'Failed to cancel upgrade', 'error');
            }
        })
        .catch(() => ShenaApp.showNotification('Failed to cancel upgrade', 'error'));
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Are you sure you want to cancel this upgrade request?',
            proceed,
            null,
            { type: 'warning', title: 'Cancel Upgrade', confirmText: 'Cancel Request' }
        );
        return;
    }

    if (!confirm('Are you sure you want to cancel this upgrade request?')) return;
    proceed();
}
</script>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
