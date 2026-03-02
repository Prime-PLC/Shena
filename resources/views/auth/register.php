<?php 
// Helper function to get old input value
function old($field, $default = '') {
    return $_SESSION['old_input'][$field] ?? $default;
}

// Helper function to check if field has error
function hasError($field) {
    return isset($_SESSION['error_field']) && $_SESSION['error_field'] === $field;
}

// Get current step from session or default to 1
$currentStep = $_SESSION['registration_step'] ?? 1;

include VIEWS_PATH . '/layouts/header.php'; 
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Manrope', sans-serif;
    }
    
    body {
        background: #F7F7F9;
    }
    
    .playfair {
        font-family: 'Playfair Display', serif;
    }
    
    .registration-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .registration-header h1 {
        font-family: 'Playfair Display', serif;
        color: #7F3D9E;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .registration-header p {
        color: #6B7280;
        font-size: 1rem;
    }
    
    .progress-section {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .step-indicator {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        position: relative;
    }
    
    .step-indicator::before {
        content: '';
        position: absolute;
        top: 30px;
        left: 0;
        right: 0;
        height: 3px;
        background: #E5E7EB;
        z-index: 0;
    }
    
    .step-indicator .progress-bar-fill {
        position: absolute;
        top: 30px;
        left: 0;
        height: 3px;
        background: #7F3D9E;
        z-index: 1;
        transition: width 0.3s ease;
    }
    
    .step-item {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 2;
    }
    
    .step-number {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: white;
        border: 3px solid #E5E7EB;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        color: #9CA3AF;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .step-item.active .step-number,
    .step-item.completed .step-number {
        background: #7F3D9E;
        border-color: #7F3D9E;
        color: white;
    }
    
    .step-label {
        font-size: 0.9rem;
        color: #6B7280;
        font-weight: 500;
    }
    
    .step-item.active .step-label {
        color: #7F3D9E;
        font-weight: 700;
    }
    
    .main-content {
        display: flex;
        gap: 30px;
    }
    
    .form-section {
        flex: 1;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .payment-sidebar {
        width: 320px;
        flex-shrink: 0;
    }
    
    .payment-card {
        background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(127, 61, 158, 0.15);
        text-align: center;
    }
    
    .payment-icon {
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .payment-icon i {
        font-size: 1.8rem;
        color: #7F3D9E;
    }
    
    .payment-card h3 {
        font-family: 'Playfair Display', serif;
        color: #7F3D9E;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    .payment-detail {
        background: white;
        padding: 15px;
        border-radius: 15px;
        margin-bottom: 15px;
    }
    
    .payment-detail-label {
        font-size: 0.75rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    
    .payment-detail-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #7F3D9E;
    }
    
    .qr-code {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-top: 20px;
    }
    
    .qr-code img {
        width: 120px;
        height: 120px;
    }
    
    .qr-note {
        font-size: 0.75rem;
        color: #7F3D9E;
        font-style: italic;
        margin-top: 10px;
    }
    
    .section-header {
        background: #7F3D9E;
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .section-header i {
        font-size: 1.5rem;
    }
    
    .section-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .section-header .required-badge {
        margin-left: auto;
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-label {
        color: #1A1A1A;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-size: 0.75rem;
    }
    
    .form-control, .form-select {
        border: 2px solid #E5E7EB;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 40px;
    }
    
    .btn-save {
        background: white;
        border: 2px solid #E5E7EB;
        color: #6B7280;
        padding: 14px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-save:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }
    
    .btn-continue {
        flex: 1;
        background: #7F3D9E;
        border: none;
        color: white;
        padding: 14px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-continue:hover {
        background: #6B3587;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(127, 61, 158, 0.3);
    }
    
    .package-card {
        border: 3px solid #E5E7EB;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .package-card:hover {
        border-color: #7F3D9E;
        box-shadow: 0 6px 20px rgba(127, 61, 158, 0.15);
    }
    
    .package-card.selected {
        border-color: #7F3D9E;
        background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 30%);
        box-shadow: 0 6px 20px rgba(127, 61, 158, 0.2);
    }
    
    .package-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #7F3D9E 0%, #9C27B0 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .package-icon i {
        color: white;
        font-size: 1.5rem;
    }
    
    .package-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: #7F3D9E;
        margin-bottom: 10px;
    }
    
    .package-price {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #E5E7EB;
    }
    
    .package-price-label {
        color: #6B7280;
        font-size: 0.9rem;
    }
    
    .package-price-value {
        font-weight: 700;
        color: #1A1A1A;
        font-size: 1rem;
    }
    
    .btn-select {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 2px solid #7F3D9E;
        background: white;
        color: #7F3D9E;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-top: 15px;
    }
    
    .package-card.selected .btn-select {
        background: #7F3D9E;
        color: white;
    }
    
    .info-note {
        background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
        padding: 20px;
        border-radius: 15px;
        border-left: 4px solid #7F3D9E;
        margin: 30px 0;
    }
    
    .info-note i {
        color: #7F3D9E;
        margin-right: 10px;
    }
    
    .help-section {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        padding: 20px;
        border-radius: 15px;
        margin-top: 20px;
        text-align: center;
    }
    
    .help-section h4 {
        font-size: 0.85rem;
        color: #92400E;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }
    
    .help-contact {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #78350F;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    @media (max-width: 992px) {
        .main-content {
            flex-direction: column-reverse;
        }
        
        .payment-sidebar {
            width: 100%;
        }
    }
    
    .dependents-section {
        background: #7F3D9E;
        color: white;
        padding: 20px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
    }
    
    .dependents-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .dependents-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .dependents-subtitle {
        font-size: 0.75rem;
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .dependent-form-row {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 15px;
    }
    
    .btn-add-dependent {
        background: rgba(255,255,255,0.2);
        border: 2px dashed rgba(255,255,255,0.4);
        color: white;
        padding: 12px;
        border-radius: 10px;
        width: 100%;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
    }
    
    .btn-add-dependent:hover {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.6);
    }
    
    .calculator-card {
        background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(127, 61, 158, 0.15);
        margin-bottom: 20px;
    }
    
    .calculator-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .calculator-icon {
        width: 45px;
        height: 45px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .calculator-icon i {
        color: #7F3D9E;
        font-size: 1.3rem;
    }
    
    .calculator-title {
        font-family: 'Playfair Display', serif;
        color: #7F3D9E;
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
    }
    
    .calc-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(127, 61, 158, 0.2);
    }
    
    .calc-row:last-child {
        border-bottom: none;
        padding-top: 15px;
        margin-top: 10px;
        border-top: 2px solid #7F3D9E;
    }
    
    .calc-label {
        color: #6B7280;
        font-size: 0.95rem;
    }
    
    .calc-value {
        font-weight: 700;
        color: #7F3D9E;
        font-size: 1rem;
    }
    
    .calc-row:last-child .calc-value {
        font-size: 1.8rem;
    }
    
    .policy-info {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-top: 15px;
    }
    
    .policy-info h5 {
        font-size: 0.75rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    
    .policy-info ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .policy-info li {
        padding: 8px 0;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
    }
    
    .policy-info li:last-child {
        border-bottom: none;
    }
    
    .policy-info strong {
        color: #7F3D9E;
    }
    
    .paybill-card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-top: 15px;
        text-align: center;
    }
    
    .paybill-card h5 {
        font-size: 0.75rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    
    .paybill-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #7F3D9E;
        margin-bottom: 10px;
    }
    
    .paybill-note {
        font-size: 0.85rem;
        color: #6B7280;
        font-style: italic;
    }
    
    .summary-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 20px;
    }
    
    .summary-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 15px;
        border-bottom: 2px solid #E5E7EB;
        margin-bottom: 20px;
    }
    
    .summary-header h3 {
        font-family: 'Playfair Display', serif;
        color: #7F3D9E;
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .edit-btn {
        background: none;
        border: none;
        color: #7F3D9E;
        font-size: 0.85rem;
        text-decoration: underline;
        cursor: pointer;
        font-weight: 600;
    }
    
    .summary-section {
        margin-bottom: 25px;
    }
    
    .summary-section h4 {
        font-size: 0.75rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    
    .summary-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 12px;
    }
    
    .summary-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .summary-label {
        font-size: 0.85rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .summary-value {
        font-size: 1rem;
        color: #1A1A1A;
        font-weight: 600;
    }
    
    .package-badge {
        background: linear-gradient(135deg, #7F3D9E 0%, #9C27B0 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin: 20px 0;
    }
    
    .package-badge-icon {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .package-badge-content h5 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        font-weight: 700;
    }
    
    .package-badge-content p {
        margin: 0;
        font-size: 0.85rem;
        opacity: 0.9;
    }
    
    .dependents-list {
        background: #F9FAFB;
        padding: 15px;
        border-radius: 10px;
    }
    
    .dependent-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #E5E7EB;
    }
    
    .dependent-item:last-child {
        border-bottom: none;
    }
    
    .dependent-number {
        width: 30px;
        height: 30px;
        background: #7F3D9E;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }
    
    .dependent-name {
        flex: 1;
        font-weight: 600;
        color: #1A1A1A;
    }
    
    .dependent-age {
        color: #6B7280;
        font-size: 0.9rem;
    }
    
    .fee-card {
        background: linear-gradient(135deg, #7F3D9E 0%, #6B3587 100%);
        color: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 25px;
    }
    
    .fee-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .fee-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fee-icon i {
        font-size: 1.5rem;
    }
    
    .fee-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
    }
    
    .fee-amount {
        font-size: 3rem;
        font-weight: 700;
        text-align: center;
        margin: 20px 0;
    }
    
    .fee-instructions {
        background: rgba(255,255,255,0.1);
        padding: 15px;
        border-radius: 10px;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    .mpesa-code-input {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-top: 20px;
    }
    
    .mpesa-code-input label {
        color: #7F3D9E;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        display: block;
    }
    
    .mpesa-code-input input {
        background: #F9FAFB;
        border: 2px solid #E5E7EB;
        padding: 15px;
        border-radius: 10px;
        width: 100%;
        font-size: 1.1rem;
        font-weight: 700;
        text-align: center;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    
    .mpesa-code-input .form-text {
        color: #6B7280;
        font-size: 0.75rem;
        text-align: center;
        margin-top: 10px;
    }
    
    .terms-checkbox {
        background: #F9FAFB;
        padding: 20px;
        border-radius: 15px;
        margin: 20px 0;
    }
    
    .terms-checkbox .form-check {
        display: flex;
        align-items: start;
        gap: 12px;
    }
    
    .terms-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 3px;
        flex-shrink: 0;
    }
    
    .terms-checkbox label {
        font-size: 0.9rem;
        color: #4B5563;
        line-height: 1.6;
    }
    
    .terms-checkbox a {
        color: #7F3D9E;
        font-weight: 600;
        text-decoration: none;
    }
    
    .terms-checkbox a:hover {
        text-decoration: underline;
    }
    
    .btn-complete {
        background: linear-gradient(135deg, #7F3D9E 0%, #9C27B0 100%);
        border: none;
        color: white;
        padding: 18px 40px;
        border-radius: 15px;
        font-weight: 700;
        font-size: 1.1rem;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.3s ease;
    }
    
    .btn-complete:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(127, 61, 158, 0.4);
    }
    
    .btn-back {
        background: white;
        border: 2px solid #E5E7EB;
        color: #6B7280;
        padding: 14px 30px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }
</style>

<div class="registration-container">
    <!-- Header -->
    <div class="registration-header text-center mb-4">
        <h1 class="playfair">Online Registration</h1>
        <p>Become a member of the SHENA Companion welfare association today.</p>
    </div>
    
    <!-- Progress Section -->
    <div class="progress-section">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #7F3D9E; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 5px;">Current Step</div>
                <h2 class="playfair" style="color: #1A1A1A; font-size: 1.8rem; margin: 0;">Step 1: Personal Details</h2>
            </div>
            <div style="text-align: right;">
                <div style="color: #7F3D9E; font-size: 3rem; font-weight: 700; line-height: 1;">25%</div>
                <div style="color: #6B7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px;">Completion</div>
            </div>
        </div>
        
        <div class="step-indicator">
            <div class="progress-bar-fill" style="width: 25%;"></div>
            <div class="step-item active completed">
                <div class="step-number"><i class="fas fa-user"></i></div>
                <div class="step-label">1. Personal</div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-label">2. Package</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-label">3. Dependents</div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-label">4. Payment</div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Form Section -->
        <div class="form-section">
            <form method="POST" action="/register/step1" id="step1Form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                
                <!-- Personal Information Section -->
                <div class="section-header">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h3>Personal Information</h3>
                    </div>
                    <span class="required-badge">Required</span>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="full_name" class="form-label">Full Name (As per ID)</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="e.g. John Doe" value="<?php echo e(old('full_name')); ?>" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="id_number" class="form-label">National ID Number</label>
                        <input type="text" class="form-control" id="id_number" name="id_number" placeholder="12345678" value="<?php echo e(old('id_number')); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="phone" class="form-label">M-Pesa Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="0712 345 678" value="<?php echo e(old('phone')); ?>" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="address" class="form-label">Residential Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Town, Estate, House No." value="<?php echo e(old('address')); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo e(old('email')); ?>">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo e(old('date_of_birth')); ?>" required>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn-save">
                        <i class="fas fa-save"></i> Save Progress
                    </button>
                    <button type="submit" class="btn-continue">
                        Continue to Package
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Payment Sidebar -->
        <div class="payment-sidebar">
            <div class="payment-card">
                <div class="payment-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3>Payment Portal</h3>
                
                <div class="payment-detail">
                    <div class="payment-detail-label">Lipa na M-Pesa Paybill</div>
                    <div class="payment-detail-value">4163987</div>
                </div>
                
                <div class="payment-detail">
                    <div class="payment-detail-label">Account Name</div>
                    <div style="font-size: 1.3rem; font-weight: 700; color: #7F3D9E;">SHENA</div>
                </div>
                
                <div class="qr-code">
                    <img id="qr-paybill-img" data-paybill="4163987" data-account="4163987" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="QR Code">
                    <div class="qr-note">Scan QR to Pay</div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px dashed rgba(127, 61, 158, 0.3);">
                    <div style="font-size: 0.85rem; color: #7F3D9E; font-style: italic; text-align: center;">
                        "Excellence in Every Action"
                    </div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #7F3D9E; text-align: center; margin-top: 5px;">
                        We Are Royal
                    </div>
                </div>
            </div>
            
            <!-- Registration Help -->
            <div class="help-section">
                <h4>Registration Help</h4>
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 5px;">
                    <i class="fas fa-phone-alt"></i>
                    <div class="help-contact">+254 748 585 067</div>
                </div>
                <div style="font-size: 0.85rem; color: #92400E;">Customer Support</div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and interactions
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format phone number
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('0') && !value.startsWith('254')) {
                value = '0' + value;
            }
            e.target.value = value.slice(0, 10);
        });
    }
    
    // Save progress functionality
    const saveButtons = document.querySelectorAll('.btn-save');
    saveButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Save form data to localStorage
            const form = this.closest('form');
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            localStorage.setItem('registrationProgress', JSON.stringify(data));
            
            // Show success message
            ShenaApp.showNotification('Progress saved successfully!', 'success');
        });
    });
    
    // Load saved progress if available
    const savedData = localStorage.getItem('registrationProgress');
    if (savedData) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input && !input.value) {
                input.value = data[key];
            }
        });
    }
});
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>

<script>
// Fetch server-generated QR data URI and set image src
(function(){
    try {
        var img = document.getElementById('qr-paybill-img');
        if (!img) return;
        var paybill = img.getAttribute('data-paybill');
        var account = img.getAttribute('data-account');
        var url = '/qr/paybill?paybill=' + encodeURIComponent(paybill) + '&account=' + encodeURIComponent(account);

        fetch(url)
            .then(function(res){ return res.json(); })
            .then(function(json){
                if (json && json.data_uri) {
                    img.src = json.data_uri;
                }
            })
            .catch(function(err){ console.error('QR fetch error', err); });
    } catch (e) { console.error(e); }
})();
</script>
