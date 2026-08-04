<?php 
$page = 'beneficiaries';
include __DIR__ . '/../layouts/member-header.php';

$beneficiaries = $beneficiaries ?? [];
$coverageSummary = $coverage_summary ?? ['tier' => 'individual', 'limits' => ['spouse' => 0, 'children' => 0, 'parents' => 0, 'inlaws' => 0, 'other' => 0], 'total_slots' => 0];
$planLimits = $coverageSummary['limits'] ?? ['spouse' => 0, 'children' => 0, 'parents' => 0, 'inlaws' => 0, 'other' => 0];
$maxBeneficiaries = (int)($coverageSummary['total_slots'] ?? 0);
$availableSlots = max(0, $maxBeneficiaries - count($beneficiaries));
$planTierLabel = ucfirst((string)($coverageSummary['tier'] ?? 'individual'));
$planTier = strtolower((string)($coverageSummary['tier'] ?? 'individual'));
$relationshipCounts = [
    'spouse' => 0,
    'child' => 0,
    'parent' => 0,
    'father_in_law' => 0,
    'mother_in_law' => 0,
];
$normalizeRelation = static function ($relationship): string {
    $value = strtolower(trim((string)$relationship));
    $value = str_replace(['-', ' '], '_', $value);
    $aliases = [
        'wife' => 'spouse',
        'husband' => 'spouse',
        'son' => 'child',
        'daughter' => 'child',
        'father' => 'parent',
        'mother' => 'parent',
        'fatherinlaw' => 'father_in_law',
        'motherinlaw' => 'mother_in_law',
    ];
    return $aliases[$value] ?? $value;
};
foreach ($beneficiaries as $beneficiary) {
    $relation = $normalizeRelation($beneficiary['relationship'] ?? '');
    if (isset($relationshipCounts[$relation])) {
        $relationshipCounts[$relation]++;
    }
}

$relationOptions = [];
if ((int)($planLimits['spouse'] ?? 0) > $relationshipCounts['spouse']) {
    $relationOptions[] = ['value' => 'spouse', 'label' => 'Spouse'];
}
if ((int)($planLimits['children'] ?? 0) > $relationshipCounts['child']) {
    $relationOptions[] = ['value' => 'child', 'label' => 'Child'];
}
if ((int)($planLimits['parents'] ?? 0) > $relationshipCounts['parent']) {
    $relationOptions[] = ['value' => 'parent', 'label' => 'Parent'];
}
$inlawSlotsUsed = $relationshipCounts['father_in_law'] + $relationshipCounts['mother_in_law'];
if ((int)($planLimits['inlaws'] ?? 0) > $inlawSlotsUsed) {
    $relationOptions[] = ['value' => 'father_in_law', 'label' => 'Father-in-Law'];
    $relationOptions[] = ['value' => 'mother_in_law', 'label' => 'Mother-in-Law'];
}
?>

<style>
.beneficiaries-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FC;
    max-width: 100%;
    margin: 0 0 0 0;
}

main {
    padding: 0 !important;
    margin: 0 !important;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.page-title h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #4A1468;
    margin: 0;
}

.search-bar {
    position: relative;
    width: 300px;
}

.search-bar input {
    width: 100%;
    padding: 10px 16px 10px 40px;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
}

.search-bar i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9CA3AF;
}

/* Main 2/3 and 1/3 Layout */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    align-items: start;
}

.content-area {
    width: 100%;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4A1468;
    margin: 0;
}

.add-dependent-btn {
    background: #7F3D9E;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.3s;
}

.add-dependent-btn:hover {
    background: #6B2D8A;
    transform: translateY(-1px);
}

.section-description {
    font-size: 0.9rem;
    color: #6B7280;
    margin: 0 0 30px 0;
}

.beneficiaries-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.beneficiary-card {
    background: white;
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    position: relative;
}

.beneficiary-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 18px;
}

.beneficiary-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #7F3D9E;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.beneficiary-avatar.male {
    background: #5E2B7A;
}

.beneficiary-info h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 6px 0;
}

.beneficiary-info p {
    font-size: 0.85rem;
    color: #6B7280;
    margin: 0 0 4px 0;
}

.age-bracket {
    font-size: 0.8rem;
    color: #9CA3AF;
}

.status-badge-card {
    position: absolute;
    top: 24px;
    right: 28px;
}

.active-badge-card {
    background: #D1FAE5;
    color: #059669;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
}

.waiting-badge-card {
    background: #FEF3C7;
    color: #D97706;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
}

.edit-details-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #7F3D9E;
    background: transparent;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    padding: 8px 0;
    transition: all 0.3s;
}

.edit-details-btn:hover {
    color: #6B2D8A;
}

.add-member-card {
    background: white;
    border: 2px dashed #D1D5DB;
    border-radius: 16px;
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    cursor: pointer;
    transition: all 0.3s;
}

.add-member-card:hover {
    border-color: #7F3D9E;
    background: #F9FAFB;
}

.add-icon {
    width: 60px;
    height: 60px;
    background: #F3F4F6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.add-icon i {
    font-size: 1.8rem;
    color: #9CA3AF;
}

.add-member-card h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #1F2937;
    margin: 0 0 6px 0;
}

.add-member-card p {
    font-size: 0.85rem;
    color: #6B7280;
    margin: 0;
}

/* Right Sidebar (1/3) */
.right-sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 20px;
}

.coverage-policy-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    border-left: 4px solid #7F20B0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.policy-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 25px;
}

.policy-icon {
    width: 32px;
    height: 32px;
    background: #F59E0B;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.policy-icon i {
    color: white;
    font-size: 1rem;
}

.policy-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.policy-section {
    margin-bottom: 25px;
}

.policy-section:last-child {
    margin-bottom: 0;
}

.policy-section h4 {
    font-size: 0.75rem;
    font-weight: 700;
    color: #6B7280;
    letter-spacing: 1px;
    margin: 0 0 12px 0;
}

.policy-section p {
    font-size: 0.85rem;
    color: #4B5563;
    line-height: 1.6;
    margin: 0;
}

.benefit-limit {
    background: #F3E8FF;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-top: 25px;
}

.benefit-limit h4 {
    font-size: 0.75rem;
    font-weight: 700;
    color: #7F3D9E;
    letter-spacing: 1px;
    margin: 0 0 8px 0;
}

.benefit-limit h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #7F3D9E;
    margin: 0 0 4px 0;
}

.benefit-limit .currency-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #7F3D9E;
    margin-right: 4px;
}

.benefit-limit p {
    font-size: 0.8rem;
    color: #9333EA;
    margin: 0;
}

.need-help-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
}

.need-help-card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 12px 0;
}

.need-help-card p {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.5;
    margin: 0 0 20px 0;
}

.chat-btn {
    background: #F59E0B;
    color: #1F2937;
    border: none;
    padding: 14px 0;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
}

.chat-btn:hover {
    background: #D97706;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .beneficiaries-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-wrap: wrap;
        gap: 12px;
    }

    .search-bar {
        width: 100%;
    }
}
</style>

<div class="beneficiaries-container dependant-shell">
    <div class="page-header">
        <div class="page-title">
            <h1>Manage Dependents & Beneficiaries</h1>
        </div>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search dependents...">
        </div>
    </div>

    <div class="content-grid">
        <div class="content-area">
            <div class="section-header">
                <h2>Your Covered Family</h2>
                <?php if ($availableSlots > 0): ?>
                <button class="add-dependent-btn" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                    <i class="fas fa-user-plus"></i> Add New Dependent
                </button>
                <?php else: ?>
                <button class="add-dependent-btn" style="opacity:0.5; cursor:not-allowed; background:#9CA3AF;" disabled
                    title="No slots available. Upgrade your plan to cover more family members.">
                    <i class="fas fa-lock"></i> No Slots Available
                </button>
                <?php endif; ?>
            </div>
            <p class="section-description">
                Your <?php echo htmlspecialchars($planTierLabel); ?> plan allows spouse: <?php echo (int)($planLimits['spouse'] ?? 0); ?>,
                children: <?php echo (int)($planLimits['children'] ?? 0); ?>,
                parents: <?php echo (int)($planLimits['parents'] ?? 0); ?>,
                in-laws: <?php echo (int)($planLimits['inlaws'] ?? 0); ?>.
                Available dependent slots: <?php echo (int)$availableSlots; ?>.
            </p>
            
            <div class="beneficiaries-grid">
                <?php if (!empty($beneficiaries)): ?>
                    <?php foreach ($beneficiaries as $index => $beneficiary): ?>
                        <div class="beneficiary-card">
                            <div class="status-badge-card">
                                <span class="<?php echo ($beneficiary['is_active'] ?? true) ? 'active-badge-card' : 'waiting-badge-card'; ?>">
                                    <?php echo ($beneficiary['is_active'] ?? true) ? 'ACTIVE' : 'WAITING'; ?>
                                </span>
                            </div>
                            <div class="beneficiary-header">
                                <div class="beneficiary-avatar <?php echo in_array(strtolower($beneficiary['relationship'] ?? ''), ['son', 'father', 'brother']) ? 'male' : ''; ?>">
                                    <?php echo strtoupper(substr($beneficiary['full_name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div class="beneficiary-info">
                                    <h3><?php echo htmlspecialchars($beneficiary['full_name'] ?? 'Unknown'); ?></h3>
                                    <p><?php echo htmlspecialchars(ucfirst($beneficiary['relationship'] ?? 'Relation')); ?></p>
                                    <p class="age-bracket">Age Bracket: <?php echo htmlspecialchars($beneficiary['age_bracket'] ?? '35-40 years'); ?></p>
                                </div>
                            </div>
                            <button class="edit-details-btn" onclick="editBeneficiary(<?php echo $beneficiary['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit Details
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="beneficiary-card" style="text-align: center;">
                        <div class="beneficiary-header" style="justify-content: center;">
                            <div class="beneficiary-avatar">?</div>
                            <div class="beneficiary-info">
                                <h3>No beneficiaries yet</h3>
                                <p>Add a dependent to start managing your covered family members.</p>
                            </div>
                        </div>
                        <button class="edit-details-btn" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                            <i class="fas fa-user-plus"></i> Add Beneficiary
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($availableSlots > 0): ?>
                <div class="add-member-card" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                    <div class="add-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h4>Add Beneficiary</h4>
                    <p>Available slots: <?php echo $availableSlots; ?> / <?php echo $maxBeneficiaries; ?></p>
                </div>
                <?php else: ?>
                <div class="add-member-card" style="border-color:#F87171; cursor:default;" onclick="window.location.href='/member/upgrade'">
                    <div class="add-icon" style="background:#FEE2E2;">
                        <i class="fas fa-layer-group" style="color:#7F3D9E;"></i>
                    </div>
                    <h4 style="color:#DC2626;">Plan Limit Reached</h4>
                    <p><?php echo $maxBeneficiaries > 0 ? "All {$maxBeneficiaries} slot(s) used." : "Your current plan does not include dependents."; ?> Upgrade to add more family members.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="right-sidebar">
            <div class="coverage-policy-card">
                <div class="policy-header">
                    <div class="policy-icon">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <h3>Coverage Policy</h3>
                </div>
                
                <div class="policy-section">
                    <h4>COVERED MEMBERS</h4>
                    <p>
                        <?php if (in_array($planTier, ['individual', 'executive'])): ?>
                            Principal member only. Full coverage applies after a 3-month waiting period.
                        <?php elseif ($planTier === 'family'): ?>
                            Principal member + spouse. Full coverage applies after a 3-month waiting period.
                        <?php elseif ($planTier === 'extended_family_1'): ?>
                            Principal member, spouse, up to 4 children (under 21), and parents. Full coverage after a 3-month waiting period. A 6-month waiting period applies to parents for natural causes.
                        <?php elseif ($planTier === 'extended_family_2'): ?>
                            Principal member, spouse, up to 4 children, parents, and in-laws. A 6-month waiting period applies to extended family members for natural causes.
                        <?php else: ?>
                            Includes all plan-covered members. Waiting periods apply for natural causes.
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if (in_array($planTier, ['family', 'extended_family_1', 'extended_family_2'])): ?>
                <div class="policy-section">
                    <h4>NUCLEAR FAMILY</h4>
                    <p>Includes spouse and up to 4 biological or legally adopted children under 21 years. Full coverage applies after a 3-month waiting period.</p>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($planTier, ['extended_family_1', 'extended_family_2'])): ?>
                <div class="policy-section">
                    <h4>EXTENDED FAMILY</h4>
                    <p>Includes parents<?php echo $planTier === 'extended_family_2' ? ' and in-laws' : ''; ?>. A 6-month waiting period applies for natural causes.</p>
                </div>
                <?php endif; ?>
                
                <div class="benefit-limit">
                    <h4>BENEFIT LIMIT</h4>
                    <h2>KES 15,000</h2>
                    <p>per member</p>
                </div>
            </div>
            
            <div class="need-help-card">
                <h3>Need Help?</h3>
                <p>Unsure about relationship proof documents? Chat with our support team.</p>
                <button class="chat-btn" onclick="window.location.href='/member/support'">START CHAT</button>
            </div>
        </div>
    </div>
</div>

<!-- Upgrade CTA Card -->
<div class="upgrade-cta-section">
    <div class="dependant-upgrade-card">
        <div class="upgrade-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="upgrade-content">
            <h2>Unlock More Coverage for Your Loved Ones</h2>
            <p class="upgrade-subtitle">Upgrade your plan and protect more family members with enhanced benefits</p>
            
            <div class="tier-comparison">
                <div class="tier-item">
                    <div class="tier-badge bronze">Individual</div>
                    <div class="tier-value">Self</div>
                    <div class="tier-label">Coverage</div>
                </div>
                <div class="tier-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="tier-item highlight">
                    <div class="tier-badge silver">Family</div>
                    <div class="tier-value">2 Adults</div>
                    <div class="tier-label">Coverage</div>
                </div>
                <div class="tier-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="tier-item highlight">
                    <div class="tier-badge gold">Extended 1</div>
                    <div class="tier-value">+ Parents</div>
                    <div class="tier-label">Coverage</div>
                </div>
                <div class="tier-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="tier-item highlight">
                    <div class="tier-badge platinum">Extended 2</div>
                    <div class="tier-value">+ In-laws</div>
                    <div class="tier-label">Coverage</div>
                </div>
            </div>
            
            <div class="upgrade-benefits">
                <div class="benefit-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Cover more family members</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Priority claim processing</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Enhanced funeral services</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Dedicated support team</span>
                </div>
            </div>
            
            <button class="upgrade-btn" onclick="window.location.href='/member/upgrade'">
                <span>Upgrade Now</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<style>
.upgrade-cta-section {
    margin: 3rem 0;
    padding: 0 1rem;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dependant-upgrade-card {
    background: #7F3D9E;
    border-radius: 20px;
    padding: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(127, 32, 176, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dependant-upgrade-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(127, 32, 176, 0.4);
}

.dependant-upgrade-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(-10%, -10%) scale(1.1);
    }
}

.upgrade-icon {
    text-align: center;
    margin-bottom: 1.5rem;
}

.upgrade-icon i {
    font-size: 4rem;
    color: #FFD700;
    text-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.upgrade-content {
    position: relative;
    z-index: 1;
}

.upgrade-content h2 {
    color: white;
    font-size: 2.2rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.upgrade-subtitle {
    color: rgba(255, 255, 255, 0.95);
    font-size: 1.1rem;
    text-align: center;
    margin-bottom: 2.5rem;
}

.tier-comparison {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.tier-item {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    min-width: 120px;
    transition: all 0.3s ease;
}

.tier-item.highlight {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.05);
}

.tier-badge {
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    margin-bottom: 0.8rem;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tier-badge.bronze {
    background: #CD7F32;
    color: white;
}

.tier-badge.silver {
    background: #C0C0C0;
    color: #333;
}

.tier-badge.gold {
    background: #FFD700;
    color: #333;
}

.tier-badge.platinum {
    background: #E5E7EB;
    color: #111827;
}

.tier-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.tier-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

.tier-arrow {
    color: white;
    font-size: 1.5rem;
    opacity: 0.7;
}

.upgrade-benefits {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    color: white;
    font-size: 1rem;
    padding: 0.5rem;
}

.benefit-item i {
    color: #10B981;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.upgrade-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    background: white;
    color: #7F20B0;
    font-size: 1.2rem;
    font-weight: 600;
    padding: 1rem 3rem;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    margin: 0 auto;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.upgrade-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    background: #FFD700;
    color: #333;
}

.upgrade-btn i {
    transition: transform 0.3s ease;
}

.upgrade-btn:hover i {
    transform: translateX(5px);
}

@media (max-width: 768px) {
    .dependant-upgrade-card {
        padding: 2rem 1.5rem;
    }
    
    .upgrade-content h2 {
        font-size: 1.6rem;
    }
    
    .upgrade-subtitle {
        font-size: 1rem;
    }
    
    .tier-comparison {
        gap: 0.8rem;
    }
    
    .tier-item {
        min-width: 90px;
        padding: 1rem;
    }
    
    .tier-value {
        font-size: 2rem;
    }
    
    .tier-arrow {
        font-size: 1.2rem;
    }
    
    .upgrade-benefits {
        grid-template-columns: 1fr;
    }
    
    .upgrade-btn {
        font-size: 1rem;
        padding: 0.8rem 2rem;
    }
}
</style>

<!-- Edit Beneficiary Modal -->
<div class="modal fade" id="editBeneficiaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/beneficiaries/update">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="beneficiary_id" id="editBeneficiaryId">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="editFullName" class="form-control" value="<?php echo getOldValue('full_name_edit') ?: ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relationship *</label>
                        <select name="relationship" id="editRelationship" class="form-control" required>
                            <option value="">Select relationship...</option>
                            <?php foreach ($relationOptions as $rel): ?>
                                <option value="<?php echo htmlspecialchars($rel['value']); ?>"><?php echo htmlspecialchars($rel['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Number (Optional)</label>
                        <input type="text" name="id_number" id="editIdNumber" class="form-control" value="<?php echo getOldValue('id_number_edit') ?: ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="editDateOfBirth" class="form-control" min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" value="<?php echo getOldValue('date_of_birth_edit') ?: ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number (Optional for minors)</label>
                        <input type="tel" name="phone_number" id="editPhoneNumber" class="form-control" value="<?php echo getOldValue('phone_number_edit') ?: ''; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Beneficiary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Beneficiary Modal -->
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/beneficiaries" id="addBeneficiaryForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo getOldValue('full_name'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relationship *</label>
                        <select name="relationship" class="form-control" required <?php echo empty($relationOptions) ? 'disabled' : ''; ?>>
                            <option value="">Select relationship...</option>
                            <?php foreach ($relationOptions as $rel): ?>
                            <option value="<?php echo htmlspecialchars($rel['value']); ?>" <?php echo (getOldValue('relationship') === $rel['value']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rel['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Number (Optional)</label>
                        <input type="text" name="id_number" class="form-control" value="<?php echo getOldValue('id_number'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="form-control" min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" value="<?php echo getOldValue('date_of_birth'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number (Optional for minors)</label>
                        <input type="tel" name="phone_number" class="form-control" value="<?php echo getOldValue('phone_number'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" <?php echo empty($relationOptions) ? 'disabled' : ''; ?>>Add Beneficiary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBeneficiary(id) {
    const beneficiaries = <?php echo json_encode($beneficiaries ?? []); ?>;
    const beneficiary = beneficiaries.find(b => b.id == id);
    if (!beneficiary) return;
    
    document.getElementById('editBeneficiaryId').value = beneficiary.id;
    document.getElementById('editFullName').value = beneficiary.full_name;
    const relSelect = document.getElementById('editRelationship');
    const relVal = beneficiary.relationship || '';
    let found = false;
    for (let i = 0; i < relSelect.options.length; i++) {
        if (relSelect.options[i].value === relVal) { relSelect.selectedIndex = i; found = true; break; }
    }
    if (!found) {
        const opt = document.createElement('option');
        opt.value = relVal; opt.textContent = relVal; opt.selected = true;
        relSelect.appendChild(opt);
    }
    document.getElementById('editIdNumber').value = beneficiary.id_number;
    document.getElementById('editDateOfBirth').value = beneficiary.date_of_birth || '';
    document.getElementById('editPhoneNumber').value = beneficiary.phone_number || '';
    
    new bootstrap.Modal(document.getElementById('editBeneficiaryModal')).show();
}
</script>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
