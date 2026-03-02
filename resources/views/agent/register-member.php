<?php 
$page = 'register'; 
include __DIR__ . '/../layouts/agent-header.php';

// Helper to get old form data or empty string
$getOldValue = function($field) {
    $old = $_SESSION['form_data'][$field] ?? '';
    // Clear form data after first use so it doesn't persist
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data'][$field]);
        if (empty($_SESSION['form_data'])) {
            unset($_SESSION['form_data']);
        }
    }
    return htmlspecialchars($old);
};
?>

<style>
/* Register Member Page Styles */
.register-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

.register-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
}

.register-title-section h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.register-title-section p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.btn-back {
    background: white;
    color: #6B7280;
    border: 1px solid #E5E7EB;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-back:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
    color: #4B5563;
}

.registration-form-card {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-section {
    margin-bottom: 40px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #F3F4F6;
}

.section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #7F20B0 0%, #9D3CC9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.section-title {
    flex: 1;
}

.section-title h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 2px 0;
}

.section-title p {
    font-size: 13px;
    color: #9CA3AF;
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-label .required {
    color: #DC2626;
    margin-left: 2px;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    font-size: 14px;
    color: #1F2937;
    background: white;
    transition: all 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #7F20B0;
    box-shadow: 0 0 0 3px rgba(127, 32, 176, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-hint {
    display: block;
    font-size: 12px;
    color: #9CA3AF;
    margin-top: 6px;
}

.package-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.package-option {
    position: relative;
}

.package-radio {
    position: absolute;
    opacity: 0;
}

.package-label {
    display: block;
    padding: 20px;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.package-radio:checked + .package-label {
    border-color: #7F20B0;
    background: #F9F5FF;
}

.package-radio:checked + .package-label::after {
    content: '';
    position: absolute;
    top: 12px;
    right: 12px;
    width: 24px;
    height: 24px;
    background: #7F20B0;
    border-radius: 50%;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z'/%3E%3C/svg%3E");
    background-size: 14px;
    background-position: center;
    background-repeat: no-repeat;
}

.package-name {
    font-size: 16px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 4px;
}

.package-price {
    font-size: 20px;
    font-weight: 700;
    color: #7F20B0;
    margin-bottom: 8px;
}

.package-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.package-features li {
    font-size: 13px;
    color: #6B7280;
    padding: 4px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.package-features li::before {
    content: '✓';
    color: #10B981;
    font-weight: 700;
}

.form-checkbox {
    display: flex;
    align-items: start;
    gap: 12px;
}

.form-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    border: 2px solid #D1D5DB;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 2px;
}

.form-checkbox input[type="checkbox"]:checked {
    background: #7F20B0;
    border-color: #7F20B0;
}

.form-checkbox label {
    font-size: 14px;
    color: #4B5563;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 32px;
    border-top: 2px solid #F3F4F6;
}

.btn-reset {
    background: white;
    color: #6B7280;
    border: 1px solid #E5E7EB;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-reset:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
}

.btn-submit {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

.error-message {
    background: #FEE2E2;
    color: #991B1B;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.success-message {
    background: #D1FAE5;
    color: #065F46;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 768px) {
    .register-container {
        padding: 20px 15px;
    }

    .register-header {
        flex-direction: column;
        gap: 16px;
    }

    .btn-back {
        width: 100%;
        justify-content: center;
    }

    .registration-form-card {
        padding: 24px;
    }

    .form-grid,
    .package-options {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-reset,
    .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="register-container">
    <div class="register-header">
        <div class="register-title-section">
            <h1>Register New Member</h1>
            <p>Add a new member to your portfolio and earn commission</p>
        </div>
        <a href="/agent/members" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Members
        </a>
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

    <div class="registration-form-card">
        <form method="POST" action="/agent/register-member/store" id="memberRegistrationForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
            
            <!-- Personal Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="section-title">
                        <h3>Personal Information</h3>
                        <p>Basic details about the member</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" class="form-input" id="first_name" name="first_name" required placeholder="Enter first name" value="<?php echo $getOldValue('first_name'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" class="form-input" id="last_name" name="last_name" required placeholder="Enter last name" value="<?php echo $getOldValue('last_name'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="id_number" class="form-label">National ID Number <span class="required">*</span></label>
                        <input type="text" class="form-input" id="id_number" name="id_number" required placeholder="e.g., 12345678" value="<?php echo $getOldValue('id_number'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth" class="form-label">Date of Birth <span class="required">*</span></label>
                        <input type="date" class="form-input" id="date_of_birth" name="date_of_birth" required value="<?php echo $getOldValue('date_of_birth'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo $getOldValue('gender') === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $getOldValue('gender') === 'female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <div class="section-title">
                        <h3>Contact Information</h3>
                        <p>How we can reach the member</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" class="form-input" id="phone" name="phone" required placeholder="+254712345678" value="<?php echo $getOldValue('phone'); ?>">
                        <small class="form-hint">Format: +254712345678</small>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-input" id="email" name="email" placeholder="member@example.com" value="<?php echo $getOldValue('email'); ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="address" class="form-label">Physical Address</label>
                        <textarea class="form-textarea" id="address" name="address" rows="3" placeholder="Enter physical address"><?php echo $getOldValue('address'); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Next of Kin Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="section-title">
                        <h3>Next of Kin Information</h3>
                        <p>Emergency contact person</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="next_of_kin" class="form-label">Next of Kin Name <span class="required">*</span></label>
                        <input type="text" class="form-input" id="next_of_kin" name="next_of_kin" required placeholder="Full name" value="<?php echo $getOldValue('next_of_kin'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="next_of_kin_phone" class="form-label">Next of Kin Phone <span class="required">*</span></label>
                        <input type="tel" class="form-input" id="next_of_kin_phone" name="next_of_kin_phone" required placeholder="+254712345678" value="<?php echo $getOldValue('next_of_kin_phone'); ?>">
                    </div>
                </div>
            </div>

            <!-- Package Selection Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="section-title">
                        <h3>Package Selection</h3>
                        <p>Choose the best coverage plan</p>
                    </div>
                </div>
                
                <div class="package-options">
                    <div class="package-option">
                        <input type="radio" class="package-radio" id="package_individual" name="package" value="individual" required <?php echo $getOldValue('package') === 'individual' ? 'checked' : ''; ?>>
                        <label for="package_individual" class="package-label">
                            <div class="package-name">Individual Plan</div>
                            <div class="package-price">KES 500/month</div>
                            <ul class="package-features">
                                <li>Individual coverage</li>
                                <li>Funeral benefits</li>
                                <li>24/7 support</li>
                            </ul>
                        </label>
                    </div>
                    <div class="package-option">
                        <input type="radio" class="package-radio" id="package_couple" name="package" value="couple" <?php echo $getOldValue('package') === 'couple' ? 'checked' : ''; ?>>
                        <label for="package_couple" class="package-label">
                            <div class="package-name">Couple Plan</div>
                            <div class="package-price">KES 800/month</div>
                            <ul class="package-features">
                                <li>Couple coverage</li>
                                <li>Extended benefits</li>
                                <li>Priority support</li>
                            </ul>
                        </label>
                    </div>
                    <div class="package-option">
                        <input type="radio" class="package-radio" id="package_family" name="package" value="family" <?php echo $getOldValue('package') === 'family' ? 'checked' : ''; ?>>
                        <label for="package_family" class="package-label">
                            <div class="package-name">Family Plan</div>
                            <div class="package-price">KES 1,200/month</div>
                            <ul class="package-features">
                                <li>Full family coverage</li>
                                <li>Comprehensive benefits</li>
                                <li>Dedicated support</li>
                            </ul>
                        </label>
                    </div>
                    <div class="package-option">
                        <input type="radio" class="package-radio" id="package_executive" name="package" value="executive" <?php echo $getOldValue('package') === 'executive' ? 'checked' : ''; ?>>
                        <label for="package_executive" class="package-label">
                            <div class="package-name">Executive Plan</div>
                            <div class="package-price">KES 2,000/month</div>
                            <ul class="package-features">
                                <li>Premium coverage</li>
                                <li>VIP benefits</li>
                                <li>Concierge service</li>
                            </ul>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Password Section -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="section-title">
                        <h3>Account Security</h3>
                        <p>Login credentials for the member</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <input type="password" class="form-input" id="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                        <small class="form-hint">Must be at least 8 characters long</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                        <input type="password" class="form-input" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-section">
                <div class="form-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I confirm that the member has read and agreed to the <strong>terms and conditions</strong> of Shena Companion Welfare Association
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="reset" class="btn-reset">
                    <i class="fas fa-undo"></i>
                    Reset Form
                </button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i>
                    Register Member
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
document.getElementById('memberRegistrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        ShenaApp.showNotification('Passwords do not match! Please ensure both password fields are identical.', 'warning');
        document.getElementById('confirm_password').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('.btn-submit');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('0')) {
        value = '254' + value.substring(1);
    }
    if (!value.startsWith('254')) {
        value = '254' + value;
    }
    e.target.value = '+' + value.substring(0, 12);
});

document.getElementById('next_of_kin_phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('0')) {
        value = '254' + value.substring(1);
    }
    if (!value.startsWith('254')) {
        value = '254' + value;
    }
    e.target.value = '+' + value.substring(0, 12);
});
</script>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
