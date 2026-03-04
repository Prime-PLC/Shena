<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Access - SHENA Companion</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .login-container {
            display: flex;
            min-height: 100vh;
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, rgba(127, 61, 158, 0.95) 0%, rgba(94, 43, 122, 0.95) 100%),
                        url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1200&q=80') center/cover;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: visible;
            min-height: 100vh;
        }
        
        .left-panel::before {
            content: '';
            position: absolute;
            top: 60px;
            left: 60px;
            width: 380px;
            height: 260px;
            display: grid;
            grid-template-columns: repeat(3, 120px);
            grid-template-rows: repeat(2, 120px);
            gap: 8px;
            opacity: 0.15;
            pointer-events: none;
        }
        
        .left-panel::before {
            background: 
                linear-gradient(white, white) 0 0 / 120px 120px,
                linear-gradient(white, white) 128px 0 / 120px 120px,
                linear-gradient(white, white) 256px 0 / 120px 120px,
                linear-gradient(white, white) 0 128px / 120px 120px,
                linear-gradient(white, white) 128px 128px / 120px 120px,
                linear-gradient(white, white) 256px 128px / 120px 120px;
            background-repeat: no-repeat;
            border-radius: 8px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 60px;
            position: relative;
            z-index: 1;
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-text {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            color: white;
            line-height: 1.1;
            margin-bottom: 30px;
        }
        
        .hero-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            line-height: 1.7;
            max-width: 450px;
            margin-bottom: 40px;
        }
        
        .mission-btn {
            background: white;
            color: #7F3D9E;
            padding: 14px 35px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        
        .mission-btn:hover {
            transform: translateY(-2px);
            color: #7F3D9E;
        }
        
        .footer-text {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            position: relative;
            z-index: 1;
        }
        
        .right-panel {
            flex: 1;
            background: #F7F7F9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            min-height: 100vh;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 50px 60px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-height: calc(100vh - 80px);
            overflow: auto;
        }
        
        .portal-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #9C27B0 0%, #7F3D9E 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        
        .portal-icon i {
            color: white;
            font-size: 2rem;
        }
        
        .login-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #1A1A1A;
            text-align: center;
            margin-bottom: 12px;
        }
        
        .subtitle {
            text-align: center;
            color: #6B7280;
            font-size: 0.95rem;
            margin-bottom: 40px;
        }
        
        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 40px;
            border-bottom: 2px solid #E5E7EB;
        }
        
        .tab {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: #6B7280;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: #7F3D9E;
            border-bottom-color: #7F3D9E;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            color: #1A1A1A;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #7F3D9E;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            cursor: pointer;
        }
        
        .forgot-password {
            color: #7F3D9E;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            float: right;
            margin-bottom: 8px;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #9C27B0 0%, #7F3D9E 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(127, 61, 158, 0.3);
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(127, 61, 158, 0.4);
        }
        
        .register-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #E5E7EB;
        }
        
        .register-text {
            color: #6B7280;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        
        .register-btn {
            width: 100%;
            background: transparent;
            color: #7F3D9E;
            padding: 14px;
            border: 2px solid #7F3D9E;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .register-btn:hover {
            background: #F3E8FF;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
        }
        
        .footer-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6B7280;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: #7F3D9E;
        }
        
        .copyright {
            text-align: center;
            color: #9CA3AF;
            font-size: 0.75rem;
            margin-top: 24px;
        }
        
        .copyright a {
            color: #7F3D9E;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .left-panel {
                min-height: 40vh;
                padding: 40px 30px;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .login-card {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div>
                <div class="logo-section">
                    <div class="logo-icon">
                        <img src="/public/images/shena-logo.png" alt="SHENA" style="width: 35px; height: 35px; object-fit: contain;">
                    </div>
                    <span class="logo-text">SHENA Companion</span>
                </div>
                
                <div class="hero-content">
                    <h1>We Are Royal</h1>
                    <p>Protecting your family's dignity with compassion. Join our mission-driven welfare association.</p>
                    <a href="/about" class="mission-btn">Our Mission</a>
                </div>
            </div>
            
            <div class="footer-text">
                ESTABLISHED FOR COMMUNITIES ACROSS KENYA
            </div>
        </div>
        
        <!-- Right Panel -->
        <div class="right-panel">
            <div class="login-card">
                <div class="portal-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                
                <h2>Portal Access</h2>
                <p class="subtitle">Manage your association membership</p>
                
                <?php
                // Display session-based error/success (set by controllers on redirect)
                if (!empty($_SESSION['error'])) :
                ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php
                endif;

                if (!empty($_SESSION['success'])) :
                ?>
                    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php
                endif;

                // Display errors passed directly to the view
                if (!empty($errors)) :
                    if (is_array($errors)) :
                ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                        <?php foreach ($errors as $err) : ?>
                            <li><?php echo htmlspecialchars($err, ENT_QUOTES); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php
                    else :
                ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errors, ENT_QUOTES); ?></div>
                <?php
                    endif;
                endif;

                if (!empty($success)) :
                ?>
                    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></div>
                <?php endif; ?>

                <?php
                // Inactive member: show special message and optional redirect to payment
                if (!empty($inactive) || !empty($reactivate_url)) :
                    $payUrl = htmlspecialchars($reactivate_url ?? $payment_url ?? '/payments', ENT_QUOTES);
                    $inactiveMsg = htmlspecialchars($inactive_message ?? 'Your membership is inactive. Please complete payment to reactivate.', ENT_QUOTES);
                ?>
                    <div class="alert alert-warning" role="alert" id="inactiveAlert">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Membership Inactive:</strong>
                                <div><?php echo $inactiveMsg; ?></div>
                            </div>
                            <div class="text-end">
                                <a href="<?php echo $payUrl; ?>" class="btn btn-sm btn-primary">Reactivate Now</a>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">You will be redirected to the payment page in <span id="redirectSeconds">5</span> seconds...</div>
                    </div>
                    <script>
                        (function(){
                            var secondsEl = document.getElementById('redirectSeconds');
                            var sec = 5;
                            var target = <?php echo json_encode($payUrl); ?>;
                            var t = setInterval(function(){
                                sec -= 1;
                                if (secondsEl) secondsEl.textContent = sec;
                                if (sec <= 0) {
                                    clearInterval(t);
                                    try { window.location.href = target; } catch(e){}
                                }
                            }, 1000);
                        })();
                    </script>
                <?php endif; ?>

                <div class="tabs">
                    <div class="tab active">Sign In</div>
                    <div class="tab" onclick="window.location.href='/register'">Register</div>
                </div>
                
                <form method="POST" action="/login" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Member ID / Email</label>
                           <input type="text" name="email" class="form-control" placeholder="Enter your ID or email" required
                               value="<?php echo htmlspecialchars($email ?? $_POST['email'] ?? ($_SESSION['email'] ?? ''), ENT_QUOTES); ?>" aria-describedby="emailHelp">
                           <?php if (!empty($_SESSION['email'])) { unset($_SESSION['email']); } ?>
                    </div>
                    
                    <div class="form-group">
                        <a href="#" class="forgot-password">Forget Password?</a>
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-btn">Login to Dashboard</button>
                </form>
                
                <div class="register-section">
                    <p class="register-text">New to SHENA Companion? Join our welfare association today.</p>
                    <a href="/register" class="register-btn">Start Registration</a>
                </div>
                
                <div class="footer-links">
                    <a href="/admin/login" class="footer-link">
                        <i class="fas fa-user-shield"></i>
                        Agent Login
                    </a>
                    <a href="/admin/login" class="footer-link">
                        <i class="fas fa-briefcase"></i>
                        Staff Portal
                    </a>
                </div>
                
                <div class="copyright">
                    © 2024 SHENA Companion. All Rights Reserved. 
                    <a href="#">Privacy Policy</a> | 
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus email input if present; focus password if email filled
        (function(){
            try {
                var email = document.querySelector('input[name="email"]');
                var password = document.getElementById('password');
                var alertEl = document.querySelector('.alert');
                if (alertEl) {
                    // focus first input and make alert dismissible after 6s
                    if (email) email.focus();
                    setTimeout(function(){ alertEl.style.display = 'none'; }, 6000);
                } else if (email && email.value) {
                    if (password) password.focus(); else email.focus();
                } else if (email) {
                    email.focus();
                }
            } catch(e) {}
        })();

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
