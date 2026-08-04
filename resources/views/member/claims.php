<?php
$page = 'claims';
include __DIR__ . '/../layouts/member-header.php';

// Sample data for demonstration
$claims = $claims ?? [];
$beneficiaries = $beneficiaries ?? [];
$csrf_token = $csrf_token ?? '';
$activeClaims = array_filter($claims, fn($c) => $c['status'] !== 'approved' && $c['status'] !== 'rejected');
$pastClaims = array_filter($claims, fn($c) => $c['status'] === 'approved' || $c['status'] === 'rejected');
$hasBeneficiaries = !empty($beneficiaries);

// Debugging output (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log('Claims page loaded - Has beneficiaries: ' . ($hasBeneficiaries ? 'yes' : 'no'));
    error_log('Beneficiary count: ' . count($beneficiaries));
    error_log('CSRF token present: ' . (!empty($csrf_token) ? 'yes' : 'no'));
}

// No mock data - use real data from controller
?>

<style>
main {
    padding: 0 !important;
    margin: 0 !important;
}

.claims-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FC;
    max-width: 100%;
    margin: 0;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #4A1468;
    margin: 0 0 30px 0;
}

.alert-banner {
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.success-banner {
    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
    border-left: 4px solid #10B981;
}

.error-banner {
    background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
    border-left: 4px solid #EF4444;
}

.alert-banner button:hover {
    opacity: 0.7;
}

.hero-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}

.hero-card::after {
    content: '📋';
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 120px;
    opacity: 0.1;
}

.hero-card h2 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 12px 0;
    position: relative;
    z-index: 1;
}

.hero-card p {
    font-size: 1rem;
    margin: 0 0 25px 0;
    color: rgba(255, 255, 255, 0.9);
    max-width: 500px;
    position: relative;
    z-index: 1;
}

.submit-claim-btn {
    background: #F59E0B;
    color: #1F2937;
    border: none;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    position: relative;
    z-index: 1;
}

.submit-claim-btn:hover {
    background: #D97706;
    transform: translateY(-2px);
}

.submit-claim-btn:disabled {
    background: #9CA3AF;
    color: #6B7280;
    cursor: not-allowed;
    opacity: 0.6;
    transform: none;
}

.submit-claim-btn:disabled:hover {
    background: #9CA3AF;
    transform: none;
}

.claim-method-card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    border: 2px solid transparent;
    animation: slideDown 0.5s ease-out;
}

.claim-method-card:nth-child(2) {
    animation-delay: 0.1s;
}

.claim-method-card:hover {
    border-color: #7F20B0;
    box-shadow: 0 8px 24px rgba(127, 32, 176, 0.15);
    transform: translateY(-4px);
}

.claim-method-card[data-method="cash"]:hover {
    border-color: #F59E0B;
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
}

.method-icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.claim-method-card:hover .method-icon-wrapper {
    animation: pulse 0.6s ease-in-out;
}

.benefits-list {
    margin-bottom: 24px;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #F3F4F6;
    font-size: 0.9rem;
    color: #4B5563;
}

.benefit-item:last-child {
    border-bottom: none;
}

.benefit-item i {
    font-size: 16px;
    flex-shrink: 0;
}

.method-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 24px;
}

.method-select-btn {
    width: 100%;
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.method-select-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(127, 32, 176, 0.4);
}

.method-select-btn:disabled {
    background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%);
    cursor: not-allowed;
    opacity: 0.6;
}

.method-select-btn:disabled:hover {
    transform: none;
    box-shadow: 0 4px 12px rgba(156, 163, 175, 0.3);
}

.method-select-btn.cash-alt {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.method-select-btn.cash-alt:hover {
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
}

@media (max-width: 768px) {
    .claim-method-card {
        margin-bottom: 20px;
    }
    
    .claims-container > div:first-child {
        grid-template-columns: 1fr !important;
    }
    
    .documents-sidebar {
        display: none;
    }
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.tabs {
    display: flex;
    gap: 20px;
}

.tab {
    background: transparent;
    border: none;
    padding: 8px 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #6B7280;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s;
}

.tab.active {
    color: #7F3D9E;
    border-bottom-color: #7F3D9E;
}

.claim-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all 0.3s ease;
    cursor: pointer;
}

.claim-card:hover {
    box-shadow: 0 4px 16px rgba(127, 32, 176, 0.15);
    transform: translateY(-4px);
}

.claim-card * {
    color: inherit;
}

.claim-card .claim-info h4,
.claim-card .claim-info h3,
.claim-card .claim-info p,
.claim-card .claim-meta p,
.claim-card .next-step-content h4,
.claim-card .next-step-content p {
    color: inherit !important;
}

.claim-card .claim-info h4 {
    color: #6B7280 !important;
}

.claim-card .claim-info h3 {
    color: #1F2937 !important;
}

.claim-card .claim-info p {
    color: #6B7280 !important;
}

.claim-card .status-badge {
    background: #FEF3C7 !important;
    color: #D97706 !important;
}

.claim-card .next-step-card i {
    color: #7F3D9E !important;
}

.claim-card .next-step-content h4 {
    color: #1F2937 !important;
}

.claim-card .next-step-content p {
    color: #4B5563 !important;
}

.claim-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}

.claim-info h4 {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6B7280;
    letter-spacing: 0.5px;
    margin: 0 0 8px 0;
}

.claim-info h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 12px 0;
}

.claim-info p {
    font-size: 0.9rem;
    color: #6B7280;
    margin: 6px 0;
}

.claim-meta {
    text-align: right;
}

.claim-meta p {
    font-size: 0.75rem;
    color: #6B7280;
    margin: 0 0 8px 0;
}

.claim-meta h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 10px 0;
}

.status-badge {
    background: #FEF3C7;
    color: #D97706;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.progress-tracker {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 35px 0;
}

.progress-tracker::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 30px;
    right: 30px;
    height: 2px;
    background: #E5E7EB;
    z-index: 0;
}

.progress-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    font-size: 1.3rem;
}

.step-icon.completed {
    background: #7F3D9E;
    color: white;
}

.step-icon.processing {
    background: #7F3D9E;
    color: white;
}

.step-icon.pending {
    background: #E5E7EB;
    color: #9CA3AF;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1F2937;
    margin-bottom: 4px;
    text-align: center;
}

.step-date {
    font-size: 0.75rem;
    color: #6B7280;
}

.step-status {
    font-size: 0.75rem;
    color: #F59E0B;
    font-style: italic;
}

.next-step-card {
    background: #F3E8FF;
    border-left: 4px solid #7F3D9E;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.next-step-card i {
    color: #7F3D9E;
    font-size: 1.2rem;
    margin-top: 2px;
}

.next-step-content h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 8px 0;
}

.next-step-content p {
    font-size: 0.85rem;
    color: #4B5563;
    line-height: 1.6;
    margin: 0;
}

.past-claims-section {
    margin-top: 50px;
}

.past-claims-section h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #6B7280;
    letter-spacing: 1px;
    margin: 0 0 20px 0;
}

.past-claim-item {
    background: white;
    border-radius: 16px;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    transition: all 0.3s;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    text-decoration: none;
    color: inherit;
}

.past-claim-item:hover {
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.15);
    transform: translateX(4px);
}

.past-claim-item .past-claim-info h4 {
    color: #1F2937 !important;
}

.past-claim-item .past-claim-info p {
    color: #6B7280 !important;
}

.past-claim-item .past-claim-amount {
    color: #1F2937 !important;
}

.past-claim-item .past-claim-icon {
    background: #D1FAE5 !important;
    color: #059669 !important;
}

.past-claim-item .past-claim-arrow {
    color: #9CA3AF !important;
}

.past-claim-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #D1FAE5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #059669;
    font-size: 1.2rem;
}

.past-claim-info {
    flex: 1;
    margin: 0 20px;
}

.past-claim-info h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.past-claim-info p {
    font-size: 0.85rem;
    color: #6B7280;
    margin: 0;
}

.past-claim-amount {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1F2937;
    margin-right: 15px;
}

.past-claim-arrow {
    color: #9CA3AF;
    font-size: 1.2rem;
}

.documents-sidebar {
    background: #5E2B7A;
    border-radius: 20px;
    padding: 25px;
    color: white;
    position: sticky;
    top: 20px;
}

.documents-sidebar h4 {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1px;
    margin: 0 0 20px 0;
    color: rgba(255, 255, 255, 0.8);
}

.document-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.document-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.document-icon.checked {
    background: #10B981;
    color: white;
    font-size: 0.7rem;
}

.document-icon.unchecked {
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.document-item span {
    font-size: 0.9rem;
    color: white;
}

.document-note {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 20px;
    line-height: 1.5;
}

@media (max-width: 1024px) {
    .claims-container {
        padding: 20px;
    }
    
    .progress-tracker {
        flex-wrap: wrap;
    }
}

/* Modal Styling */
.modal-content {
    border-radius: 20px;
    border: none;
}

.modal-header {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 20px 20px 0 0;
    border: none;
}

.modal-header .modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

.modal-header .btn-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 35px 30px;
}

.modal-body h6 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1F2937;
    margin: 25px 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #E5E7EB;
}

.modal-body h6:first-child {
    margin-top: 0;
}

.modal-body .form-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.modal-body .form-control,
.modal-body .form-select {
    border: 1.5px solid #E5E7EB;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.modal-body .form-control:focus,
.modal-body .form-select:focus {
    border-color: #7F3D9E;
    box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
}

.modal-body .alert-info {
    background: #F0F9FF;
    border-left: 4px solid #3B82F6;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 25px;
}

.modal-body .alert-info i {
    color: #3B82F6;
}

.modal-body .text-muted {
    font-size: 0.8rem;
    color: #6B7280;
    margin-top: 4px;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 1px solid #E5E7EB;
    border-radius: 0 0 20px 20px;
}

.modal-footer .btn {
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
}

.modal-footer .btn-secondary {
    background: #F3F4F6;
    color: #374151;
    border: none;
}

.modal-footer .btn-secondary:hover {
    background: #E5E7EB;
}

.modal-footer .btn-primary {
    background: #7F3D9E;
    border: none;
}

.modal-footer .btn-primary:hover {
    background: #6B2D8A;
}
</style>

<div class="claims-container">
    <div style="display: grid; grid-template-columns: 1fr 200px; gap: 30px; align-items: start;">
        <div>
            <h1 class="page-title">Claims Center</h1>
            
            <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const flashMessages = [
                            <?php if (isset($_SESSION['success'])): ?>{ type: 'success', message: <?php echo json_encode($_SESSION['success']); ?> },<?php unset($_SESSION['success']); endif; ?>
                            <?php if (isset($_SESSION['error'])): ?>{ type: 'error', message: <?php echo json_encode($_SESSION['error']); ?> },<?php unset($_SESSION['error']); endif; ?>
                        ];

                        flashMessages.forEach(function(flash) {
                            if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                                ShenaApp.alert(flash.message, flash.type);
                                return;
                            }
                            if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                                ShenaApp.showNotification(flash.message, flash.type, 5000);
                                return;
                            }
                            console.warn(flash.message);
                        });
                    });
                </script>
            <?php endif; ?>
            
            <?php 
            // Check if member is in maturity period
            $isInMaturityPeriod = false;
            $maturityMessage = '';
            if (!empty($member['maturity_ends'])) {
                $maturityDate = new DateTime($member['maturity_ends']);
                $today = new DateTime();
                
                if ($today < $maturityDate) {
                    $isInMaturityPeriod = true;
                    $daysRemaining = $today->diff($maturityDate)->days;
                    $maturityDateFormatted = $maturityDate->format('F j, Y');
                    $maturityMessage = "Your membership is currently in the maturity period. You will be eligible to submit claims after <strong>{$maturityDateFormatted}</strong> ({$daysRemaining} days remaining).";
                }
            }
            
            // Show maturity period notice
            if ($isInMaturityPeriod): 
            ?>
            <div style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-left: 4px solid #F59E0B; padding: 20px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);">
                <div style="display: flex; align-items: start; gap: 15px;">
                    <i class="fas fa-info-circle" style="color: #D97706; font-size: 24px; margin-top: 2px;"></i>
                    <div>
                        <h4 style="margin: 0 0 8px 0; color: #92400E; font-size: 1.1rem; font-weight: 600;">
                            Maturity Period In Progress
                        </h4>
                        <p style="margin: 0; color: #78350F; line-height: 1.6;">
                            <?php echo $maturityMessage; ?>
                        </p>
                        <p style="margin: 10px 0 0 0; color: #78350F; font-size: 0.9rem;">
                            <strong>Why?</strong> The maturity period ensures your membership contributions are up to date before benefit claims can be processed. 
                            If you have an urgent situation, please contact SHENA administration for assistance.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Claim Options Cards -->
            <div style="margin-bottom: 40px;">
                <h2 style="font-size: 1.5rem; font-weight: 700; color: #1F2937; margin: 0 0 8px 0;">
                    Choose Your Claim Method
                </h2>
                <p style="color: #6B7280; margin: 0 0 30px 0;">
                    Select the type of benefit you wish to claim. You can choose between comprehensive funeral services or a cash payout.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
                    <!-- Standard Services Card -->
                    <div class="claim-method-card" data-method="standard">
                        <div class="method-icon-wrapper" style="background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);">
                            <i class="fas fa-hands-helping" style="font-size: 32px; color: white;"></i>
                        </div>
                        <h3 style="font-size: 1.3rem; font-weight: 700; color: #1F2937; margin: 16px 0 8px 0;">
                            Standard Services
                        </h3>
                        <p style="color: #6B7280; font-size: 0.95rem; line-height: 1.6; margin: 0 0 20px 0;">
                            Comprehensive funeral services provided by SHENA including mortuary, casket, hearse, and burial support.
                        </p>
                        
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #10B981;"></i>
                                <span>Professional mortuary services</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #10B981;"></i>
                                <span>Quality casket provided</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #10B981;"></i>
                                <span>Hearse transportation</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #10B981;"></i>
                                <span>Burial ceremony support</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #10B981;"></i>
                                <span>Up to 14 days mortuary coverage</span>
                            </div>
                        </div>
                        
                        <div class="method-badge" style="background: #F3E8FF; color: #7F20B0;">
                            <i class="fas fa-star"></i> Most Popular
                        </div>
                        
                        <button class="method-select-btn" data-method="standard" data-bs-toggle="modal" data-bs-target="#submitClaimModal" <?php echo $isInMaturityPeriod ? 'disabled' : ''; ?>>
                            <i class="fas fa-arrow-right"></i> Select Standard Services
                        </button>
                    </div>
                    
                    <!-- Cash Alternative Card -->
                    <div class="claim-method-card" data-method="cash">
                        <div class="method-icon-wrapper" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                            <i class="fas fa-money-bill-wave" style="font-size: 32px; color: white;"></i>
                        </div>
                        <h3 style="font-size: 1.3rem; font-weight: 700; color: #1F2937; margin: 16px 0 8px 0;">
                            Cash Alternative
                        </h3>
                        <p style="color: #6B7280; font-size: 0.95rem; line-height: 1.6; margin: 0 0 20px 0;">
                            Receive a cash payout of KSH 20,000 instead of funeral services. Subject to mutual agreement with SHENA.
                        </p>
                        
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #F59E0B;"></i>
                                <span>KSH 20,000 cash payout</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #F59E0B;"></i>
                                <span>Flexible funeral arrangements</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: #F59E0B;"></i>
                                <span>Direct financial support</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-info-circle" style="color: #6B7280;"></i>
                                <span style="color: #6B7280;">Requires detailed justification</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-info-circle" style="color: #6B7280;"></i>
                                <span style="color: #6B7280;">Subject to admin approval</span>
                            </div>
                        </div>
                        
                        <div class="method-badge" style="background: #FEF3C7; color: #D97706;">
                            <i class="fas fa-handshake"></i> By Agreement
                        </div>
                        
                        <button class="method-select-btn cash-alt" data-method="cash" data-bs-toggle="modal" data-bs-target="#submitClaimModal" <?php echo $isInMaturityPeriod ? 'disabled' : ''; ?>>
                            <i class="fas fa-arrow-right"></i> Request Cash Alternative
                        </button>
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border-left: 4px solid #3B82F6; padding: 20px; border-radius: 12px; margin-top: 24px;">
                    <div style="display: flex; gap: 15px;">
                        <i class="fas fa-info-circle" style="color: #2563EB; font-size: 20px; margin-top: 2px;"></i>
                        <div>
                            <h4 style="margin: 0 0 8px 0; color: #1E40AF; font-size: 1rem; font-weight: 600;">
                                Important Information
                            </h4>
                            <p style="margin: 0; color: #1E40AF; font-size: 0.9rem; line-height: 1.6;">
                                <strong>Standard Services</strong> are provided at no additional cost and are processed immediately. 
                                <strong>Cash Alternative</strong> requires a detailed reason and is subject to mutual agreement per Policy Section 12. 
                                Processing may take longer for cash alternative requests.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Track My Claims -->
            <div class="section-header">
                <h3>Track My Claims</h3>
                <div class="tabs">
                    <button class="tab active" data-tab="all">All Claims</button>
                    <button class="tab" data-tab="active">Active (<?php echo count($activeClaims); ?>)</button>
                    <button class="tab" data-tab="past">Past (<?php echo count($pastClaims); ?>)</button>
                </div>
            </div>
            
            <!-- Active Claims -->
            <div class="claims-section" data-section="active">
                <?php if (!empty($activeClaims)): ?>
                    <?php foreach ($activeClaims as $claim): ?>
                    <a href="/claims/view/<?php echo $claim['id']; ?>" class="claim-card">
                        <div class="claim-header">
                            <div class="claim-info">
                                <h4>Claim ID: <?php echo htmlspecialchars($claim['id']); ?></h4>
                                <h3><?php echo htmlspecialchars($claim['deceased_name'] ?? 'Unknown'); ?> (<?php echo htmlspecialchars($claim['relationship'] ?? 'N/A'); ?>)</h3>
                                <p>Date of Death: <?php echo htmlspecialchars($claim['date_of_death'] ?? 'N/A'); ?></p>
                                <p>Place of Death: <?php echo htmlspecialchars($claim['place_of_death'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="claim-meta">
                                <p>STATUS</p>
                                <span class="status-badge"><?php echo strtoupper(htmlspecialchars($claim['status'] ?? 'submitted')); ?></span>
                                <p style="margin-top: 10px; font-size: 0.8rem; color: #6B7280;">
                                    Submitted: <?php echo htmlspecialchars(date('M d, Y', strtotime($claim['created_at'] ?? 'now'))); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Next Step -->
                        <div class="next-step-card">
                            <i class="fas fa-info-circle"></i>
                            <div class="next-step-content">
                                <h4>Next Step:</h4>
                                <p>Your claim has been submitted and is under review. SHENA will contact you within 1-3 business days.</p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="claim-card" style="text-align: center; color: #6B7280;">
                        <p style="margin: 0;">No active claims at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Past Claims -->
            <div class="past-claims-section claims-section" data-section="past">
                <h3>PAST CLAIMS</h3>
                <?php if (!empty($pastClaims)): ?>
                    <?php foreach ($pastClaims as $claim): ?>
                    <a href="/claims/view/<?php echo $claim['id']; ?>" class="past-claim-item">
                        <div class="past-claim-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="past-claim-info">
                            <h4><?php echo htmlspecialchars($claim['deceased_name'] ?? 'Unknown'); ?> (<?php echo htmlspecialchars($claim['relationship'] ?? 'N/A'); ?>)</h4>
                            <p>Claim ID: <?php echo htmlspecialchars($claim['id']); ?> • Paid <?php echo htmlspecialchars($claim['paid_date'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="past-claim-amount">KES <?php echo number_format($claim['payout'] ?? 0, 2); ?></div>
                        <div class="past-claim-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="color: #6B7280;">No past claims to show.</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Documents Sidebar -->
        <div class="documents-sidebar">
            <h4>3 ESSENTIAL DOCUMENTS</h4>
            <div class="document-item">
                <div class="document-icon checked">
                    <i class="fas fa-check"></i>
                </div>
                <span>ID Copy (Deceased)</span>
            </div>
            <div class="document-item">
                <div class="document-icon unchecked"></div>
                <span>Chief's Letter</span>
            </div>
            <div class="document-item">
                <div class="document-icon unchecked"></div>
                <span>Mortuary Invoice</span>
            </div>
            <p class="document-note">These must be submitted for any claim to be processed</p>
        </div>
    </div>
</div>

<!-- Submit Claim Modal -->
<div class="modal fade" id="submitClaimModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="claimSubmissionForm" method="POST" action="/claims" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-medical"></i> Submit New Burial Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="modalCloseBtn"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Service-Based Claims:</strong> SHENA provides comprehensive funeral services including mortuary bills (max 14 days), body dressing, executive coffin, transportation, and equipment.
                    </div>
                    
                    <h6><i class="fas fa-user"></i> Deceased Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="deceased_name" class="form-control" placeholder="Enter full name" value="<?php echo getOldValue('deceased_name'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID/Birth Certificate Number <span class="text-danger">*</span></label>
                            <input type="text" name="deceased_id_number" class="form-control" placeholder="Enter ID number" value="<?php echo getOldValue('deceased_id_number'); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Beneficiary <span class="text-danger">*</span></label>
                            <select name="beneficiary_id" class="form-select" required <?php echo !$hasBeneficiaries ? 'disabled' : ''; ?>>
                                <option value="">Select beneficiary</option>
                                <?php foreach ($beneficiaries as $beneficiary): ?>
                                    <option value="<?php echo (int)$beneficiary['id']; ?>">
                                        <?php echo htmlspecialchars($beneficiary['full_name'] . ' (' . $beneficiary['relationship'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$hasBeneficiaries): ?>
                                <small class="text-muted">Add a beneficiary before submitting a claim.</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Death <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_death" class="form-control" value="<?php echo getOldValue('date_of_death'); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Place of Death <span class="text-danger">*</span></label>
                        <input type="text" name="place_of_death" class="form-control" placeholder="City, Hospital, or Location" value="<?php echo getOldValue('place_of_death'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cause of Death <span class="text-danger">*</span></label>
                        <textarea name="cause_of_death" class="form-control" rows="2" placeholder="Brief description of cause" required><?php echo getOldValue('cause_of_death'); ?></textarea>
                        <small class="text-muted">Excluded: self-medication, drug abuse, criminal acts, riots/war, hazardous activities</small>
                    </div>
                    
                    <h6><i class="fas fa-hospital"></i> Mortuary & Service Details</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mortuary Name <span class="text-danger">*</span></label>
                            <input type="text" name="mortuary_name" class="form-control" placeholder="Name of mortuary" value="<?php echo getOldValue('mortuary_name'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Days in Mortuary <span class="text-danger">*</span></label>
                            <input type="number" name="mortuary_days_count" class="form-control" min="0" max="14" placeholder="Max 14 days" value="<?php echo getOldValue('mortuary_days_count'); ?>" required>
                            <small class="text-muted">Maximum 14 days covered per policy</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mortuary Bill Amount (KES) <span class="text-danger">*</span></label>
                        <input type="number" name="mortuary_bill_amount" class="form-control" min="0" step="0.01" placeholder="0.00" value="<?php echo getOldValue('mortuary_bill_amount'); ?>" required>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6 class="mb-2"><i class="fas fa-exchange-alt"></i> Cash Alternative Option</h6>
                        <p class="mb-2 small">Per Policy Section 12: In exceptional circumstances, you may request a cash alternative of <strong>KSH 20,000</strong> instead of service delivery. Both parties must agree.</p>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="request_cash_alternative" id="requestCashAlternative" value="1" <?php echo isOldValueChecked('request_cash_alternative', '1'); ?>>
                            <label class="form-check-label" for="requestCashAlternative">
                                <strong>I request cash alternative (KSH 20,000)</strong>
                            </label>
                        </div>
                        <div id="cashAlternativeReasonField" style="display: none;">
                            <label class="form-label">Reason for Cash Alternative Request <span class="text-danger">*</span></label>
                            <textarea name="cash_alternative_reason" id="cashAlternativeReason" class="form-control" rows="3" placeholder="Please provide a detailed reason (min 50 characters)"><?php echo getOldValue('cash_alternative_reason'); ?></textarea>
                            <small class="text-muted">Your request will be reviewed by administration. Valid reasons include security concerns, logistical challenges, or other exceptional circumstances.</small>
                        </div>
                    </div>
                    
                    <script>
                    document.getElementById('requestCashAlternative').addEventListener('change', function() {
                        const reasonField = document.getElementById('cashAlternativeReasonField');
                        const reasonTextarea = document.getElementById('cashAlternativeReason');
                        if (this.checked) {
                            reasonField.style.display = 'block';
                            reasonTextarea.setAttribute('required', 'required');
                            reasonTextarea.setAttribute('minlength', '50');
                        } else {
                            reasonField.style.display = 'none';
                            reasonTextarea.removeAttribute('required');
                            reasonTextarea.removeAttribute('minlength');
                        }
                    });
                    </script>
                    
                    <h6><i class="fas fa-paperclip"></i> Supporting Documents <span class="badge bg-secondary">Can be added later</span></h6>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-exclamation-circle text-warning"></i> The following 3 documents are mandatory for claim processing
                    </p>
                    <div class="mb-3">
                        <label class="form-label">1. Copy of ID / Birth Certificate <span class="text-danger">*</span></label>
                        <input type="file" name="id_copy" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">2. Chief's Letter <span class="text-danger">*</span></label>
                        <input type="file" name="chief_letter" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">3. Mortuary Invoice <span class="text-danger">*</span></label>
                        <input type="file" name="mortuary_invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Death Certificate <span class="badge bg-secondary">Optional</span></label>
                        <input type="file" name="death_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitClaimBtn" <?php echo !$hasBeneficiaries ? 'disabled' : ''; ?>>
                        <span id="submitBtnText"><i class="fas fa-check-circle"></i> Submit Claim</span>
                        <span id="submitBtnLoading" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Claim method selection handling
let selectedClaimMethod = 'standard'; // default

document.querySelectorAll('.method-select-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        selectedClaimMethod = this.getAttribute('data-method');
        
        const modalTitle = document.querySelector('#submitClaimModal .modal-title');
        const cashAltCheckbox = document.getElementById('requestCashAlternative');
        const reasonField = document.getElementById('cashAlternativeReasonField');
        const reasonTextarea = document.getElementById('cashAlternativeReason');
        const formAlert = document.querySelector('#submitClaimModal .alert-warning');
        
        if (selectedClaimMethod === 'cash') {
            // Cash Alternative selected
            modalTitle.innerHTML = '<i class="fas fa-money-bill-wave"></i> Submit Claim - Cash Alternative Request';
            cashAltCheckbox.checked = true;
            reasonField.style.display = 'block';
            reasonTextarea.setAttribute('required', 'required');
            reasonTextarea.setAttribute('minlength', '50');
            formAlert.style.background = 'linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%)';
            formAlert.style.borderLeft = '4px solid #F59E0B';
            
            // Add helpful message
            const existingMsg = formAlert.querySelector('.cash-selected-msg');
            if (!existingMsg) {
                const msg = document.createElement('p');
                msg.className = 'cash-selected-msg small';
                msg.style.cssText = 'margin: 0; color: #92400E; font-weight: 600;';
                msg.innerHTML = '<i class="fas fa-info-circle"></i> You have selected Cash Alternative. Please provide a detailed reason below.';
                formAlert.insertBefore(msg, formAlert.firstChild);
            }
        } else {
            // Standard Services selected
            modalTitle.innerHTML = '<i class="fas fa-hands-helping"></i> Submit Claim - Standard Services';
            cashAltCheckbox.checked = false;
            reasonField.style.display = 'none';
            reasonTextarea.removeAttribute('required');
            reasonTextarea.removeAttribute('minlength');
            reasonTextarea.value = '';
            formAlert.style.background = '';
            formAlert.style.borderLeft = '';
            
            // Remove cash alternative message if exists
            const existingMsg = formAlert.querySelector('.cash-selected-msg');
            if (existingMsg) {
                existingMsg.remove();
            }
        }
    });
});

// Tab switching functionality
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        const selected = this.dataset.tab;
        document.querySelectorAll('.claims-section').forEach(section => {
            if (selected === 'all') {
                section.style.display = '';
                return;
            }

            section.style.display = section.dataset.section === selected ? '' : 'none';
        });
    });
});

// Claim submission form handling
(function() {
    const form = document.getElementById('claimSubmissionForm');
    const submitBtn = document.getElementById('submitClaimBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitBtnLoading = document.getElementById('submitBtnLoading');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    if (!form) return;
    
    let isSubmitting = false;
    
    form.addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
        
        // Prevent double submission
        if (isSubmitting) {
            console.log('Already submitting, preventing duplicate submission');
            e.preventDefault();
            return false;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            console.log('Form validation failed');
            e.preventDefault();
            e.stopPropagation();
            form.classList.add('was-validated');
            
            // Show validation error alert
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
            
            // Show alert
            ShenaApp.showNotification('Please fill in all required fields correctly.', 'warning');
            return false;
        }
        
        // Check for cash alternative validation
        const cashAltCheckbox = document.getElementById('requestCashAlternative');
        const cashAltReason = document.getElementById('cashAlternativeReason');
        if (cashAltCheckbox && cashAltCheckbox.checked) {
            if (!cashAltReason.value || cashAltReason.value.length < 50) {
                e.preventDefault();
                ShenaApp.showNotification('Please provide a detailed reason for cash alternative request (minimum 50 characters).', 'warning');
                cashAltReason.focus();
                return false;
            }
        }
        
        console.log('Form validation passed, submitting...');
        
        // Show loading state
        isSubmitting = true;
        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        modalCloseBtn.disabled = true;
        submitBtnText.style.display = 'none';
        submitBtnLoading.style.display = 'inline-block';
        
        // Allow form to submit naturally
        return true;
    });
    
    // Reset form when modal is hidden
    const modal = document.getElementById('submitClaimModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            if (!isSubmitting) {
                form.reset();
                form.classList.remove('was-validated');
                
                // Reset to standard services
                selectedClaimMethod = 'standard';
                const modalTitle = document.querySelector('#submitClaimModal .modal-title');
                const cashAltCheckbox = document.getElementById('requestCashAlternative');
                const reasonField = document.getElementById('cashAlternativeReasonField');
                const reasonTextarea = document.getElementById('cashAlternativeReason');
                const formAlert = document.querySelector('#submitClaimModal .alert-warning');
                
                modalTitle.innerHTML = '<i class="fas fa-file-medical"></i> Submit New Burial Claim';
                cashAltCheckbox.checked = false;
                reasonField.style.display = 'none';
                reasonTextarea.removeAttribute('required');
                reasonTextarea.removeAttribute('minlength');
                reasonTextarea.value = '';
                formAlert.style.background = '';
                formAlert.style.borderLeft = '';
                
                // Remove cash alternative message if exists
                const existingMsg = formAlert.querySelector('.cash-selected-msg');
                if (existingMsg) {
                    existingMsg.remove();
                }
            }
        });
    }
    
    // Auto-dismiss success alert after 10 seconds
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        setTimeout(function() {
            successAlert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            successAlert.style.opacity = '0';
            successAlert.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                successAlert.remove();
            }, 500);
        }, 10000);
    }
})();
</script>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
