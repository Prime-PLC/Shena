<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<div class="container" style="max-width: 560px; margin: 48px auto;">
    <div class="card shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body p-4 p-md-5">
            <h2 class="mb-2" style="color:#6d28d9;">Create Your Password</h2>
            <p class="text-muted mb-4">Set a password to complete your account setup.</p>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="/register/create-password">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    <small class="text-muted">Use at least 8 characters.</small>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                </div>

                <button type="submit" class="btn w-100" style="background:#6d28d9;color:#fff;">Save Password</button>
            </form>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
