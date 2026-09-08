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
    .form-section { flex: 1; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); min-height: 500px; }
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
    .form-control:focus, .form-select:focus { border-color: #7F3D9E; box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1); outline: none; }
    
    .action-buttons { display: flex; gap: 15px; margin-top: 40px; }
    .btn-back { background: white; border: 2px solid #E5E7EB; color: #6B7280; padding: 14px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 10px; }
    .btn-back:hover { border-color: #7F3D9E; color: #7F3D9E; }
    .btn-continue { flex: 1; background: #7F3D9E; border: none; color: white; padding: 14px 30px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; }
    .btn-continue:hover { background: #6B3587; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(127, 61, 158, 0.3); }
    
    .package-card { border: 3px solid #E5E7EB; border-radius: 20px; padding: 30px; margin-bottom: 20px; cursor: pointer; transition: all 0.3s ease; position: relative; }
    .package-card:hover { border-color: #7F3D9E; box-shadow: 0 6px 20px rgba(127, 61, 158, 0.15); }
    .package-card.selected { border-color: #7F3D9E; background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 30%); box-shadow: 0 6px 20px rgba(127, 61, 158, 0.2); }
    .package-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #7F3D9E 0%, #9C27B0 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .package-icon i { color: white; font-size: 1.5rem; }
    .package-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #7F3D9E; margin-bottom: 10px; }
    .package-price { display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #E5E7EB; }
    .package-price-label { color: #6B7280; font-size: 0.9rem; }
    .package-price-value { font-weight: 700; color: #1A1A1A; font-size: 1rem; }
    
    .help-section { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center; }
    .help-section h4 { font-size: 0.85rem; color: #92400E; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
    .help-contact { display: flex; align-items: center; justify-content: center; gap: 10px; color: #78350F; font-weight: 700; font-size: 1.1rem; }
    
    .step-content { display: none; }
    .step-content.active { display: block; animation: fadeIn 0.5s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    @media (max-width: 992px) { .main-content { flex-direction: column-reverse; } .payment-sidebar { width: 100%; } }
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
                <h2 class="playfair" id="step-title" style="color: #1A1A1A; font-size: 1.8rem; margin: 0;">Step 1: Personal Details</h2>
            </div>
            <div style="text-align: right;">
                <div id="completion-percent" style="color: #7F3D9E; font-size: 3rem; font-weight: 700; line-height: 1;">25%</div>
                <div style="color: #6B7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px;">Completion</div>
            </div>
        </div>
        
        <div class="step-indicator">
            <div class="progress-bar-fill" id="progress-bar" style="width: 25%;"></div>
            <div class="step-item active completed" data-step="1">
                <div class="step-number"><i class="fas fa-user"></i></div>
                <div class="step-label">1. Personal</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">2. Package</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">3. Dependents</div>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">4. Payment</div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Form Section -->
        <div class="form-section">
            <form id="registrationForm">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <input type="hidden" name="payment_method" value="mpesa">
                <input type="hidden" name="package_id" id="package_id" value="">
                
                <!-- STEP 1: Personal Details -->
                <div class="step-content active" id="step1">
                    <div class="section-header">
                        <i class="fas fa-user-circle"></i>
                        <div><h3>Personal Information</h3></div>
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
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
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
                    
                    <div class="action-buttons">
                        <button type="button" class="btn-continue" onclick="nextStep()">
                            Continue to Package <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- STEP 2: Package Selection -->
                <div class="step-content" id="step2">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="package-card" onclick="selectPackage('individual_below_70', this)">
                                <div class="package-icon"><i class="fas fa-user"></i></div>
                                <div class="package-title">Individual</div>
                                <div class="package-price">
                                    <span class="package-price-label">70 Years and Below</span>
                                    <span class="package-price-value">Ksh 100</span>
                                </div>
                                <div class="package-price">
                                    <span class="package-price-label">Above 70 Years</span>
                                    <span class="package-price-value">Ksh 200</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="package-card" onclick="selectPackage('couple_below_70', this)">
                                <div class="package-icon"><i class="fas fa-users"></i></div>
                                <div class="package-title">Family/Couples</div>
                                <div class="package-price">
                                    <span class="package-price-label">70 Years and Below</span>
                                    <span class="package-price-value">Ksh 150</span>
                                </div>
                                <div class="package-price">
                                    <span class="package-price-label">Above 70 Years</span>
                                    <span class="package-price-value">Ksh 300</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="package-card" onclick="selectPackage('executive', this)">
                                <div class="package-icon"><i class="fas fa-crown"></i></div>
                                <div class="package-title">Executive</div>
                                <div class="package-price">
                                    <span class="package-price-label">All Ages</span>
                                    <span class="package-price-value">Ksh 500</span>
                                </div>
                                <div class="package-price" style="border-bottom: none;">
                                    <span class="package-price-label">Premium Coverage</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn-back" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn-continue" onclick="nextStep()">
                            Continue to Dependents <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- STEP 3: Dependents (Optional) -->
                <div class="step-content" id="step3">
                    <div class="section-header">
                        <i class="fas fa-users"></i>
                        <div><h3>Add Dependents (Optional)</h3></div>
                    </div>
                    
                    <p style="color: #6B7280; margin-bottom: 30px;">You can add dependents now or skip this step and add them later from your dashboard.</p>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn-back" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn-continue" onclick="nextStep()">
                            Continue to Payment <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- STEP 4: Final Review & Payment -->
                <div class="step-content" id="step4">
                    <div class="section-header">
                        <i class="fas fa-check-circle"></i>
                        <div><h3>Review & Complete Registration</h3></div>
                    </div>
                    
                    <div style="background: #F9FAFB; padding: 20px; border-radius: 15px; margin-bottom: 20px;">
                        <h4 style="color: #1A1A1A; margin-bottom: 15px; font-weight: 700;">Registration Summary</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <div style="font-size: 0.85rem; color: #6B7280; margin-bottom: 5px;">Full Name</div>
                                <div id="summary_name" style="font-weight: 600; color: #1A1A1A;"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.85rem; color: #6B7280; margin-bottom: 5px;">National ID</div>
                                <div id="summary_id" style="font-weight: 600; color: #1A1A1A;"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.85rem; color: #6B7280; margin-bottom: 5px;">Email</div>
                                <div id="summary_email" style="font-weight: 600; color: #1A1A1A;"></div>
                            </div>
                            <div>
                                <div style="font-size: 0.85rem; color: #6B7280; margin-bottom: 5px;">Phone</div>
                                <div id="summary_phone" style="font-weight: 600; color: #1A1A1A;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%); padding: 25px; border-radius: 15px; margin-bottom: 20px;">
                        <h4 style="color: #7F3D9E; margin-bottom: 10px; font-weight: 700;">Registration Fee</h4>
                        <div style="font-size: 2.5rem; font-weight: 700; color: #7F3D9E;">KSH 200</div>
                        <p style="color: #6B7280; margin: 10px 0 0 0; font-size: 0.9rem;">One-time registration payment</p>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn-back" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn-continue">
                            Complete Registration <i class="fas fa-check"></i>
                        </button>
                    </div>
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
                    <div style="font-size: 0.85rem; color: #7F3D9E; font-style: italic; text-align: center;">"Excellence in Every Action"</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #7F3D9E; text-align: center; margin-top: 5px;">We Are Royal</div>
                </div>
            </div>
            
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
let currentStep = 1;
const totalSteps = 4;

function updateProgress() {
    const stepTitles = [
        'Step 1: Personal Details',
        'Step 2: Package Selection',
        'Step 3: Add Dependents',
        'Step 4: Final Review & Payment'
    ];
    const completionPercent = [25, 50, 75, 100];
    
    document.getElementById('step-title').textContent = stepTitles[currentStep - 1];
    document.getElementById('completion-percent').textContent = completionPercent[currentStep - 1] + '%';
    document.getElementById('progress-bar').style.width = completionPercent[currentStep - 1] + '%';
    
    // Update step indicators
    document.querySelectorAll('.step-item').forEach((item, index) => {
        const stepNum = index + 1;
        item.classList.remove('active', 'completed');
        
        if (stepNum < currentStep) {
            item.classList.add('completed');
            item.querySelector('.step-number').innerHTML = '<i class="fas fa-check"></i>';
        } else if (stepNum === currentStep) {
            item.classList.add('active', 'completed');
            if (stepNum === 1) item.querySelector('.step-number').innerHTML = '<i class="fas fa-user"></i>';
            else if (stepNum === 2) item.querySelector('.step-number').innerHTML = '<i class="fas fa-box"></i>';
            else if (stepNum === 3) item.querySelector('.step-number').innerHTML = '<i class="fas fa-users"></i>';
            else if (stepNum === 4) item.querySelector('.step-number').innerHTML = '<i class="fas fa-credit-card"></i>';
        } else {
            item.querySelector('.step-number').textContent = stepNum;
        }
    });
    
    // Show/hide step content
    document.querySelectorAll('.step-content').forEach((content, index) => {
        content.classList.toggle('active', index + 1 === currentStep);
    });
}

function nextStep() {
    if (currentStep < totalSteps) {
        // Validate current step
        const currentStepEl = document.getElementById('step' + currentStep);
        const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
        let valid = true;
        
        inputs.forEach(input => {
            if (!input.value) {
                input.style.borderColor = '#ef4444';
                valid = false;
            } else {
                input.style.borderColor = '#E5E7EB';
            }
        });
        
        if (!valid && currentStep === 1) {
            ShenaApp.alert('Please fill in all required fields', 'warning');
            return;
        }
        
        if (currentStep === 2 && !document.getElementById('package_id').value) {
            ShenaApp.alert('Please select a package', 'warning');
            return;
        }
        
        // Update summary on step 4
        if (currentStep === 3) {
            document.getElementById('summary_name').textContent = 
                document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value;
            document.getElementById('summary_id').textContent = document.getElementById('national_id').value;
            document.getElementById('summary_email').textContent = document.getElementById('email').value;
            document.getElementById('summary_phone').textContent = document.getElementById('phone').value;
        }
        
        currentStep++;
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function selectPackage(packageId, element) {
    document.querySelectorAll('.package-card').forEach(card => card.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('package_id').value = packageId;
}

// Form submission
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/register', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('Registration successful! Redirecting...', 'success', 2000);
            window.location.href = data.redirect || '/dashboard';
        } else {
            ShenaApp.alert('Error: ' + (data.message || 'Registration failed'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ShenaApp.alert('An error occurred. Please try again.', 'error');
    });
});

// Auto-format phone number
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0 && !value.startsWith('0') && !value.startsWith('254')) {
        value = '0' + value;
    }
    e.target.value = value.slice(0, 10);
});

// Initialize
updateProgress();
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
