<?php
$page = 'notifications';
include __DIR__ . '/../layouts/member-header.php';

$preferences = $preferences ?? [];
$memberData = $member ?? [];
?>

<style>
main {
    padding: 0 !important;
    margin: 0 !important;
}

.notifications-container {
    padding: 30px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
    max-width: 100%;
    overflow-x: hidden;
}

.page-header {
    margin-bottom: 32px;
}

.page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.page-header p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.settings-grid {
    max-width: 900px;
    margin: 0 auto;
}

.settings-card {
    background: white;
    border-radius: 16px;
    padding: 0;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.settings-card-header {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    padding: 20px 28px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.settings-card-header i {
    font-size: 24px;
    color: white;
}

.settings-card-header h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin: 0;
}

.settings-card-body {
    padding: 28px;
}

.notification-option {
    padding: 20px 0;
    border-bottom: 1px solid #F3F4F6;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
}

.notification-option:last-child {
    border-bottom: none;
}

.notification-info {
    flex: 1;
}

.notification-info h4 {
    font-size: 15px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 6px 0;
}

.notification-info p {
    font-size: 13px;
    color: #6B7280;
    margin: 0;
    line-height: 1.6;
}

.toggle-switch {
    position: relative;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #E5E7EB;
    transition: .3s;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.toggle-switch input:disabled + .toggle-slider {
    opacity: 0.5;
    cursor: not-allowed;
}

.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}

.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: border-color 0.2s;
}

.form-select:focus {
    outline: none;
    border-color: #7F20B0;
    box-shadow: 0 0 0 3px rgba(127, 32, 176, 0.1);
}

.form-text {
    font-size: 12px;
    color: #6B7280;
    margin-top: 6px;
    display: block;
}

.save-btn {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(127, 32, 176, 0.4);
}

.contact-info-card {
    background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
    border: 2px solid #BAE6FD;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 16px;
}

.contact-info-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
    flex-shrink: 0;
}

.contact-info-content {
    flex: 1;
}

.contact-info-content h4 {
    font-size: 14px;
    font-weight: 700;
    color: #1E40AF;
    margin: 0 0 8px 0;
}

.contact-detail {
    font-size: 13px;
    color: #1E40AF;
    margin: 4px 0;
}

.contact-detail strong {
    font-weight: 600;
}

.contact-link {
    font-size: 13px;
    color: #2563EB;
    text-decoration: none;
    margin-top: 8px;
    display: inline-block;
    font-weight: 600;
}

.contact-link:hover {
    text-decoration: underline;
}

.section-divider {
    padding: 16px 28px;
    background: #F9FAFB;
    border-top: 1px solid #E5E7EB;
    border-bottom: 1px solid #E5E7EB;
}

.section-divider h4 {
    font-size: 13px;
    font-weight: 700;
    color: #6B7280;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.success-alert {
    background: #ECFDF5;
    border-left: 4px solid #10B981;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.success-alert i {
    color: #10B981;
    font-size: 20px;
}

.success-alert-text {
    font-size: 14px;
    color: #065F46;
    font-weight: 600;
}

.error-alert {
    background: #FEF2F2;
    border-left: 4px solid #EF4444;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.error-alert i {
    color: #EF4444;
    font-size: 20px;
}

.error-alert-text {
    font-size: 14px;
    color: #991B1B;
    font-weight: 600;
}
</style>

<div class="notifications-container">
    <div class="page-header">
        <h1>Notification Settings</h1>
        <p>Manage how and when you receive notifications from us</p>
    </div>

    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const flashMessages = [
                    <?php if (isset($_SESSION['success'])): ?>{ type: 'success', message: <?php echo json_encode($_SESSION['success']); ?> },<?php unset($_SESSION['success']); endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>{ type: 'error', message: <?php echo json_encode($_SESSION['error']); ?> },<?php unset($_SESSION['error']); endif; ?>
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

    <div class="settings-grid">
        <form action="/member/notification-settings" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
            
            <!-- Email Notifications -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="fas fa-envelope"></i>
                    <h3>Email Notifications</h3>
                </div>
                <div class="settings-card-body">
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Payment Reminders</h4>
                            <p>Receive email reminders before your payment is due</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_payment_reminders" value="1" 
                                   <?php echo ($preferences['email_payment_reminders'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Payment Confirmations</h4>
                            <p>Get instant email confirmation when payments are received</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_payment_confirmations" value="1" 
                                   <?php echo ($preferences['email_payment_confirmations'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Claim Updates</h4>
                            <p>Receive updates about your claim submissions and status changes</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_claim_updates" value="1" 
                                   <?php echo ($preferences['email_claim_updates'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Newsletters & Updates</h4>
                            <p>Stay informed with our latest news, tips, and member benefits</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_newsletters" value="1" 
                                   <?php echo ($preferences['email_newsletters'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- SMS Notifications -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="fas fa-sms"></i>
                    <h3>SMS Notifications</h3>
                </div>
                <div class="settings-card-body">
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Payment Reminders</h4>
                            <p>Get SMS reminders before payments are due</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_payment_reminders" value="1" 
                                   <?php echo ($preferences['sms_payment_reminders'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Payment Confirmations</h4>
                            <p>Instant SMS confirmation when your payments are processed</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_payment_confirmations" value="1" 
                                   <?php echo ($preferences['sms_payment_confirmations'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Claim Updates</h4>
                            <p>Get SMS notifications about important claim status changes</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_claim_updates" value="1" 
                                   <?php echo ($preferences['sms_claim_updates'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Critical Alerts</h4>
                            <p>Important account and security notifications (always enabled)</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_important_alerts" value="1" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="fas fa-sliders-h"></i>
                    <h3>General Preferences</h3>
                </div>
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label">Notification Frequency</label>
                        <select class="form-select" name="notification_frequency">
                            <option value="immediate" <?php echo ($preferences['notification_frequency'] ?? 'immediate') === 'immediate' ? 'selected' : ''; ?>>
                                Immediate - Receive notifications as they happen
                            </option>
                            <option value="daily_digest" <?php echo ($preferences['notification_frequency'] ?? '') === 'daily_digest' ? 'selected' : ''; ?>>
                                Daily Digest - Once per day summary
                            </option>
                            <option value="weekly_digest" <?php echo ($preferences['notification_frequency'] ?? '') === 'weekly_digest' ? 'selected' : ''; ?>>
                                Weekly Digest - Weekly summary
                            </option>
                        </select>
                        <small class="form-text">Choose how often you want to receive non-urgent notifications</small>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-info">
                            <h4>Marketing Communications</h4>
                            <p>Receive promotional offers, partner benefits, and special deals</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="marketing_communications" value="1" 
                                   <?php echo ($preferences['marketing_communications'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn">
                <i class="fas fa-save"></i>
                Save Preferences
            </button>
        </form>

        <!-- Contact Information -->
        <div style="margin-top: 24px;">
            <div class="contact-info-card">
                <div class="contact-info-icon">
                    <i class="fas fa-address-card"></i>
                </div>
                <div class="contact-info-content">
                    <h4>Your Contact Information</h4>
                    <div class="contact-detail"><strong>Email:</strong> <?php echo htmlspecialchars($memberData['email'] ?? 'N/A'); ?></div>
                    <div class="contact-detail"><strong>Phone:</strong> <?php echo htmlspecialchars($memberData['phone'] ?? 'N/A'); ?></div>
                    <a href="/profile" class="contact-link">
                        <i class="fas fa-edit"></i> Update Contact Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
