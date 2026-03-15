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
        padding: 12px 18px;
        border-radius: 10px;
        font-weight: 600;
    }
    .btn-register:hover { background: #5b21b6; }
    .support-text { color: #64748b; font-size: 0.85rem; }
    /* 2-step plan picker */
    .plan-type-select { font-size: 1rem; }
    .age-bracket-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; }
    .abo-option {
        flex: 1 1 calc(50% - 10px);
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        cursor: pointer;
        background: #fafafa;
        transition: border-color .18s, background .18s;
        user-select: none;
    }
    .abo-option:hover { border-color: #a78bfa; background: #faf5ff; }
    .abo-option.selected { border-color: #6d28d9; background: #f5f3ff; }
    .abo-label { font-weight: 600; color: #1e1b4b; font-size: 0.9rem; }
    .abo-price { color: #6d28d9; font-weight: 700; font-size: 0.95rem; margin-top: 2px; }
    .plan-summary-box {
        background: #f5f3ff;
        border: 1.5px solid #c4b5fd;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 4px;
    }
    .plan-summary-box strong { color: #4c1d95; }
    .plan-summary-price { font-weight: 700; color: #6d28d9; font-size: 1.05rem; }
    @media (max-width: 576px) { .abo-option { flex: 1 1 100%; } }

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
                        <h2>Create your SHENA account in minutes</h2>
                        <p>Start with just your core details. After your first login, we’ll guide you to complete the rest from your dashboard.</p>
                    </div>
                    <p style="font-size:0.82rem; opacity:.85;">Serving communities across Kenya with compassionate support.</p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="simple-card-header">
                    <h1>Register in One Step</h1>
                    <p>We send an OTP to your phone, then you create your password.</p>
                </div>

                <div class="simple-card-body">
                    <div class="hint-text">Fields marked with <span class="required-star">*</span> are required.</div>
                    <div class="quick-note">
                        Registration fee: <strong>KES <?php echo number_format(defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200); ?></strong> (pay after account creation).
                    </div>
                    <div class="quick-note" style="background:#F3E8FF;border-color:#DDD6FE;color:#4C1D95;">
                        <strong>Verification step:</strong> After submitting this form, a verification code will be sent to your mobile phone number for OTP confirmation.
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

                        <!-- Step 1: Plan Type -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="plan_type" class="form-label">Select Plan Type <span class="required-star">*</span></label>
                                <select class="form-control plan-type-select" id="plan_type" name="plan_type">
                                    <option value="">Choose a plan...</option>
                                    <option value="individual">Individual &mdash; Principal member only (KES 100 &ndash; 650/month)</option>
                                    <option value="family">Family &mdash; Principal + Spouse (KES 150 flat/month)</option>
                                    <option value="extended_family_1">Extended Family 1 &mdash; Couple + Children + Parents (KES 250 &ndash; 650/month)</option>
                                    <option value="extended_family_2">Extended Family 2 &mdash; Couple + Children + Parents + In-laws (KES 300 &ndash; 650/month)</option>
                                    <option value="executive">Executive &mdash; Premium Individual (KES 300 or 500/month)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Step 2: Age bracket (shown when plan needs it) -->
                        <div class="row">
                            <div class="col-12 mb-2" id="age_bracket_row" style="display:none;">
                                <label class="form-label">Age Bracket <span class="required-star">*</span> &mdash; <em id="age_bracket_hint" class="hint-text"></em></label>
                                <div class="age-bracket-grid" id="age_bracket_options"></div>
                            </div>
                        </div>

                        <!-- Selected plan summary -->
                        <div class="row">
                            <div class="col-12 mb-1" id="plan_summary_row" style="display:none;">
                                <div class="plan-summary-box">
                                    <strong id="plan_summary_name"></strong>
                                    <span class="plan-summary-price" id="plan_summary_price"></span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="package_id" id="package_id_hidden">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="corporate_couple_count" class="form-label">Corporate Couple Count</label>
                                <select class="form-control" id="corporate_couple_count" name="corporate_couple_count">
                                    <option value="0" selected>None</option>
                                    <option value="1">1 Additional Couple (+KES 150)</option>
                                    <option value="2">2 Additional Couples (+KES 300)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="required-star">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="0712345678" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="national_id" class="form-label">National ID Number <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="national_id" name="national_id" placeholder="12345678" required>
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
    </div>
</div>

<script>
// ── Package picker data ─────────────────────────────────────────────────────
const tierPackageMap = {
    individual: {
        flat: false,
        hint: 'Your age',
        brackets: [
            { key: 'individual_below_70',  label: 'Below 70 years', price: 100 },
            { key: 'individual_71_80',     label: '71 – 80 years',  price: 350 },
            { key: 'individual_81_90',     label: '81 – 90 years',  price: 450 },
            { key: 'individual_91_100',    label: '91 – 100 years', price: 650 }
        ]
    },
    family: {
        flat: true,
        packageKey: 'couple_below_70',
        price: 150,
        label: 'Family Plan – Principal + Spouse'
    },
    extended_family_1: {
        flat: false,
        hint: 'Age of the oldest parent you are covering',
        brackets: [
            { key: 'couple_children_parents_below_70', label: 'Below 70 years', price: 250 },
            { key: 'couple_children_parents_70_80',    label: '70 – 80 years',  price: 350 },
            { key: 'couple_children_parents_81_90',    label: '81 – 90 years',  price: 450 },
            { key: 'couple_children_parents_91_100',   label: '91 – 100 years', price: 650 }
        ]
    },
    extended_family_2: {
        flat: false,
        hint: 'Age of the oldest parent or in-law you are covering',
        brackets: [
            { key: 'couple_children_parents_inlaws_below_70', label: 'Below 70 years', price: 300 },
            { key: 'couple_children_parents_inlaws_71_80',    label: '71 – 80 years',  price: 400 },
            { key: 'couple_children_parents_inlaws_81_90',    label: '81 – 90 years',  price: 550 },
            { key: 'couple_children_parents_inlaws_91_100',   label: '91 – 100 years', price: 650 }
        ]
    },
    executive: {
        flat: false,
        hint: 'Your age',
        brackets: [
            { key: 'executive_below_70', label: 'Below 70 years',       price: 300 },
            { key: 'executive_above_70', label: '70 years and above', price: 500 }
        ]
    }
};

// Preselect values injected by server
const preselectPlan    = <?php echo json_encode($preselect_plan    ?? ''); ?>;
const preselectBracket = <?php echo json_encode($preselect_bracket ?? ''); ?>;

document.addEventListener('DOMContentLoaded', function () {
    const form           = document.getElementById('simpleRegistrationForm');
    const submitBtn      = document.getElementById('submitBtn');
    const resultBox      = document.getElementById('resultBox');
    const phoneInput     = document.getElementById('phone');
    const planTypeSelect = document.getElementById('plan_type');
    const bracketRow     = document.getElementById('age_bracket_row');
    const bracketHint    = document.getElementById('age_bracket_hint');
    const bracketOptions = document.getElementById('age_bracket_options');
    const summaryRow     = document.getElementById('plan_summary_row');
    const summaryName    = document.getElementById('plan_summary_name');
    const summaryPrice   = document.getElementById('plan_summary_price');
    const packageHidden  = document.getElementById('package_id_hidden');

    function formatKES(n) { return 'KES ' + n.toLocaleString() + '/month'; }

    function showSummary(name, price) {
        summaryName.textContent  = name;
        summaryPrice.textContent = formatKES(price);
        summaryRow.style.display = '';
    }

    function clearBracket() {
        bracketOptions.innerHTML = '';
        bracketRow.style.display = 'none';
        packageHidden.value      = '';
        summaryRow.style.display = 'none';
    }

    function renderBrackets(tier) {
        bracketOptions.innerHTML = '';
        bracketHint.textContent  = tier.hint;
        tier.brackets.forEach(function(b) {
            const el = document.createElement('div');
            el.className = 'abo-option';
            el.dataset.key   = b.key;
            el.dataset.price = b.price;
            el.innerHTML = '<div class="abo-label">' + b.label + '</div><div class="abo-price">' + formatKES(b.price) + '</div>';
            el.addEventListener('click', function() {
                bracketOptions.querySelectorAll('.abo-option').forEach(function(o) { o.classList.remove('selected'); });
                el.classList.add('selected');
                packageHidden.value = b.key;
                showSummary(planTypeSelect.options[planTypeSelect.selectedIndex].text.split('—')[0].trim() + ' · ' + b.label, b.price);
            });
            bracketOptions.appendChild(el);
        });
        bracketRow.style.display = '';
    }

    function applyBracketPreselect(bracketKey) {
        const opts = bracketOptions.querySelectorAll('.abo-option');
        opts.forEach(function(o) {
            if (o.dataset.key === bracketKey) { o.click(); }
        });
    }

    planTypeSelect.addEventListener('change', function() {
        const val = planTypeSelect.value;
        clearBracket();
        if (!val || !tierPackageMap[val]) return;
        const tier = tierPackageMap[val];
        if (tier.flat) {
            packageHidden.value = tier.packageKey;
            showSummary(tier.label, tier.price);
        } else {
            renderBrackets(tier);
        }
    });

    // Apply preselect from server-injected params
    if (preselectPlan && tierPackageMap[preselectPlan]) {
        planTypeSelect.value = preselectPlan;
        planTypeSelect.dispatchEvent(new Event('change'));
        if (preselectBracket) {
            applyBracketPreselect(
                tierPackageMap[preselectPlan].brackets
                    ? tierPackageMap[preselectPlan].brackets.find(function(b) {
                          return b.key.endsWith(preselectBracket) || b.key === preselectBracket;
                      })?.key || preselectBracket
                    : ''
            );
        }
    }

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

        // Validate package was fully selected
        if (!packageHidden.value) {
            resultBox.innerHTML = '<div class="alert alert-warning">Please select a plan type' +
                (document.getElementById('age_bracket_row').style.display !== 'none' ? ' and an age bracket' : '') + '.</div>';
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
                        ${data.message || 'Please proceed with OTP verification.'}
                    </div>
                `;
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1200);
                } else {
                    form.reset();
                }
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
