<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Dashboard'; ?> - Shena Companion</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            border-radius: 0.35rem;
            margin: 0.1rem 0.5rem;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .navbar {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .text-primary {
            color: #4e73df !important;
        }
        
        .text-success {
            color: #1cc88a !important;
        }
        
        .text-info {
            color: #36b9cc !important;
        }
        
        .text-warning {
            color: #f6c23e !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        /* Dropdown submenu styling */
        .sidebar .collapse {
            transition: height 0.3s ease;
        }
        
        .sidebar .nav-link[data-bs-toggle="collapse"] {
            position: relative;
        }
        
        .sidebar .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
            position: absolute;
            right: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .sidebar .nav-link[data-bs-toggle="collapse"]:not(.collapsed) .fa-chevron-down {
            transform: rotate(180deg);
        }
        
        .sidebar .collapse .nav-link {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }
        
        .sidebar .collapse .nav-link i {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Shena Companion</h5>
                        <small class="text-white-50">Admin Portal</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin' || $_SERVER['REQUEST_URI'] == '/admin/dashboard') ? 'active' : ''; ?>" href="/admin">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <!-- Member Management -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/members') !== false) ? 'active' : ''; ?>" href="/admin/members">
                                <i class="fas fa-users"></i>
                                Member Management
                            </a>
                        </li>
                        
                        <!-- Agent Management -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/agents') !== false) ? 'active' : ''; ?>" href="/admin/agents">
                                <i class="fas fa-user-tie"></i>
                                Agent Management
                            </a>
                        </li>
                        
                        <!-- Claims -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/claims') !== false) ? 'active' : ''; ?>" href="/admin/claims">
                                <i class="fas fa-file-medical"></i>
                                Claims
                            </a>
                        </li>
                        
                        <!-- Payments -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/reconciliation') !== false) ? 'active' : ''; ?>" 
                               href="#" 
                               data-bs-toggle="collapse" 
                               data-bs-target="#paymentsMenu" 
                               aria-expanded="false">
                                <i class="fas fa-money-bill-wave"></i>
                                Payments
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/reconciliation') !== false) ? 'show' : ''; ?>" id="paymentsMenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin/payments') ? 'active' : ''; ?>" href="/admin/payments">
                                            <i class="fas fa-list"></i> All Payments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments/reconciliation') !== false) ? 'active' : ''; ?>" href="/admin/payments/reconciliation">
                                            <i class="fas fa-balance-scale"></i> Reconciliation
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments/pending') !== false) ? 'active' : ''; ?>" href="/admin/payments/pending">
                                            <i class="fas fa-clock"></i> Pending Payments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments/failed') !== false) ? 'active' : ''; ?>" href="/admin/payments/failed">
                                            <i class="fas fa-exclamation-triangle"></i> Failed Payments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/plan-upgrades') !== false) ? 'active' : ''; ?>" href="/admin/plan-upgrades">
                                            <i class="fas fa-arrow-up-right-dots"></i> Plan Upgrades
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        
                        <!-- Communications -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/communications') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/email-campaigns') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/bulk-sms') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/notifications') !== false) ? 'active' : ''; ?>" href="/admin/communications">
                                <i class="fas fa-comments"></i>
                                Communications
                            </a>
                        </li>
                        
                        <!-- Reports & Analytics -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/financial-dashboard') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/analytics') !== false) ? 'active' : ''; ?>" href="/admin/reports">
                                <i class="fas fa-chart-line"></i>
                                Reports & Analytics
                            </a>
                        </li>
                        
                        <!-- System Settings -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/notification-settings') !== false) ? 'active' : ''; ?>" href="/admin/settings">
                                <i class="fas fa-cog"></i>
                                System Settings
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                            <a class="nav-link" href="/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user-circle me-2"></i>
                                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="/admin/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success']) || isset($_SESSION['error']) || isset($_SESSION['warning']) || isset($_SESSION['info'])): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const flashMessages = [
                                <?php if (isset($_SESSION['success'])): ?>{ type: 'success', message: <?php echo json_encode($_SESSION['success']); ?> },<?php unset($_SESSION['success']); endif; ?>
                                <?php if (isset($_SESSION['error'])): ?>{ type: 'error', message: <?php echo json_encode($_SESSION['error']); ?> },<?php unset($_SESSION['error']); endif; ?>
                                <?php if (isset($_SESSION['warning'])): ?>{ type: 'warning', message: <?php echo json_encode($_SESSION['warning']); ?> },<?php unset($_SESSION['warning']); endif; ?>
                                <?php if (isset($_SESSION['info'])): ?>{ type: 'info', message: <?php echo json_encode($_SESSION['info']); ?> },<?php unset($_SESSION['info']); endif; ?>
                            ];

                            flashMessages.forEach(function(flash) {
                                if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                                    ShenaApp.alert(flash.message, flash.type);
                                    return;
                                }
                                if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                                    ShenaApp.showNotification(flash.message, flash.type, 5000);
                                    return;
                                }
                                console.warn(flash.message);
                            });
                        });
                    </script>
                <?php endif; ?>
