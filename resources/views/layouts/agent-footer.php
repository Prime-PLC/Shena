    </div>
    
    <!-- Footer -->
    <footer class="agent-footer">
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
                            <li><a href="/agent/dashboard">Dashboard</a></li>
                            <li><a href="/agent/members">Members</a></li>
                            <li><a href="/agent/payouts">Payouts</a></li>
                            <li><a href="/agent/resources">Resources</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h6>Support</h6>
                        <ul>
                            <li><a href="/agent/support">Help Center</a></li>
                            <li><a href="/agent/profile">Account Settings</a></li>
                            <li><a href="#">Training Materials</a></li>
                            <li><a href="#">Contact Admin</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h6>Agent Portal</h6>
                        <p class="footer-portal-text">For technical support or urgent assistance, please contact administration.</p>
                        <div class="footer-contact">
                            <i class="fas fa-phone"></i>
                            <span>+254 700 000 000</span>
                        </div>
                        <div class="footer-contact">
                            <i class="fas fa-envelope"></i>
                            <span>agents@shena.co.ke</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> Shena Companion Welfare Association. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <span>•</span>
                    <a href="#">Terms of Service</a>
                    <span>•</span>
                    <a href="#">Agent Agreement</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
    .agent-footer {
        background: white;
        border-top: 1px solid #E5E7EB;
        margin-top: 60px;
        padding: 48px 0 24px;
        margin-left: 280px;
        transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body.sidebar-collapsed .agent-footer {
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
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Sidebar Toggle Functionality
        function isMobileLayout() {
            return window.innerWidth <= 1024;
        }

        function openMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            if (isMobileLayout()) {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
                return;
            }
            // Desktop: toggle collapsed icon-only mode
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            if (!sidebar) return;
            sidebar.classList.toggle('collapsed');
            body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Restore sidebar state on page load + close drawer on nav-link tap
        document.addEventListener('DOMContentLoaded', function() {
            if (!isMobileLayout()) {
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (sidebarCollapsed) {
                    const sidebar = document.querySelector('.sidebar');
                    if (sidebar) {
                        sidebar.classList.add('collapsed');
                        document.body.classList.add('sidebar-collapsed');
                    }
                }
            }

            // Close the mobile drawer when a nav link is tapped
            document.querySelectorAll('.sidebar-nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (isMobileLayout()) closeMobileSidebar();
                });
            });
        });

        // Logout Confirmation
        function confirmLogout() {
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
            { name: 'Dashboard', description: 'View agent dashboard and statistics', icon: 'fa-th-large', url: '/agent/dashboard' },
            { name: 'Members', description: 'View and manage registered members', icon: 'fa-users', url: '/agent/members' },
            { name: 'Register Member', description: 'Register a new member', icon: 'fa-user-plus', url: '/agent/register-member' },
            { name: 'Payouts', description: 'View commissions and request payouts', icon: 'fa-money-check-alt', url: '/agent/payouts' },
            { name: 'Resources', description: 'Training materials and documents', icon: 'fa-book', url: '/agent/resources' },
            { name: 'Support', description: 'Get help and contact admin', icon: 'fa-headset', url: '/agent/support' },
            { name: 'Profile', description: 'Manage your agent profile', icon: 'fa-user-circle', url: '/agent/profile' },
            { name: 'Settings', description: 'Account and security settings', icon: 'fa-cog', url: '/agent/profile' },
            { name: 'Change Password', description: 'Update your password', icon: 'fa-lock', url: '/agent/profile' },
        ];

        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                
                if (query.length === 0) {
                    searchResults.classList.remove('active');
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
                    searchResults.classList.add('active');
                } else {
                    searchResults.innerHTML = `
                        <div class="search-no-results">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                            No results found for "${query}"
                        </div>
                    `;
                    searchResults.classList.add('active');
                }
            });

            // Close search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.remove('active');
                }
            });

            // Close search results on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchResults.classList.remove('active');
                    searchInput.blur();
                }
            });
        }

        // Initialize DataTables if present
        $(document).ready(function() {
            if ($('#membersTable').length) {
                $('#membersTable').DataTable({
                    order: [[6, 'desc']],
                    pageLength: 25
                });
            }
            if ($('#commissionsTable').length) {
                $('#commissionsTable').DataTable({
                    order: [[0, 'desc']],
                    pageLength: 25
                });
            }
        });
    </script>
    <script src="/public/js/app.js"></script>
</body>
</html>
