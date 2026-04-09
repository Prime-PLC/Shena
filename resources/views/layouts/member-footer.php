	</main>
	</div>
</div>

<!-- Footer -->
<footer class="member-footer">
	<div class="footer-container">
		<div class="footer-main">
			<div class="footer-brand">
				<div class="footer-logo">
					<div class="footer-logo-icon">
						<i class="fas fa-hands-helping"></i>
					</div>
					<div>
						<h5>Shena Companion</h5>
						<p>Welfare Association</p>
					</div>
				</div>
				<p class="footer-description">Providing affordable funeral services and burial expense coverage to our members across Kenya.</p>
			</div>
			
			<div class="footer-links">
				<div class="footer-section">
					<h6>Quick Links</h6>
					<ul>
						<li><a href="/dashboard">Dashboard</a></li>
						<li><a href="/payments">Contributions</a></li>
						<li><a href="/beneficiaries">Beneficiaries</a></li>
						<li><a href="/member/upgrade">Upgrade Plan</a></li>
					</ul>
				</div>
				
				<div class="footer-section">
					<h6>Support</h6>
					<ul>
						<li><a href="/member/support">Help Center</a></li>
						<li><a href="/profile">Profile Settings</a></li>
						<li><a href="/member/notification-settings">Notifications</a></li>
						<li><a href="/policy-booklet">Policy Booklet</a></li>
					</ul>
				</div>
				
				<div class="footer-section">
					<h6>Contact Us</h6>
					<p class="footer-portal-text">For assistance or inquiries, please contact our support team.</p>
					<div class="footer-contact">
						<i class="fas fa-phone"></i>
						<span>+254 748 585 067</span>
					</div>
					<div class="footer-contact">
						<i class="fas fa-envelope"></i>
						<span>support@shenacompanion.org</span>
					</div>
				</div>
			</div>
		</div>
		
		<div class="footer-bottom">
			<p>© <?php echo date('Y'); ?> Shena Companion Welfare Association. All rights reserved.</p>
			<div class="footer-bottom-links">
				<a href="/privacy-policy">Privacy Policy</a>
				<span>•</span>
				<a href="/terms-and-conditions">Terms of Service</a>
				<span>•</span>
				<a href="/policy-booklet">Member Agreement</a>
			</div>
		</div>
	</div>
</footer>

<?php include VIEWS_PATH . '/partials/member-help-widget.php'; ?>

<style>
.member-footer {
	background: white;
	border-top: 1px solid #E5E7EB;
	margin-top: 60px;
	padding: 48px 0 24px;
	margin-left: 280px;
	transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar.collapsed ~ .main-content .member-footer {
	margin-left: 80px;
}

.footer-container {
	padding: 0 30px 0 25px;
}

.footer-main {
	display: grid;
	grid-template-columns: 1fr 2fr;
	gap: 60px;
	margin-bottom: 40px;
	padding-bottom: 32px;
	border-bottom: 1px solid #F3F4F6;
}

.footer-brand {
	max-width: 350px;
}

.footer-logo {
	display: flex;
	align-items: center;
	gap: 14px;
	margin-bottom: 16px;
}

.footer-logo-icon {
	width: 48px;
	height: 48px;
	background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	color: white;
	font-size: 22px;
	box-shadow: 0 4px 12px rgba(127, 32, 176, 0.2);
}

.footer-logo h5 {
	font-family: 'Playfair Display', serif;
	font-size: 20px;
	font-weight: 700;
	color: #1F2937;
	margin: 0 0 2px 0;
}

.footer-logo p {
	font-size: 12px;
	color: #6B7280;
	margin: 0;
	font-weight: 600;
}

.footer-description {
	font-size: 14px;
	color: #6B7280;
	line-height: 1.6;
	margin: 0;
}

.footer-links {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 40px;
}

.footer-section h6 {
	font-size: 13px;
	font-weight: 700;
	color: #1F2937;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 16px;
}

.footer-section ul {
	list-style: none;
	padding: 0;
	margin: 0;
}

.footer-section ul li {
	margin-bottom: 10px;
}

.footer-section ul li a {
	font-size: 14px;
	color: #6B7280;
	text-decoration: none;
	transition: color 0.2s;
}

.footer-section ul li a:hover {
	color: #7F20B0;
}

.footer-portal-text {
	font-size: 13px;
	color: #6B7280;
	line-height: 1.5;
	margin-bottom: 14px;
}

.footer-contact {
	display: flex;
	align-items: center;
	gap: 10px;
	margin-bottom: 8px;
	font-size: 13px;
	color: #4B5563;
}

.footer-contact i {
	color: #7F20B0;
	font-size: 14px;
	width: 18px;
}

.footer-bottom {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-top: 24px;
}

.footer-bottom p {
	font-size: 13px;
	color: #6B7280;
	margin: 0;
}

.footer-bottom-links {
	display: flex;
	align-items: center;
	gap: 12px;
}

.footer-bottom-links a {
	font-size: 13px;
	color: #6B7280;
	text-decoration: none;
	transition: color 0.2s;
}

.footer-bottom-links a:hover {
	color: #7F20B0;
}

.footer-bottom-links span {
	color: #D1D5DB;
}

@media (max-width: 1024px) {
	.footer-main {
		grid-template-columns: 1fr;
		gap: 40px;
	}

	.footer-links {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 768px) {
	.footer-container {
		padding: 0 20px;
	}

	.footer-links {
		grid-template-columns: 1fr;
		gap: 30px;
	}

	.footer-bottom {
		flex-direction: column;
		gap: 16px;
		text-align: center;
	}
	
	.member-footer {
		margin-left: 0;
	}
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		// Sidebar Toggle Functionality
		function isMobileLayout() {
			return window.innerWidth <= 1024;
		}

		function openMobileSidebar() {
			const sidebar = document.getElementById('memberSidebar');
			const overlay = document.getElementById('sidebarOverlay');
			sidebar.classList.add('active');
			if (overlay) {
				overlay.classList.add('active');
				document.body.style.overflow = 'hidden';
			}
		}

		function closeMobileSidebar() {
			const sidebar = document.getElementById('memberSidebar');
			const overlay = document.getElementById('sidebarOverlay');
			sidebar.classList.remove('active');
			if (overlay) {
				overlay.classList.remove('active');
				document.body.style.overflow = '';
			}
		}

		function toggleSidebar() {
			if (isMobileLayout()) {
				const sidebar = document.getElementById('memberSidebar');
				if (sidebar.classList.contains('active')) {
					closeMobileSidebar();
				} else {
					openMobileSidebar();
				}
				return;
			}
			// Desktop: collapse to icon-only
			const sidebar = document.getElementById('memberSidebar');
			sidebar.classList.toggle('collapsed');
			if (sidebar.classList.contains('collapsed')) {
				localStorage.setItem('memberSidebarCollapsed', 'true');
			} else {
				localStorage.removeItem('memberSidebarCollapsed');
			}
		}

		// Close mobile sidebar on nav link click
		document.addEventListener('DOMContentLoaded', function() {
			// Restore desktop collapsed state
			const sidebar = document.getElementById('memberSidebar');
			if (!isMobileLayout() && localStorage.getItem('memberSidebarCollapsed') === 'true') {
				sidebar.classList.add('collapsed');
			}

			// Close sidebar when a nav link is tapped on mobile
			document.querySelectorAll('.sidebar-nav-link').forEach(function(link) {
				link.addEventListener('click', function() {
					if (isMobileLayout()) { closeMobileSidebar(); }
				});
			});
		});

		// Logout Handler
		function handleLogout() {
			if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
				ShenaApp.confirmAction(
					'Are you sure you want to logout?',
					function() { window.location.href = '/logout'; },
					null,
					{ type: 'warning', title: 'Confirm Logout', confirmText: 'Logout' }
				);
				return;
			}
			if (confirm('Are you sure you want to logout?')) {
				window.location.href = '/logout';
			}
		}

		// Global Search Functionality
		const searchFeatures = [
			{ name: 'Dashboard', description: 'View your dashboard and statistics', icon: 'fa-th-large', url: '/dashboard' },
			{ name: 'Contributions', description: 'View and manage your payments', icon: 'fa-credit-card', url: '/payments' },
			{ name: 'Beneficiaries', description: 'Manage your beneficiaries', icon: 'fa-users', url: '/beneficiaries' },
			{ name: 'Upgrade Plan', description: 'Upgrade your membership package', icon: 'fa-arrow-up', url: '/member/upgrade' },
			{ name: 'Settings', description: 'Manage your account settings', icon: 'fa-cog', url: '/profile' },
			{ name: 'Notifications', description: 'Notification preferences', icon: 'fa-bell', url: '/member/notification-settings' },
			{ name: 'Profile', description: 'View and edit your profile', icon: 'fa-user-circle', url: '/profile' },
			{ name: 'Support', description: 'Get help and contact support', icon: 'fa-headset', url: '/member/support' },
		];

		const searchInput = document.getElementById('globalSearch');
		const searchResults = document.getElementById('searchResults');

		if (searchInput && searchResults) {
			searchInput.addEventListener('input', function(e) {
				const query = e.target.value.toLowerCase().trim();
				
				if (query.length === 0) {
					searchResults.style.display = 'none';
					return;
				}

				const filtered = searchFeatures.filter(feature => 
					feature.name.toLowerCase().includes(query) || 
					feature.description.toLowerCase().includes(query)
				);

				if (filtered.length > 0) {
					searchResults.innerHTML = filtered.map(feature => `
						<div class="search-result-item" onclick="window.location.href='${feature.url}'">
							<div class="search-result-icon">
								<i class="fas ${feature.icon}"></i>
							</div>
							<div class="search-result-content">
								<h6>${feature.name}</h6>
								<p>${feature.description}</p>
							</div>
						</div>
					`).join('');
					searchResults.style.display = 'block';
				} else {
					searchResults.innerHTML = `
						<div class="search-result-item" style="justify-content: center;">
							<p style="color: #9CA3AF; margin: 0;">No results found</p>
						</div>
					`;
					searchResults.style.display = 'block';
				}
			});

			// Close search results when clicking outside
			document.addEventListener('click', function(e) {
				if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
					searchResults.style.display = 'none';
				}
			});
		}
	</script>
		<script src="/public/js/app.js"></script>
</body>
</html>
