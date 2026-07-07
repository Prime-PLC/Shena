<?php
// Include form data helper for form persistence on validation errors
require_once __DIR__ . '/../../../app/helpers/FormDataHelper.php';

$notificationCount = $notificationCount ?? null;
if ($notificationCount === null) {
    $notificationCount = 0;
    if (isset($_SESSION['user_id'])) {
        try {
            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare(
                'SELECT COUNT(*) FROM communication_recipients WHERE user_id = :user_id AND status <> "read"'
            );
            $stmt->execute([':user_id' => (int)$_SESSION['user_id']]);
            $notificationCount = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $notificationCount = 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Dashboard'; ?> - Shena Companion</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Modals -->
    <link href="/public/css/modals.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: #F8F9FA;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: white;
            border-right: 1px solid #E5E7EB;
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-logo-text,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-section-title,
        .sidebar.collapsed .admin-info {
            display: none;
        }

        .sidebar.collapsed .sidebar-logo {
            justify-content: center;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
        }

        .sidebar.collapsed .admin-profile {
            justify-content: center;
            width: 50px;
            left: 10px;
        }

        .sidebar.collapsed .nav-submenu {
            display: none !important;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #F3F4F6;
            flex-shrink: 0;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .sidebar-logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
        }

        .sidebar-logo-text span {
            font-weight: 400;
            color: #7F3D9E;
        }

        .sidebar-toggle {
            position: absolute;
            top: 24px;
            right: -15px;
            width: 30px;
            height: 30px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            z-index: 1001;
        }

        .sidebar-toggle:hover {
            background: #F9FAFB;
            transform: scale(1.1);
        }

        .sidebar-toggle i {
            color: #7F3D9E;
            font-size: 14px;
            transition: transform 0.3s;
        }

        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Scrollable Nav Container */
        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav-container::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 4px;
        }

        .sidebar-nav {
            padding: 10px 10px 20px;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #6B7280;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }

        .nav-link:hover {
            background: #F9FAFB;
            color: #7F3D9E;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #F3E8FF 0%, #EDE9FE 100%);
            color: #7F3D9E;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: #7F3D9E;
            border-radius: 0 4px 4px 0;
        }

        .nav-link i {
            font-size: 16px;
            width: 20px;
        }

        .nav-section-title {
            padding: 20px 16px 8px;
            font-size: 11px;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Submenu Styles */
        .nav-submenu {
            display: none;
            padding-left: 32px;
            margin-top: 4px;
        }

        .nav-submenu.show {
            display: block;
        }

        .nav-submenu .nav-link {
            padding: 8px 16px;
            font-size: 13px;
        }

        .nav-link.has-submenu {
            position: relative;
        }

        .nav-link.has-submenu::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 16px;
            transition: transform 0.2s;
        }

        .nav-link.has-submenu.open::after {
            transform: rotate(180deg);
        }

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 999;
            transition: left 0.3s ease;
        }

        .sidebar.collapsed + .top-header {
            left: 70px;
        }

        .search-bar {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .search-bar i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 14px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 16px 10px 44px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            color: #1F2937;
            transition: all 0.2s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #7F3D9E;
            box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
        }

        .search-bar input::placeholder {
            color: #D1D5DB;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Admin Profile in Top Nav */
        .header-admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #F9FAFB;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .header-admin-profile:hover {
            background: #F3F4F6;
        }

        .header-admin-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #F59E0B 0%, #F97316 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .header-admin-info {
            display: flex;
            flex-direction: column;
        }

        .header-admin-name {
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
        }

        .header-admin-role {
            font-size: 11px;
            color: #9CA3AF;
        }

        .header-admin-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 200px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 8px 0;
            display: none;
            z-index: 1000;
        }

        .header-admin-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            background: #F9FAFB;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .notification-btn:hover {
            background: #F3F4F6;
        }

        .notification-btn i {
            color: #6B7280;
            font-size: 18px;
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            background: #EF4444;
            color: white;
            border-radius: 999px;
            border: 2px solid white;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .btn-new-registration {
            padding: 10px 20px;
            background: linear-gradient(135deg, #7F3D9E 0%, #7F3D9E 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-new-registration:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
            transition: margin-left 0.3s ease;
            min-width: 0;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }

        /* Admin Profile (Bottom Pinned) */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid #E5E7EB;
            flex-shrink: 0;
            background: white;
        }

        .admin-profile {
            padding: 12px;
            background: #F9FAFB;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .admin-profile:hover {
            background: #F3F4F6;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #F59E0B 0%, #F97316 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .admin-info {
            flex: 1;
        }

        .admin-name {
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 2px;
        }

        .admin-email {
            font-size: 11px;
            color: #9CA3AF;
        }

        .admin-menu-btn {
            color: #9CA3AF;
            font-size: 16px;
        }

        .admin-profile {
            position: relative;
        }

        /* Admin Dropdown Menu */
        .admin-dropdown-menu {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 0;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 8px 0;
            display: none;
            z-index: 1000;
        }

        .admin-dropdown-menu.show {
            display: block;
            animation: slideUp 0.2s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: #1F2937;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background: #F9FAFB;
            color: #7F3D9E;
        }

        .dropdown-item i {
            width: 18px;
            font-size: 14px;
            text-align: center;
        }

        .dropdown-item.logout-item {
            border-top: 1px solid #E5E7EB;
            margin-top: 4px;
            padding-top: 12px;
            color: #DC2626;
        }

        .dropdown-item.logout-item:hover {
            background: #FEE2E2;
            color: #B91C1C;
        }

        /* Search Results Dropdown */
        .search-results {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }

        .search-results.show {
            display: block;
        }

        .search-result-item {
            padding: 12px 16px;
            border-bottom: 1px solid #F3F4F6;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: #F9FAFB;
        }

        .search-result-title {
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .search-result-subtitle {
            font-size: 12px;
            color: #9CA3AF;
        }

        .search-no-results {
            padding: 20px;
            text-align: center;
            color: #9CA3AF;
            font-size: 14px;
        }

        /* Sidebar overlay / backdrop */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Mobile hamburger button */
        .mobile-menu-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: #F3F4F6;
            border: none;
            border-radius: 8px;
            color: #7F3D9E;
            font-size: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .top-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                width: 260px !important;
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            /* Restore full text in drawer mode even if .collapsed is set */
            .sidebar.collapsed .sidebar-logo-text,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .nav-section-title,
            .sidebar.collapsed .admin-info {
                display: block !important;
            }

            .sidebar.collapsed .sidebar-logo {
                justify-content: flex-start !important;
            }

            .top-header,
            .sidebar.collapsed + .top-header {
                left: 0;
            }

            .main-content,
            .sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: none;
            }

            .header-admin-info {
                display: none;
            }

            .mobile-menu-btn {
                display: inline-flex;
            }
        }

        @media (max-width: 640px) {
            .search-bar {
                display: none;
            }

            .top-header {
                padding: 0 16px;
            }
        }

        /* ── Extra mobile polish (≤ 480 px) ───────────────────────── */
        @media (max-width: 480px) {
            .main-content {
                padding: 16px 12px !important;
                margin-left: 0 !important;
            }
            .top-header {
                padding: 0 12px !important;
                height: 58px !important;
            }
            /* Push content down for shorter header */
            .main-content { margin-top: 58px !important; }

            /* Make stat/info cards single-column */
            .stats-grid,
            .stat-cards,
            [class*="stats-grid"],
            [class*="stat-cards"] {
                grid-template-columns: 1fr 1fr !important;
                gap: 10px !important;
            }

            /* Tables: wrap for horizontal scroll */
            .table-responsive,
            table.table { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }

            /* Card/section padding */
            .card, .content-card, .section-card { padding: 14px !important; }

            /* Page heading */
            .page-header h1,
            .page-title { font-size: 1.2rem !important; }

            /* Action buttons row: wrap & full width */
            .header-actions-row,
            .page-actions { flex-wrap: wrap; gap: 8px; }
            .page-actions .btn,
            .btn-new-registration { width: 100% !important; justify-content: center; }
        }

        /* Single-column on very narrow (< 360 px) */
        @media (max-width: 360px) {
            .stats-grid,
            .stat-cards,
            [class*="stats-grid"],
            [class*="stat-cards"] {
                grid-template-columns: 1fr !important;
            }
        }

        /* ── Universal table & flex overflows (all breakpoints) ── */
        @media (max-width: 992px) {
            /* Every table scrolls horizontally instead of pushing layout */
            .main-content table {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Inline flex page-headers: stack vertically on mobile */
            .page-header [style*="display: flex"],
            .main-content > [style*="display: flex"],
            .modern-card > [style*="display: flex"] {
                flex-wrap: wrap !important;
                gap: 8px !important;
            }

            /* Button rows: wrap and stretch */
            [style*="display: flex"][style*="gap"] > .modern-btn,
            [style*="display: flex"][style*="gap"] > .btn {
                flex: 1 1 auto;
                min-width: 0;
            }

            /* Page containers: never wider than viewport
               NOTE: overflow-x:hidden is NOT set here because it would clip
               nested table-scroll-wrap overflow-x:auto containers.
               The body-level overflow-x:hidden already prevents page-level scroll. */
            .main-content > div,
            .main-content > section {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile sidebar overlay/backdrop -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="/public/images/shena-logo.png" alt="SHENA Logo">
                <div class="sidebar-logo-text">
                    SHENA <span>Companion</span>
                </div>
            </div>
            <div class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-chevron-left"></i>
            </div>
        </div>

        <div class="sidebar-nav-container">
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin' || $_SERVER['REQUEST_URI'] == '/admin/dashboard') ? 'active' : ''; ?>" href="/admin/dashboard">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Member Management -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/members') !== false) ? 'active' : ''; ?>" href="/admin/members">
                            <i class="fas fa-users"></i>
                            <span>Member Management</span>
                        </a>
                    </li>

                    <!-- Agent Management -->
                    <li class="nav-item">
                        <a class="nav-link has-submenu <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/agents') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/agent-') !== false) ? 'active' : ''; ?>" href="#" onclick="toggleSubmenu(event, 'agents-submenu')">
                            <i class="fas fa-user-tie"></i>
                            <span>Agent Management</span>
                        </a>
                        <ul class="nav-submenu" id="agents-submenu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin/agents') ? 'active' : ''; ?>" href="/admin/agents">
                                    <i class="fas fa-users-cog"></i>
                                    <span>Manage Agents</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'agents/resources') !== false) ? 'active' : ''; ?>" href="/admin/agents/resources">
                                    <i class="fas fa-folder-open"></i>
                                    <span>Resource Library</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Claims -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/claims') !== false) ? 'active' : ''; ?>" href="/admin/claims">
                            <i class="fas fa-file-medical"></i>
                            <span>Claims</span>
                        </a>
                    </li>

                    <!-- Payments -->
                    <li class="nav-item">
                        <a class="nav-link has-submenu <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/financial') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/plan-upgrades') !== false) ? 'active' : ''; ?>" href="#" onclick="toggleSubmenu(event, 'payments-submenu')">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payments</span>
                        </a>
                        <ul class="nav-submenu" id="payments-submenu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin/payments') ? 'active' : ''; ?>" href="/admin/payments">
                                    <i class="fas fa-list"></i>
                                    <span>All Payments</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/payments/breakdown') !== false) ? 'active' : ''; ?>" href="/admin/payments/breakdown">
                                    <i class="fas fa-table-cells"></i>
                                    <span>Payment Breakdown</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'payments/reconciliation') !== false || strpos($_SERVER['REQUEST_URI'], 'payments-reconciliation') !== false) ? 'active' : ''; ?>" href="/admin/payments-reconciliation">
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Reconciliation</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'financial-dashboard') !== false) ? 'active' : ''; ?>" href="/admin/financial-dashboard">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Financial Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'plan-upgrades') !== false) ? 'active' : ''; ?>" href="/admin/plan-upgrades">
                                    <i class="fas fa-arrow-up-right-dots"></i>
                                    <span>Plan Upgrades</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Communications -->
                    <li class="nav-item">
                        <a class="nav-link has-submenu <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/email-campaigns') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/sms-campaigns') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/notifications') !== false) ? 'active' : ''; ?>" href="#" onclick="toggleSubmenu(event, 'communications-submenu')">
                            <i class="fas fa-comments"></i>
                            <span>Communications</span>
                        </a>
                        <ul class="nav-submenu" id="communications-submenu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'email-campaigns') !== false) ? 'active' : ''; ?>" href="/admin/email-campaigns">
                                    <i class="fas fa-envelope"></i>
                                    <span>Email Campaigns</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'sms-campaigns') !== false) ? 'active' : ''; ?>" href="/admin/sms-campaigns">
                                    <i class="fas fa-sms"></i>
                                    <span>SMS Campaigns</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/notifications') !== false) ? 'active' : ''; ?>" href="/admin/notifications">
                                    <i class="fas fa-bell"></i>
                                    <span>Notifications</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Reports and Analysis -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false) ? 'active' : ''; ?>" href="/admin/reports">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports & Analysis</span>
                        </a>
                    </li>

                    <!-- System Settings -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false) ? 'active' : ''; ?>" href="/admin/settings">
                            <i class="fas fa-cog"></i>
                            <span>System Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Admin Profile (Bottom Pinned) -->
        <div class="sidebar-footer">
            <div class="admin-profile" onclick="toggleAdminMenu(event)">
                <div class="admin-avatar">AD</div>
                <div class="admin-info">
                    <div class="admin-name">Admin Director</div>
                    <div class="admin-email">admin@shena.com</div>
                </div>
                <i class="fas fa-ellipsis-v admin-menu-btn"></i>
                
                <!-- Dropdown Menu -->
                <div class="admin-dropdown-menu" id="adminDropdownMenu">
                    <a href="/admin/settings" class="dropdown-item">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile Settings</span>
                    </a>
                    <a href="/logout" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Header -->
    <div class="top-header">
        <div class="top-header-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="openMobileSidebar()" aria-label="Open menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="adminSearch" placeholder="Search members, claims, payments..." onkeyup="searchAdmin(this.value)">
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <div class="header-actions">
            <a href="/admin/notifications" class="notification-btn">
                <i class="fas fa-bell"></i>
                <?php if (!empty($notificationCount)): ?>
                    <span class="notification-badge"><?php echo (int)$notificationCount; ?></span>
                <?php endif; ?>
            </a> 

            <!-- Admin Profile in Top Nav -->
            <div class="header-admin-profile" onclick="toggleHeaderAdminMenu(event)">
                <div class="header-admin-avatar">AD</div>
                <div class="header-admin-info">
                    <div class="header-admin-name">Admin Director</div>
                    <div class="header-admin-role">System Administrator</div>
                </div>
                <i class="fas fa-chevron-down" style="color: #9CA3AF; font-size: 12px;"></i>
                
                <!-- Header Admin Dropdown -->
                <div class="header-admin-dropdown" id="headerAdminDropdown">
                    <a href="/admin/settings" class="dropdown-item">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile Settings</span>
                    </a>
                    <a href="/logout" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (function_exists('renderFlashMessagesScript')) { renderFlashMessagesScript(5000); } ?>

    <script>
        // Sidebar Toggle Functionality
        function isMobileLayout() {
            return window.innerWidth <= 992;
        }

        function openMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Toggle sidebar collapse (desktop) or drawer (mobile)
        function toggleSidebar() {
            if (isMobileLayout()) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
                return;
            }
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (!isMobileLayout()) {
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (sidebarCollapsed) {
                    document.getElementById('sidebar').classList.add('collapsed');
                }
            }

            // Close the mobile drawer when a nav link is tapped
            document.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (isMobileLayout() && !this.classList.contains('has-submenu')) {
                        closeMobileSidebar();
                    }
                });
            });
        });

        // Toggle admin dropdown menu in sidebar
        function toggleAdminMenu(event) {
            event.stopPropagation();
            const menu = document.getElementById('adminDropdownMenu');
            menu.classList.toggle('show');
        }

        // Toggle admin dropdown menu in header
        function toggleHeaderAdminMenu(event) {
            event.stopPropagation();
            const menu = document.getElementById('headerAdminDropdown');
            menu.classList.toggle('show');
        }

        // Close admin menus when clicking outside
        document.addEventListener('click', function(event) {
            const sidebarMenu = document.getElementById('adminDropdownMenu');
            const sidebarProfile = document.querySelector('.sidebar-footer .admin-profile');
            const headerMenu = document.getElementById('headerAdminDropdown');
            const headerProfile = document.querySelector('.header-admin-profile');
            
            if (sidebarMenu && sidebarProfile && !sidebarProfile.contains(event.target)) {
                sidebarMenu.classList.remove('show');
            }
            
            if (headerMenu && headerProfile && !headerProfile.contains(event.target)) {
                headerMenu.classList.remove('show');
            }
        });

        // Toggle submenu function
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            const parentLink = event.currentTarget;
            
            // Toggle the submenu visibility
            submenu.classList.toggle('show');
            parentLink.classList.toggle('open');
            
            // Close other submenus
            document.querySelectorAll('.nav-submenu').forEach(menu => {
                if (menu.id !== submenuId) {
                    menu.classList.remove('show');
                }
            });
            
            document.querySelectorAll('.nav-link.has-submenu').forEach(link => {
                if (link !== parentLink) {
                    link.classList.remove('open');
                }
            });
        }
        
        // Auto-expand active submenu on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeLinks = document.querySelectorAll('.nav-submenu .nav-link.active');
            activeLinks.forEach(link => {
                const submenu = link.closest('.nav-submenu');
                if (submenu) {
                    submenu.classList.add('show');
                    const parentLink = submenu.previousElementSibling;
                    if (parentLink) {
                        parentLink.classList.add('open');
                    }
                }
            });
        });

        // Admin Search Functionality
        const searchableItems = [
            // Dashboard
            { title: 'Dashboard', subtitle: 'Analytics & Overview', url: '/admin/dashboard' },
            
            // Members
            { title: 'Member Management', subtitle: 'View & Manage Members', url: '/admin/members' },
            { title: 'Register New Member', subtitle: 'Members', url: '/admin/members/register' },
            { title: 'Add Member', subtitle: 'Members', url: '/admin/members/register' },
            
            // Agents
            { title: 'Manage Agents', subtitle: 'Agent Management', url: '/admin/agents' },
            { title: 'Agent Portal', subtitle: 'Agent Management', url: '/admin/agents' },
            { title: 'Resource Library', subtitle: 'Agent Resources', url: '/admin/agents/resources' },
            { title: 'Agent Resources', subtitle: 'Agent Management', url: '/admin/agents/resources' },
            
            // Claims
            { title: 'Claims Management', subtitle: 'Claims', url: '/admin/claims' },
            { title: 'All Claims', subtitle: 'Claims', url: '/admin/claims' },
            { title: 'Submit Claim', subtitle: 'Claims', url: '/admin/claims/submit' },
            { title: 'New Claim', subtitle: 'Claims', url: '/admin/claims/submit' },
            
            // Payments
            { title: 'All Payments', subtitle: 'Payments', url: '/admin/payments' },
            { title: 'Payment Breakdown', subtitle: 'Payments', url: '/admin/payments/breakdown' },
            { title: 'Paid', subtitle: 'Payments', url: '/admin/payments/breakdown?group=paid_current' },
            { title: 'Not Paid', subtitle: 'Payments', url: '/admin/payments/breakdown?group=unpaid_current' },
            { title: 'In Arrears', subtitle: 'Payments', url: '/admin/payments/breakdown?group=in_arrears' },
            { title: 'Payment History', subtitle: 'Payments', url: '/admin/payments' },
            { title: 'Reconciliation', subtitle: 'Payments', url: '/admin/payments-reconciliation' },
            { title: 'Payment Reconciliation', subtitle: 'Payments', url: '/admin/payments-reconciliation' },
            { title: 'Financial Dashboard', subtitle: 'Payments', url: '/admin/financial-dashboard' },
            { title: 'Finance Dashboard', subtitle: 'Payments', url: '/admin/financial-dashboard' },
            { title: 'M-Pesa Payments', subtitle: 'Payments', url: '/admin/payments' },
            
            // Communications
            { title: 'Communications', subtitle: 'Communications Hub', url: '/admin/communications' },
            { title: 'Email Campaigns', subtitle: 'Communications', url: '/admin/email-campaigns' },
            { title: 'Bulk Email', subtitle: 'Communications', url: '/admin/email-campaigns' },
            { title: 'SMS Campaigns', subtitle: 'Communications', url: '/admin/sms-campaigns' },
            { title: 'Bulk SMS', subtitle: 'Communications', url: '/admin/sms-campaigns' },
            { title: 'Notifications', subtitle: 'Communications', url: '/admin/notifications' },
            { title: 'System Notifications', subtitle: 'Communications', url: '/admin/notifications' },
            { title: 'Notification Logs', subtitle: 'Communications', url: '/admin/notifications' },
            
            // Reports
            { title: 'Reports & Analysis', subtitle: 'Reports', url: '/admin/reports' },
            { title: 'Analytics', subtitle: 'Reports', url: '/admin/reports' },
            { title: 'System Reports', subtitle: 'Reports', url: '/admin/reports' },
            
            // Settings
            { title: 'System Settings', subtitle: 'Settings', url: '/admin/settings' },
            { title: 'Settings', subtitle: 'Configuration', url: '/admin/settings' },
            { title: 'Notification Settings', subtitle: 'Settings', url: '/admin/notification-settings' },
            { title: 'Email Settings', subtitle: 'Settings', url: '/admin/settings' },
            { title: 'SMS Settings', subtitle: 'Settings', url: '/admin/settings' },
            { title: 'Payment Settings', subtitle: 'Settings', url: '/admin/settings' },
            { title: 'M-Pesa Configuration', subtitle: 'Settings', url: '/admin/settings' },
            { title: 'Security Settings', subtitle: 'Settings', url: '/admin/settings' }
        ];

        function searchAdmin(query) {
            const resultsContainer = document.getElementById('searchResults');
            
            if (!query.trim()) {
                resultsContainer.classList.remove('show');
                return;
            }

            const filtered = searchableItems.filter(item => 
                item.title.toLowerCase().includes(query.toLowerCase()) ||
                item.subtitle.toLowerCase().includes(query.toLowerCase())
            );

            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<div class="search-no-results">No results found</div>';
            } else {
                resultsContainer.innerHTML = filtered.map(item => `
                    <div class="search-result-item" onclick="window.location.href='${item.url}'">
                        <div class="search-result-title">${item.title}</div>
                        <div class="search-result-subtitle">${item.subtitle}</div>
                    </div>
                `).join('');
            }

            resultsContainer.classList.add('show');
        }

        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            const searchBar = document.querySelector('.search-bar');
            const resultsContainer = document.getElementById('searchResults');
            
            if (searchBar && !searchBar.contains(event.target)) {
                resultsContainer.classList.remove('show');
            }
        });
    </script>
