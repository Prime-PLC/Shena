<?php
$page = '';
$title = $title ?? 'Portal Access - SHENA Companion';
include VIEWS_PATH . '/layouts/header.php';
?>

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
            min-height: 0;
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, rgba(127, 61, 158, 0.92) 0%, rgba(94, 43, 122, 0.92) 100%),
                        url('/public/images/background-image1.jpeg') center/cover no-repeat;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: visible;
            min-height: 560px;
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
            align-items: flex-start;
            justify-content: center;
            padding: 24px;
            min-height: 560px;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 32px 36px;
            width: 100%;
            max-width: 560px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-height: none;
            overflow: visible;
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
            margin-bottom: 20px;
        }

        .auth-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .auth-method-btn {
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            background: white;
            color: #4B5563;
            padding: 10px 12px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .auth-method-btn.active {
            border-color: #7F3D9E;
            background: #F3E8FF;
            color: #7F3D9E;
        }

        .auth-panel {
            display: none;
        }

        .auth-panel.active {
            display: block;
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
            margin-bottom: 16px;
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
            margin-top: 16px;
            padding-top: 14px;
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
            margin-top: 18px;
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
            margin-top: 12px;
        }
        
        .copyright a {
            color: #7F3D9E;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            body {
                overflow: auto;
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
                max-height: none;
                overflow: visible;
            }

            .right-panel {
                align-items: stretch;
                min-height: auto;
            }
        }

        @media (max-width: 576px) {
            .left-panel { min-height: 200px; padding: 24px 20px; }
            .hero-content h1 { font-size: 1.8rem; }
            .login-card { padding: 24px 16px; border-radius: 16px; }
        }
    </style>
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

                if (!empty($_SESSION['info'])) :
                ?>
                    <div class="alert alert-info" role="alert"><?php echo htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?></div>
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

                <!-- ── Step 1: Credentials ───────────────────────────────── -->
                <div id="credStep">
                    <form id="loginForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

                        <div class="form-group">
                            <label class="form-label">National ID or Member Number</label>
                            <input type="text" name="credential" id="credInput" class="form-control"
                                   placeholder="e.g. 12345678 or SWA-001" required autocomplete="username"
                                   value="<?php echo htmlspecialchars($email ?? $_POST['email'] ?? ($_SESSION['email'] ?? ''), ENT_QUOTES); ?>">
                            <small style="color:#9CA3AF; font-size:0.8rem;">You may also use your email address.</small>
                            <?php if (!empty($_SESSION['email'])) { unset($_SESSION['email']); } ?>
                        </div>

                        <div class="form-group">
                            <a href="/forgot-password" class="forgot-password">Forgot Password?</a>
                            <label class="form-label">Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="passwordInput" class="form-control"
                                       placeholder="Enter your password" required autocomplete="current-password">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                            </div>
                        </div>

                        <div id="loginMsg" style="display:none;"></div>

                        <button type="submit" class="login-btn" id="loginBtn">Login &rarr;</button>
                    </form>
                </div>

                <!-- ── Step 2: OTP (shown after password verified) ───────── -->
                <div id="otpStep" style="display:none;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="width:60px; height:60px; background:#F3E8FF; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                            <i class="fas fa-shield-alt" style="color:#7F3D9E; font-size:1.4rem;"></i>
                        </div>
                        <p id="otpHint" style="color:#6B7280; font-size:0.9rem; margin:0;">Enter the 6-digit code sent to your phone.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="text-align:center; display:block;">Verification Code</label>
                        <input type="text" id="otpInput" class="form-control" maxlength="6" inputmode="numeric"
                               placeholder="&mdash; &mdash; &mdash; &mdash; &mdash; &mdash;"
                               style="text-align:center; font-size:1.6rem; letter-spacing:0.5rem; font-weight:700;">
                    </div>

                    <div id="otpMsg" style="display:none;"></div>

                    <p style="text-align:center; font-size:0.84rem; color:#9CA3AF; margin-top:12px;">
                        Didn&rsquo;t receive it?
                        <button type="button" id="resendBtn" style="font-size:0.84rem; color:#7F3D9E; border:none; background:none; cursor:pointer; padding:0;">Resend Code</button>
                        <span id="resendTimer" style="color:#9CA3AF;"></span>
                    </p>
                    <p style="text-align:center; margin-top:4px;">
                        <button type="button" onclick="backToCreds()" style="font-size:0.84rem; color:#6B7280; border:none; background:none; cursor:pointer; padding:0;">&larr; Back</button>
                    </p>
                </div>

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
                    &copy; 2024 SHENA Companion. All Rights Reserved.
                    <a href="#">Privacy Policy</a> |
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var inp  = document.getElementById('passwordInput');
            var icon = document.querySelector('.password-toggle');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
            } else {
                inp.type = 'password';
                icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
            }
        }

        function backToCreds() {
            document.getElementById('otpStep').style.display  = 'none';
            document.getElementById('credStep').style.display = '';
            clearMsg(document.getElementById('otpMsg'));
        }

        function showMsg(el, text, type) {
            el.innerHTML = '<div class="alert alert-' + type + ' py-2 mb-0 small">' + text + '</div>';
            el.style.display = '';
        }
        function clearMsg(el) { el.style.display = 'none'; el.innerHTML = ''; }

        (function () {
            var loginForm   = document.getElementById('loginForm');
            var credStep    = document.getElementById('credStep');
            var otpStep     = document.getElementById('otpStep');
            var loginBtn    = document.getElementById('loginBtn');
            var loginMsg    = document.getElementById('loginMsg');
            var otpInput    = document.getElementById('otpInput');
            var otpMsg      = document.getElementById('otpMsg');
            var otpHint     = document.getElementById('otpHint');
            var resendBtn   = document.getElementById('resendBtn');
            var resendTimer = document.getElementById('resendTimer');
            var csrfToken   = loginForm.querySelector('[name="csrf_token"]').value;
            var lastCredential = '';
            var resendCountdown = null;

            function startResendTimer(seconds) {
                resendBtn.disabled = true;
                clearInterval(resendCountdown);
                var remaining = seconds;
                resendTimer.textContent = ' (' + remaining + 's)';
                resendCountdown = setInterval(function () {
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(resendCountdown);
                        resendBtn.disabled = false;
                        resendTimer.textContent = '';
                    } else {
                        resendTimer.textContent = ' (' + remaining + 's)';
                    }
                }, 1000);
            }

            // ── Step 1: verify credential + password ─────────────────
            loginForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearMsg(loginMsg);
                var credential = document.getElementById('credInput').value.trim();
                var password   = document.getElementById('passwordInput').value;
                if (!credential || !password) {
                    showMsg(loginMsg, 'Please enter your ID / Member Number and password.', 'warning');
                    return;
                }
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verifying...';
                var fd = new FormData();
                fd.append('csrf_token', csrfToken);
                fd.append('credential', credential);
                fd.append('password',   password);
                fetch('/login/verify', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success && data.otp_required) {
                            lastCredential = credential;
                            otpHint.textContent = 'Enter the 6-digit code sent to ' + (data.masked_phone || 'your phone') + '.';
                            credStep.style.display = 'none';
                            otpStep.style.display  = '';
                            otpInput.value = '';
                            otpInput.disabled = false;
                            clearMsg(otpMsg);
                            startResendTimer(60);
                            setTimeout(function () { otpInput.focus(); }, 80);
                        } else if (data.success) {
                            window.location.href = data.redirect || '/dashboard';
                        } else {
                            showMsg(loginMsg, data.message || 'Invalid credentials.', 'danger');
                        }
                    })
                    .catch(function () { showMsg(loginMsg, 'Network error. Please try again.', 'danger'); })
                    .finally(function () {
                        loginBtn.disabled = false;
                        loginBtn.innerHTML = 'Login &rarr;';
                    });
            });

            // ── Step 2: auto-verify OTP when 6 digits entered ────────
            otpInput.addEventListener('input', function () {
                var code = this.value.replace(/\D/g, '');
                this.value = code;
                if (code.length === 6) { verifyOtp(code); }
            });

            function verifyOtp(code) {
                clearMsg(otpMsg);
                otpInput.disabled = true;
                var fd = new FormData();
                fd.append('csrf_token', csrfToken);
                fd.append('otp_code',   code);
                fetch('/login/otp/verify', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            showMsg(otpMsg, '<i class="fas fa-check-circle me-1"></i>' + (data.message || 'Login successful!'), 'success');
                            setTimeout(function () { window.location.href = data.redirect || '/dashboard'; }, 700);
                        } else {
                            showMsg(otpMsg, data.message || 'Invalid code. Try again.', 'danger');
                            otpInput.disabled = false;
                            otpInput.value = '';
                            otpInput.focus();
                        }
                    })
                    .catch(function () {
                        showMsg(otpMsg, 'Network error. Please try again.', 'danger');
                        otpInput.disabled = false;
                    });
            }

            // ── Resend OTP ────────────────────────────────────────────
            resendBtn.addEventListener('click', function () {
                clearMsg(otpMsg);
                resendBtn.disabled = true;
                var fd = new FormData();
                fd.append('csrf_token', csrfToken);
                fd.append('id_number',  lastCredential);
                fetch('/login/otp/send', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            showMsg(otpMsg, data.message || 'New code sent.', 'success');
                            startResendTimer(60);
                        } else {
                            showMsg(otpMsg, data.message || 'Failed to resend.', 'warning');
                            resendBtn.disabled = false;
                        }
                    })
                    .catch(function () {
                        showMsg(otpMsg, 'Could not resend code.', 'danger');
                        resendBtn.disabled = false;
                    });
            });

            // Auto-focus on load
            (function () {
                try {
                    var alertEl   = document.querySelector('.alert');
                    var credInput = document.getElementById('credInput');
                    var passInput = document.getElementById('passwordInput');
                    if (alertEl) { setTimeout(function(){ alertEl.style.display='none'; }, 6000); }
                    if (credInput && credInput.value && passInput) { passInput.focus(); }
                    else if (credInput) { credInput.focus(); }
                } catch(e) {}
            })();
        })();
    </script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
