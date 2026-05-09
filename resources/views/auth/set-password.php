<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<div class="container" style="max-width: 560px; margin: 48px auto;">
    <div class="card shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="/public/images/logo.png" alt="SHENA" style="height:56px;" onerror="this.style.display='none'">
                <h2 class="mt-3 mb-1" style="color:#6d28d9;">Set Your Password</h2>
                <p class="text-muted">Welcome to SHENA Companion Welfare. Create a secure password to access your member portal.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></div>
                <div class="text-center mt-3">
                    <a href="/login" class="btn" style="background:#6d28d9;color:#fff;">Go to Login</a>
                </div>
            <?php else: ?>

            <?php if (!empty($memberName)): ?>
                <div class="alert alert-info py-2 mb-3" style="font-size:0.95rem;">
                    Hi <strong><?php echo htmlspecialchars($memberName, ENT_QUOTES); ?></strong>, please set a password to complete your account activation.
                </div>
            <?php endif; ?>

            <form method="POST" action="/set-password" id="setPasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? '', ENT_QUOTES); ?>">

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">New Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           minlength="8" required autocomplete="new-password"
                           placeholder="At least 8 characters">
                    <div class="form-text">Use at least 8 characters.</div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           minlength="8" required autocomplete="new-password"
                           placeholder="Re-enter your password">
                </div>

                <button type="submit" class="btn w-100 py-2" style="background:#6d28d9;color:#fff;font-weight:600;">
                    Activate My Account
                </button>
            </form>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('setPasswordForm')?.addEventListener('submit', function(e) {
    var pw  = document.getElementById('password').value;
    var cpw = document.getElementById('confirm_password').value;
    if (pw !== cpw) {
        e.preventDefault();
        if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
            ShenaApp.alert('Passwords do not match. Please try again.', 'warning');
        } else {
            console.warn('Passwords do not match. Please try again.');
        }
        document.getElementById('confirm_password').focus();
    }
});
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
