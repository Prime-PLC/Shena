<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<style>
    body { background: #F7F7F9; }
    .simple-register-wrap { max-width: 1200px; margin: 32px auto; padding: 0 16px; }
    .simple-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 10px 36px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        min-height: 720px;
    }
    .side-panel {
        background: linear-gradient(135deg, rgba(127, 61, 158, 0.92) 0%, rgba(94, 43, 122, 0.92) 100%), url('/public/images/background-image1.jpeg') center/cover no-repeat;
        color: #fff;
        padding: 40px;
        min-height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .side-panel h2 { font-family: 'Playfair Display', serif; font-size: 2.2rem; line-height: 1.2; margin-bottom: 14px; }
    .side-panel p { color: rgba(255,255,255,0.92); line-height: 1.7; margin-bottom: 0; }
    .side-badge {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 999px;
        display: inline-block;
        padding: 6px 14px;
        font-size: 0.78rem;
        letter-spacing: .8px;
        font-weight: 700;
        margin-bottom: 16px;
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
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-register:hover { background: #5b21b6; }
    .support-text { color: #64748b; font-size: 0.85rem; }
    .product-summary { background:#f5f3ff; border:1px solid #ddd6fe; border-radius:10px; padding:12px; color:#4c1d95; font-size:.88rem; line-height:1.55; }

    @media (max-width: 991px) {
        .side-panel { min-height: 260px; }
        .simple-card { min-height: auto; }
    }
</style>

<div class="simple-register-wrap">
    <div class="simple-card">
        <div class="row g-0">
            <div class="col-lg-5">
                <div class="side-panel">
                    <div>
                        <span class="side-badge">QUICK SIGNUP</span>
                        <h2>Join SHENA in seconds</h2>
                        <p>Choose Basic or Platinum, then complete verification and activation from your dashboard.</p>
                    </div>
                    <div style="margin-top:auto; padding-top:24px;">
                        <div style="background:rgba(255,255,255,0.15); border-radius:12px; padding:14px 16px; font-size:0.85rem; line-height:1.6;">
                            <div style="font-weight:700; margin-bottom:6px; color:rgba(255,255,255,0.9);">What happens after signup?</div>
                            <div>✔ OTP verification</div>
                            <div>✔ Create your password</div>
                            <div>✔ Choose Basic or Platinum</div>
                            <div>✔ Pay KES 200 registration fee</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="simple-card-header">
                    <h1>Create Your Account</h1>
                    <p>Just your name and phone number. We'll verify you by OTP, then you create your password.</p>
                </div>

                <div class="simple-card-body">
                    <div class="hint-text">Fields marked with <span class="required-star">*</span> are required.</div>
                    <div class="quick-note" style="background:#F3E8FF;border-color:#DDD6FE;color:#4C1D95;">
                        <strong>Verification step:</strong> After submitting, a 6-digit verification code will be sent to your phone for confirmation.
                    </div>

                    <form id="simpleRegistrationForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                        <input type="hidden" name="payment_method" value="mpesa">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required autocomplete="given-name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required autocomplete="family-name">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="required-star">*</span></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="package_id" class="form-label">Basic membership package <span class="required-star">*</span></label>
                                <select class="form-select" id="package_id" name="package_id" required>
                                    <option value="">Select a Basic package</option>
                                    <?php foreach (($packages ?? []) as $packageKey => $package): ?>
                                        <option value="<?php echo e($packageKey); ?>" data-monthly="<?php echo (float) ($package['monthly_contribution'] ?? 0); ?>">
                                            <?php echo e($package['name'] ?? $packageKey); ?> — KES <?php echo number_format((float) ($package['monthly_contribution'] ?? 0)); ?>/month
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="platinum_opt_in" class="form-label">Product tier <span class="required-star">*</span></label>
                            <select class="form-select" id="platinum_opt_in" name="platinum_opt_in" required>
                                <option value="0" selected>SHENA Basic — funeral and last-respect cover</option>
                                <option value="1">SHENA Platinum — inpatient and welfare cover</option>
                            </select>
                            <small class="text-muted">Platinum replaces the Basic monthly contribution for the selected package group. It provides up to 20 inpatient bed-cover days each calendar year after approval and maturity.</small>
                        </div>
                        <div class="product-summary" id="productSummary" aria-live="polite">Choose your date of birth and Basic package to see the monthly contribution.</div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="required-star">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="0712345678" required autocomplete="tel">
                                <small class="text-muted" style="font-size:0.82rem;">Enter your active Safaricom or Airtel number.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="national_id" class="form-label">National ID Number <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="national_id" name="national_id" placeholder="e.g. 12345678" pattern="[0-9]{7,8}" required autocomplete="off">
                                <small class="text-muted" style="font-size:0.82rem;">Your 7 or 8-digit Kenyan National ID. Used as your account identifier.</small>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="submit" class="btn-register" id="submitBtn">Create Account &rarr;</button>
                            <span class="support-text">Need help? Call +254 748 585 067</span>
                        </div>
                    </form>

                    <div id="resultBox" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form      = document.getElementById('simpleRegistrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const resultBox = document.getElementById('resultBox');
    const phoneInput = document.getElementById('phone');
    const dobInput = document.getElementById('date_of_birth');
    const packageInput = document.getElementById('package_id');
    const tierInput = document.getElementById('platinum_opt_in');
    const productSummary = document.getElementById('productSummary');

    function platinumPrice(age) {
        if (age === null || Number.isNaN(age)) return null;
        if (age < 70) return 300;
        if (age <= 80) return 550;
        if (age <= 90) return 650;
        if (age <= 100) return 850;
        return null;
    }

    function updateProductSummary() {
        const option = packageInput.options[packageInput.selectedIndex];
        const basic = Number(option?.dataset.monthly || 0);
        let age = null;
        if (dobInput.value) {
            const dob = new Date(dobInput.value + 'T00:00:00');
            age = Math.floor((Date.now() - dob.getTime()) / 31557600000);
        }
        const wantsPlatinum = tierInput.value === '1';
        const platinum = wantsPlatinum ? platinumPrice(age) : 0;
        if (!basic) {
            productSummary.textContent = 'Choose a Basic package to see the monthly contribution.';
        } else if (wantsPlatinum && platinum === null) {
            productSummary.textContent = 'Basic: KES ' + basic.toLocaleString() + '/month. Enter a valid date of birth to calculate the Platinum monthly contribution.';
        } else if (wantsPlatinum) {
            productSummary.textContent = 'Platinum monthly contribution: KES ' + platinum.toLocaleString() + '. This replaces the Basic rate of KES ' + basic.toLocaleString() + ' for this package group.';
        } else {
            productSummary.textContent = 'Basic monthly contribution: KES ' + basic.toLocaleString() + '.';
        }
    }
    [dobInput, packageInput, tierInput].forEach(function (input) { input.addEventListener('change', updateProductSummary); });
    updateProductSummary();

    phoneInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 12) { value = value.slice(0, 12); }
        e.target.value = value;
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        resultBox.innerHTML = '';

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        fetch('/register', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                resultBox.innerHTML =
                    '<div class="alert alert-success">' +
                    '<strong>Almost there!</strong><br>' +
                    (data.message || 'Please check your phone for the OTP verification code.') +
                    '</div>';
                if (data.redirect) {
                    setTimeout(function () { window.location.href = data.redirect; }, 1500);
                }
            } else {
                resultBox.innerHTML =
                    '<div class="alert alert-danger">' +
                    (data.message || 'Registration failed. Please try again.') +
                    '</div>';
            }
        })
        .catch(function () {
            resultBox.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account →';
        });
    });
});
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
