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
    <title><?php echo $title ?? 'Agent Portal - Shena Companion'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/modals.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            background: #F8F9FA;
            font-family: 'Manrope', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #4A1468 0%, #7F20B0 50%, #4A1468 100%);
            padding: 0;
            z-index: 1000;
            overflow: hidden;
            transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .sidebar-logo-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu-label {
            display: none;
        }

        .sidebar.collapsed .sidebar-nav-link span {
            display: none;
        }

        .sidebar.collapsed .agent-support {
            display: none;
        }

        .sidebar.collapsed .sidebar-logo {
            padding: 0 14px 24px 14px;
            text-align: center;
        }

        .sidebar.collapsed .sidebar-logo-icon {
            margin: 0 auto;
        }

        .sidebar.collapsed .sidebar-nav-link {
            justify-content: center;
            padding: 16px 0;
            gap: 0;
        }

        .sidebar.collapsed .sidebar-nav-link::before {
            left: 0;
        }

        .sidebar.collapsed .sidebar-nav-link:hover {
            padding-left: 0;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .sidebar-logo {
            padding: 32px 24px 24px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .sidebar.collapsed .sidebar-logo {
            justify-content: center;
            padding: 0 14px 24px 14px;
        }

        .sidebar-logo-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .sidebar.collapsed .sidebar-logo-icon {
            margin: 0;
        }

        .sidebar-logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-logo-icon:hover {
            transform: scale(1.05);
        }

        .sidebar-logo-text {
            transition: opacity 0.2s ease;
            flex: 1;
            min-width: 0;
        }

        .sidebar-logo-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin: 0 0 2px 0;
            letter-spacing: 0.5px;
            line-height: 1;
        }

        .sidebar-logo-text p {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.75);
            margin: 0;
            letter-spacing: 1.2px;
            font-weight: 600;
        }

        .sidebar-toggle {
            position: fixed;
            top: 24px;
            left: 256px;
            width: 40px;
            height: 40px;
            background: white;
            border: 3px solid #7F20B0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1002;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .sidebar-toggle {
            left: 56px;
        }

        .sidebar-toggle:hover {
            background: #7F20B0;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(127, 32, 176, 0.4);
        }

        .sidebar-toggle i {
            font-size: 16px;
            color: #7F20B0;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover i {
            color: white;
        }

        body.sidebar-collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        .sidebar-menu-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
        }

        .sidebar-menu-container::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-menu-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .sidebar-menu-label {
            padding: 0 24px;
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 24px 0 12px 0;
            transition: opacity 0.2s ease;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav-item {
            margin: 0;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 24px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.25s ease;
            border-left: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .sidebar-nav-link span {
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }

        .sidebar-nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: white;
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }

        .sidebar-nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            padding-left: 28px;
        }

        .sidebar-nav-link.active {
            background: rgba(255, 255, 255, 0.18);
            color: white;
            font-weight: 600;
        }

        .sidebar-nav-link.active::before {
            transform: scaleY(1);
        }

        .sidebar-nav-link i {
            font-size: 18px;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        /* Agent Support Section */
        .agent-support {
            position: relative;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.25);
            padding: 22px 24px;
            backdrop-filter: blur(10px);
            transition: opacity 0.2s ease;
            flex-shrink: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .agent-support-label {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.65);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .agent-support-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 14px;
            line-height: 1.6;
        }

        .btn-contact-admin {
            background: rgba(255, 255, 255, 0.98);
            color: #7F20B0;
            border: none;
            padding: 11px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-contact-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 255, 255, 0.3);
            background: white;
        }

        .btn-contact-admin i {
            font-size: 14px;
        }

        /* Coverage Level Widget */
        .coverage-widget {
            padding: 0 24px;
            margin: 24px 0;
        }

        .coverage-label {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .coverage-amount {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }

        .coverage-subtitle {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Top Bar */
        .top-bar {
            position: fixed;
            left: 280px;
            top: 0;
            right: 0;
            height: 80px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #E5E7EB;
            padding: 0 30px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .top-bar {
            left: 80px;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .portal-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1F2937;
            white-space: nowrap;
        }

        .search-box {
            flex: 0 0 400px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 14px;
            z-index: 1;
        }

        .search-results {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            border: 1px solid #E5E7EB;
        }

        .search-results.active {
            display: block;
        }

        .search-result-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #F3F4F6;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: #F9FAFB;
        }

        .search-result-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7F20B0;
            font-size: 14px;
            flex-shrink: 0;
        }

        .search-result-content h6 {
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
            margin: 0 0 2px 0;
        }

        .search-result-content p {
            font-size: 12px;
            color: #6B7280;
            margin: 0;
        }

        .search-no-results {
            padding: 24px;
            text-align: center;
            color: #9CA3AF;
            font-size: 13px;
        }

        .search-input {
            width: 100%;
            padding: 11px 16px 11px 42px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #4B5563;
            transition: all 0.3s ease;
            background: #F9FAFB;
        }

        .search-input:focus {
            outline: none;
            border-color: #7F20B0;
            box-shadow: 0 0 0 3px rgba(127, 32, 176, 0.1);
            background: white;
        }

        .search-input::placeholder {
            color: #9CA3AF;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: #F9FAFB;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            background: #EF4444;
            color: white;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .icon-btn:hover {
            background: #F3F4F6;
            color: #4B5563;
        }

        .icon-btn.logout-btn {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            color: white;
        }

        .icon-btn.logout-btn:hover {
            background: linear-gradient(135deg, #B91C1C 0%, #991B1B 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .user-avatar {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 17px;
            box-shadow: 0 2px 8px rgba(127, 32, 176, 0.25);
            transition: transform 0.2s;
        }

        .user-profile:hover .user-avatar {
            transform: scale(1.05);
        }

        .user-info h6 {
            font-size: 14px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 3px 0;
        }

        .user-info p {
            font-size: 11px;
            color: #7F20B0;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            margin-top: 80px;
            min-height: calc(100vh - 80px);
            transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .main-content {
            margin-left: 80px;
        }

        /* Mobile overlay backdrop */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Mobile hamburger button */
        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #E5E7EB;
            background: #F9FAFB;
            color: #374151;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            font-size: 16px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px !important;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            /* Restore full sidebar text in drawer mode */
            .sidebar.collapsed { width: 280px !important; }
            .sidebar.collapsed .sidebar-logo-text,
            .sidebar.collapsed .sidebar-menu-label,
            .sidebar.collapsed .sidebar-nav-link span,
            .sidebar.collapsed .agent-support { display: block !important; opacity: 1 !important; }
            .sidebar.collapsed .sidebar-nav-link { justify-content: flex-start !important; padding: 13px 24px !important; gap: 14px !important; }

            .sidebar-toggle {
                display: none;
            }

            .top-bar {
                left: 0 !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .mobile-menu-btn {
                display: inline-flex;
            }
        }

        @media (max-width: 640px) {
            .search-box {
                display: none;
            }
            .user-info {
                display: none;
            }
            .top-bar {
                padding: 12px 16px !important;
            }
        }

        /* ── Extra mobile polish (≤ 480 px) ───────────────────────── */
        @media (max-width: 480px) {
            .main-content {
                padding: 16px 12px !important;
                margin-left: 0 !important;
            }
            .top-bar {
                padding: 10px 12px !important;
                height: auto !important;
                min-height: 56px;
            }
            /* Stat/KPI card grids */
            .stats-grid,
            .stat-grid,
            .kpi-grid,
            [class*="stats-grid"],
            [class*="stat-grid"] {
                grid-template-columns: 1fr 1fr !important;
                gap: 10px !important;
            }
            /* Tables */
            .table-responsive,
            table.table { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            /* Cards */
            .card, .content-card, .info-card { padding: 14px !important; }
            /* Hide right-side user avatar text on small screens */
            .user-profile .user-info { display: none; }
            /* Buttons */
            .btn, .action-btn { min-width: 0 !important; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .page-header .btn { width: 100%; justify-content: center; }
        }

        @media (max-width: 360px) {
            .stats-grid,
            .stat-grid,
            .kpi-grid,
            [class*="stats-grid"] {
                grid-template-columns: 1fr !important;
            }
        }

        /* ── Universal table & flex overflows (all breakpoints) ── */
        @media (max-width: 1024px) {
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
            .modern-card > [style*="display: flex"],
            .agent-dashboard-container > [style*="display: flex"] {
                flex-wrap: wrap !important;
                gap: 8px !important;
            }

            /* Button rows: wrap and stretch */
            [style*="display: flex"][style*="gap"] > .modern-btn,
            [style*="display: flex"][style*="gap"] > .btn {
                flex: 1 1 auto;
                min-width: 0;
            }

            /* Page containers: never wider than viewport */
            .main-content > div,
            .main-content > section {
                max-width: 100%;
                overflow-x: hidden;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile sidebar overlay/backdrop -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <img src="/public/images/shena-logo.png" alt="Shena Logo">
            </div>
            <div class="sidebar-logo-text">
                <h1>SHENA</h1>
                <p>COMPANION</p>
            </div>
        </div>

        <div class="sidebar-menu-container">
            <div class="sidebar-menu-label">MAIN MENU</div>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="/agent/dashboard" class="sidebar-nav-link<?php echo (isset($page) && $page === 'dashboard') ? ' active' : ''; ?>">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="/agent/members" class="sidebar-nav-link<?php echo (isset($page) && $page === 'members') ? ' active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Members</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="/agent/register-member" class="sidebar-nav-link<?php echo (isset($page) && $page === 'register-member') ? ' active' : ''; ?>">
                    <i class="fas fa-user-plus"></i>
                    <span>Register Member</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-menu-label">FINANCIALS</div>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="/agent/payouts" class="sidebar-nav-link<?php echo (isset($page) && $page === 'payouts') ? ' active' : ''; ?>">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Payouts</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-menu-label">RESOURCES</div>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="/agent/resources" class="sidebar-nav-link<?php echo (isset($page) && $page === 'resources') ? ' active' : ''; ?>">
                    <i class="fas fa-book"></i>
                    <span>Training & Docs</span>
                </a>
            </li>
        </ul>
        </div>

        <div class="agent-support">
            <div class="agent-support-label">AGENT SUPPORT</div>
            <div class="agent-support-text">Need help with a claim or registration?</div>
            <button class="btn-contact-admin" onclick="location.href='/agent/support'">
                <i class="fas fa-headset"></i>
                Contact Admin
            </button>
        </div>
    </div>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="fas fa-chevron-left"></i>
    </button>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="openMobileSidebar()" aria-label="Open menu">
                <i class="fas fa-bars"></i>
            </button>
            <span class="portal-title">Agent Portal</span>
        </div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="globalSearch" placeholder="Search features, pages, members..." autocomplete="off">
            <div class="search-results" id="searchResults"></div>
        </div>

        <div class="top-bar-actions">
            <button class="icon-btn" onclick="location.href='/agent/notifications'" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if (!empty($notificationCount)): ?>
                    <span class="notification-badge"><?php echo (int)$notificationCount; ?></span>
                <?php endif; ?>
            </button>
            <button class="icon-btn" onclick="location.href='/agent/profile'" title="Settings">
                <i class="fas fa-cog"></i>
            </button>
            <button class="icon-btn logout-btn" onclick="confirmLogout()" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
            <div class="user-profile" onclick="location.href='/agent/profile'" style="cursor: pointer;" title="View Profile">
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($agent['first_name'] ?? 'Sarah'); ?> <?php echo htmlspecialchars($agent['last_name'] ?? 'Jenkins'); ?></h6>
                    <p><?php echo htmlspecialchars($agent['agent_number'] ?? 'GOLD TIER AGENT'); ?></p>
                </div>
                <div class="user-avatar">
                    <?php 
                        $firstName = $agent['first_name'] ?? 'S';
                        $lastName = $agent['last_name'] ?? 'J';
                        echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const message = <?php echo json_encode($_SESSION['flash_message']); ?>;
                    const type = <?php echo json_encode($_SESSION['flash_type'] ?? 'info'); ?>;

                    if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                        ShenaApp.alert(message, type);
                        return;
                    }
                    if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                        ShenaApp.showNotification(message, type, 5000);
                        return;
                    }
                    console.warn(message);
                });
            </script>
            <?php 
                unset($_SESSION['flash_message']); 
                unset($_SESSION['flash_type']); 
            ?>
        <?php endif; ?>
