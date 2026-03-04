<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<style>
    body { background: #f8fafc; }
    .simple-register-wrap { max-width: 860px; margin: 36px auto; padding: 0 16px; }
    .simple-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .simple-card-header {
        padding: 24px;
        border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(135deg, #f5f3ff 0%, #faf5ff 100%);
    }
    .simple-card-header h1 { margin: 0 0 6px 0; font-size: 1.5rem; color: #4c1d95; }
    .simple-card-header p { margin: 0; color: #475569; }
    .simple-card-body { padding: 24px; }
    .hint-text { color: #64748b; font-size: 0.9rem; margin-bottom: 16px; }
    .required-star { color: #dc2626; }
    .quick-note {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        font-size: 0.9rem;
        color: #334155;
        margin-bottom: 16px;
    }
    .actions { display: flex; gap: 10px; align-items: center; margin-top: 8px; }
    .btn-register {
        border: 0;
        background: #6d28d9;
        color: #fff;
        padding: 12px 18px;
        border-radius: 10px;
        font-weight: 600;
    }
    .btn-register:hover { background: #5b21b6; }
    .support-text { color: #64748b; font-size: 0.85rem; }
</style>

<div class="simple-register-wrap">
    <div class="simple-card">
        <div class="simple-card-header">
            <h1>Register in One Step</h1>
            <p>Fill the details below and submit. We create your account immediately and send login details via email.</p>
        </div>

        <div class="simple-card-body">
            <div class="hint-text">Fields marked with <span class="required-star">*</span> are required.</div>
            <div class="quick-note">
                Registration fee: <strong>KES <?php echo number_format(defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200); ?></strong> (pay after account creation).
            </div>

            <form id="simpleRegistrationForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <input type="hidden" name="payment_method" value="mpesa">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name <span class="required-star">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="required-star">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="national_id" class="form-label">National ID <span class="required-star">*</span></label>
                        <input type="text" class="form-control" id="national_id" name="national_id" inputmode="numeric" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth <span class="required-star">*</span></label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="required-star">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="0712345678" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address <span class="required-star">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Address (Optional)</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Town / Estate">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="package_id" class="form-label">Plan (Optional)</label>
                        <select class="form-select" id="package_id" name="package_id">
                            <option value="">Auto-select based on age</option>
                            <?php foreach (($packages ?? []) as $package): ?>
                                <option
                                    value="<?php echo e($package['id']); ?>"
                                    data-age-min="<?php echo e($package['age_min'] ?? ''); ?>"
                                    data-age-max="<?php echo e($package['age_max'] ?? ''); ?>"
                                >
                                    <?php echo e($package['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="packageHint" class="form-text text-muted mt-1">Enter date of birth to view eligible plans.</div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-register" id="submitBtn">Create Account</button>
                    <span class="support-text">Need help? Call +254 748 585 067</span>
                </div>
            </form>

            <div id="resultBox" class="mt-4"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('simpleRegistrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const resultBox = document.getElementById('resultBox');
    const phoneInput = document.getElementById('phone');
    const dobInput = document.getElementById('date_of_birth');
    const packageSelect = document.getElementById('package_id');
    const packageHint = document.getElementById('packageHint');

    phoneInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 12) {
            value = value.slice(0, 12);
        }
        e.target.value = value;
    });

    function calculateAge(dob) {
        if (!dob) {
            return 0;
        }

        const birth = new Date(dob);
        if (Number.isNaN(birth.getTime())) {
            return 0;
        }

        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }

        return age;
    }

    function filterPackagesByAge() {
        const age = calculateAge(dobInput.value);
        const options = packageSelect.querySelectorAll('option[value]');
        let visibleCount = 0;

        options.forEach((option) => {
            const value = option.getAttribute('value') || '';
            if (value === '') {
                option.hidden = false;
                return;
            }

            const min = parseInt(option.dataset.ageMin || '0', 10);
            const max = parseInt(option.dataset.ageMax || '200', 10);

            const eligible = age > 0 && age >= min && age <= max;
            option.hidden = !eligible;

            if (eligible) {
                visibleCount++;
            }
        });

        const selected = packageSelect.options[packageSelect.selectedIndex];
        if (selected && selected.hidden) {
            packageSelect.value = '';
        }

        if (age <= 0) {
            packageHint.textContent = 'Enter date of birth to view eligible plans.';
        } else if (visibleCount === 0) {
            packageHint.textContent = 'No matching plans for this age. Leave as auto-select and continue.';
        } else {
            packageHint.textContent = `Showing ${visibleCount} eligible plan(s) for age ${age}.`;
        }
    }

    dobInput.addEventListener('change', filterPackagesByAge);
    dobInput.addEventListener('input', filterPackagesByAge);
    filterPackagesByAge();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        resultBox.innerHTML = '';

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const formData = new FormData(form);

        fetch('/register', {
            method: 'POST',
            body: formData
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                resultBox.innerHTML = `
                    <div class="alert alert-success">
                        <strong>Registration successful.</strong><br>
                        Member Number: <strong>${data.member_number || '-'}</strong><br>
                        ${data.message || 'Please check your email for next steps.'}
                    </div>
                `;
                form.reset();
            } else {
                resultBox.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Registration failed. Please try again.'}
                    </div>
                `;
            }
        })
        .catch(() => {
            resultBox.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        });
    });
});
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
