<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Verify Payment'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .verification-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .verification-header {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .verification-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .verification-body {
            padding: 40px;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid var(--secondary-color);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        
        .info-box h6 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-verify {
            background: var(--success-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-verify:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        
        .btn-verify:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        
        #verificationStatus {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        #verificationStatus.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            display: block;
        }
        
        #verificationStatus.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            display: block;
        }
        
        #verificationStatus.loading {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            display: block;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .example-code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            text-align: center;
            margin: 10px 0;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <!-- Header -->
        <div class="verification-header">
            <i class="fas fa-shield-check"></i>
            <h2>Verify Your Payment</h2>
            <p class="mb-0">Enter your M-Pesa transaction code to activate your account instantly</p>
        </div>
        
        <!-- Body -->
        <div class="verification-body">
            <!-- Info Box -->
            <div class="info-box">
                <h6><i class="fas fa-info-circle"></i> How to find your M-Pesa code</h6>
                <ul class="mb-0">
                    <li>Check the M-Pesa confirmation message you received</li>
                    <li>The code looks like: <strong>QBK5RTUNM2</strong> (10 characters)</li>
                    <li>It's usually in the format: "Confirmed. Ksh200.00 sent to..."</li>
                </ul>
                <div class="example-code mt-2">
                    Example: <strong>QBK5RTUNM2</strong>
                </div>
            </div>
            
            <!-- Verification Form -->
            <form id="verificationForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-receipt"></i> M-Pesa Transaction Code *
                    </label>
                    <input 
                        type="text" 
                        name="transaction_code" 
                        id="transactionCode"
                        class="form-control" 
                        placeholder="e.g., QBK5RTUNM2"
                        required
                        maxlength="15"
                        style="text-transform: uppercase;">
                    <small class="text-muted">Enter the transaction code from your M-Pesa message</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-phone"></i> Your Phone Number *
                    </label>
                    <input 
                        type="tel" 
                        name="phone_number" 
                        id="phoneNumber"
                        class="form-control" 
                        placeholder="0712345678"
                        required
                        pattern="[0-9]{10}"
                        maxlength="10">
                    <small class="text-muted">The phone number you used for payment</small>
                </div>
                
                <button type="submit" class="btn btn-verify" id="verifyBtn">
                    <i class="fas fa-check-circle"></i> Verify Payment
                </button>
            </form>
            
            <!-- Status Message -->
            <div id="verificationStatus"></div>
            
            <!-- Back Link -->
            <div class="back-link">
                <a href="/register" class="text-muted">
                    <i class="fas fa-arrow-left"></i> Back to Registration
                </a>
                <span class="mx-2">|</span>
                <a href="/login" class="text-muted">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
            
            <!-- Help Section -->
            <div class="alert alert-secondary mt-4">
                <h6><i class="fas fa-question-circle"></i> Need Help?</h6>
                <p class="mb-0">
                    <strong>Contact Support:</strong><br>
                    Phone: <?php echo ADMIN_PHONE ?? '+254712345678'; ?><br>
                    Email: <?php echo ADMIN_EMAIL ?? 'info@shenacompanion.org'; ?>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-uppercase transaction code
        document.getElementById('transactionCode').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
        
        // Format phone number
        document.getElementById('phoneNumber').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 10);
        });
        
        // Handle form submission
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const verifyBtn = document.getElementById('verifyBtn');
            const statusDiv = document.getElementById('verificationStatus');
            const form = e.target;
            
            // Disable button
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner"></span> Verifying...';
            
            // Show loading status
            statusDiv.className = 'loading';
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying your payment...';
            
            // Submit verification
            const formData = new FormData(form);
            
            fetch('/verify-transaction', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success
                    statusDiv.className = 'success';
                    statusDiv.innerHTML = `
                        <i class="fas fa-check-circle"></i> <strong>Success!</strong><br>
                        ${data.message}<br>
                        ${data.member_number ? '<strong>Member Number: ' + data.member_number + '</strong><br>' : ''}
                        <small>Redirecting to login...</small>
                    `;
                    
                    // Redirect to login after 3 seconds
                    setTimeout(() => {
                        window.location.href = data.redirect || '/login';
                    }, 3000);
                } else {
                    // Show error
                    statusDiv.className = 'error';
                    statusDiv.innerHTML = `
                        <i class="fas fa-exclamation-circle"></i> <strong>Verification Failed</strong><br>
                        ${data.message}
                    `;
                    
                    // Re-enable button
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify Payment';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusDiv.className = 'error';
                statusDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i> <strong>Error</strong><br>
                    Network error. Please check your connection and try again.
                `;
                
                // Re-enable button
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify Payment';
            });
        });
    </script>
</body>
</html>
