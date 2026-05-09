<?php 
$page = 'register'; 
$initialStep = (int)($_SESSION['error_step'] ?? 1);
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['error_step'], $_SESSION['error']);
include __DIR__ . '/../layouts/agent-header.php';

// Helper to get old form data or empty string
$getOldValue = function($field) {
    $old = $_SESSION['form_data'][$field] ?? '';
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

.form-stepper {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-bottom: 28px;
}

.step-pill {
    border: 1px solid #E5E7EB;
    background: #F9FAFB;
    color: #6B7280;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12px;
    font-weight: 700;
}

.step-pill.active {
    background: #F5F0FB;
    border-color: #7F20B0;
    color: #4C1D95;
}

.step-pill.done {
    background: #ECFDF5;
    border-color: #10B981;
    color: #065F46;
}

.form-section {
    margin-bottom: 40px;
}

.form-section[data-step] {
    display: none;
}

.form-section[data-step].active {
    display: block;
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
    .package-options,
    .form-stepper {
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

    <?php if ($flashError): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const flashMessages = [
                    { type: 'error', message: <?php echo json_encode($flashError); ?> }
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

    <div class="registration-form-card">
        <form method="POST" action="/agent/register-member/store" id="memberRegistrationForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
            <div class="form-stepper" aria-label="Registration progress">
                <div class="step-pill active" data-step-indicator="1">1. Personal</div>
                <div class="step-pill" data-step-indicator="2">2. Contact</div>
                <div class="step-pill" data-step-indicator="3">3. Next of Kin</div>
                <div class="step-pill" data-step-indicator="4">4. Package</div>
                <div class="step-pill" data-step-indicator="5">5. Access</div>
                <div class="step-pill" data-step-indicator="6">6. Confirm</div>
            </div>
            
            <!-- Personal Information Section -->
            <div class="form-section active" data-step="1">
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
            <div class="form-section" data-step="2">
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
            <div class="form-section" data-step="3">
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
            <div class="form-section" data-step="4">
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
                    <?php foreach (($packages ?? []) as $packageKey => $package): ?>
                        <div class="package-option">
                            <input
                                type="radio"
                                class="package-radio"
                                id="package_<?php echo htmlspecialchars($packageKey); ?>"
                                name="package"
                                value="<?php echo htmlspecialchars($packageKey); ?>"
                                required
                                <?php echo $getOldValue('package') === $packageKey ? 'checked' : ''; ?>
                            >
                            <label for="package_<?php echo htmlspecialchars($packageKey); ?>" class="package-label">
                                <div class="package-name"><?php echo htmlspecialchars($package['name'] ?? $packageKey); ?></div>
                                <div class="package-price">KES <?php echo number_format((float)($package['monthly_contribution'] ?? 0)); ?>/month</div>
                                <ul class="package-features">
                                    <li><?php echo htmlspecialchars($package['description'] ?? 'Standard coverage package'); ?></li>
                                    <li>Category: <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string)($package['category'] ?? '')))); ?></li>
                                    <?php if (isset($package['age_min'], $package['age_max'])): ?>
                                        <li>Age Band: <?php echo (int)$package['age_min']; ?> - <?php echo (int)$package['age_max']; ?> years</li>
                                    <?php endif; ?>
                                </ul>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-grid" style="margin-top: 16px;">
                    <div class="form-group">
                        <label for="corporate_couple_count" class="form-label">Corporate Couple Count</label>
                        <select class="form-select" id="corporate_couple_count" name="corporate_couple_count">
                            <option value="0" <?php echo $getOldValue('corporate_couple_count') === '0' ? 'selected' : ''; ?>>None</option>
                            <option value="1" <?php echo $getOldValue('corporate_couple_count') === '1' ? 'selected' : ''; ?>>1 Additional Couple (+KES 150)</option>
                            <option value="2" <?php echo $getOldValue('corporate_couple_count') === '2' ? 'selected' : ''; ?>>2 Additional Couples (+KES 300)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Account Security Notice -->
            <div class="form-section" data-step="5">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="section-title">
                        <h3>Account Security</h3>
                        <p>How the member will access the portal</p>
                    </div>
                </div>
                <div class="alert alert-info" style="border-radius:8px;">
                    <i class="fas fa-sms me-2"></i>
                    <strong>Activation link will be sent automatically.</strong><br>
                    After registration, the member will receive an SMS with a secure link to set their own password. The link expires in 48 hours.
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-section" data-step="6">
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
                <button type="button" class="btn-reset step-back" style="display:none;">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </button>
                <button type="button" class="btn-submit step-next">
                    Continue
                    <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn-submit step-submit" style="display:none;">
                    <i class="fas fa-user-plus"></i>
                    Register Member
                </button>
            </div>
        </form>
        <?php unset($_SESSION['form_data']); ?>
    </div>
</div>

<script>
const memberForm = document.getElementById('memberRegistrationForm');
const memberSections = Array.from(memberForm.querySelectorAll('.form-section[data-step]'));
const memberIndicators = Array.from(memberForm.querySelectorAll('[data-step-indicator]'));
const memberBackBtn = memberForm.querySelector('.step-back');
const memberNextBtn = memberForm.querySelector('.step-next');
const memberSubmitBtn = memberForm.querySelector('.step-submit');
let memberCurrentStep = 1;
const memberInitialStep = <?php echo json_encode(max(1, min(6, $initialStep))); ?>;

function showMemberStep(step) {
    memberCurrentStep = step;
    memberSections.forEach(function (section) {
        const isActive = Number(section.dataset.step) === step;
        section.classList.toggle('active', isActive);
        section.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.disabled = !isActive;
        });
    });
    memberIndicators.forEach(function (indicator) {
        const indicatorStep = Number(indicator.dataset.stepIndicator);
        indicator.classList.toggle('active', indicatorStep === step);
        indicator.classList.toggle('done', indicatorStep < step);
    });
    memberBackBtn.style.display = step > 1 ? '' : 'none';
    memberNextBtn.style.display = step < memberSections.length ? '' : 'none';
    memberSubmitBtn.style.display = step === memberSections.length ? '' : 'none';
}

function validateMemberStep() {
    const section = memberForm.querySelector('.form-section[data-step="' + memberCurrentStep + '"]');
    const fields = Array.from(section.querySelectorAll('input, select, textarea'));
    for (const field of fields) {
        if (!field.checkValidity()) {
            field.reportValidity();
            return false;
        }
    }
    return true;
}

memberNextBtn.addEventListener('click', function () {
    if (validateMemberStep()) showMemberStep(Math.min(memberCurrentStep + 1, memberSections.length));
});

memberBackBtn.addEventListener('click', function () {
    showMemberStep(Math.max(memberCurrentStep - 1, 1));
});

memberForm.addEventListener('submit', function(e) {
    if (!validateMemberStep()) {
        e.preventDefault();
        return;
    }
    memberSections.forEach(function (section) {
        section.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.disabled = false;
        });
    });
    const submitBtn = this.querySelector('.step-submit');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
});

showMemberStep(memberInitialStep);

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

(function () {
    const form = document.getElementById('memberRegistrationForm');
    const storageKey = 'shena_agent_member_registration_draft';
    if (!form || !window.localStorage) return;

    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
        Object.keys(saved).forEach(function (name) {
            const field = form.elements[name];
            if (field && !field.value && name !== 'csrf_token') field.value = saved[name];
        });
    } catch (_) {}

    form.addEventListener('input', function () {
        const data = {};
        Array.from(form.elements).forEach(function (field) {
            if (!field.name || field.name === 'csrf_token') return;
            if ((field.type === 'radio' || field.type === 'checkbox') && !field.checked) return;
            data[field.name] = field.value;
        });
        localStorage.setItem(storageKey, JSON.stringify(data));
    });

    form.addEventListener('submit', function () {
        localStorage.removeItem(storageKey);
    });
})();
</script>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
