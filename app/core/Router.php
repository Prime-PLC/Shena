<?php
/**
 * Router Class - Handles URL routing
 */
class Router 
{
    private $routes = [];
    
    public function __construct()
    {
        $this->loadRoutes();
    }
    
    private function loadRoutes()
    {
        // Public Routes
        $this->addRoute('GET', '/', 'HomeController@index');
        $this->addRoute('GET', '/about', 'HomeController@about');
        $this->addRoute('GET', '/membership', 'HomeController@membership');
        $this->addRoute('GET', '/services', 'HomeController@services');
        $this->addRoute('GET', '/contact', 'HomeController@contact');
        $this->addRoute('GET', '/gallery', 'HomeController@gallery');
        $this->addRoute('GET', '/privacy-policy', 'HomeController@privacyPolicy');
        $this->addRoute('GET', '/terms-and-conditions', 'HomeController@termsAndConditions');
        $this->addRoute('GET', '/policy-booklet', 'HomeController@policyBooklet');
        $this->addRoute('POST', '/contact', 'HomeController@submitContact');
        
        // Authentication Routes
        $this->addRoute('GET', '/login', 'AuthController@showLogin');
        $this->addRoute('POST', '/login', 'AuthController@login');
        $this->addRoute('POST', '/login/otp/send', 'AuthController@sendLoginOtp');
        $this->addRoute('POST', '/login/otp/verify', 'AuthController@verifyLoginOtp');
        $this->addRoute('GET', '/register', 'AuthController@showPublicRegistration');
        $this->addRoute('POST', '/register', 'AuthController@processPublicRegistration');
        $this->addRoute('GET', '/register/verify-otp', 'AuthController@showSignupOtpVerification');
        $this->addRoute('POST', '/register/verify-otp', 'AuthController@verifySignupOtp');
        $this->addRoute('POST', '/register/resend-otp', 'AuthController@resendSignupOtp');
        $this->addRoute('GET', '/register/create-password', 'AuthController@showCreatePassword');
        $this->addRoute('POST', '/register/create-password', 'AuthController@storeCreatedPassword');
        $this->addRoute('GET', '/logout', 'AuthController@logout');
        
        // Forgot Password Routes
        $this->addRoute('GET', '/forgot-password', 'AuthController@showForgotPassword');
        $this->addRoute('POST', '/forgot-password/send-otp', 'AuthController@sendForgotPasswordOtp');
        $this->addRoute('POST', '/forgot-password/verify-otp', 'AuthController@verifyForgotPasswordOtp');
        $this->addRoute('POST', '/forgot-password/reset', 'AuthController@resetPassword');
        
        // Registration Complete & Payment Routes
        $this->addRoute('GET', '/registration/complete', 'AuthController@registrationComplete');
        $this->addRoute('POST', '/registration/pay', 'AuthController@initiateRegistrationPayment');
        $this->addRoute('POST', '/register/initiate-payment', 'AuthController@initiatePublicRegistrationPayment');
        
        // Transaction Verification/Recovery
        $this->addRoute('GET', '/verify-transaction', 'AuthController@showTransactionVerification');
        $this->addRoute('POST', '/verify-transaction', 'AuthController@verifyTransaction');
        
        // Legacy Registration Routes (old form - kept for backward compatibility)
        $this->addRoute('GET', '/register-old', 'AuthController@showRegister');
        $this->addRoute('POST', '/register-old/submit', 'AuthController@register');
        
        // Public Registration Routes (alias)
        $this->addRoute('GET', '/register-public', 'AuthController@showPublicRegistration');
        $this->addRoute('POST', '/register/process', 'AuthController@processPublicRegistration');
        
        // Member Routes (Protected)
        $this->addRoute('GET', '/dashboard', 'MemberController@dashboard');
        $this->addRoute('GET', '/profile', 'MemberController@profile');
        $this->addRoute('POST', '/profile', 'MemberController@updateProfile');
        $this->addRoute('POST', '/member/profile/complete', 'MemberController@completeProfileFromPopup');
        $this->addRoute('POST', '/member/onboarding/package', 'MemberController@updatePackageFromOnboarding');
        $this->addRoute('POST', '/member/onboarding/dismiss', 'MemberController@dismissOnboarding');

        // Agent Routes (Protected)
        $this->addRoute('GET', '/agent/dashboard', 'AgentDashboardController@dashboard');
        $this->addRoute('GET', '/agent/payouts', 'AgentDashboardController@payouts');
        $this->addRoute('POST', '/agent/payouts/request', 'AgentDashboardController@requestPayout');
        $this->addRoute('POST', '/profile/next-of-kin', 'MemberController@updateNextOfKin');
        $this->addRoute('GET', '/payments', 'MemberController@payments');
        $this->addRoute('GET', '/member/payments/export', 'MemberController@exportPaymentHistory');
        $this->addRoute('GET', '/member/payments/export-receipt', 'MemberController@exportPaymentReceipt');
        $this->addRoute('POST', '/payments/verify-transaction', 'MemberController@verifyTransaction');
        $this->addRoute('GET', '/beneficiaries', 'MemberController@beneficiaries');
        $this->addRoute('POST', '/beneficiaries', 'MemberController@addBeneficiary');
        $this->addRoute('POST', '/beneficiaries/delete', 'MemberController@deleteBeneficiary');
        $this->addRoute('POST', '/beneficiaries/update', 'MemberController@updateBeneficiary');
        $this->addRoute('GET', '/claims', 'MemberController@claims');
        $this->addRoute('GET', '/claims/view/{id}', 'MemberController@viewClaim');
        $this->addRoute('POST', '/claims', 'MemberController@submitClaim');
        
        // Member Plan Upgrade Routes
        $this->addRoute('GET', '/member/upgrade', 'MemberController@viewUpgrade');
        $this->addRoute('POST', '/member/upgrade/request', 'MemberController@requestUpgrade');
        $this->addRoute('GET', '/member/upgrade/status', 'MemberController@checkUpgradeStatus');
        $this->addRoute('POST', '/member/upgrade/cancel', 'MemberController@cancelUpgrade');
        
        // Member Support Route
        $this->addRoute('GET', '/member/support', 'MemberController@viewSupport');
        $this->addRoute('POST', '/member/support/submit', 'MemberController@submitSupport');
        
        // Member Notification Settings Routes
        $this->addRoute('GET', '/member/notification-settings', 'MemberController@viewNotificationSettings');
        $this->addRoute('POST', '/member/notification-settings', 'MemberController@updateNotificationSettings');
        
        // Member Notifications Page
        $this->addRoute('GET', '/member/notifications', 'MemberController@notifications');
        $this->addRoute('POST', '/member/notifications/mark-read', 'MemberController@markNotificationAsRead');
        $this->addRoute('POST', '/member/notifications/mark-all-read', 'MemberController@markAllNotificationsAsRead');
        $this->addRoute('POST', '/member/notifications/delete', 'MemberController@deleteNotification');
        $this->addRoute('POST', '/member/notifications/clear-all', 'MemberController@clearAllNotifications');
        
        // Admin Routes (Super Admin & Manager)
        $this->addRoute('GET', '/admin-login', 'AdminController@showLogin');
        $this->addRoute('POST', '/admin-login', 'AdminController@login');
        $this->addRoute('GET', '/admin/login', 'AdminController@showLogin');
        $this->addRoute('POST', '/admin/login', 'AdminController@login');
        $this->addRoute('GET', '/admin', 'AdminController@dashboard');
        $this->addRoute('GET', '/admin/dashboard', 'AdminController@dashboard');
        $this->addRoute('GET', '/admin/members', 'AdminController@members');
        $this->addRoute('GET', '/admin/members/export-csv', 'AdminController@exportMembersCSV');
        $this->addRoute('GET', '/admin/members/register', 'AdminController@registerMember');
        $this->addRoute('POST', '/admin/members/register', 'AdminController@registerMember');
        $this->addRoute('GET', '/admin/members/view/{id}', 'AdminController@viewMember');
        $this->addRoute('GET', '/admin/members/edit/{id}', 'AdminController@editMember');
        $this->addRoute('POST', '/admin/members/update/{id}', 'AdminController@updateMember');
        $this->addRoute('POST', '/admin/members/suspend/{id}', 'AdminController@suspendMember');
        $this->addRoute('POST', '/admin/members/activate/{id}', 'AdminController@activateMember');
        $this->addRoute('GET', '/admin/member/{id}', 'AdminController@viewMember');
        $this->addRoute('POST', '/admin/member/activate', 'AdminController@activateMember');
        $this->addRoute('POST', '/admin/member/deactivate', 'AdminController@deactivateMember');
        $this->addRoute('POST', '/admin/member/{id}/activate', 'AdminController@activateMember');
        $this->addRoute('POST', '/admin/member/{id}/deactivate', 'AdminController@deactivateMember');
        $this->addRoute('GET', '/admin/payments', 'AdminController@payments');
        $this->addRoute('POST', '/admin/payments/verify', 'PaymentController@verifyAdminPayment');
        $this->addRoute('GET', '/admin/payments/search-members', 'PaymentController@searchMembers');
        $this->addRoute('GET', '/admin/payments/confirm/{id}', 'PaymentController@confirmPayment');
        $this->addRoute('GET', '/admin/payments/fail/{id}', 'PaymentController@failPayment');
        $this->addRoute('GET', '/admin/claims', 'AdminController@claims');
        $this->addRoute('GET', '/admin/claims/view/{id}', 'AdminController@viewClaim');
        $this->addRoute('GET', '/admin/claims/completed', 'AdminController@viewCompletedClaims');
        $this->addRoute('GET', '/admin/claims/track-services', 'AdminController@viewTrackServices');
        // Admin AJAX API
        $this->addRoute('GET', '/admin/api/members', 'AdminApiController@members');
        $this->addRoute('POST', '/admin/claims/approve', 'AdminController@approveClaim');
        $this->addRoute('POST', '/admin/claims/{id}/approve', 'AdminController@approveClaim');
        $this->addRoute('POST', '/admin/claims/approve-cash', 'AdminController@approveClaimCashAlternative');
        $this->addRoute('POST', '/admin/claims/{id}/approve-cash', 'AdminController@approveClaimCashAlternative');
        $this->addRoute('GET', '/admin/claims/track/{id}', 'AdminController@trackServiceDelivery');
        $this->addRoute('POST', '/admin/claims/track/{id}', 'AdminController@trackServiceDelivery');
        $this->addRoute('POST', '/admin/claims/complete', 'AdminController@completeClaim');
        $this->addRoute('POST', '/admin/claims/{id}/complete', 'AdminController@completeClaim');
        $this->addRoute('POST', '/admin/claims/{id}/reject', 'AdminController@rejectClaim');
        $this->addRoute('GET', '/admin/reports', 'AdminController@reports');
        $this->addRoute('GET', '/admin/reports/export', 'AdminController@exportReport');
        $this->addRoute('GET', '/admin/communications', 'AdminController@communications');
        $this->addRoute('POST', '/admin/send-message', 'AdminController@sendMessage');
        $this->addRoute('POST', '/admin/communications/send-email', 'AdminController@sendEmail');
        $this->addRoute('POST', '/admin/communications/send-sms', 'AdminController@sendSMS');
        
        // SMS Campaign Management Routes (integrated in communications)
        $this->addRoute('POST', '/admin/communications/create-campaign', 'BulkSmsController@createCampaign');
        $this->addRoute('POST', '/admin/communications/send-campaign', 'BulkSmsController@sendCampaign');
        $this->addRoute('POST', '/admin/communications/cancel-campaign', 'BulkSmsController@cancelCampaign');
        $this->addRoute('POST', '/admin/communications/process-queue', 'BulkSmsController@processQueue');
        $this->addRoute('POST', '/admin/communications/quick-sms', 'BulkSmsController@quickSms');
        $this->addRoute('POST', '/admin/communications/send-now', 'BulkSmsController@sendNow');
        $this->addRoute('POST', '/admin/communications/edit-campaign', 'BulkSmsController@editCampaign');
        $this->addRoute('POST', '/admin/communications/pause-campaign', 'BulkSmsController@pauseCampaign');
        $this->addRoute('POST', '/admin/communications/reschedule', 'BulkSmsController@reschedule');
        $this->addRoute('POST', '/admin/communications/send-queue-item', 'BulkSmsController@sendQueueItem');
        $this->addRoute('POST', '/admin/communications/retry-queue-item', 'BulkSmsController@retryQueueItem');
        $this->addRoute('POST', '/admin/communications/delete-queue-item', 'BulkSmsController@deleteQueueItem');
        $this->addRoute('GET', '/admin/communications/campaign/{id}', 'BulkSmsController@viewCampaign');
        $this->addRoute('GET', '/admin/communications/templates', 'BulkSmsController@templates');
        
        // SMS Campaign Management Routes (dedicated page)
        $this->addRoute('GET', '/admin/sms-campaigns', 'BulkSmsController@index');
        
        // Email Campaign Management Routes
        $this->addRoute('GET', '/admin/email-campaigns', 'BulkEmailController@index');
        $this->addRoute('POST', '/admin/email-campaigns/create', 'BulkEmailController@createCampaign');
        $this->addRoute('POST', '/admin/email-campaigns/send', 'BulkEmailController@sendCampaign');
        $this->addRoute('POST', '/admin/email-campaigns/cancel', 'BulkEmailController@cancelCampaign');
        $this->addRoute('POST', '/admin/email-campaigns/pause', 'BulkEmailController@pauseCampaign');
        $this->addRoute('POST', '/admin/email-campaigns/reschedule', 'BulkEmailController@reschedule');
        $this->addRoute('POST', '/admin/email-campaigns/retry-failed', 'BulkEmailController@retryFailed');
        $this->addRoute('POST', '/admin/email-campaigns/quick-email', 'BulkEmailController@quickEmail');
        $this->addRoute('GET', '/admin/email-campaigns/campaign/{id}', 'BulkEmailController@viewCampaign');
        $this->addRoute('GET', '/admin/email-campaigns/templates', 'BulkEmailController@templates');
        
        $this->addRoute('GET', '/admin/settings', 'AdminController@settings');
        $this->addRoute('POST', '/admin/settings', 'AdminController@updateSettings');
        
        // Phase 4: M-Pesa Configuration Routes
        $this->addRoute('GET', '/admin/mpesa-config', 'AdminController@viewMpesaConfig');
        $this->addRoute('POST', '/admin/mpesa-config', 'AdminController@updateMpesaConfig');
        
        // Phase 4: Plan Upgrades Management Routes
        $this->addRoute('GET', '/admin/plan-upgrades', 'AdminController@viewPlanUpgrades');
        $this->addRoute('GET', '/admin/plan-upgrades/export', 'AdminController@exportPlanUpgrades');
        $this->addRoute('POST', '/admin/plan-upgrades/complete/{id}', 'AdminController@completePlanUpgrade');
        $this->addRoute('POST', '/admin/plan-upgrades/cancel/{id}', 'AdminController@cancelPlanUpgrade');
        $this->addRoute('POST', '/admin/plan-upgrades/approve/{id}', 'AdminController@approvePlanUpgrade');
        $this->addRoute('POST', '/admin/plan-upgrades/reject/{id}', 'AdminController@rejectPlanUpgrade');
        
        // Phase 4: Financial Dashboard Routes
        $this->addRoute('GET', '/admin/financial-dashboard', 'AdminController@viewFinancialDashboard');
        $this->addRoute('GET', '/admin/financial-dashboard/export', 'AdminController@exportFinancialReport');
        
        // Payment Reconciliation Routes (Admin Only) - Phase 2
        $this->addRoute('GET', '/admin/payments/reconciliation', 'PaymentController@viewReconciliation');
        $this->addRoute('GET', '/admin/payments-reconciliation', 'PaymentController@viewReconciliation');
        $this->addRoute('GET', '/admin/payments/unmatched', 'PaymentController@viewUnmatchedPayments');
        $this->addRoute('GET', '/admin/payments/{id}/matches', 'PaymentController@getPotentialMatches');
        $this->addRoute('POST', '/admin/payments/reconcile', 'PaymentController@manualReconcile');
        $this->addRoute('GET', '/admin/payments/reconciliation-stats', 'PaymentController@getReconciliationStats');
        
        // Agent Management Routes (Admin Only)
        $this->addRoute('GET', '/admin/agents', 'AgentController@index');
        $this->addRoute('GET', '/admin/agents/export-csv', 'AgentController@exportAgentsCSV');
        $this->addRoute('GET', '/admin/agents/create', 'AgentController@create');
        $this->addRoute('POST', '/admin/agents/store', 'AgentController@store');
        $this->addRoute('GET', '/admin/agents/view/{id}', 'AgentController@show');
        $this->addRoute('GET', '/admin/agents/edit/{id}', 'AgentController@edit');
        $this->addRoute('POST', '/admin/agents/update/{id}', 'AgentController@update');
        $this->addRoute('POST', '/admin/agents/status/{id}', 'AgentController@updateStatus');
        $this->addRoute('POST', '/admin/agents/reactivate-suspended', 'AgentController@reactivateSuspendedAgents');
        $this->addRoute('GET', '/admin/agents/performance-report', 'AgentController@performanceReport');
        $this->addRoute('GET', '/admin/commissions', 'AgentController@commissions');
        $this->addRoute('GET', '/admin/commissions/export', 'AgentController@exportCommissions');
        $this->addRoute('POST', '/admin/commissions/approve/{id}', 'AgentController@approveCommission');
        $this->addRoute('POST', '/admin/commissions/approve-all', 'AgentController@approveAllCommissions');
        $this->addRoute('POST', '/admin/commissions/pay/{id}', 'AgentController@markCommissionPaid');
        $this->addRoute('GET', '/admin/agents/resources', 'AgentController@resources');
        $this->addRoute('POST', '/admin/agents/resources/upload', 'AgentController@uploadResource');
        $this->addRoute('GET', '/admin/agents/resources/export', 'AgentController@exportResources');
        $this->addRoute('GET', '/admin/agents/resources/download/{id}', 'AgentController@downloadResource');
        $this->addRoute('POST', '/admin/agents/resources/delete/{id}', 'AgentController@deleteResource');

        
        // Payout Request Routes (Admin)
        $this->addRoute('GET', '/admin/payouts', 'AdminController@payoutRequests');
        $this->addRoute('POST', '/admin/payouts/{id}/process', 'AdminController@processPayoutRequest');


        // Agent Dashboard Routes (Agent Only)
        $this->addRoute('GET', '/agent/dashboard', 'AgentDashboardController@dashboard');
        $this->addRoute('GET', '/agent/profile', 'AgentDashboardController@profile');
        $this->addRoute('POST', '/agent/profile', 'AgentDashboardController@updateProfile');
        $this->addRoute('POST', '/agent/profile/update', 'AgentDashboardController@updateProfile');
        $this->addRoute('POST', '/agent/password/update', 'AgentDashboardController@updatePassword');
        $this->addRoute('GET', '/agent/members', 'AgentDashboardController@members');
        $this->addRoute('GET', '/agent/payouts', 'AgentDashboardController@payouts');
        $this->addRoute('POST', '/agent/payouts/request', 'AgentDashboardController@requestPayout');
        $this->addRoute('GET', '/agent/resources', 'AgentDashboardController@resources');
        $this->addRoute('GET', '/agent/resources/download/{id}', 'AgentDashboardController@downloadResource');
        $this->addRoute('GET', '/agent/member-details/{id}', 'AgentDashboardController@memberDetails');

        $this->addRoute('POST', '/agent/member-details/{id}/claim-request', 'AgentDashboardController@requestClaimAssistance');
        $this->addRoute('POST', '/agent/member-details/{id}/payment-assist', 'AgentDashboardController@requestPaymentAssistance');
        $this->addRoute('POST', '/agent/member-details/{id}/dependents/add', 'AgentDashboardController@addDependent');
        $this->addRoute('GET', '/agent/member-details/{id}/statement', 'AgentDashboardController@downloadStatement');
        $this->addRoute('GET', '/agent/support', 'AgentDashboardController@support');
        $this->addRoute('GET', '/agent/register-member', 'AgentDashboardController@registerMember');
        $this->addRoute('POST', '/agent/register-member/store', 'AgentDashboardController@storeRegisterMember');
        
        // Agent Notifications Page
        $this->addRoute('GET', '/agent/notifications', 'AgentDashboardController@notifications');
        $this->addRoute('POST', '/agent/notifications/mark-read', 'AgentDashboardController@markNotificationAsRead');
        $this->addRoute('POST', '/agent/notifications/mark-all-read', 'AgentDashboardController@markAllNotificationsAsRead');
        $this->addRoute('POST', '/agent/notifications/delete', 'AgentDashboardController@deleteNotification');
        $this->addRoute('POST', '/agent/notifications/clear-all', 'AgentDashboardController@clearAllNotifications');
        
        // Bulk SMS Routes (Admin & Manager)
        $this->addRoute('GET', '/admin/bulk-sms', 'BulkSmsController@index');
        $this->addRoute('GET', '/admin/bulk-sms/create', 'BulkSmsController@create');
        $this->addRoute('POST', '/admin/bulk-sms/store', 'BulkSmsController@store');
        $this->addRoute('GET', '/admin/bulk-sms/view/{id}', 'BulkSmsController@view');
        $this->addRoute('POST', '/admin/bulk-sms/send/{id}', 'BulkSmsController@send');
        $this->addRoute('POST', '/admin/bulk-sms/delete/{id}', 'BulkSmsController@delete');
        $this->addRoute('GET', '/admin/bulk-sms/preview-recipients', 'BulkSmsController@previewRecipients');
        
        // Settings Routes (Admin & Manager)
        $this->addRoute('GET', '/admin/notifications', 'AdminController@notifications');
        $this->addRoute('POST', '/admin/notifications/mark-read', 'AdminController@markNotificationAsRead');
        $this->addRoute('POST', '/admin/notifications/mark-all-read', 'AdminController@markAllNotificationsAsRead');
        $this->addRoute('GET', '/admin/notification-settings', 'SettingsController@index');
        $this->addRoute('POST', '/admin/settings/update', 'SettingsController@update');
        $this->addRoute('POST', '/admin/settings/test-fallback', 'SettingsController@testFallback');
        
        // API Routes
        $this->addRoute('POST', '/api/mpesa/callback', 'PaymentController@mpesaCallback');
        $this->addRoute('POST', '/api/payment/initiate', 'PaymentController@initiatePayment');
        // QR image route
        $this->addRoute('GET', '/qr/paybill', 'PaymentController@qrPaybill');
        
        // Payment Routes (Member & Public)
        $this->addRoute('POST', '/payment/initiate', 'PaymentController@initiatePayment');
        $this->addRoute('GET', '/payment/status', 'PaymentController@queryPaymentStatus');
        
        // Error Routes
        $this->addRoute('GET', '/error/404', 'ErrorController@notFound');
        $this->addRoute('GET', '/error/500', 'ErrorController@serverError');
        $this->addRoute('GET', '/error/403', 'ErrorController@forbidden');
    }
    
    public function addRoute($method, $path, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action
        ];
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }
        
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $method, $path)) {
                $this->executeRoute($route, $path);
                return;
            }
        }
        
        // No route found - 404
        header('HTTP/1.0 404 Not Found');
        $this->executeAction('ErrorController@notFound');
    }
    
    private function matchRoute($route, $method, $path)
    {
        if ($route['method'] !== $method) {
            return false;
        }
        
        $routePath = $route['path'];
        
        // Convert route parameters {id} to regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $path);
    }
    
    private function executeRoute($route, $path)
    {
        // Extract parameters from URL
        $routePath = $route['path'];
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        preg_match($pattern, $path, $matches);
        array_shift($matches); // Remove full match
        
        $this->executeAction($route['action'], $matches);
    }
    
    private function executeAction($action, $params = [])
    {
        list($controller, $method) = explode('@', $action);
        
        if (!class_exists($controller)) {
            throw new Exception("Controller {$controller} not found");
        }
        
        $controllerInstance = new $controller();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method {$method} not found in {$controller}");
        }
        
        call_user_func_array([$controllerInstance, $method], $params);
    }
}
