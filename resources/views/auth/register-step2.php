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
    .payment-card h3 { font-family: 'Playfair Display', serif; color: #7F3D9E; font-size: 1.3rem; font-weight: 700; margin-bottom: 20px; }
    .payment-detail { background: white; padding: 15px; border-radius: 15px; margin-bottom: 15px; }
    .payment-detail-label { font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .payment-detail-value { font-size: 1.6rem; font-weight: 700; color: #7F3D9E; }
    .qr-code { background: white; padding: 20px; border-radius: 15px; margin-top: 20px; }
    .qr-code img { width: 120px; height: 120px; }
    .qr-note { font-size: 0.75rem; color: #7F3D9E; font-style: italic; margin-top: 10px; }
    .package-card { border: 3px solid #E5E7EB; border-radius: 20px; padding: 30px; margin-bottom: 20px; cursor: pointer; transition: all 0.3s ease; position: relative; }
    .package-card:hover { border-color: #7F3D9E; box-shadow: 0 6px 20px rgba(127, 61, 158, 0.15); }
    .package-card.selected { border-color: #7F3D9E; background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 30%); box-shadow: 0 6px 20px rgba(127, 61, 158, 0.2); }
    .package-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #7F3D9E 0%, #9C27B0 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .package-icon i { color: white; font-size: 1.5rem; }
    .package-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #7F3D9E; margin-bottom: 10px; }
    .package-price { display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #E5E7EB; }
    .package-price-label { color: #6B7280; font-size: 0.9rem; }
    .package-price-value { font-weight: 700; color: #1A1A1A; font-size: 1rem; }
    .btn-select { width: 100%; padding: 12px; border-radius: 10px; border: 2px solid #7F3D9E; background: white; color: #7F3D9E; font-weight: 600; transition: all 0.3s ease; margin-top: 15px; }
    .package-card.selected .btn-select { background: #7F3D9E; color: white; }
    .info-note { background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%); padding: 20px; border-radius: 15px; border-left: 4px solid #7F3D9E; margin: 30px 0; }
    .info-note i { color: #7F3D9E; margin-right: 10px; }
    .action-buttons { display: flex; gap: 15px; margin-top: 40px; }
    .btn-back { background: white; border: 2px solid #E5E7EB; color: #6B7280; padding: 14px 30px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px; transition: all 0.3s ease; }
    .btn-back:hover { border-color: #7F3D9E; color: #7F3D9E; }
    .btn-continue { flex: 1; background: #7F3D9E; border: none; color: white; padding: 14px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-continue:hover { background: #6B3587; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(127, 61, 158, 0.3); }
    .help-section { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center; }
    .help-section h4 { font-size: 0.85rem; color: #92400E; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
    .help-contact { display: flex; align-items: center; justify-content: center; gap: 10px; color: #78350F; font-weight: 700; font-size: 1.1rem; }
    @media (max-width: 992px) { .main-content { flex-direction: column-reverse; } .payment-sidebar { width: 100%; } }
</style>

<div class="registration-container">
    <!-- Header -->
    <div class="registration-header text-center mb-4">
        <h1 class="playfair">Package Selection</h1>
        <p>Choose a contribution plan that best fits your needs.</p>
    </div>
    
    <!-- Progress Section -->
    <div class="progress-section">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #7F3D9E; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 5px;">Current Step</div>
                <h2 class="playfair" style="color: #1A1A1A; font-size: 1.8rem; margin: 0;">Step 2: Package Selection</h2>
            </div>
            <div style="text-align: right;">
                <div style="color: #7F3D9E; font-size: 3rem; font-weight: 700; line-height: 1;">50%</div>
                <div style="color: #6B7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px;">Completion</div>
            </div>
        </div>
        
        <div class="step-indicator">
            <div class="progress-bar-fill" style="width: 50%;"></div>
            <div class="step-item completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">1. Personal</div>
            </div>
            <div class="step-item active completed">
                <div class="step-number"><i class="fas fa-box"></i></div>
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
            <form method="POST" action="/register/step2" id="step2Form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                
                <div class="row">
                    <!-- Individual Package -->
                    <div class="col-md-4">
                        <div class="package-card" onclick="selectPackage('individual')">
                            <div class="package-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="package-title">Individual</div>
                            <div class="package-price">
                                <span class="package-price-label">Below 70 Years</span>
                                <span class="package-price-value">Ksh 100</span>
                            </div>
                            <div class="package-price">
                                <span class="package-price-label">Above 70 Years</span>
                                <span class="package-price-value">Ksh 200</span>
                            </div>
                            <button type="button" class="btn-select">Select Plan</button>
                            <input type="radio" name="package" value="individual" id="package_individual" style="display: none;">
                        </div>
                    </div>
                    
                    <!-- Family/Couples Package -->
                    <div class="col-md-4">
                        <div class="package-card selected" onclick="selectPackage('family')">
                            <div class="package-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="package-title">Family/Couples</div>
                            <div class="package-price">
                                <span class="package-price-label">Below 70 Years</span>
                                <span class="package-price-value">Ksh 150</span>
                            </div>
                            <div class="package-price">
                                <span class="package-price-label">Above 70 Years</span>
                                <span class="package-price-value">Ksh 300</span>
                            </div>
                            <button type="button" class="btn-select">Selected</button>
                            <input type="radio" name="package" value="family" id="package_family" style="display: none;" checked>
                        </div>
                    </div>
                    
                    <!-- Executive Package -->
                    <div class="col-md-4">
                        <div class="package-card" onclick="selectPackage('executive')">
                            <div class="package-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="package-title">Executive</div>
                            <div class="package-price">
                                <span class="package-price-label">All Ages</span>
                                <span class="package-price-value">Ksh 500</span>
                            </div>
                            <div class="package-price" style="border-bottom: none;">
                                <span class="package-price-label">Premium/Unlimited/Excluded</span>
                                <span class="package-price-value"></span>
                            </div>
                            <button type="button" class="btn-select">Select Plan</button>
                            <input type="radio" name="package" value="executive" id="package_executive" style="display: none;">
                        </div>
                    </div>
                </div>
                
                <!-- Note on Coverage -->
                <div class="info-note">
                    <div style="display: flex; align-items: start; gap: 15px;">
                        <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-top: 3px;"></i>
                        <div>
                            <h4 style="color: #1A1A1A; font-weight: 700; margin-bottom: 10px;">Note on Coverage</h4>
                            <p style="color: #4B5563; margin: 0; line-height: 1.6;">
                                Monthly contribution rates are subject to age bracket confirmation. The registration fee of Ksh 200 is a one-time payment separate from monthly contributions.
                            </p>
                        </div>
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
                    <button type="button" class="btn-back" onclick="window.location='/register'">
                        <i class="fas fa-arrow-left"></i>
                        Back to Personal Details
                    </button>
                    <button type="submit" class="btn-continue">
                        Continue to Dependents
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Payment Sidebar -->
        <div class="payment-sidebar">
            <div class="payment-card">
                <div class="payment-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3>Reg. Fee Payment</h3>
                
                <div class="payment-detail">
                    <div class="payment-detail-label">Paybill Number</div>
                    <div class="payment-detail-value">4163987</div>
                </div>
                
                <div class="payment-detail">
                    <div class="payment-detail-label">Amount Payable</div>
                    <div style="font-size: 1.6rem; font-weight: 700; color: #7F3D9E;">KSH 200</div>
                </div>
                
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=4163987" alt="QR Code">
                    <div class="qr-note">"Scan QR to Pay Fee"</div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px dashed rgba(127, 61, 158, 0.3);">
                    <div style="font-size: 0.85rem; color: #7F3D9E; font-style: italic; text-align: center;">
                        "Excellence in Every Action"
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
function selectPackage(type) {
    // Remove selected class from all cards
    document.querySelectorAll('.package-card').forEach(card => {
        card.classList.remove('selected');
        card.querySelector('.btn-select').textContent = 'Select Plan';
    });
    
    // Add selected class to clicked card
    const selectedCard = event.currentTarget;
    selectedCard.classList.add('selected');
    selectedCard.querySelector('.btn-select').textContent = 'Selected';
    
    // Check the radio button
    document.getElementById('package_' + type).checked = true;
}
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
