<?php 
$page = 'support'; 
include __DIR__ . '/../layouts/agent-header.php';
?>

<style>
.support-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

.support-header {
    margin-bottom: 32px;
}

.support-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 8px 0;
}

.support-header p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.support-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.support-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.support-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.support-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7F20B0 0%, #9D3CC9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-bottom: 20px;
}

.support-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 12px 0;
}

.support-card p {
    font-size: 14px;
    color: #6B7280;
    line-height: 1.6;
    margin-bottom: 20px;
}

.support-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.support-info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #374151;
}

.support-info-item i {
    width: 24px;
    text-align: center;
    color: #7F20B0;
}

.support-info-item strong {
    color: #1F2937;
}

.btn-contact {
    background: linear-gradient(135deg, #7F20B0 0%, #9D3CC9 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    margin-top: 16px;
}

.btn-contact:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.faq-section {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.faq-section h2 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 24px 0;
}

.faq-item {
    padding: 20px 0;
    border-bottom: 1px solid #E5E7EB;
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    font-weight: 600;
    font-size: 16px;
    color: #1F2937;
    margin-bottom: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.faq-question i {
    color: #7F20B0;
    font-size: 12px;
}

.faq-answer {
    font-size: 14px;
    color: #6B7280;
    line-height: 1.6;
    padding-left: 0;
}
</style>

<div class="support-container">
    <div class="support-header">
        <h1>Agent Support</h1>
        <p>Get help with registration, commissions, and member management</p>
    </div>

    <div class="support-grid">
        <!-- Email Support -->
        <div class="support-card">
            <div class="support-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h3>Email Support</h3>
            <p>Send us an email and we'll get back to you within 24 hours</p>
            <div class="support-info">
                <div class="support-info-item">
                    <i class="fas fa-at"></i>
                    <span><strong>Email:</strong> agents@shenacompanion.co.ke</span>
                </div>
                <div class="support-info-item">
                    <i class="fas fa-clock"></i>
                    <span>Response time: 24 hours</span>
                </div>
            </div>
            <button class="btn-contact" onclick="window.location.href='mailto:agents@shenacompanion.co.ke'">
                <i class="fas fa-paper-plane"></i> Send Email
            </button>
        </div>

        <!-- Phone Support -->
        <?php
            $supportPhone   = defined('ADMIN_PHONE') ? ADMIN_PHONE : '+254748585067';
            $supportPhoneWa = ltrim(preg_replace('/[^0-9]/', '', $supportPhone), '0');
            $supportPhoneDisplay = preg_replace('/^\+/', '', $supportPhone);
            $supportPhoneDisplay = '+' . wordwrap(ltrim($supportPhoneDisplay, '+'), 3, ' ', true);
        ?>
        <div class="support-card">
            <div class="support-icon">
                <i class="fas fa-phone"></i>
            </div>
            <h3>Phone Support</h3>
            <p>Call our agent support line for immediate assistance</p>
            <div class="support-info">
                <div class="support-info-item">
                    <i class="fas fa-phone-alt"></i>
                    <span><strong>Hotline:</strong> <?php echo htmlspecialchars($supportPhone); ?></span>
                </div>
                <div class="support-info-item">
                    <i class="fas fa-clock"></i>
                    <span>Mon - Fri: 8:00 AM - 6:00 PM</span>
                </div>
            </div>
            <button class="btn-contact" onclick="window.location.href='tel:<?php echo htmlspecialchars($supportPhone); ?>'">
                <i class="fas fa-phone"></i> Call Now
            </button>
        </div>

        <!-- WhatsApp Support -->
        <div class="support-card">
            <div class="support-icon">
                <i class="fab fa-whatsapp"></i>
            </div>
            <h3>WhatsApp Support</h3>
            <p>Chat with our support team on WhatsApp</p>
            <div class="support-info">
                <div class="support-info-item">
                    <i class="fab fa-whatsapp"></i>
                    <span><strong>Number:</strong> <?php echo htmlspecialchars($supportPhone); ?></span>
                </div>
                <div class="support-info-item">
                    <i class="fas fa-clock"></i>
                    <span>Mon - Sat: 8:00 AM - 8:00 PM</span>
                </div>
            </div>
            <button class="btn-contact" onclick="window.open('https://wa.me/<?php echo $supportPhoneWa; ?>', '_blank')">
                <i class="fab fa-whatsapp"></i> Chat on WhatsApp
            </button>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>How do I register a new member?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Go to the Members section and click "Register New Member". Fill in all required information including ID number, contact details, and package selection. The member will receive login credentials via email.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>When will I receive my commission?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Commissions are processed within 48 hours after a member's payment is confirmed. You'll receive a notification once your commission is approved and paid to your registered M-Pesa number.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>How can I track my members' status?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Visit the Members section to see all your registered members. You can view their payment status, policy details, and claim history by clicking on any member's profile.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>What documents are required for member registration?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                You'll need the member's National ID or Passport, proof of residence, and contact information. For dependents, birth certificates or ID cards are required.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>How do I update my profile information?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Click on your profile icon at the top right, then select "Profile". You can update your contact information, address, and payment details. Remember to save changes before exiting.
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
