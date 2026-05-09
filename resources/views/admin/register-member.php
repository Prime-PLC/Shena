<?php
$old = $_SESSION['form_data'] ?? [];
$flashError = $_SESSION['error'] ?? '';
$initialStep = (int)($_SESSION['error_step'] ?? 1);
unset($_SESSION['error'], $_SESSION['error_step']);
include_once __DIR__ . '/../layouts/admin-header.php';
$oldValue = function ($field, $default = '') use ($old) {
    return htmlspecialchars((string)($old[$field] ?? $default), ENT_QUOTES);
};
?>

<style>
    .registration-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .form-header {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px 12px 0 0;
        margin-bottom: 0;
    }

    .form-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .form-header p {
        margin: 0;
        opacity: 0.9;
    }

    .registration-form {
        background: white;
        border-radius: 0 0 12px 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-stepper {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 24px;
    }

    .step-pill {
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        color: #6B7280;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.82rem;
        font-weight: 700;
        text-align: left;
    }

    .step-pill.active {
        background: #F5F0FB;
        border-color: #7F3D9E;
        color: #4C1D95;
    }

    .step-pill.done {
        background: #ECFDF5;
        border-color: #10B981;
        color: #065F46;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section[data-step] {
        display: none;
    }

    .form-section[data-step].active {
        display: block;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #E5E7EB;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-input,
    .form-select {
        padding: 0.75rem;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #E5E7EB;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #6B7280;
        border: 1px solid #D1D5DB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
    }

    .required {
        color: #EF4444;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .registration-container {
            padding: 16px;
        }
        .form-stepper {
            grid-template-columns: 1fr;
        }
        .form-actions {
            flex-direction: column;
        }
        .btn {
            width: 100%;
        }
    }
</style>

<div class="registration-container">
    <div class="form-header">
        <h1><i class="fas fa-user-plus"></i> Register New Member</h1>
        <p>Complete the form below to register a new member</p>
    </div>

    <form class="registration-form" action="/admin/members/register" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
        <div class="form-stepper" aria-label="Registration progress">
            <div class="step-pill active" data-step-indicator="1">1. Personal</div>
            <div class="step-pill" data-step-indicator="2">2. Contact</div>
            <div class="step-pill" data-step-indicator="3">3. Membership</div>
            <div class="step-pill" data-step-indicator="4">4. Next of Kin</div>
        </div>
        <!-- Personal Information -->
        <div class="form-section active" data-step="1">
            <h2 class="section-title">Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-input" value="<?php echo $oldValue('first_name'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-input" value="<?php echo $oldValue('last_name'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">National ID <span class="required">*</span></label>
                    <input type="text" name="id_number" class="form-input" value="<?php echo $oldValue('id_number'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth <span class="required">*</span></label>
                    <input type="date" name="date_of_birth" class="form-input" value="<?php echo $oldValue('date_of_birth'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender <span class="required">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo (($old['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (($old['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Select Status</option>
                        <option value="single" <?php echo (($old['marital_status'] ?? '') === 'single') ? 'selected' : ''; ?>>Single</option>
                        <option value="married" <?php echo (($old['marital_status'] ?? '') === 'married') ? 'selected' : ''; ?>>Married</option>
                        <option value="divorced" <?php echo (($old['marital_status'] ?? '') === 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="widowed" <?php echo (($old['marital_status'] ?? '') === 'widowed') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="form-section" data-step="2">
            <h2 class="section-title">Contact Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone Number <span class="required">*</span></label>
                    <input type="tel" name="phone" class="form-input" placeholder="0712345678" value="<?php echo $oldValue('phone'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address <small class="text-muted">(optional)</small></label>
                    <input type="email" name="email" class="form-input" value="<?php echo $oldValue('email'); ?>">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Physical Address</label>
                    <input type="text" name="address" class="form-input" value="<?php echo $oldValue('address'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">County</label>
                    <input type="text" name="county" class="form-input" value="<?php echo $oldValue('county'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sub-County</label>
                    <input type="text" name="sub_county" class="form-input" value="<?php echo $oldValue('sub_county'); ?>">
                </div>
            </div>
        </div>

        <!-- Membership Details -->
        <div class="form-section" data-step="3">
            <h2 class="section-title">Membership Details</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Package <span class="required">*</span></label>
                    <select name="package" class="form-select" required>
                        <option value="">Select Package</option>
                        <?php foreach (($packages ?? []) as $packageKey => $package): ?>
                            <option value="<?php echo htmlspecialchars($packageKey); ?>" <?php echo (($old['package'] ?? '') === $packageKey) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(($package['name'] ?? $packageKey) . ' - KES ' . number_format((float)($package['monthly_contribution'] ?? 0), 0) . '/month'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Corporate Couple Count</label>
                    <select name="corporate_couple_count" class="form-select">
                        <option value="0" <?php echo (($old['corporate_couple_count'] ?? '0') === '0') ? 'selected' : ''; ?>>None</option>
                        <option value="1" <?php echo (($old['corporate_couple_count'] ?? '') === '1') ? 'selected' : ''; ?>>1 Additional Couple (+KES 150)</option>
                        <option value="2" <?php echo (($old['corporate_couple_count'] ?? '') === '2') ? 'selected' : ''; ?>>2 Additional Couples (+KES 300)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Referred By (Agent Number)</label>
                    <input type="text" name="agent_number" class="form-input" placeholder="Optional" value="<?php echo $oldValue('agent_number'); ?>">
                </div>
            </div>
        </div>

        <!-- Next of Kin -->
        <div class="form-section" data-step="4">
            <h2 class="section-title">Next of Kin <small class="text-muted" style="font-size:0.8rem;font-weight:400">(optional — can be added later)</small></h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="next_of_kin_name" class="form-input" value="<?php echo $oldValue('next_of_kin_name'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="next_of_kin_relationship" class="form-input" value="<?php echo $oldValue('next_of_kin_relationship'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="next_of_kin_phone" class="form-input" value="<?php echo $oldValue('next_of_kin_phone'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">National ID</label>
                    <input type="text" name="next_of_kin_id" class="form-input" value="<?php echo $oldValue('next_of_kin_id'); ?>">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='/admin/members';">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-secondary step-back" style="display:none;">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button type="button" class="btn btn-primary step-next">
                Continue <i class="fas fa-arrow-right"></i>
            </button>
            <button type="submit" class="btn btn-primary step-submit" style="display:none;">
                <i class="fas fa-check"></i> Register Member
            </button>
        </div>
    </form>
    <?php unset($_SESSION['form_data']); ?>
</div>

<script>
(function () {
    const form = document.querySelector('.registration-form');
    const storageKey = 'shena_admin_member_registration_draft';
    if (!form || !window.localStorage) return;
    const sections = Array.from(form.querySelectorAll('.form-section[data-step]'));
    const indicators = Array.from(form.querySelectorAll('[data-step-indicator]'));
    const backBtn = form.querySelector('.step-back');
    const nextBtn = form.querySelector('.step-next');
    const submitBtn = form.querySelector('.step-submit');
    let currentStep = 1;
    const initialStep = <?php echo json_encode(max(1, min(4, $initialStep))); ?>;
    const flashError = <?php echo json_encode($flashError); ?>;

    function showFlash(message, type) {
        if (!message) return;
        const show = function () {
            if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                ShenaApp.alert(message, type);
                return;
            }
            if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                ShenaApp.showNotification(message, type, 7000);
                return;
            }
            console.warn(message);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', show, { once: true });
        } else {
            show();
        }
    }

    function showStep(step) {
        currentStep = step;
        sections.forEach(function (section) {
            const isActive = Number(section.dataset.step) === step;
            section.classList.toggle('active', isActive);
            section.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.disabled = !isActive;
            });
        });
        indicators.forEach(function (indicator) {
            const indicatorStep = Number(indicator.dataset.stepIndicator);
            indicator.classList.toggle('active', indicatorStep === step);
            indicator.classList.toggle('done', indicatorStep < step);
        });
        backBtn.style.display = step > 1 ? '' : 'none';
        nextBtn.style.display = step < sections.length ? '' : 'none';
        submitBtn.style.display = step === sections.length ? '' : 'none';
    }

    function validateCurrentStep() {
        const section = form.querySelector('.form-section[data-step="' + currentStep + '"]');
        const fields = Array.from(section.querySelectorAll('input, select, textarea'));
        for (const field of fields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    nextBtn.addEventListener('click', function () {
        if (validateCurrentStep()) showStep(Math.min(currentStep + 1, sections.length));
    });

    backBtn.addEventListener('click', function () {
        showStep(Math.max(currentStep - 1, 1));
    });

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

    form.addEventListener('submit', function (event) {
        if (!validateCurrentStep()) {
            event.preventDefault();
            return;
        }
        sections.forEach(function (section) {
            section.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.disabled = false;
            });
        });
        localStorage.removeItem(storageKey);
    });

    showStep(initialStep);
    showFlash(flashError, 'error');
})();
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
