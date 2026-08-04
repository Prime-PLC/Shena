<?php 
$page = 'members'; 
include __DIR__ . '/../layouts/agent-header.php';

// Data passed from controller: $member, $dependents, $payment_history
$coverageSummary = $coverage_summary ?? ['tier' => 'individual', 'limits' => ['spouse' => 0, 'children' => 0, 'parents' => 0, 'inlaws' => 0, 'other' => 0], 'total_slots' => 0];
$planLimits = $coverageSummary['limits'] ?? ['spouse' => 0, 'children' => 0, 'parents' => 0, 'inlaws' => 0, 'other' => 0];
$planTierLabel = ucfirst((string)($coverageSummary['tier'] ?? 'individual'));
$dependantRelationshipOptions = $dependant_relationship_options ?? [];
// Generate member initials
$memberInitials = strtoupper(substr($member['first_name'] ?? 'M', 0, 1) . substr($member['last_name'] ?? 'M', 0, 1));

// Format member status for display
$memberStatus = strtoupper($member['status'] ?? 'active');
$statusClass = ($memberStatus === 'ACTIVE') ? 'success' : (($memberStatus === 'SUSPENDED') ? 'warning' : 'danger');

// Format joined date
$joinedDate = isset($member['created_at']) ? date('M Y', strtotime($member['created_at'])) : 'N/A';

// Format location (county or address)
$location = $member['county'] ?? ($member['address'] ?? 'N/A');

// Get plan details
$planName = $member['package'] ?? 'Standard Plan';
$monthlyPremium = 0;

// Set premium based on package
switch($planName) {
    case 'basic':
        $monthlyPremium = 500.00;
        $displayPlanName = 'Basic Plan';
        break;
    case 'standard':
        $monthlyPremium = 1000.00;
        $displayPlanName = 'Standard Plan';
        break;
    case 'premium':
        $monthlyPremium = 2000.00;
        $displayPlanName = 'Premium Plan';
        break;
    default:
        $monthlyPremium = 1000.00;
        $displayPlanName = ucfirst($planName);
}

// Process dependents to add initials
if (!empty($dependents)) {
    foreach ($dependents as &$dependent) {
        $firstName = explode(' ', $dependent['first_name'] ?? $dependent['name'] ?? 'D')[0];
        $lastName = explode(' ', $dependent['last_name'] ?? $dependent['name'] ?? 'D')[1] ?? $dependent['name'] ?? 'D';
        $dependent['initials'] = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        
        // Calculate age if date_of_birth is available
        if (isset($dependent['date_of_birth'])) {
            $dob = new DateTime($dependent['date_of_birth']);
            $now = new DateTime();
            $dependent['age'] = $now->diff($dob)->y;
        } else {
            $dependent['age'] = 'N/A';
        }
        
        // Format ID number
        if (isset($dependent['id_number'])) {
            $dependent['display_id'] = $dependent['id_number'];
        } else {
            $dependent['display_id'] = 'N/A';
        }
    }
}

// Process payment history
if (!empty($payment_history)) {
    foreach ($payment_history as &$payment) {
        $payment['month'] = date('F Y', strtotime($payment['created_at'] ?? $payment['payment_date'] ?? 'now'));
        $payment['display_amount'] = $payment['amount'] ?? 0;
        $payment['status_text'] = strtoupper($payment['status'] ?? 'pending');
        $payment['status_class'] = ($payment['status'] === 'completed' || $payment['status'] === 'success') ? 'success' : 'warning';
        $payment['description'] = $payment['payment_method'] ?? 'M-Pesa';
    }
}
?>

<style>
/* Member Details Page Styles */
.member-details-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

/* Breadcrumb */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.btn-back {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid #E5E7EB;
    background: white;
    color: #6B7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
}

.breadcrumb-text {
    font-size: 14px;
    color: #6B7280;
}

.breadcrumb-text strong {
    color: #1F2937;
}

/* Member Header Card */
.member-header-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px rgba(127, 32, 176, 0.2);
    position: relative;
    overflow: hidden;
}

.member-header-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.member-header-content {
    display: flex;
    align-items: center;
    gap: 24px;
    position: relative;
    z-index: 1;
}

.member-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 48px;
    font-weight: 700;
    color: #7F20B0;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.member-info-header {
    flex: 1;
}

.member-name-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.member-name-header {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 700;
    color: white;
    margin: 0;
}

.status-badge-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    background: #059669;
    color: white;
}

.status-badge-header i {
    font-size: 8px;
}

.member-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 16px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
}

.meta-item i {
    color: rgba(255, 255, 255, 0.7);
}

.member-actions {
    display: flex;
    gap: 12px;
    margin-left: auto;
}

.btn-initiate-claim,
.btn-assist-payment {
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-initiate-claim {
    background: rgba(255, 255, 255, 0.95);
    color: #7F20B0;
}

.btn-initiate-claim:hover {
    background: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-assist-payment {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-assist-payment:hover {
    background: rgba(255, 255, 255, 0.25);
}

.btn-initiate-claim,
.btn-assist-payment {
    opacity: 0.6;
}

/* Main Grid */
.details-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 24px;
}

/* Dependent List Section */
.dependent-list-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-header-details {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #E5E7EB;
}

.section-title-details {
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-icon-details {
    color: #7F20B0;
    font-size: 20px;
}

.section-title-details h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.section-subtitle {
    font-size: 12px;
    color: #9CA3AF;
    margin-top: 2px;
}

.btn-add-dependent {
    color: #7F20B0;
    font-size: 14px;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: underline;
}

/* Dependent Table */
.dependent-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.dependent-table thead th {
    font-size: 11px;
    font-weight: 700;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #E5E7EB;
}

.dependent-table tbody tr {
    transition: background-color 0.2s;
}

.dependent-table tbody tr:hover {
    background: #F9FAFB;
}

.dependent-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #F3F4F6;
}

.dependent-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dependent-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.dependent-name {
    font-size: 14px;
    font-weight: 600;
    color: #1F2937;
}

.dependent-relation {
    font-size: 13px;
    color: #6B7280;
}

.dependent-id {
    font-size: 13px;
    color: #6B7280;
    font-family: monospace;
}

.dependent-age {
    font-size: 14px;
    color: #4B5563;
}

/* Contact and Plan Section */
.contact-plan-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.info-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-card-header {
    font-size: 11px;
    font-weight: 700;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
}

.info-item {
    margin-bottom: 16px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.info-label i {
    color: #9CA3AF;
}

.info-value {
    font-size: 15px;
    font-weight: 600;
    color: #1F2937;
}

.plan-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.plan-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

/* Payment History Section */
.payment-history-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.payment-history-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #E5E7EB;
}

.payment-icon {
    color: #059669;
    font-size: 20px;
}

.payment-history-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 0;
    border-bottom: 1px solid #F3F4F6;
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-info h6 {
    font-size: 14px;
    font-weight: 600;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.payment-info p {
    font-size: 12px;
    color: #9CA3AF;
    margin: 0;
}

.payment-amount-status {
    text-align: right;
}

.payment-amount {
    font-size: 16px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 4px;
}

.payment-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}

.payment-status-badge.success {
    background: #D1FAE5;
    color: #059669;
}

.payment-status-badge.warning {
    background: #FEF3C7;
    color: #D97706;
}

.download-statement {
    text-align: center;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid #E5E7EB;
}

.btn-download-statement {
    color: #7F20B0;
    font-size: 14px;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: underline;
}

/* Support Tip Card */
.support-tip-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 16px;
    padding: 24px;
    color: white;
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.support-tip-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.support-tip-icon {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.support-tip-header h4 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}

.support-tip-text {
    font-size: 13px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 16px;
}

.btn-view-playbook {
    background: white;
    color: #7F20B0;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    width: 100%;
    cursor: pointer;
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-view-playbook:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
}

/* Responsive */
@media (max-width: 1200px) {
    .details-grid {
        grid-template-columns: 1fr;
    }

    .contact-plan-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .member-details-container {
        padding: 20px 15px;
    }

    .member-header-card {
        padding: 24px;
    }

    .member-header-content {
        flex-direction: column;
        text-align: center;
    }

    .member-actions {
        margin-left: 0;
        width: 100%;
        flex-direction: column;
    }

    .btn-initiate-claim,
    .btn-assist-payment {
        width: 100%;
        justify-content: center;
    }

    .member-avatar-large {
        width: 100px;
        height: 100px;
        font-size: 40px;
    }

    .member-name-header {
        font-size: 28px;
    }

    .member-meta {
        flex-direction: column;
        gap: 8px;
    }

    .dependent-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="member-details-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb-nav">
        <button class="btn-back" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span class="breadcrumb-text">Portfolio → <strong>Member Details</strong></span>
    </div>

    <!-- Member Header Card -->
    <div class="member-header-card">
        <div class="member-header-content">
            <div class="member-avatar-large"><?php echo $memberInitials; ?></div>
            <div class="member-info-header">
                <div class="member-name-row">
                    <h1 class="member-name-header"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h1>
                    <span class="status-badge-header">
                        <i class="fas fa-circle"></i>
                        <?php echo $memberStatus; ?>
                    </span>
                </div>
                <div class="member-meta">
                    <span class="meta-item">
                        <i class="fas fa-id-card"></i>
                        <?php echo htmlspecialchars($member['member_number'] ?? 'N/A'); ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($location); ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-calendar"></i>
                        Joined <?php echo $joinedDate; ?>
                    </span>
                </div>
            </div>
            <div class="member-actions">
                <button class="btn-initiate-claim" type="button" onclick="ShenaApp.showNotification('Claim processing is only handled by admins.', 'info'); return false;">
                    <i class="fas fa-file-medical"></i>
                    Initiate Claim
                </button>
                <button class="btn-assist-payment" type="button" onclick="openAssistPaymentModal()">
                    <i class="fas fa-hand-holding-usd"></i>
                    Assist Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="details-grid">
        <!-- Left Column -->
        <div>
            <!-- Dependent List -->
            <div class="dependent-list-card">
                <div class="section-header-details">
                    <div class="section-title-details">
                        <i class="fas fa-users section-icon-details"></i>
                        <div>
                            <h3>Dependent List</h3>
                            <p class="section-subtitle"><?php echo count($dependents); ?> family member<?php echo count($dependents) != 1 ? 's' : ''; ?> covered under this policy • <?php echo htmlspecialchars($planTierLabel); ?> limits: spouse <?php echo (int)($planLimits['spouse'] ?? 0); ?>, children <?php echo (int)($planLimits['children'] ?? 0); ?>, parents <?php echo (int)($planLimits['parents'] ?? 0); ?>, in-laws <?php echo (int)($planLimits['inlaws'] ?? 0); ?></p>
                        </div>
                    </div>
                    <button class="btn-add-dependent" type="button" data-bs-toggle="modal" data-bs-target="#addDependentModal">Add Dependent</button>
                </div>

                <?php if (empty($dependents)): ?>
                    <div class="empty-state" style="padding: 40px 20px; text-align: center;">
                        <i class="fas fa-users" style="font-size: 48px; color: #D1D5DB; margin-bottom: 16px;"></i>
                        <h3 style="font-size: 16px; color: #6B7280; margin: 0 0 8px 0;">No Dependents Added</h3>
                        <p style="font-size: 14px; color: #9CA3AF; margin: 0;">Click "Add Dependent" to add family members</p>
                    </div>
                <?php else: ?>
                <table class="dependent-table">
                    <thead>
                        <tr>
                            <th>NAME</th>
                            <th>RELATION</th>
                            <th>ID NUMBER</th>
                            <th>AGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dependents as $dependent): ?>
                        <?php
                            $dependentName = $dependent['full_name'] ?? trim(($dependent['first_name'] ?? '') . ' ' . ($dependent['last_name'] ?? ''));
                            $dependentIdNumber = $dependent['id_number'] ?? ($dependent['display_id'] ?? 'N/A');
                            $dependentAge = $dependent['age'] ?? 'N/A';
                            $nameParts = preg_split('/\s+/', trim($dependentName));
                            $firstInitial = !empty($nameParts[0]) ? strtoupper(substr($nameParts[0], 0, 1)) : 'D';
                            $lastInitial = count($nameParts) > 1 ? strtoupper(substr($nameParts[1], 0, 1)) : $firstInitial;
                            $dependentInitials = $dependent['initials'] ?? ($firstInitial . $lastInitial);
                        ?>
                        <tr>
                            <td>
                                <div class="dependent-info">
                                    <div class="dependent-avatar"><?php echo htmlspecialchars($dependentInitials); ?></div>
                                    <span class="dependent-name"><?php echo htmlspecialchars($dependentName); ?></span>
                                </div>
                            </td>
                            <td class="dependent-relation"><?php echo htmlspecialchars($dependent['relationship'] ?? 'N/A'); ?></td>
                            <td class="dependent-id"><?php echo htmlspecialchars($dependentIdNumber); ?></td>
                            <td class="dependent-age"><?php echo htmlspecialchars($dependentAge); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Contact Information & Plan Details -->
            <div class="contact-plan-grid">
                <!-- Contact Information -->
                <div class="info-card">
                    <div class="info-card-header">Contact Information</div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-phone"></i>
                            Mobile Phone
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></div>
                    </div>
                </div>

                <!-- Plan Details -->
                <div class="info-card">
                    <div class="info-card-header">Plan Details</div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-shield-alt"></i>
                            Active Plan
                        </div>
                        <div class="plan-badge">
                            <div class="plan-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($displayPlanName); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-money-bill-wave"></i>
                            Monthly Premium
                        </div>
                        <div class="info-value">KES <?php echo number_format($monthlyPremium, 2); ?> / month</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Payment History -->
            <div class="payment-history-card">
                <div class="payment-history-header">
                    <i class="fas fa-history payment-icon"></i>
                    <h3>Payment History</h3>
                </div>

                <?php if (empty($payment_history)): ?>
                    <div class="empty-state" style="padding: 40px 20px; text-align: center;">
                        <i class="fas fa-receipt" style="font-size: 48px; color: #D1D5DB; margin-bottom: 16px;"></i>
                        <h3 style="font-size: 16px; color: #6B7280; margin: 0 0 8px 0;">No Payment History</h3>
                        <p style="font-size: 14px; color: #9CA3AF; margin: 0;">Payment records will appear here</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($payment_history as $payment): ?>
                    <div class="payment-item">
                        <div class="payment-info">
                            <h6><?php echo htmlspecialchars($payment['month']); ?></h6>
                            <p>Paid via <?php echo htmlspecialchars($payment['description']); ?></p>
                        </div>
                        <div class="payment-amount-status">
                            <div class="payment-amount">KES <?php echo number_format($payment['display_amount'], 2); ?></div>
                            <span class="payment-status-badge <?php echo $payment['status_class']; ?>">
                                <?php echo $payment['status_text']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="download-statement">
                    <a class="btn-download-statement" href="/agent/member-details/<?php echo (int)$member['id']; ?>/statement">Download Full Statement</a>
                </div>
            </div>

            <!-- Support Tip -->
            <div class="support-tip-card">
                <div class="support-tip-header">
                    <div class="support-tip-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Support Tip</h4>
                </div>
                <p class="support-tip-text">
                    Member missed last payment. Remind them to settle any arrears to avoid policy degradation. Check their payment history for details.
                </p>
                <button class="btn-view-playbook" onclick="window.location.href='/agent/resources'">View Agent Playbook</button>
            </div>
        </div>
    </div>
</div>

<!-- Claim Assistance Modal -->
<div class="modal fade" id="claimAssistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/agent/member-details/<?php echo (int)$member['id']; ?>/claim-request">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-medical"></i> Claim Assistance Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deceased Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="deceased_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Death <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_death" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="claim_notes" class="form-control" rows="3" placeholder="Provide any extra details for admin review"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Dependent Modal -->
<div class="modal fade" id="addDependentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/agent/member-details/<?php echo (int)$member['id']; ?>/dependents/add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-users"></i> Add Dependent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relationship <span class="text-danger">*</span></label>
                        <select name="relationship" class="form-control" required>
                            <option value="">Select relationship...</option>
                            <?php foreach ($dependantRelationshipOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['value'] ?? ''); ?>"><?php echo htmlspecialchars($option['label'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Number (Optional)</label>
                        <input type="text" name="id_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number (Optional for minors)</label>
                        <input type="tel" name="phone_number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" <?php echo empty($dependantRelationshipOptions) ? 'disabled' : ''; ?>>Add Dependent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assist Payment Modal -->
<div class="modal fade" id="assistPaymentModal" tabindex="-1" aria-labelledby="assistPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: white; border: none; padding: 20px 24px;">
                <div>
                    <h5 class="modal-title" id="assistPaymentModalLabel" style="font-weight: 700; margin: 0;">
                        <i class="fas fa-hand-holding-usd" style="margin-right: 8px;"></i>Send M-Pesa Payment Request
                    </h5>
                    <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.85;">
                        Member: <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        &nbsp;(<?php echo htmlspecialchars($member['member_number'] ?? ''); ?>)
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div id="agentPayStatus" style="display: none; margin-bottom: 16px;">
                    <div class="alert" id="agentPayStatusMsg" style="border-radius: 8px;"></div>
                </div>
                <div class="mb-3">
                    <label for="agentPayPhone" class="form-label" style="font-weight: 600; color: #374151;">M-Pesa Phone Number <span style="color: #EF4444;">*</span></label>
                    <input type="tel" id="agentPayPhone" class="form-control" placeholder="07XXXXXXXX"
                        value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>"
                        style="border-radius: 8px; border: 1px solid #D1D5DB; padding: 10px 14px;">
                    <div style="font-size: 12px; color: #6B7280; margin-top: 4px;">Format: 07XXXXXXXX or 254XXXXXXXXX</div>
                </div>
                <div class="mb-3">
                    <label for="agentPayAmount" class="form-label" style="font-weight: 600; color: #374151;">Amount (KES) <span style="color: #EF4444;">*</span></label>
                    <input type="number" id="agentPayAmount" class="form-control" placeholder="0.00" min="1" step="1"
                        value="<?php echo (int)($member['monthly_contribution'] ?? 0) > 0 ? (int)$member['monthly_contribution'] : ''; ?>"
                        style="border-radius: 8px; border: 1px solid #D1D5DB; padding: 10px 14px;">
                </div>
                <div class="mb-3">
                    <label for="agentPayType" class="form-label" style="font-weight: 600; color: #374151;">Payment Type</label>
                    <select id="agentPayType" class="form-select" style="border-radius: 8px; border: 1px solid #D1D5DB; padding: 10px 14px;">
                        <option value="monthly">Monthly Contribution</option>
                        <option value="reactivation">Reactivation Fee</option>
                        <option value="penalty">Penalty</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="border: none; padding: 16px 24px 24px; gap: 10px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" id="agentPayBtn" onclick="submitAgentPayment(<?php echo (int)($member['id'] ?? 0); ?>)"
                    style="background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-paper-plane"></i> Send M-Pesa Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openAssistPaymentModal() {
    const modal = new bootstrap.Modal(document.getElementById('assistPaymentModal'));
    document.getElementById('agentPayStatus').style.display = 'none';
    document.getElementById('agentPayBtn').disabled = false;
    document.getElementById('agentPayBtn').innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
    modal.show();
}

async function submitAgentPayment(memberId) {
    const btn = document.getElementById('agentPayBtn');
    const statusDiv = document.getElementById('agentPayStatus');
    const statusMsg = document.getElementById('agentPayStatusMsg');
    const phone = document.getElementById('agentPayPhone').value.trim();
    const amount = parseFloat(document.getElementById('agentPayAmount').value);
    const paymentType = document.getElementById('agentPayType').value;

    if (!phone || phone.length < 9) {
        ShenaApp.showNotification('Please enter a valid phone number.', 'warning');
        return;
    }
    if (!amount || amount <= 0) {
        ShenaApp.showNotification('Please enter a valid amount.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    statusDiv.style.display = 'none';

    try {
        const response = await fetch('/agent/member-details/' + memberId + '/payment-assist', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: '<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>',
                phone_number: phone,
                amount: amount,
                payment_type: paymentType
            })
        });

        const data = await response.json();

        statusDiv.style.display = 'block';
        if (data.success) {
            statusMsg.className = 'alert alert-success';
            statusMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            btn.innerHTML = '<i class="fas fa-check"></i> Sent';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('assistPaymentModal'))?.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
                statusDiv.style.display = 'none';
            }, 3500);
        } else {
            statusMsg.className = 'alert alert-danger';
            statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> ' + (data.error || 'Failed to initiate payment.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
        }
    } catch (err) {
        statusDiv.style.display = 'block';
        statusMsg.className = 'alert alert-danger';
        statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> Network error. Please try again.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send M-Pesa Request';
    }
}
</script>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
