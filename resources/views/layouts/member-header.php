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
	<title><?php echo $title ?? 'Member Portal - Shena Companion'; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link href="/public/css/modals.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		
		body { 
			background: #F8F9FC; 
			font-family: 'Manrope', sans-serif;
		}
		
		.dashboard-wrapper {
			display: flex;
			min-height: 100vh;
		}
		
		.sidebar {
			width: 280px;
			background: #7F3D9E;
			color: white;
			position: fixed;
			height: 100vh;
			overflow: hidden;
			z-index: 1000;
			transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			display: flex;
			flex-direction: column;
		}
		
		.sidebar.collapsed {
			width: 80px;
		}
		
		.sidebar-logo {
			padding: 24px 20px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.1);
			display: flex;
			align-items: center;
			gap: 16px;
			transition: all 0.3s ease;
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

		.sidebar.collapsed .sidebar-logo-text {
			display: none;
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
			color: rgba(255, 255, 255, 0.7);
			margin: 0;
			letter-spacing: 2px;
			font-weight: 600;
		}

		.sidebar-menu-container {
			flex: 1;
			overflow-y: auto;
			padding-bottom: 20px;
		}

		.sidebar-menu-container::-webkit-scrollbar {
			width: 4px;
		}

		.sidebar-menu-container::-webkit-scrollbar-thumb {
			background: rgba(255, 255, 255, 0.3);
			border-radius: 2px;
		}

		.sidebar-menu-label {
			padding: 24px 20px 12px;
			font-size: 11px;
			font-weight: 700;
			color: rgba(255, 255, 255, 0.5);
			letter-spacing: 1px;
			text-transform: uppercase;
			transition: opacity 0.2s ease;
		}

		.sidebar.collapsed .sidebar-menu-label {
			display: none;
		}
		
		.sidebar-nav {
			padding: 0;
			list-style: none;
			margin: 0;
		}

		.sidebar-nav-item {
			margin: 0;
		}
		
		.sidebar-nav-link {
			display: flex;
			align-items: center;
			gap: 16px;
			padding: 14px 20px;
			color: rgba(255, 255, 255, 0.8);
			text-decoration: none;
			transition: all 0.2s ease;
			font-size: 15px;
			font-weight: 500;
			border-left: 3px solid transparent;
		}

		.sidebar.collapsed .sidebar-nav-link {
			justify-content: center;
			padding: 14px 0;
		}
		
		.sidebar-nav-link i {
			width: 20px;
			font-size: 18px;
			text-align: center;
			flex-shrink: 0;
		}

		.sidebar-nav-link span {
			transition: opacity 0.2s ease;
		}

		.sidebar.collapsed .sidebar-nav-link span {
			display: none;
		}
		
		.sidebar-nav-link:hover {
			background: rgba(255, 255, 255, 0.1);
			color: white;
			border-left-color: rgba(255, 255, 255, 0.3);
		}
		
		.sidebar-nav-link.active {
			background: rgba(255, 255, 255, 0.15);
			color: white;
			border-left-color: #FFD700;
		}

		/* Member Support Section */
		.member-support {
			position: relative;
			bottom: 0;
			left: 0;
			right: 0;
			background: rgba(0, 0, 0, 0.25);
			padding: 20px 20px 24px 20px;
			backdrop-filter: blur(10px);
			transition: all 0.3s ease;
			border-top: 1px solid rgba(255, 255, 255, 0.1);
			flex-shrink: 0;
		}

		.member-support-label {
			font-size: 10px;
			font-weight: 700;
			color: rgba(255, 255, 255, 0.6);
			letter-spacing: 1.2px;
			text-transform: uppercase;
			margin-bottom: 8px;
		}

		.member-support-text {
			font-size: 13px;
			color: rgba(255, 255, 255, 0.9);
			margin-bottom: 12px;
			line-height: 1.5;
		}

		.btn-contact-support {
			background: rgba(255, 255, 255, 0.95);
			color: #7F20B0;
			border: none;
			padding: 10px 18px;
			border-radius: 8px;
			font-weight: 700;
			font-size: 13px;
			width: 100%;
			cursor: pointer;
			transition: all 0.2s ease;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		}

		.btn-contact-support:hover {
			background: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
		}

		.btn-contact-support i {
			font-size: 14px;
		}

		.sidebar.collapsed .member-support {
			opacity: 0;
			pointer-events: none;
		}

		.sidebar-toggle-btn {
			position: fixed;
			top: 24px;
			left: 256px;
			width: 40px;
			height: 40px;
			background: white;
			border: 1px solid #E5E7EB;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			z-index: 1001;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		}

		.sidebar.collapsed + .sidebar-toggle-btn {
			left: 56px;
		}

		.sidebar-toggle-btn:hover {
			background: #F9FAFB;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		}

		.sidebar-toggle-btn i {
			color: #6B7280;
			font-size: 16px;
			transition: transform 0.3s ease;
		}

		.sidebar.collapsed + .sidebar-toggle-btn i {
			transform: rotate(180deg);
		}
		
		.main-content {
			margin-left: 280px;
			flex: 1;
			padding: 0;
			transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		}

		.sidebar.collapsed ~ .main-content {
			margin-left: 80px;
		}
		
		.top-bar {
			background: white;
			padding: 20px 40px;
			border-bottom: 1px solid #E5E7EB;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 24px;
		}
		
		.top-bar-left h1 {
			font-size: 1.5rem;
			font-weight: 600;
			color: #1F2937;
			margin: 0;
		}

		.top-bar-right {
			display: flex;
			align-items: center;
			gap: 20px;
		}

		.search-container {
			position: relative;
		}

		.search-input-wrapper {
			position: relative;
			width: 300px;
		}

		.search-input {
			width: 100%;
			padding: 10px 16px 10px 40px;
			border: 1px solid #E5E7EB;
			border-radius: 8px;
			font-size: 14px;
			transition: all 0.2s;
			background: #F9FAFB;
		}

		.search-input:focus {
			outline: none;
			border-color: #7F3D9E;
			background: white;
			box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
		}

		.search-icon {
			position: absolute;
			left: 12px;
			top: 50%;
			transform: translateY(-50%);
			color: #9CA3AF;
			font-size: 14px;
		}

		.search-results {
			position: absolute;
			top: calc(100% + 8px);
			left: 0;
			right: 0;
			background: white;
			border: 1px solid #E5E7EB;
			border-radius: 12px;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
			max-height: 400px;
			overflow-y: auto;
			z-index: 1002;
		}

		.search-result-item {
			padding: 12px 16px;
			border-bottom: 1px solid #F3F4F6;
			cursor: pointer;
			transition: background 0.2s;
			display: flex;
			align-items: center;
			gap: 12px;
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
			background: linear-gradient(135deg, #7F3D9E 0%, #A855F7 100%);
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			font-size: 14px;
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
			color: #7F20B0;
		}

		.logout-btn {
			background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
			color: white;
			border: none;
			padding: 10px 20px;
			border-radius: 8px;
			font-weight: 600;
			font-size: 14px;
			display: flex;
			align-items: center;
			gap: 8px;
			cursor: pointer;
			transition: all 0.2s;
		}

		.logout-btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
		}

		.profile-menu {
			position: relative;
			display: flex;
			align-items: center;
		}

		.profile-button {
			background: transparent;
			border: none;
			padding: 0;
			display: flex;
			align-items: center;
			gap: 10px;
			cursor: pointer;
		}

		.profile-button:focus {
			outline: none;
		}

		.profile-caret {
			font-size: 12px;
			color: #9CA3AF;
		}

		.profile-dropdown {
			position: absolute;
			top: calc(100% + 10px);
			right: 0;
			min-width: 200px;
			background: white;
			border: 1px solid #E5E7EB;
			border-radius: 12px;
			box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
			padding: 8px;
			opacity: 0;
			visibility: hidden;
			transform: translateY(-6px);
			transition: all 0.2s ease;
			z-index: 1003;
		}

		.profile-menu:focus-within .profile-dropdown,
		.profile-menu:hover .profile-dropdown {
			opacity: 1;
			visibility: visible;
			transform: translateY(0);
		}

		.profile-dropdown-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 10px 12px;
			border-radius: 8px;
			color: #374151;
			text-decoration: none;
			font-size: 14px;
			font-weight: 600;
			background: transparent;
			border: none;
			width: 100%;
			cursor: pointer;
			text-align: left;
		}

		.profile-dropdown-item:hover {
			background: #F9FAFB;
			color: #7F20B0;
		}

		.profile-dropdown-item.logout {
			color: #DC2626;
		}

		.profile-dropdown-item.logout:hover {
			background: #FEF2F2;
			color: #B91C1C;
		}
		
		.user-profile {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		
		.user-profile-text {
			text-align: right;
		}
		
		.user-profile-text h4 {
			font-size: 0.95rem;
			font-weight: 600;
			color: #1F2937;
			margin: 0;
		}
		
		.user-profile-text p {
			font-size: 0.8rem;
			color: #6B7280;
			margin: 0;
		}
		
		.user-avatar {
			width: 45px;
			height: 45px;
			border-radius: 50%;
			background: linear-gradient(135deg, #7F3D9E, #A855F7);
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			font-weight: 600;
			font-size: 1.1rem;
		}
		
		/* ── Mobile overlay backdrop ─────────────────────────────── */
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

		/* ── Mobile hamburger in top-bar ─────────────────────────── */
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

		@media (max-width: 1024px) {
			/* Desktop collapse button – hide on mobile/tablet */
			.sidebar-toggle-btn {
				display: none;
			}

			/* Sidebar is a full drawer on mobile/tablet */
			.sidebar {
				transform: translateX(-100%);
				width: 280px !important; /* never icon-only on mobile */
				transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			}

			/* Restore all text that collapsed hides, since drawer always shows full sidebar */
			.sidebar .sidebar-logo-text,
			.sidebar .sidebar-menu-label,
			.sidebar .sidebar-nav-link span,
			.sidebar .member-support {
				display: block !important;
				opacity: 1 !important;
			}
			.sidebar .sidebar-nav-link {
				justify-content: flex-start !important;
				padding: 14px 20px !important;
				gap: 16px !important;
			}
			.sidebar.active {
				transform: translateX(0);
			}

			.main-content {
				margin-left: 0 !important;
			}

			.mobile-menu-btn {
				display: inline-flex;
			}

			.top-bar {
				padding: 16px 20px !important;
			}
		}

		@media (max-width: 640px) {
			/* Hide search on very small screens */
			.search-container {
				display: none;
			}
			/* Hide name/ID text, keep avatar only */
			.user-profile-text {
				display: none;
			}
			.top-bar {
				padding: 12px 16px !important;
			}
			.top-bar-left h1 {
				font-size: 1.1rem;
			}
		}
	</style>
</head>
<body>
	<div class="dashboard-wrapper">
		<!-- Mobile sidebar overlay/backdrop -->
		<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

		<!-- Sidebar -->
		<aside class="sidebar" id="memberSidebar">
			<div class="sidebar-logo">
				<div class="sidebar-logo-icon">
					<img src="/public/images/shena-logo.png" alt="Shena Logo" onerror="this.parentElement.innerHTML='<div style=\\'font-family: Playfair Display, serif; font-size: 20px; font-weight: 700; color: #7F3D9E; text-align: center;\\'>SC</div>'">
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
					<a href="/dashboard" class="sidebar-nav-link <?php echo ($page ?? '') === 'dashboard' ? 'active' : ''; ?>">
						<i class="fas fa-th-large"></i>
						<span>Dashboard</span>
					</a>
				</li>
				<li class="sidebar-nav-item">
					<a href="/payments" class="sidebar-nav-link <?php echo ($page ?? '') === 'payments' ? 'active' : ''; ?>">
						<i class="fas fa-credit-card"></i>
						<span>Contributions</span>
					</a>
				</li>
				<li class="sidebar-nav-item">
					<a href="/claims" class="sidebar-nav-link <?php echo ($page ?? '') === 'claims' ? 'active' : ''; ?>">
						<i class="fas fa-file-medical"></i>
						<span>Claims</span>
					</a>
				</li>
				<li class="sidebar-nav-item">
					<a href="/beneficiaries" class="sidebar-nav-link <?php echo ($page ?? '') === 'beneficiaries' ? 'active' : ''; ?>">
						<i class="fas fa-users"></i>
						<span>Beneficiaries</span>
					</a>
				</li>
			</ul>

			<div class="sidebar-menu-label">SERVICES</div>
			<ul class="sidebar-nav">
				<li class="sidebar-nav-item">
					<a href="/member/upgrade" class="sidebar-nav-link <?php echo ($page ?? '') === 'upgrade' ? 'active' : ''; ?>">
						<i class="fas fa-arrow-up"></i>
						<span>Upgrade Plan</span>
					</a>
				</li>
				<li class="sidebar-nav-item">
					<a href="/profile" class="sidebar-nav-link <?php echo ($page ?? '') === 'settings' ? 'active' : ''; ?>">
						<i class="fas fa-cog"></i>
						<span>Settings</span>
					</a>
				</li>
			</ul>
			</div>
			
			<div class="member-support">
				<div class="member-support-label">MEMBER SUPPORT</div>
				<div class="member-support-text">Need help or have questions?</div>
				<button class="btn-contact-support" onclick="location.href='/member/support'">
					<i class="fas fa-headset"></i>
					Contact Support
				</button>
			</div>
		</aside>

		<!-- Sidebar Toggle Button -->
		<button class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleSidebar()">
			<i class="fas fa-chevron-left"></i>
		</button>
		
		<!-- Main Content -->
		<div class="main-content">
			<div class="top-bar">
				<div class="top-bar-left" style="display:flex;align-items:center;gap:12px;">
					<button class="mobile-menu-btn" id="mobileMenuBtn" onclick="openMobileSidebar()" aria-label="Open menu">
						<i class="fas fa-bars"></i>
					</button>
					<h1>Member Portal</h1>
				</div>
				<div class="top-bar-right">
					<div class="search-container">
						<div class="search-input-wrapper">
							<i class="fas fa-search search-icon"></i>
							<input type="text" class="search-input" id="globalSearch" placeholder="Search features...">
						</div>
						<div class="search-results" id="searchResults" style="display: none;"></div>
					</div>
					<button class="icon-btn" onclick="location.href='/member/notifications'" title="Notifications">
						<i class="fas fa-bell"></i>
						<?php if (!empty($notificationCount)): ?>
							<span class="notification-badge"><?php echo (int)$notificationCount; ?></span>
						<?php endif; ?>
					</button>
					<div class="profile-menu">
						<button class="profile-button" type="button" aria-haspopup="true" aria-expanded="false">
							<div class="user-profile">
								<div class="user-profile-text">
									<h4><?php echo htmlspecialchars($member['first_name'] ?? 'John') . ' ' . htmlspecialchars($member['last_name'] ?? 'Doe'); ?></h4>
									<p>ID: <?php echo htmlspecialchars($member['member_number'] ?? 'SH-98238'); ?></p>
								</div>
								<div class="user-avatar">
									<?php echo strtoupper(substr($member['first_name'] ?? 'J', 0, 1)); ?>
								</div>
							</div>
							<i class="fas fa-chevron-down profile-caret"></i>
						</button>
						<div class="profile-dropdown" role="menu">
							<a class="profile-dropdown-item" href="/profile">
								<i class="fas fa-user-cog"></i>
								Profile Settings
							</a>
							<a class="profile-dropdown-item" href="/member/notification-settings">
								<i class="fas fa-bell"></i>
								Notifications
							</a>
							<button class="profile-dropdown-item logout" type="button" onclick="handleLogout()">
								<i class="fas fa-sign-out-alt"></i>
								Logout
							</button>
						</div>
					</div>
				</div>
			</div>
	<main>
