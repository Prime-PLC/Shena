<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<style>
.fp-container { max-width: 460px; margin: 60px auto 40px; padding: 0 16px; }
.fp-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.10); overflow: hidden; }
.fp-header { background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%); padding: 32px; text-align: center; color: #fff; }
.fp-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 6px; }
.fp-header p { font-size: .92rem; opacity: .88; margin: 0; }
.fp-body { padding: 32px; }
.fp-step { display: none; }
.fp-step.active { display: block; }
.step-indicator { display: flex; justify-content: center; gap: 8px; margin-bottom: 28px; }
.step-dot { width: 10px; height: 10px; border-radius: 50%; background: #e5e7eb; transition: background .3s; }
.step-dot.active { background: #6d28d9; }
.step-dot.done { background: #059669; }
.form-label { font-weight: 500; font-size: .9rem; color: #374151; }
.form-control { border-radius: 8px; border: 1.5px solid #e5e7eb; padding: 10px 14px; font-size: .95rem; }
.form-control:focus { border-color: #6d28d9; box-shadow: 0 0 0 3px rgba(109,40,217,.12); outline: none; }
.btn-primary-fp { width: 100%; background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%); color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: opacity .2s; }
.btn-primary-fp:disabled { opacity: .6; cursor: not-allowed; }
.btn-link-fp { background: none; border: none; color: #6d28d9; font-size: .88rem; cursor: pointer; padding: 0; text-decoration: underline; }
.alert-fp { padding: 10px 14px; border-radius: 8px; font-size: .9rem; margin-bottom: 16px; }
.alert-fp.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.alert-fp.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.back-to-login { text-align: center; margin-top: 20px; font-size: .9rem; color: #6b7280; }
.back-to-login a { color: #6d28d9; text-decoration: none; font-weight: 500; }
.otp-input { letter-spacing: 6px; text-align: center; font-size: 1.4rem; font-weight: 700; }
.password-wrapper { position: relative; }
.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0; }
</style>

<div class="fp-container">
    <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert-fp error" style="margin-bottom:16px;"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="fp-card">
        <div class="fp-header">
            <div style="font-size:2.5rem; margin-bottom:8px;">🔐</div>
            <h1>Reset Your Password</h1>
            <p>We'll send a verification code to your phone.</p>
        </div>
        <div class="fp-body">
            <div class="step-indicator">
                <div class="step-dot active" id="dot-1"></div>
                <div class="step-dot" id="dot-2"></div>
                <div class="step-dot" id="dot-3"></div>
            </div>

            <div id="alert-box"></div>

            <!-- Step 1: Enter phone -->
            <div class="fp-step active" id="step-1">
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" placeholder="07XXXXXXXX or 254XXXXXXXXX" autocomplete="tel">
                    <small class="text-muted" style="font-size:.82rem;">Enter the phone number linked to your account.</small>
                </div>
                <button class="btn-primary-fp" id="btn-send-otp" onclick="sendOtp()">Send Reset Code</button>
            </div>

            <!-- Step 2: Enter OTP -->
            <div class="fp-step" id="step-2">
                <p style="color:#6b7280; font-size:.9rem; margin-bottom:20px;">Enter the 6-digit code sent to <strong id="display-phone"></strong>.</p>
                <div class="mb-3">
                    <label for="otp" class="form-label">Verification Code</label>
                    <input type="text" class="form-control otp-input" id="otp" maxlength="6" inputmode="numeric" placeholder="— — — — — —">
                </div>
                <button class="btn-primary-fp" id="btn-verify-otp" onclick="verifyOtp()">Verify Code</button>
                <div style="text-align:center; margin-top:14px;">
                    <button class="btn-link-fp" onclick="goBack(1)" id="btn-resend">Didn't receive it? Go back to resend</button>
                </div>
            </div>

            <!-- Step 3: New password -->
            <div class="fp-step" id="step-3">
                <form method="POST" action="/forgot-password/reset" id="reset-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES) ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="Min. 8 characters" required autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePwd('password')"><i class="fas fa-eye" id="eye-pwd"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" placeholder="Re-enter password" required autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePwd('confirm_password')"><i class="fas fa-eye" id="eye-conf"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary-fp" id="btn-reset">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
    <div class="back-to-login"><a href="/login">&larr; Back to Login</a></div>
</div>

<script>
const csrfToken = <?= json_encode($csrf_token ?? '') ?>;

function showAlert(message, type = 'error') {
    document.getElementById('alert-box').innerHTML =
        `<div class="alert-fp ${type}">${escHtml(message)}</div>`;
}

function clearAlert() {
    document.getElementById('alert-box').innerHTML = '';
}

function escHtml(text) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(text));
    return d.innerHTML;
}

function setStep(n) {
    [1, 2, 3].forEach(i => {
        document.getElementById('step-' + i).classList.toggle('active', i === n);
        const dot = document.getElementById('dot-' + i);
        dot.classList.remove('active', 'done');
        if (i < n) dot.classList.add('done');
        else if (i === n) dot.classList.add('active');
    });
    clearAlert();
}

function goBack(toStep) { setStep(toStep); }

async function sendOtp() {
    const phone = document.getElementById('phone').value.trim();
    if (!phone) { showAlert('Please enter your phone number.'); return; }
    const btn = document.getElementById('btn-send-otp');
    btn.disabled = true; btn.textContent = 'Sending…';

    try {
        const res = await fetch('/forgot-password/send-otp', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({csrf_token: csrfToken, phone})
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('display-phone').textContent = phone;
            setStep(2);
        } else {
            showAlert(data.message || 'Failed to send code.');
        }
    } catch (e) {
        showAlert('Network error. Please try again.');
    } finally {
        btn.disabled = false; btn.textContent = 'Send Reset Code';
    }
}

async function verifyOtp() {
    const otp = document.getElementById('otp').value.trim();
    if (otp.length < 4) { showAlert('Enter the code sent to your phone.'); return; }
    const btn = document.getElementById('btn-verify-otp');
    btn.disabled = true; btn.textContent = 'Verifying…';

    try {
        const res = await fetch('/forgot-password/verify-otp', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({csrf_token: csrfToken, otp})
        });
        const data = await res.json();
        if (data.success) {
            setStep(3);
        } else {
            showAlert(data.message || 'Incorrect code. Try again.');
        }
    } catch (e) {
        showAlert('Network error. Please try again.');
    } finally {
        btn.disabled = false; btn.textContent = 'Verify Code';
    }
}

document.getElementById('reset-form').addEventListener('submit', function(e) {
    const pw = document.getElementById('password').value;
    const conf = document.getElementById('confirm_password').value;
    if (pw !== conf) {
        e.preventDefault();
        showAlert('Passwords do not match.');
    }
});

function togglePwd(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId === 'password' ? 'eye-pwd' : 'eye-conf');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Allow pressing Enter to advance
document.getElementById('otp').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') verifyOtp();
});
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
