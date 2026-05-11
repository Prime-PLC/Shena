<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sign In'; ?> - Shena Companion</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: #F5F5F7;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Left Panel */
        .left-panel {
            flex: 1 1 50%;
            width: 50%;
            min-width: 0;
            min-height: 100vh;
            min-height: 100dvh;
            background: linear-gradient(180deg, #8B5CF6 0%, #7C3AED 50%, #6B21A8 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            padding: clamp(28px, 5vw, 60px) clamp(20px, 4vw, 40px);
        }

        .logo-container {
            text-align: center;
            margin-bottom: clamp(28px, 7vh, 60px);
        }

        .logo-icon {
            width: clamp(72px, 10vw, 100px);
            height: clamp(72px, 10vw, 100px);
            margin: 0 auto 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .logo-icon img {
            width: 70%;
            height: 70%;
            object-fit: contain;
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: clamp(30px, 4vw, 42px);
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 0;
            overflow-wrap: anywhere;
        }

        .tagline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(17px, 2.4vw, 22px);
            font-weight: 400;
            font-style: italic;
            color: rgba(255, 255, 255, 0.95);
        }

        .portal-label {
            position: absolute;
            bottom: clamp(20px, 4vh, 40px);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Right Panel */
        .right-panel {
            flex: 1 1 50%;
            width: 50%;
            min-width: 0;
            min-height: 100vh;
            min-height: 100dvh;
            background: #F5F5F7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(24px, 6vw, 60px);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 4vw, 32px);
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 13px;
            color: #9CA3AF;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 11px;
            font-weight: 700;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .password-label-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .forgot-link {
            font-size: 12px;
            color: #8B5CF6;
            text-decoration: none;
            font-weight: 600;
            text-transform: none;
        }

        .forgot-link:hover {
            color: #7C3AED;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            font-size: 13px;
            color: #1F2937;
            transition: all 0.2s;
            background: white;
        }

        .form-control::placeholder {
            color: #D1D5DB;
            font-size: 13px;
        }

        .form-control:focus {
            outline: none;
            border-color: #8B5CF6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .remember-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #8B5CF6;
        }

        .remember-wrapper label {
            font-size: 12px;
            color: #6B7280;
            cursor: pointer;
            margin: 0;
        }

        .btn-signin {
            width: 100%;
            padding: 13px 24px;
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-signin:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
        }

        .btn-signin:active {
            transform: translateY(0);
        }

        .security-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 24px;
            padding: 14px;
            background: #FEF3C7;
            border-left: 3px solid #F59E0B;
            border-radius: 6px;
        }

        .security-notice i {
            color: #F59E0B;
            font-size: 14px;
            margin-top: 2px;
        }

        .security-notice p {
            font-size: 11px;
            color: #92400E;
            margin: 0;
            line-height: 1.5;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: none;
            font-size: 13px;
        }

        .alert-danger {
            background: #FEE2E2;
            color: #DC2626;
        }

        /* Responsive */
        @media (max-width: 992px) {
            body {
                flex-direction: column;
            }

            .left-panel,
            .right-panel {
                width: 100%;
            }

            .left-panel {
                min-height: 32vh;
                min-height: 32dvh;
                padding: 36px 20px;
            }

            .logo-icon {
                width: 80px;
                height: 80px;
            }

            .brand-name {
                font-size: 32px;
            }

            .tagline {
                font-size: 18px;
            }

            .right-panel {
                min-height: auto;
                padding: 40px 20px;
            }
        }

        @media (max-width: 576px) {
            .left-panel {
                min-height: auto;
                padding: 28px 18px 22px;
            }

            .logo-container {
                margin-bottom: 16px;
            }

            .logo-icon {
                width: 64px;
                height: 64px;
                border-radius: 16px;
            }

            .brand-name {
                font-size: 28px;
            }

            .tagline {
                font-size: 16px;
            }

            .portal-label {
                position: static;
                margin-top: 12px;
                text-align: center;
                letter-spacing: 1.5px;
            }

            .right-panel {
                padding: 28px 16px;
            }

            .login-container {
                max-width: 100%;
            }

            .security-notice {
                align-items: flex-start;
            }
        }

        @media (max-height: 720px) and (min-width: 993px) {
            .left-panel,
            .right-panel {
                min-height: 720px;
            }

            .logo-container {
                margin-bottom: 28px;
            }

            .security-notice {
                margin-top: 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Left Panel -->
    <div class="left-panel">
        <div class="logo-container">
            <div class="logo-icon">
                <img src="/public/images/shena-logo.png" alt="SHENA Logo">
            </div>
            <h1 class="brand-name">SHENA Companion</h1>
            <p class="tagline">We Are Royal</p>
        </div>
        
        <div class="portal-label">
            STAFF ADMINISTRATION PORTAL
        </div>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
        <div class="login-container">
            <h2 class="login-title">Sign In</h2>
            <p class="login-subtitle">Please enter your administrative credential to access the portal.</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Admin Email / ID</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="rensheard@neric-2025" 
                               required 
                               autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <div class="password-label-wrapper">
                        <label class="form-label">Password</label>
                        <a href="/admin/forgot-password" class="forgot-link">Forgot Password?</a>
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password">
                    </div>
                </div>

                <div class="remember-wrapper">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember this device</label>
                </div>

                <button type="submit" class="btn-signin">
                    Sign In to Portal
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="security-notice">
                <i class="fas fa-shield-alt"></i>
                <p>All logins are authenticated and stored for security. If you suspect unauthorized access or need to report a suspicious system activity, please contact IT.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on username field
        document.getElementById('username').focus();
        
        // Add loading state to button on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.querySelector('.btn-signin');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
