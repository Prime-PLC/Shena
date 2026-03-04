<?php include VIEWS_PATH . '/layouts/header.php'; ?>
<?php $resendSeconds = (int)($resend_remaining_seconds ?? 0); ?>

<div class="container" style="max-width: 520px; margin: 48px auto;">
    <div class="card shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body p-4 p-md-5">
            <h2 class="mb-2" style="color:#6d28d9;">Verify Your Phone</h2>
            <p class="text-muted mb-4">Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($masked_phone ?? '', ENT_QUOTES); ?></strong>.</p>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="/register/verify-otp">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">

                <div class="mb-3">
                    <label for="otp_code" class="form-label">OTP Code</label>
                    <input
                        type="text"
                        class="form-control"
                        id="otp_code"
                        name="otp_code"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        placeholder="Enter 6-digit code"
                        required
                    >
                </div>

                <button type="submit" class="btn w-100" style="background:#6d28d9;color:#fff;">Verify & Continue</button>
            </form>

            <form method="POST" action="/register/resend-otp" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">
                <button
                    type="submit"
                    id="resendBtn"
                    class="btn w-100"
                    style="background:#fff;color:#6d28d9;border:1px solid #6d28d9;"
                    <?php echo $resendSeconds > 0 ? 'disabled' : ''; ?>
                >
                    <?php if ($resendSeconds > 0): ?>
                        Resend in <span id="resendCountdown"><?php echo $resendSeconds; ?></span>s
                    <?php else: ?>
                        Resend Code
                    <?php endif; ?>
                </button>
            </form>

            <div class="mt-3 text-center">
                <a href="/register" class="text-decoration-none">Start registration again</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var seconds = <?php echo $resendSeconds; ?>;
    var btn = document.getElementById('resendBtn');
    var counter = document.getElementById('resendCountdown');

    if (!btn || !counter || seconds <= 0) {
        return;
    }

    var timer = setInterval(function () {
        seconds -= 1;
        if (seconds <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.textContent = 'Resend Code';
            return;
        }
        counter.textContent = String(seconds);
    }, 1000);
});
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
