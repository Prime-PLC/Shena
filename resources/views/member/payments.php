<?php
$page = 'payments';
include __DIR__ . '/../layouts/member-header.php';

$payments = $payments ?? [];
// Recalculate totals from real data only
$total_paid = array_sum(array_column(array_filter($payments, fn($p) => ($p['status'] ?? '') === 'completed'), 'amount'));
$pending_count = count(array_filter($payments, fn($p) => ($p['status'] ?? '') !== 'completed'));
?>

<style>
main {
    padding: 0 !important;
    margin: 0 !important;
    width: 100%;
    overflow-x: hidden;
}

.payments-container {
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

.main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 350px;
    gap: 24px;
    align-items: start;
    max-width: 100%;
}

@media (max-width: 1400px) {
    .main-grid {
        grid-template-columns: minmax(0, 1fr) 300px;
    }
}

@media (max-width: 1200px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar-right {
        max-width: 600px;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    .payments-container {
        padding: 20px 15px;
    }
}

.total-contributions-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 16px;
    padding: 32px;
    color: white;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.2);
}

.total-contributions-card::after {
    content: 'KES';
    position: absolute;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 120px;
    font-weight: 700;
    opacity: 0.05;
}

.total-contributions-card h4 {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin: 0 0 12px 0;
    color: rgba(255, 255, 255, 0.8);
    position: relative;
    z-index: 1;
}

.total-contributions-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 42px;
    font-weight: 700;
    margin: 0 0 12px 0;
    position: relative;
    z-index: 1;
}

.total-contributions-card p {
    font-size: 14px;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.total-contributions-card p i {
    font-size: 12px;
}

.membership-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.membership-card h4 {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #6B7280;
    margin: 0 0 12px 0;
}

.membership-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.membership-status h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #D1FAE5;
    color: #059669;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.status-badge i {
    width: 6px;
    height: 6px;
    background: #059669;
    border-radius: 50%;
}

.verification-icon {
    color: #7F20B0;
    font-size: 24px;
    margin-top: 12px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.section-header h3 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.section-controls {
    display: flex;
    gap: 12px;
    align-items: center;
}

.filter-btn, .year-selector {
    background: white;
    border: 1px solid #E5E7EB;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #4B5563;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.filter-btn:hover, .year-selector:hover {
    border-color: #7F20B0;
    color: #7F20B0;
}

.year-selector i {
    color: #7F20B0;
}

.payments-table {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    max-width: 100%;
}

.payments-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    max-width: 100%;
}

.payments-table table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
}

.payments-table thead {
    background: #F9FAFB;
}

.payments-table thead th {
    padding: 16px 20px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: #6B7280;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.payments-table tbody tr {
    border-bottom: 1px solid #F3F4F6;
    transition: background 0.2s;
}

.payments-table tbody tr:hover {
    background: #F9FAFB;
}

.payments-table tbody tr:last-child {
    border-bottom: none;
}

.payments-table tbody td {
    padding: 16px 20px;
    font-size: 14px;
    color: #1F2937;
    white-space: nowrap;
}

.payments-table tbody td:first-child {
    font-weight: 500;
    color: #6B7280;
}

@media (max-width: 768px) {
    .payments-table thead th,
    .payments-table tbody td {
        padding: 12px 16px;
        font-size: 13px;
    }
}

.payment-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.payment-status.success {
    background: #D1FAE5;
    color: #059669;
}

.payment-status.pending {
    background: #FEF3C7;
    color: #D97706;
}

.payment-status.failed {
    background: #FEE2E2;
    color: #DC2626;
}

/* Right Sidebar */
.sidebar-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.quick-pay-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 4px 16px rgba(127, 32, 176, 0.2);
    color: white;
}

.quick-pay-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.quick-pay-card h3 i {
    color: rgba(255, 255, 255, 0.9);
    font-size: 22px;
}

.paybill-info {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.paybill-info p {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.paybill-info h2 {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 700;
    color: white;
    margin: 0;
    letter-spacing: 2px;
}

.account-ref {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.account-ref p {
    font-size: 10px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 6px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.account-ref h3 {
    font-family: 'Manrope', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: white;
    margin: 0;
    letter-spacing: 1px;
}

.how-to-pay-btn {
    background: white;
    color: #7F20B0;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.how-to-pay-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    background: rgba(255, 255, 255, 0.95);
}

.how-to-pay-btn i {
    font-size: 15px;
}

.btn-make-payment {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-make-payment:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.statements-card {
    background: white;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #F3F4F6;
}

.statements-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.statements-card h3 i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.statements-card p {
    font-size: 14px;
    color: #6B7280;
    line-height: 1.7;
    margin: 0 0 24px 0;
}

.btn-download-statement {
    background: white;
    color: #6B7280;
    border: 1px solid #E5E7EB;
    padding: 10px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-download-statement:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
}

.download-btn {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
}

.download-btn i {
    font-size: 15px;
}

@media (max-width: 1024px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
    
    .payments-container {
        padding: 20px;
    }
}

/* Payment Modal Styling */
#paymentModal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

#paymentModal .modal-header {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    padding: 24px 30px;
    border-bottom: none;
    border-radius: 16px 16px 0 0;
}

#paymentModal .modal-header .modal-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: white;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

#paymentModal .modal-header .modal-title i {
    font-size: 20px;
}

#paymentModal .modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

#paymentModal .modal-header .btn-close:hover {
    opacity: 1;
}

#paymentModal .modal-body {
    padding: 30px;
}

#paymentModal .alert-info {
    background: #EFF6FF;
    border: 1px solid #DBEAFE;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #1E40AF;
}

#paymentModal .alert-info i {
    color: #3B82F6;
    font-size: 16px;
    flex-shrink: 0;
}

#paymentModal .payment-method-label {
    font-size: 14px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 12px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payment-method-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
}

.payment-method-tab {
    padding: 14px 20px;
    border: 2px solid #E5E7EB;
    background: white;
    color: #6B7280;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    border-radius: 8px;
}

.payment-method-tab.active {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border-color: #7F20B0;
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.2);
}

.payment-method-tab:not(.active):hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
}

.payment-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 12px;
}

.payment-section-title i {
    color: #7F20B0;
    font-size: 18px;
}

.payment-description {
    font-size: 0.85rem;
    color: #6B7280;
    margin-bottom: 25px;
}

#paymentModal .form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

#paymentModal .form-control,
#paymentModal .form-select {
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.2s;
}

#paymentModal .form-control:focus,
#paymentModal .form-select:focus {
    border-color: #7F20B0;
    box-shadow: 0 0 0 3px rgba(127, 32, 176, 0.1);
    outline: none;
}

#paymentModal .form-control::placeholder {
    color: #9CA3AF;
}

#paymentModal .text-muted {
    font-size: 12px;
    color: #6B7280;
    margin-top: 4px;
    display: block;
}

#paymentModal .alert-warning {
    background: #FEF3C7;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 12px 16px;
    margin: 16px 0;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

#paymentModal .alert-warning i {
    color: #F59E0B;
    font-size: 16px;
    margin-top: 2px;
}

.btn-send-payment {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-send-payment:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.btn-send-payment:disabled {
    background: #D1D5DB;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

#paymentModal .modal-footer {
    padding: 20px 30px;
    border-top: 1px solid #E5E7EB;
    border-radius: 0 0 16px 16px;
    background: #F9FAFB;
}

#paymentModal .modal-footer .btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}

#paymentModal .modal-footer .btn-secondary {
    background: white;
    color: #6B7280;
    border: 1px solid #E5E7EB;
}

#paymentModal .modal-footer .btn-secondary:hover {
    background: #F3F4F6;
    border-color: #D1D5DB;
}

.paybill-instructions {
    background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
    border: 2px solid #BAE6FD;
    border-radius: 12px;
    padding: 24px;
    margin: 20px 0;
}

.paybill-detail {
    margin-bottom: 20px;
}

.paybill-detail:last-child {
    margin-bottom: 0;
}

.paybill-detail label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.paybill-value {
    font-size: 24px;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
}

.paybill-value.paybill-number {
    color: #2563EB;
}

.paybill-value.account-number {
    color: #059669;
}

.paybill-value.amount-value {
    color: #DC2626;
}

.payment-steps {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    padding: 24px;
    margin: 20px 0;
}

.payment-steps h6 {
    font-size: 16px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.payment-steps h6 i {
    color: #7F20B0;
}

.payment-steps ol {
    padding-left: 24px;
    margin: 0;
}

.payment-steps ol li {
    font-size: 14px;
    color: #4B5563;
    margin-bottom: 12px;
    line-height: 1.7;
    padding-left: 8px;
}

.payment-steps ol li:last-child {
    margin-bottom: 0;
}

.payment-steps ol li strong {
    color: #1F2937;
    font-weight: 600;
}

.payment-steps .highlight-blue {
    color: #2563EB;
    background: #EFF6FF;
    padding: 2px 8px;
    border-radius: 4px;
}

.payment-steps .highlight-green {
    color: #059669;
    background: #ECFDF5;
    padding: 2px 8px;
    border-radius: 4px;
}

.payment-steps .highlight-orange {
    color: #DC2626;
    background: #FEF2F2;
    padding: 2px 8px;
    border-radius: 4px;
}

.manual-payment-note {
    background: #FEF3C7;
    border-left: 4px solid #F59E0B;
    border-radius: 8px;
    padding: 16px;
    margin-top: 20px;
    display: flex;
    gap: 12px;
}

.manual-payment-note i {
    color: #F59E0B;
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.manual-payment-note p {
    margin: 0;
    font-size: 13px;
    color: #78350F;
    line-height: 1.6;
}

.manual-payment-note strong {
    color: #92400E;
}

/* Transaction Details Modal Styles */
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #6B7280;
    font-size: 14px;
}

.detail-value {
    font-size: 14px;
    color: #1F2937;
}

.transaction-details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.member-info-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<div class="payments-container">
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-warning">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <div class="page-header">
        <h1>Member Portal</h1>
        <p>Contribution History Dashboard</p>
        <div style="margin-top: 14px; display: inline-flex; align-items: center; gap: 8px; background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; border-radius: 999px; padding: 8px 14px; font-weight: 700; font-size: 0.9rem;">
            <i class="fas fa-wallet"></i>
            Your Monthly Contribution: KES <?php echo number_format((float)($member['monthly_contribution'] ?? 0), 2); ?>
        </div>
    </div>

    <div class="main-grid">
        <div>
            <!-- Total Contributions Card -->
            <div class="total-contributions-card">
                <h4>TOTAL CONTRIBUTIONS <?php echo !empty($selected_year) ? htmlspecialchars($selected_year) : date('Y'); ?></h4>
                <h2>KES <?php echo number_format($total_paid, 2); ?></h2>
                <p><i class="fas fa-arrow-up"></i> 12% increase from 2022</p>
            </div>

            <!-- Contribution Logs -->
            <div class="section-header">
                <h3>Contribution Logs</h3>
                <form class="section-controls" method="GET" action="/payments">
                    <label class="visually-hidden" for="statusFilter">Status</label>
                    <select class="filter-btn" name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="completed" <?php echo ($selected_status ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo ($selected_status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="failed" <?php echo ($selected_status ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="cancelled" <?php echo ($selected_status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <label class="visually-hidden" for="yearFilter">Year</label>
                    <select class="year-selector" name="year" id="yearFilter">
                        <option value="">All Years</option>
                        <?php foreach (($available_years ?? []) as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($selected_year ?? '') == $year ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="filter-btn" type="submit">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </form>
            </div>

            <!-- Payment Recovery Helper -->
            <div class="alert" style="background:#fff8e1; border:1px solid #f59e0b; border-radius:8px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:flex-start; gap:12px;">
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b; font-size:18px; margin-top:2px; flex-shrink:0;"></i>
                <div style="flex:1;">
                    <strong style="color:#92400e;">Made a manual M-Pesa payment not showing here?</strong>
                    <p style="margin:4px 0 8px; color:#78350f; font-size:13px;">If you paid via Paybill but it's not reflected below, tap the button to verify using your M-Pesa confirmation code.</p>
                    <button type="button" class="btn btn-sm" style="background:#f59e0b; color:#fff; border:none; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600;" onclick="document.getElementById('paymentRecoveryForm').style.display = document.getElementById('paymentRecoveryForm').style.display === 'none' ? 'block' : 'none';">
                        <i class="fas fa-search"></i> Verify My Payment
                    </button>
                    <div id="paymentRecoveryForm" style="display:none; margin-top:12px; padding:12px; background:#fffdf0; border-radius:6px; border:1px solid #fde68a;">
                        <form method="POST" action="/payments/verify-transaction">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                            <input type="hidden" name="phone_number" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>">
                            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                                <div>
                                    <label style="font-size:12px; font-weight:600; color:#78350f; display:block; margin-bottom:4px;">M-Pesa Confirmation Code</label>
                                    <input type="text" name="transaction_code" class="form-control form-control-sm" placeholder="e.g. RGH3XK02AB" style="text-transform:uppercase; width:200px;" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-success" style="font-size:13px; padding:6px 16px; font-weight:600;">
                                    <i class="fas fa-check"></i> Check &amp; Link
                                </button>
                            </div>
                            <p style="font-size:11px; color:#92400e; margin-top:6px; margin-bottom:0;">The code is the 10-character code in your M-Pesa confirmation SMS (e.g. <em>RGH3XK02AB</em>).</p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="payments-table">
                <div class="payments-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ref Number</th>
                                <th>Amount</th>
                                <th>Period</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                        <?php
                            $txnRef = $payment['mpesa_receipt_number'] ?? $payment['transaction_reference'] ?? $payment['transaction_id'] ?? '—';
                            $payDate = $payment['created_at'] ?? $payment['payment_date'] ?? null;
                            $period = $payment['period'] ?? ($payDate ? date('F Y', strtotime($payDate)) : '—');
                        ?>
                        <tr class="payment-row" data-payment='<?php echo htmlspecialchars(json_encode($payment), ENT_QUOTES); ?>' style="cursor: pointer;">
                            <td><?php echo $payDate ? date('M d, Y', strtotime($payDate)) : '—'; ?></td>
                            <td><?php echo htmlspecialchars($txnRef); ?></td>
                            <td>KES <?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($period); ?></td>
                            <td>
                                <span class="payment-status <?php echo $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'failed' ? 'failed' : 'pending'); ?>">
                                    <?php echo strtoupper($payment['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:32px; color:#6B7280;">No contribution records yet. Make your first payment to get started.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="sidebar-right">
            <!-- Membership Standing -->
            <?php
                $mbrStatus = strtoupper($member['status'] ?? 'inactive');
                $mbrStatusClass = ($member['status'] ?? '') === 'active' ? 'check-circle' : 'exclamation-circle';
                $mbrStandingLabel = ($member['status'] ?? '') === 'active' ? 'Good Standing' : ucfirst($member['status'] ?? 'inactive');
                $mbrStatusColor = ($member['status'] ?? '') === 'active' ? '' : 'color:#F59E0B;';
            ?>
            <div class="membership-card">
                <h4>MEMBERSHIP STANDING</h4>
                <div class="membership-status">
                    <h2 style="<?php echo $mbrStatusColor; ?>"><?php echo $mbrStatus; ?></h2>
                    <div class="verification-icon">
                        <i class="fas fa-<?php echo $mbrStatusClass; ?>" style="<?php echo $mbrStatusColor; ?>"></i>
                    </div>
                </div>
                <div class="status-badge">
                    <i></i>
                    <span><?php echo $mbrStandingLabel; ?></span>
                </div>
            </div>

            <!-- Quick Pay -->
            <div class="quick-pay-card">
                <h3><i class="fas fa-mobile-alt"></i> Quick Pay</h3>
                
                <div class="paybill-info">
                    <p>M-PESA PAYBILL</p>
                    <h2>4163987</h2>
                </div>

                <div class="account-ref">
                    <p>Your Account Reference (National ID)</p>
                    <h3><?php echo htmlspecialchars($member['id_number'] ?? $member['national_id'] ?? ''); ?></h3>
                </div>

                <button class="how-to-pay-btn" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-bolt"></i> Pay Now
                </button>
            </div>

            <!-- Statements -->
            <div class="statements-card">
                <h3><i class="fas fa-file-invoice"></i> Statements</h3>
                <p>Download a comprehensive record of all your contributions and transactions for your records or official documentation.</p>
                <?php
                    $statementParams = array_filter([
                        'status' => $selected_status ?? '',
                        'year' => $selected_year ?? ''
                    ]);
                    $statementUrl = '/member/payments/export';
                    if (!empty($statementParams)) {
                        $statementUrl .= '?' . http_build_query($statementParams);
                    }
                ?>
                <button class="download-btn" onclick="window.location.href='<?php echo $statementUrl; ?>'">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <button class="download-btn" id="exportPdfBtn" type="button">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Choose your preferred payment method</span>
                </div>
                
                <!-- Payment Method Selection -->
                <label class="payment-method-label">Payment Method:</label>
                <div class="payment-method-tabs">
                    <button type="button" class="payment-method-tab active" data-method="stk">
                        <i class="fas fa-mobile-alt"></i> STK Push
                    </button>
                    <button type="button" class="payment-method-tab" data-method="manual">
                        <i class="fas fa-hand-holding-usd"></i> Manual Paybill
                    </button>
                </div>
                
                <!-- STK Push Section -->
                <div id="stkPushSection">
                    <h6 class="payment-section-title">
                        <i class="fas fa-mobile-alt"></i>
                        Pay via M-Pesa STK Push
                    </h6>
                    <p class="payment-description">Enter your M-Pesa number to receive a payment prompt on your phone.</p>
                    
                    <form id="stkPushForm">
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" 
                                   placeholder="07XXXXXXXX or 2547XXXXXXXX" required
                                   value="<?php echo $member['phone'] ?? ''; ?>">
                            <small class="text-muted">Enter your M-Pesa registered phone number</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" id="amount" 
                                   value="<?php echo $member['monthly_contribution'] ?? 500; ?>" 
                                   min="1" step="0.01" required readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Type</label>
                            <select class="form-select" id="paymentType">
                                <option value="monthly" selected>Monthly Contribution</option>
                                <option value="registration">Registration Fee</option>
                                <option value="reactivation">Reactivation Fee</option>
                            </select>
                        </div>
                        
                        <div class="alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>You will receive a payment prompt on your phone. Enter your M-Pesa PIN to complete the payment.</span>
                        </div>
                        
                        <button type="submit" class="btn-send-payment" id="initiateSTKBtn">
                            <i class="fas fa-paper-plane"></i> Send Payment Request
                        </button>
                        <button type="button" class="btn btn-outline-primary w-100 mt-2" id="retrySTKBtn" style="display:none;">
                            <i class="fas fa-redo-alt"></i> Retry STK Push
                        </button>
                    </form>
                    
                    <div id="stkPushStatus" class="mt-3" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-spinner fa-spin"></i> <span id="statusMessage">Processing payment...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Manual Paybill Section -->
                <div id="manualPaybillSection" style="display: none;">
                    <h6 class="payment-section-title">
                        <i class="fas fa-hand-holding-usd"></i>
                        Manual M-Pesa Paybill Payment
                    </h6>
                    <p class="payment-description">Follow these simple steps to pay via M-Pesa Paybill from your phone.</p>
                    
                    <div class="paybill-instructions">
                        <div class="paybill-detail">
                            <label>M-PESA Paybill Number</label>
                            <div class="paybill-value paybill-number">4163987</div>
                        </div>
                        <div class="paybill-detail">
                            <label>Account Number (Your National ID)</label>
                            <div class="paybill-value account-number"><?php echo htmlspecialchars($member['id_number'] ?? $member['national_id'] ?? ''); ?></div>
                        </div>
                        <div class="paybill-detail">
                            <label>Amount to Pay</label>
                            <div class="paybill-value amount-value">KES <?php echo number_format($member['monthly_contribution'] ?? 500, 2); ?></div>
                        </div>
                    </div>
                    
                    <div class="payment-steps">
                        <h6><i class="fas fa-list-ol"></i> How to Pay</h6>
                        <ol>
                            <li>Open <strong>M-Pesa</strong> on your phone</li>
                            <li>Select <strong>Lipa na M-Pesa</strong></li>
                            <li>Select <strong>Pay Bill</strong></li>
                            <li>Enter Business Number: <strong class="highlight-blue">4163987</strong></li>
                            <li>Enter Account Number: <strong class="highlight-green"><?php echo htmlspecialchars($member['id_number'] ?? $member['national_id'] ?? ''); ?></strong></li>
                            <li>Enter Amount: <strong class="highlight-orange">KES <?php echo number_format($member['monthly_contribution'] ?? 500, 2); ?></strong></li>
                            <li>Enter your M-Pesa PIN and confirm</li>
                            <li>You will receive an SMS confirmation from M-Pesa</li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-success" style="margin-top: 20px;">
                        <i class="fas fa-check-circle"></i>
                        <span>Your payment will be automatically recorded and reflected in your account within 1-5 minutes.</span>
                    </div>
                    
                    <div class="manual-payment-note">
                        <i class="fas fa-info-circle"></i>
                        <p><strong>Important:</strong> Always use your <strong>National ID number</strong> (<strong><?php echo htmlspecialchars($member['id_number'] ?? $member['national_id'] ?? ''); ?></strong>) as the account number to ensure your payment is credited to your account.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);">
            <div class="modal-header" style="background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: white; padding: 24px 30px; border-bottom: none; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: white; margin: 0; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-receipt" style="font-size: 20px;"></i> Transaction Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div class="row">
                    <div class="col-md-8">
                        <div class="transaction-details" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
                            <div class="detail-row mb-3" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Transaction Date:</label>
                                <span id="detail-date" class="detail-value" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                            <div class="detail-row mb-3" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Reference Number:</label>
                                <span id="detail-ref" class="detail-value" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                            <div class="detail-row mb-3" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Amount:</label>
                                <span id="detail-amount" class="detail-value fw-bold text-success" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                            <div class="detail-row mb-3" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Period:</label>
                                <span id="detail-period" class="detail-value" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                            <div class="detail-row mb-3" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Status:</label>
                                <span id="detail-status" class="detail-value" style="font-size: 14px; color: #1F2937;">
                                    <span id="status-badge" class="payment-status"></span>
                                </span>
                            </div>
                            <div class="detail-row mb-3" id="payment-method-row" style="display: none; display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Payment Method:</label>
                                <span id="detail-method" class="detail-value" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                            <div class="detail-row mb-3" id="transaction-id-row" style="display: none; display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">M-Pesa Receipt:</label>
                                <span id="detail-transaction-id" class="detail-value" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                            <div class="detail-row" id="notes-row" style="display: none; display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                                <label class="detail-label" style="font-weight: 600; color: #6B7280; font-size: 14px;">Notes:</label>
                                <span id="detail-notes" class="detail-value" style="font-size: 14px; color: #1F2937;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="member-info-card p-3 border rounded" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <h6 class="text-muted mb-2" style="font-size: 14px; font-weight: 600;">Member Information</h6>
                                <p class="mb-1" style="font-weight: 600; color: #1F2937;"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></p>
                                <p class="mb-1 text-muted" style="font-size: 13px;">ID: <?php echo htmlspecialchars($member['member_number'] ?? 'N/A'); ?></p>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Phone: <?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 20px 30px; border-top: 1px solid #E5E7EB; border-radius: 0 0 16px 16px; background: #F9FAFB;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="downloadReceiptBtn" style="background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-download"></i> Download Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Verify Transaction Modal -->
<div class="modal fade" id="verifyTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Verify M-Pesa Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> If you completed a payment but it shows as pending or failed, verify it here using your M-Pesa transaction code.
                </div>

                <form id="verifyTransactionForm">
                    <div class="mb-3">
                        <label class="form-label">M-Pesa Transaction Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="transactionCode"
                               placeholder="e.g., RCH12ABC34" required maxlength="15">
                        <small class="text-muted">Check your M-Pesa message for the transaction code</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number Used <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="verifyPhoneNumber"
                               placeholder="07XXXXXXXX or 2547XXXXXXXX" required
                               value="<?php echo $member['phone'] ?? ''; ?>">
                        <small class="text-muted">Enter the phone number you paid from</small>
                    </div>

                    <div class="alert alert-warning small">
                        <strong>Note:</strong> This will search for your pending or failed payments within the last 7 days that match your transaction code and phone number.
                    </div>

                    <button type="submit" class="btn btn-warning w-100" id="verifyBtn">
                        <i class="fas fa-search"></i> Verify Transaction
                    </button>
                </form>

                <div id="verifyStatus" class="mt-3" style="display: none;">
                    <div class="alert">
                        <span id="verifyMessage"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const PAYMENT_AMOUNTS = {
    monthly: <?php echo json_encode((float)($member['monthly_contribution'] ?? 0)); ?>,
    registration: <?php echo json_encode(defined('REGISTRATION_FEE') ? (float)REGISTRATION_FEE : 200.0); ?>,
    reactivation: <?php echo json_encode(defined('REACTIVATION_FEE') ? (float)REACTIVATION_FEE : 100.0); ?>
};

function formatPaymentAmount(amount) {
    return 'KES ' + Number(amount || 0).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function syncPaymentAmount() {
    const paymentTypeSelect = document.getElementById('paymentType');
    const amountInput = document.getElementById('amount');
    if (!paymentTypeSelect || !amountInput) {
        return;
    }

    const paymentType = paymentTypeSelect.value || 'monthly';
    const amount = PAYMENT_AMOUNTS[paymentType] ?? PAYMENT_AMOUNTS.monthly;
    amountInput.value = amount;

    document.querySelectorAll('#paymentModal .amount-value').forEach(function (el) {
        el.textContent = formatPaymentAmount(amount);
    });
    document.querySelectorAll('#paymentModal .highlight-orange').forEach(function (el) {
        el.textContent = formatPaymentAmount(amount);
    });
}

const paymentTypeSelect = document.getElementById('paymentType');
if (paymentTypeSelect) {
    paymentTypeSelect.addEventListener('change', syncPaymentAmount);
    syncPaymentAmount();
}

// Payment method tab toggle
document.querySelectorAll('.payment-method-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.payment-method-tab').forEach(t => t.classList.remove('active'));
        
        // Add active class to clicked tab
        this.classList.add('active');
        
        // Show/hide sections based on data-method
        const method = this.getAttribute('data-method');
        if (method === 'stk') {
            document.getElementById('stkPushSection').style.display = 'block';
            document.getElementById('manualPaybillSection').style.display = 'none';
        } else if (method === 'manual') {
            document.getElementById('stkPushSection').style.display = 'none';
            document.getElementById('manualPaybillSection').style.display = 'block';
        }
    });
});

// Legacy radio button support (if exists)
document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'stk') {
            document.getElementById('stkPushSection').style.display = 'block';
            document.getElementById('manualPaybillSection').style.display = 'none';
        } else {
            document.getElementById('stkPushSection').style.display = 'none';
            document.getElementById('manualPaybillSection').style.display = 'block';
        }
    });
});

// STK Push Form Submission
document.getElementById('stkPushForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    await initiateMemberStkPush();
});

const retrySTKBtn = document.getElementById('retrySTKBtn');
retrySTKBtn?.addEventListener('click', async function() {
    await initiateMemberStkPush();
});

function showStkRetry(message) {
    const btn = document.getElementById('initiateSTKBtn');
    const statusDiv = document.getElementById('stkPushStatus');
    const statusMsg = document.getElementById('statusMessage');
    const retryBtn = document.getElementById('retrySTKBtn');

    statusDiv.style.display = 'block';
    statusDiv.querySelector('.alert').className = 'alert alert-danger';
    statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> ' + message;

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Payment Request';
    if (retryBtn) {
        retryBtn.style.display = '';
    }
}

async function initiateMemberStkPush() {
    const btn = document.getElementById('initiateSTKBtn');
    const statusDiv = document.getElementById('stkPushStatus');
    const statusMsg = document.getElementById('statusMessage');
    const retryBtn = document.getElementById('retrySTKBtn');
    
    const phoneNumber = document.getElementById('phoneNumber').value;
    const amount = document.getElementById('amount').value;
    const paymentType = document.getElementById('paymentType').value;
    
    // Validate phone number
    if (!phoneNumber || phoneNumber.length < 9) {
        ShenaApp.showNotification('Please enter a valid phone number', 'warning');
        return;
    }
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    statusDiv.style.display = 'none';
    if (retryBtn) {
        retryBtn.style.display = 'none';
    }
    
    try {
        const response = await fetch('/payment/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                member_id: <?php echo $member['id'] ?? 0; ?>,
                phone_number: phoneNumber,
                amount: amount,
                payment_type: paymentType
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            statusDiv.style.display = 'block';
            statusDiv.querySelector('.alert').className = 'alert alert-success';
            statusMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            
            // Poll for payment status
            if (data.checkout_request_id) {
                pollPaymentStatus(data.checkout_request_id);
            }
            
            // Reset form after 3 seconds
            setTimeout(() => {
                statusDiv.style.display = 'none';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Payment Request';
            }, 3000);
        } else {
            showStkRetry(data.error || data.message || 'Payment initiation failed');
        }
    } catch (error) {
        console.error('Payment error:', error);
        showStkRetry('Network error. Please try again.');
    }
}

// Poll payment status
function pollPaymentStatus(checkoutRequestId) {
    let attempts = 0;
    const maxAttempts = 30; // Poll for 30 seconds
    
    const interval = setInterval(async () => {
        attempts++;
        
        if (attempts > maxAttempts) {
            clearInterval(interval);
            document.getElementById('statusMessage').innerHTML = 
                '<i class="fas fa-info-circle"></i> Please check your payment history for status.';
            return;
        }
        
        try {
            const response = await fetch(`/payment/status?checkout_request_id=${checkoutRequestId}`);
            const data = await response.json();
            
            if (data.success && data.status) {
                if (String(data.status.ResultCode) === '0') {
                    // Payment successful
                    clearInterval(interval);
                    document.getElementById('statusMessage').innerHTML = 
                        '<i class="fas fa-check-circle"></i> Payment completed successfully!';
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else if (data.status.ResultCode !== undefined) {
                    // Payment failed
                    clearInterval(interval);
                    showStkRetry(data.status.ResultDesc || 'Payment failed. Please retry.');
                }
            }
        } catch (error) {
            console.error('Status check error:', error);
        }
    }, 1000);
}

// Transaction Details Modal
document.addEventListener('DOMContentLoaded', function() {
    const transactionModal = new bootstrap.Modal(document.getElementById('transactionDetailsModal'));

    // Handle payment row clicks
    document.querySelectorAll('.payment-row').forEach(row => {
        row.addEventListener('click', function() {
            const paymentData = JSON.parse(this.getAttribute('data-payment'));

            // Populate modal with payment data
            document.getElementById('detail-date').textContent = new Date(paymentData.payment_date || paymentData.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('detail-ref').textContent = paymentData.transaction_id || paymentData.mpesa_receipt_number || paymentData.transaction_reference || 'N/A';
            document.getElementById('detail-amount').textContent = 'KES ' + parseFloat(paymentData.amount || 0).toLocaleString('en-KE', { minimumFractionDigits: 2 });
            document.getElementById('detail-period').textContent = paymentData.period || 'N/A';

            // Status badge
            const statusBadge = document.getElementById('status-badge');
            statusBadge.textContent = (paymentData.status || 'pending').toUpperCase();
            statusBadge.className = 'payment-status ' + (paymentData.status === 'completed' ? 'success' : (paymentData.status === 'failed' ? 'failed' : 'pending'));

            // Optional fields
            const paymentMethodRow = document.getElementById('payment-method-row');
            const transactionIdRow = document.getElementById('transaction-id-row');
            const notesRow = document.getElementById('notes-row');

            if (paymentData.payment_method) {
                document.getElementById('detail-method').textContent = paymentData.payment_method;
                paymentMethodRow.style.display = 'flex';
            } else {
                paymentMethodRow.style.display = 'none';
            }

            if (paymentData.mpesa_receipt_number && paymentData.mpesa_receipt_number !== paymentData.transaction_id) {
                document.getElementById('detail-transaction-id').textContent = paymentData.mpesa_receipt_number;
                transactionIdRow.style.display = 'flex';
            } else {
                transactionIdRow.style.display = 'none';
            }

            if (paymentData.notes) {
                document.getElementById('detail-notes').textContent = paymentData.notes;
                notesRow.style.display = 'flex';
            } else {
                notesRow.style.display = 'none';
            }

            // Store payment ID for receipt download
            document.getElementById('downloadReceiptBtn').setAttribute('data-payment-id', paymentData.id);

            // Show modal
            transactionModal.show();
        });
    });

    // Handle receipt download
    document.getElementById('downloadReceiptBtn').addEventListener('click', function() {
        const paymentId = this.getAttribute('data-payment-id');
        if (paymentId) {
            window.open('/member/payments/export-receipt?payment_id=' + paymentId, '_blank');
        }
    });
});

// Transaction Verification Form
document.getElementById('verifyTransactionForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('verifyBtn');
    const statusDiv = document.getElementById('verifyStatus');
    const statusMsg = document.getElementById('verifyMessage');

    const transactionCode = document.getElementById('transactionCode').value.trim();
    const phoneNumber = document.getElementById('verifyPhoneNumber').value.trim();

    // Validate inputs
    if (!transactionCode || transactionCode.length < 8) {
        ShenaApp.showNotification('Please enter a valid M-Pesa transaction code', 'warning');
        return;
    }

    if (!phoneNumber || phoneNumber.length < 9) {
        ShenaApp.showNotification('Please enter a valid phone number', 'warning');
        return;
    }

    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    statusDiv.style.display = 'none';

    try {
        const formData = new FormData();
        formData.append('csrf_token', <?php echo json_encode($csrf_token ?? ''); ?>);
        formData.append('transaction_code', transactionCode);
        formData.append('phone_number', phoneNumber);

        const response = await fetch('/payments/verify-transaction', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        const data = await response.json();

        statusDiv.style.display = 'block';

        if (data.success) {
            statusDiv.querySelector('.alert').className = 'alert alert-success';
            statusMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;

            // Reload page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            statusDiv.querySelector('.alert').className = 'alert alert-danger';
            statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> ' + (data.message || 'Verification failed');

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search"></i> Verify Transaction';
        }
    } catch (error) {
        console.error('Verification error:', error);
        statusDiv.style.display = 'block';
        statusDiv.querySelector('.alert').className = 'alert alert-danger';
        statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> Network error. Please try again.';

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search"></i> Verify Transaction';
    }
});
</script>


<!-- html2pdf.js CDN for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
// Export payments table as PDF
document.getElementById('exportPdfBtn')?.addEventListener('click', function() {
    const table = document.querySelector('.payments-table-wrapper');
    if (!table) {
        ShenaApp.showNotification('Payments table not found.', 'error');
        return;
    }
    const opt = {
        margin:       0.3,
        filename:     'payments-statement.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(table).save();
});
</script>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
