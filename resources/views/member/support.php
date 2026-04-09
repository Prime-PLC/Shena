<?php
$page = 'support';
include __DIR__ . '/../layouts/member-header.php';

$memberData = $member ?? [];
?>

<style>
main {
    padding: 0 !important;
    margin: 0 !important;
}

.support-container {
    padding: 30px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
    max-width: 100%;
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

.support-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

@media (max-width: 968px) {
    .support-grid {
        grid-template-columns: 1fr;
    }
}

.support-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.support-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.support-card h3 i {
    color: #7F3D9E;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-label .required {
    color: #EF4444;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
    font-family: 'Manrope', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: #7F3D9E;
    box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
    background: white;
    cursor: pointer;
}

.form-select:focus {
    outline: none;
    border-color: #7F3D9E;
    box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
}

.btn-submit {
    background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(127, 61, 158, 0.4);
}

.contact-info {
    list-style: none;
    padding: 0;
    margin: 0;
}

.contact-info li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E5E7EB;
}

.contact-info li:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #EDE9FE 0%, #F3E8FF 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7F3D9E;
    font-size: 18px;
    flex-shrink: 0;
}

.contact-details h6 {
    font-size: 14px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.contact-details p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.contact-details a {
    color: #7F3D9E;
    text-decoration: none;
    font-weight: 600;
}

.contact-details a:hover {
    text-decoration: underline;
}

.working-hours {
    background: #F9FAFB;
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.working-hours h6 {
    font-size: 14px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.working-hours h6 i {
    color: #7F3D9E;
}

.working-hours p {
    font-size: 13px;
    color: #6B7280;
    margin: 0 0 8px 0;
}

.working-hours p:last-child {
    margin-bottom: 0;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #D1FAE5;
    color: #065F46;
    border-left: 4px solid #10B981;
}

.alert-error {
    background: #FEE2E2;
    color: #991B1B;
    border-left: 4px solid #EF4444;
}

.alert i {
    font-size: 20px;
    flex-shrink: 0;
}

.faq-section {
    margin-top: 32px;
}

.faq-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s;
}

.faq-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 15px;
    font-weight: 600;
    color: #1F2937;
}

.faq-question i {
    color: #7F3D9E;
    transition: transform 0.2s;
}

.faq-item.active .faq-question i {
    transform: rotate(180deg);
}

.faq-answer {
    display: none;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #E5E7EB;
    font-size: 14px;
    color: #6B7280;
    line-height: 1.6;
}

.faq-item.active .faq-answer {
    display: block;
}
</style>

<div class="support-container">
    <div class="page-header">
        <h1>Contact Support</h1>
        <p>We're here to help! Send us a message or reach out through our contact channels</p>
    </div>

    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const flashMessages = [
                    <?php if (isset($_SESSION['success'])): ?>{ type: 'success', message: <?php echo json_encode($_SESSION['success']); ?> },<?php unset($_SESSION['success']); endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>{ type: 'error', message: <?php echo json_encode($_SESSION['error']); ?> },<?php unset($_SESSION['error']); endif; ?>
                ];

                flashMessages.forEach(function(flash) {
                    if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                        ShenaApp.showNotification(flash.message, flash.type, 5000);
                        return;
                    }
                    alert(flash.message);
                });
            });
        </script>
    <?php endif; ?>

    <div class="support-grid">
        <!-- Support Form -->
        <div class="support-card">
            <h3><i class="fas fa-envelope"></i>Send Us a Message</h3>
            
            <form action="/member/support/submit" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                
                <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($memberData['first_name'] ?? '' . ' ' . $memberData['last_name'] ?? ''); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($memberData['email'] ?? ''); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Subject <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" name="subject" placeholder="Brief description of your issue" value="<?php echo getOldValue('subject'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Priority Level <span class="required">*</span>
                    </label>
                    <select class="form-select" name="priority" required>
                        <option value="low" <?php echo isOldValueSelected('priority', 'low'); ?>>Low - General inquiry</option>
                        <option value="normal" <?php echo isOldValueSelected('priority', 'normal') ?: (empty(getOldValue('priority')) ? 'selected' : ''); ?>>Normal - Standard support</option>
                        <option value="high" <?php echo isOldValueSelected('priority', 'high'); ?>>High - Urgent matter</option>
                        <option value="critical" <?php echo isOldValueSelected('priority', 'critical'); ?>>Critical - Immediate attention needed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Message <span class="required">*</span>
                    </label>
                    <textarea class="form-control" name="message" placeholder="Please describe your issue or question in detail..." required><?php echo getOldValue('message'); ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </button>
            </form>
        </div>

        <!-- Contact Information -->
        <div class="support-card">
            <h3><i class="fas fa-phone-alt"></i>Contact Information</h3>
            
            <ul class="contact-info">
                <?php
                    $memberSupportPhone   = defined('ADMIN_PHONE') ? ADMIN_PHONE : '+254748585067';
                    $memberSupportPhoneWa = ltrim(preg_replace('/[^0-9]/', '', $memberSupportPhone), '0');
                ?>
                <li>
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h6>Phone</h6>
                        <p><a href="tel:<?php echo htmlspecialchars($memberSupportPhone); ?>"><?php echo htmlspecialchars($memberSupportPhone); ?></a></p>
                    </div>
                </li>
                
                <li>
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h6>Email</h6>
                        <p><a href="mailto:info@shenacompanion.org">info@shenacompanion.org</a></p>
                    </div>
                </li>
                
                <li>
                    <div class="contact-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="contact-details">
                        <h6>WhatsApp</h6>
                        <p><a href="https://wa.me/<?php echo $memberSupportPhoneWa; ?>" target="_blank"><?php echo htmlspecialchars($memberSupportPhone); ?></a></p>
                    </div>
                </li>
                
                <li>
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h6>Office Location</h6>
                        <p>Kisumu, Kenya<br>P.O. Box 40148</p>
                    </div>
                </li>
            </ul>
            
            <div class="working-hours">
                <h6><i class="fas fa-clock"></i>Working Hours</h6>
                <p><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</p>
                <p><strong>Saturday:</strong> 9:00 AM - 1:00 PM</p>
                <p><strong>Sunday & Holidays:</strong> Closed</p>
                <p style="margin-top: 12px; color: #7F3D9E; font-weight: 600;">
                    <i class="fas fa-info-circle"></i> Emergency support available 24/7
                </p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <div class="support-card">
            <h3><i class="fas fa-question-circle"></i>Frequently Asked Questions</h3>
            
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question">
                    <span>How long does it take to process a claim?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Claims are typically processed within 3-5 business days after all required documents are submitted and verified.
                </div>
            </div>
            
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question">
                    <span>How can I update my payment method?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    You can update your payment method in your profile settings or contact support for assistance with payment updates.
                </div>
            </div>
            
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question">
                    <span>Can I add more dependents to my account?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Yes! You can add dependents through your beneficiaries page. The number of dependents depends on your membership package.
                </div>
            </div>
            
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question">
                    <span>What happens if I miss a payment?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    If you miss a payment, your account will be marked as inactive after 30 days. You can reactivate by clearing any outstanding balance.
                </div>
            </div>
            
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question">
                    <span>How do I upgrade my membership plan?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    You can upgrade your plan anytime through the "Upgrade Plan" section in your dashboard. The upgrade is prorated based on your current billing cycle.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
