<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    * { font-family: 'Manrope', sans-serif; }
    body { background: #F7F7F9; }
    .playfair { font-family: 'Playfair Display', serif; }
    
    .registration-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .registration-header h1 { font-family: 'Playfair Display', serif; color: #7F3D9E; font-size: 2.5rem; font-weight: 700; margin-bottom: 10px; }
    .registration-header p { color: #6B7280; font-size: 1rem; }
    
    .progress-section { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px; }
    .step-indicator { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; position: relative; }
    .step-indicator::before { content: ''; position: absolute; top: 30px; left: 0; right: 0; height: 3px; background: #E5E7EB; z-index: 0; }
    .step-indicator .progress-bar-fill { position: absolute; top: 30px; left: 0; height: 3px; background: #7F3D9E; z-index: 1; transition: width 0.3s ease; }
    .step-item { flex: 1; text-align: center; position: relative; z-index: 2; }
    .step-number { width: 60px; height: 60px; border-radius: 50%; background: white; border: 3px solid #E5E7EB; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; color: #9CA3AF; margin-bottom: 10px; transition: all 0.3s ease; }
    .step-item.active .step-number, .step-item.completed .step-number { background: #7F3D9E; border-color: #7F3D9E; color: white; }
    .step-label { font-size: 0.9rem; color: #6B7280; font-weight: 500; }
    .step-item.active .step-label { color: #7F3D9E; font-weight: 700; }
    
    .main-content { display: flex; gap: 30px; }
    .form-section { flex: 1; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .payment-sidebar { width: 320px; flex-shrink: 0; }
    
    .payment-card { background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%); padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(127, 61, 158, 0.15); text-align: center; }
    .payment-icon { width: 60px; height: 60px; background: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .payment-icon i { font-size: 1.8rem; color: #7F3D9E; }
    .payment-card h3 { font-family: 'Playfair Display', serif; color: #7F3D9E; font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; }
    .payment-detail { background: white; padding: 15px; border-radius: 15px; margin-bottom: 15px; }
    .payment-detail-label { font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .payment-detail-value { font-size: 1.8rem; font-weight: 700; color: #7F3D9E; }
    .qr-code { background: white; padding: 20px; border-radius: 15px; margin-top: 20px; }
    .qr-code img { width: 120px; height: 120px; }
    .qr-note { font-size: 0.75rem; color: #7F3D9E; font-style: italic; margin-top: 10px; }
    
    .section-header { background: #7F3D9E; color: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; }
    .section-header i { font-size: 1.5rem; }
    .section-header h3 { margin: 0; font-size: 1.2rem; font-weight: 600; }
    .section-header .required-badge { margin-left: auto; background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .form-label { color: #1A1A1A; font-weight: 600; font-size: 0.75rem; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
    .form-control, .form-select { border: 2px solid #E5E7EB; border-radius: 10px; padding: 12px 16px; font-size: 1rem; transition: all 0.3s ease; }
    .form-control:focus, .form-select:focus { border-color: #7F3D9E; box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1); }
    
    .action-buttons { display: flex; gap: 15px; margin-top: 40px; }
    .btn-save { background: white; border: 2px solid #E5E7EB; color: #6B7280; padding: 14px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; }
    .btn-save:hover { border-color: #7F3D9E; color: #7F3D9E; }
    .btn-continue { flex: 1; background: #7F3D9E; border: none; color: white; padding: 14px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-continue:hover { background: #6B3587; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(127, 61, 158, 0.3); }
    
    .help-section { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center; }
    .help-section h4 { font-size: 0.85rem; color: #92400E; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
    .help-contact { display: flex; align-items: center; justify-content: center; gap: 10px; color: #78350F; font-weight: 700; font-size: 1.1rem; }
    
    @media (max-width: 992px) { .main-content { flex-direction: column-reverse; } .payment-sidebar { width: 100%; } }

    .terms-checkbox { background: #F9FAFB; padding: 20px; border-radius: 15px; margin: 20px 0; border: 1px solid #E5E7EB; }
    .terms-checkbox .form-check { display: flex; align-items: flex-start; gap: 12px; }
    .terms-checkbox input[type="checkbox"] { width: 20px; height: 20px; margin-top: 3px; flex-shrink: 0; accent-color: #7F3D9E; }
    .terms-checkbox label { font-size: 0.9rem; color: #4B5563; line-height: 1.6; cursor: pointer; }
    .terms-checkbox a { color: #7F3D9E; font-weight: 600; text-decoration: none; }
    .terms-checkbox a:hover { text-decoration: underline; }
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
            <form method="POST" action="/register" id="step1Form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <input type="hidden" name="payment_method" value="mpesa">
                
                <!-- Package Selection (Hidden for now, will show on next step) -->
                <input type="hidden" name="package_id" id="package_id" value="individual_below_70">
                
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
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="e.g. John" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="e.g. Doe" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="national_id" class="form-label">National ID Number</label>
                        <input type="text" class="form-control" id="national_id" name="national_id" placeholder="12345678" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="phone" class="form-label">M-Pesa Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="0712 345 678" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="county" class="form-label">County</label>
                        <input type="text" class="form-control" id="county" name="county" placeholder="e.g. Nairobi" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="address" class="form-label">Residential Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Town, Estate, House No." required>
                    </div>
                </div>
                
                <!-- Consent Checkbox -->
                <div class="terms-checkbox">
                    <div class="form-check">
                        <input type="checkbox" name="consent" id="consentCheck" required>
                        <label for="consentCheck">
                            I have read and agree to SHENA Companion's
                            <a href="/terms-and-conditions" target="_blank">Terms &amp; Conditions</a>,
                            <a href="/privacy-policy" target="_blank">Privacy Policy</a>, and
                            <a href="/policy-booklet" target="_blank">Policy Booklet</a>.
                            I consent to SHENA Companion processing my personal information for membership, payment, and service delivery.
                        </label>
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
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=4163987" alt="QR Code">
                    <div class="qr-note">"Scan QR to Pay"</div>
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
            const form = this.closest('form');
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            localStorage.setItem('registrationProgress', JSON.stringify(data));
            ShenaApp.showNotification('Progress saved successfully!', 'success', 2000);
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
