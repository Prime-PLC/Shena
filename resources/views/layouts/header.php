<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'SHENA Companion - Dignified Send-off, Lasting Support'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Modals -->
    <link href="/public/css/modals.css" rel="stylesheet">
    <!-- SHENA Design System CSS -->
    <link href="/public/css/shena-main.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/public/images/shena-logo.png">
    <style>
        /* ── Public Nav – design-token driven ─────────────────────── */
        .shena-public-nav { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .shena-nav-links .nav-link {
            color: var(--shena-dark-text, #1F2937);
            font-family: var(--font-body, 'Manrope', sans-serif);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: color 0.2s ease;
            position: relative;
        }
        .shena-nav-links .nav-link::after {
            content: '';
            position: absolute;
            left: 1rem; right: 1rem; bottom: 4px;
            height: 2px;
            background: var(--shena-primary-purple, #7F3D9E);
            transform: scaleX(0);
            transition: transform 0.2s ease;
        }
        .shena-nav-links .nav-link:hover,
        .shena-nav-links .nav-link.active {
            color: var(--shena-primary-purple, #7F3D9E);
        }
        .shena-nav-links .nav-link.active::after { transform: scaleX(1); }

        /* Nav Action Buttons */
        .shena-nav-btn-login {
            color: var(--shena-primary-purple, #7F3D9E);
            background: transparent;
            border: 2px solid var(--shena-primary-purple, #7F3D9E);
            padding: 9px 26px;
            border-radius: 25px;
            font-weight: 500;
            white-space: nowrap;
            transition: background 0.2s, color 0.2s;
            font-family: var(--font-body, 'Manrope', sans-serif);
        }
        .shena-nav-btn-login:hover {
            background: var(--shena-primary-purple, #7F3D9E);
            color: #fff;
        }
        .shena-nav-btn-register {
            background: var(--shena-primary-purple, #7F3D9E);
            color: #fff;
            border: none;
            padding: 10px 26px;
            border-radius: 25px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.2s;
            font-family: var(--font-body, 'Manrope', sans-serif);
        }
        .shena-nav-btn-register:hover { background: #6A2F87; color: #fff; }
        .shena-nav-btn-dashboard {
            background: var(--shena-primary-purple, #7F3D9E);
            color: #fff;
            padding: 8px 18px;
            border-radius: 999px;
            font-weight: 600;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
            font-family: var(--font-body, 'Manrope', sans-serif);
        }
        .shena-nav-btn-dashboard:hover { background: #6A2F87; color: #fff; }
        .shena-nav-btn-logout {
            background: transparent;
            color: var(--shena-primary-purple, #7F3D9E);
            padding: 8px 18px;
            border-radius: 999px;
            font-weight: 600;
            border: 1px solid var(--shena-primary-purple, #7F3D9E);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s, color 0.2s;
            font-family: var(--font-body, 'Manrope', sans-serif);
        }
        .shena-nav-btn-logout:hover { background: var(--shena-primary-purple, #7F3D9E); color: #fff; }
        .shena-nav-btn-admin {
            background: #F6F1FA;
            color: #5A1F73;
            padding: 8px 18px;
            border-radius: 999px;
            font-weight: 600;
            border: 1px solid var(--shena-accent-gold, #C9A659);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
            font-family: var(--font-body, 'Manrope', sans-serif);
        }
        .shena-nav-btn-admin:hover { background: #EDE3F8; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top shena-public-nav" style="padding: 0.75rem 0;">
        <div class="container">
            <a class="navbar-brand" href="/" style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 0;">
                <img src="/public/images/shena-logo.png" alt="SHENA Companion" style="height: 80px; width: auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="shena-hamburger" aria-hidden="true">
                    <span class="shena-hamburger-line"></span>
                    <span class="shena-hamburger-line"></span>
                    <span class="shena-hamburger-line"></span>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto align-items-center shena-nav-links" style="gap: 0.5rem;">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'home' ? 'active' : ''; ?>" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'about' ? 'active' : ''; ?>" href="/about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'services' ? 'active' : ''; ?>" href="/services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'membership' ? 'active' : ''; ?>" href="/membership">Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'gallery' ? 'active' : ''; ?>" href="/gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'contact' ? 'active' : ''; ?>" href="/contact">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center shena-nav-actions" style="gap: 0.5rem;">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="btn shena-nav-btn-admin" href="/admin">
                                    <i class="fas fa-cog"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="btn shena-nav-btn-dashboard" href="/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn shena-nav-btn-logout" href="/logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item" style="margin-right: 8px;">
                            <a href="/login" class="btn shena-nav-btn-login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a href="/register" class="btn shena-nav-btn-register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (function_exists('renderFlashMessagesScript')) { renderFlashMessagesScript(5000); } ?>

    <main>
