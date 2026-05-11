<?php 
include VIEWS_PATH . '/layouts/header.php'; 
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Success Message -->
            <div class="alert alert-success shadow-sm">
                <h4 class="alert-heading">
                    <i class="fas fa-check-circle"></i> Registration Successful!
                </h4>
                <p>Welcome to Shena Companion Welfare Association, <strong><?php echo e($registration['name']); ?></strong>!</p>
                <hr>
                <p class="mb-0">
                    <strong>Member Number:</strong> <?php echo e($registration['member_number']); ?><br>
                    <small class="text-muted">Please keep this number for reference</small>
                </p>
            </div>

            <!-- Payment Required Card -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card"></i> Complete Your Registration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Payment Required:</strong> Please pay the registration fee of 
                        <strong>KES <?php echo number_format($registration['amount'], 2); ?></strong> 
                        to activate your membership.
                    </div>

                    <!-- Payment Method Selection -->
                    <h6 class="mb-3">Choose Payment Method:</h6>
                    
                    <div class="btn-group w-100 mb-4" role="group">
                        <input type="radio" class="btn-check" name="paymentMethod" id="methodSTK" value="stk" checked>
                        <label class="btn btn-outline-primary" for="methodSTK">
                            <i class="fas fa-mobile-alt"></i> STK Push
                        </label>
                        <input type="radio" class="btn-check" name="paymentMethod" id="methodManual" value="manual">
                        <label class="btn btn-outline-secondary" for="methodManual">
                            <i class="fas fa-hand-holding-usd"></i> Manual Paybill
                        </label>
                    </div>

                    <!-- STK Push Section -->
                    <div id="stkPushSection">
                        <h6 class="mb-3">
                            <i class="fas fa-mobile-alt text-primary"></i> Pay via M-Pesa STK Push
                        </h6>
                        <p class="text-muted">
                            You will receive a payment prompt on your phone. Enter your M-Pesa PIN to complete.
                        </p>
                        
                        <form id="stkPushForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" 
                                       placeholder="07XXXXXXXX or 2547XXXXXXXX"
                                       value="<?php echo e($registration['phone']); ?>" required>
                                <small class="text-muted">Enter your M-Pesa registered phone number</small>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                You will receive a payment prompt on <strong><?php echo e($registration['phone']); ?></strong>. 
                                Make sure your phone is on and has network coverage.
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100" id="initiateSTKBtn">
                                <i class="fas fa-paper-plane"></i> Send Payment Request (KES <?php echo number_format($registration['amount'], 2); ?>)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-lg w-100 mt-2" id="retryRegistrationSTKBtn" style="display:none;">
                                <i class="fas fa-redo-alt"></i> Retry STK Push
                            </button>
                        </form>
                        
                        <div id="stkPushStatus" class="mt-3" style="display: none;">
                            <div class="alert">
                                <i class="fas fa-spinner fa-spin"></i> <span id="statusMessage">Processing payment...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Paybill Section -->
                    <div id="manualPaybillSection" style="display: none;">
                        <h6 class="mb-3">
                            <i class="fas fa-hand-holding-usd text-secondary"></i> Pay via M-Pesa Paybill
                        </h6>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-2">
                                    <strong>Paybill Number:</strong> 
                                    <span class="text-primary fs-5"><?php echo MPESA_BUSINESS_SHORTCODE; ?></span>
                                </p>
                                <p class="mb-2">
                                    <strong>Account Number:</strong> 
                                    <span class="text-success fs-6"><?php echo e($registration['member_number']); ?></span>
                                </p>
                                <p class="mb-0">
                                    <strong>Amount:</strong> 
                                    <span class="text-danger fs-6">KES <?php echo number_format($registration['amount'], 2); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6>Steps to Pay:</h6>
                        <ol>
                            <li>Go to M-Pesa menu on your phone</li>
                            <li>Select <strong>Lipa na M-Pesa</strong></li>
                            <li>Select <strong>Pay Bill</strong></li>
                            <li>Enter Business Number: <strong><?php echo MPESA_BUSINESS_SHORTCODE; ?></strong></li>
                            <li>Enter Account Number: <strong><?php echo e($registration['member_number']); ?></strong></li>
                            <li>Enter Amount: <strong><?php echo number_format($registration['amount'], 2); ?></strong></li>
                            <li>Enter your M-Pesa PIN and confirm</li>
                        </ol>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Payment will be confirmed automatically within a few minutes. You will receive an email notification.
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- What's Next -->
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="fas fa-info-circle"></i> What happens next?
                            </h6>
                            <ol class="mb-0">
                                <li>Complete payment using any method above</li>
                                <li>Your payment will be automatically confirmed</li>
                                <li>Your account will be activated</li>
                                <li>You'll receive a confirmation email</li>
                                <li>You can then <a href="/login">login</a> and start managing your membership</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Already Paid Section -->
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle"></i> <strong>Already paid?</strong><br>
                        If you've already made payment and have your M-Pesa transaction code, 
                        <a href="/verify-transaction" class="alert-link"><strong>click here to verify instantly →</strong></a>
                    </div>

                    <!-- Skip for Now -->
                    <div class="text-center mt-4">
                        <a href="/login" class="btn btn-link">
                            Skip for now, I'll pay later
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Payment method toggle
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
    await initiateRegistrationStkPush();
});

const retryRegistrationSTKBtn = document.getElementById('retryRegistrationSTKBtn');
retryRegistrationSTKBtn?.addEventListener('click', async function() {
    await initiateRegistrationStkPush();
});

function showRegistrationRetry(message) {
    const btn = document.getElementById('initiateSTKBtn');
    const statusDiv = document.getElementById('stkPushStatus');
    const statusMsg = document.getElementById('statusMessage');
    const retryBtn = document.getElementById('retryRegistrationSTKBtn');

    statusDiv.style.display = 'block';
    statusDiv.querySelector('.alert').className = 'alert alert-danger';
    statusMsg.innerHTML = '<i class="fas fa-times-circle"></i> ' + message;

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Payment Request (KES <?php echo number_format($registration['amount'], 2); ?>)';
    if (retryBtn) {
        retryBtn.style.display = '';
    }
}

async function initiateRegistrationStkPush() {
    const btn = document.getElementById('initiateSTKBtn');
    const statusDiv = document.getElementById('stkPushStatus');
    const statusMsg = document.getElementById('statusMessage');
    const retryBtn = document.getElementById('retryRegistrationSTKBtn');
    const phoneNumber = document.getElementById('phoneNumber').value;
    
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
        const response = await fetch('/registration/pay', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                phone_number: phoneNumber,
                payment_method: 'stk'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            statusDiv.style.display = 'block';
            statusDiv.querySelector('.alert').className = 'alert alert-success';
            statusMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            
            // Show success message and redirect
            setTimeout(() => {
                statusMsg.innerHTML = '<i class="fas fa-info-circle"></i> Waiting for payment confirmation...';
            }, 3000);
            
            // Poll for payment status if checkout ID provided
            if (data.checkout_request_id) {
                pollPaymentStatus(data.checkout_request_id);
            }
        } else {
            showRegistrationRetry(data.error || data.message || 'Payment initiation failed');
        }
    } catch (error) {
        console.error('Payment error:', error);
        showRegistrationRetry('Network error. Please try again.');
    }
}

// Poll payment status
function pollPaymentStatus(checkoutRequestId) {
    let attempts = 0;
    const maxAttempts = 60; // Poll for 60 seconds
    
    const interval = setInterval(async () => {
        attempts++;
        
        if (attempts > maxAttempts) {
            clearInterval(interval);
            document.getElementById('statusMessage').innerHTML = 
                '<i class="fas fa-info-circle"></i> Payment is being processed. You will receive email confirmation.';
            
            setTimeout(() => {
                window.location.href = '/login';
            }, 3000);
            return;
        }
        
        try {
            const response = await fetch(`/payment/status?checkout_request_id=${checkoutRequestId}`);
            const data = await response.json();
            
            if (data.success && data.status) {
                if (String(data.status.ResultCode) === '0') {
                    // Payment successful
                    clearInterval(interval);
                    const statusDiv = document.getElementById('stkPushStatus');
                    statusDiv.querySelector('.alert').className = 'alert alert-success';
                    document.getElementById('statusMessage').innerHTML = 
                        '<i class="fas fa-check-circle"></i> Payment completed successfully! Redirecting to login...';
                    
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2000);
                } else if (data.status.ResultCode !== undefined) {
                    // Payment failed
                    clearInterval(interval);
                    showRegistrationRetry(data.status.ResultDesc || 'Payment failed. Please retry.');
                }
            }
        } catch (error) {
            console.error('Status check error:', error);
        }
    }, 1000);
}
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
