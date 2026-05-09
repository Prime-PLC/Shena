<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .page-header {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 6px rgba(127, 61, 158, 0.1);
    }

    .page-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
    }

    .page-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .modern-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #f3f4f6;
    }

    .modern-card h2 {
        font-family: 'Playfair Display', serif;
        color: #1f2937;
        font-size: 1.5rem;
        margin: 0 0 1.5rem 0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #8B5CF6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .form-text {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .modern-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .modern-btn.primary {
        background: #7F3D9E;
        color: white;
    }

    .modern-btn.primary:hover {
        background: #7C3AED;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(127, 61, 158, 0.3);
    }

    .modern-btn.secondary {
        background: #6b7280;
        color: white;
    }

    .modern-btn.secondary:hover {
        background: #4b5563;
    }

    .modern-btn.back {
        background: #f3f4f6;
        color: #374151;
    }

    .modern-btn.back:hover {
        background: #e5e7eb;
    }

    .alert-modern {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-modern.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .alert-modern.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .alert-modern.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .alert-modern i {
        font-size: 1.25rem;
    }

    .switch-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #d1d5db;
        transition: 0.3s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #7F3D9E;
    }

    input:checked + .slider:before {
        transform: translateX(24px);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .status-badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-badge.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .status-badge.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #f3f4f6;
    }

    .info-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.125rem;
        margin: 0 0 1rem 0;
        color: #1f2937;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-item i {
        color: #8B5CF6;
        font-size: 1rem;
        margin-top: 0.25rem;
    }

    .info-item .info-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }

    .info-item .info-value {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .instructions-list {
        list-style: none;
        padding: 0;
        counter-reset: instruction-counter;
    }

    .instructions-list li {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        color: #4b5563;
        counter-increment: instruction-counter;
    }

    .instructions-list li:before {
        content: counter(instruction-counter);
        position: absolute;
        left: 0;
        top: 0;
        width: 24px;
        height: 24px;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .security-list {
        list-style: none;
        padding: 0;
    }

    .security-list li {
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .security-list li i {
        color: #F59E0B;
    }

    .password-toggle {
        position: relative;
    }

    .password-toggle-btn {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
    }

    .password-toggle-btn:hover {
        color: #8B5CF6;
    }
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1><i class="fas fa-mobile-alt"></i> M-Pesa Configuration</h1>
            <p>Configure Safaricom Daraja API for mobile money payments</p>
        </div>
        <a href="/admin/dashboard" class="modern-btn back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = [
                <?php if (isset($_SESSION['success'])): ?>{ type: 'success', message: <?php echo json_encode($_SESSION['success']); ?> },<?php unset($_SESSION['success']); endif; ?>
                <?php if (isset($_SESSION['error'])): ?>{ type: 'error', message: <?php echo json_encode($_SESSION['error']); ?> },<?php unset($_SESSION['error']); endif; ?>
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

<div class="row">
    <!-- Main Configuration Form -->
    <div class="col-md-8">
        <div class="modern-card">
            <h2><i class="fas fa-cog"></i> API Configuration</h2>
            
            <form method="POST" action="/admin/mpesa-config" id="mpesaConfigForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <div class="form-group">
                    <label for="environment">
                        <i class="fas fa-server"></i> Environment
                    </label>
                    <select class="form-control" id="environment" name="environment" required>
                        <option value="sandbox" <?php echo ($data['config']['environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>
                            Sandbox (Testing)
                        </option>
                        <option value="production" <?php echo ($data['config']['environment'] ?? '') === 'production' ? 'selected' : ''; ?>>
                            Production (Live)
                        </option>
                    </select>
                    <small class="form-text">Use Sandbox for testing, Production for live transactions</small>
                </div>

                <div class="form-group">
                    <label for="consumer_key">
                        <i class="fas fa-key"></i> Consumer Key
                    </label>
                    <input type="text" class="form-control" id="consumer_key" name="consumer_key" 
                           value="<?php echo htmlspecialchars($data['config']['consumer_key'] ?? ''); ?>" 
                           placeholder="Enter your Consumer Key" required>
                    <small class="form-text">Get from Safaricom Daraja Portal</small>
                </div>

                <div class="form-group">
                    <label for="consumer_secret">
                        <i class="fas fa-lock"></i> Consumer Secret
                    </label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="consumer_secret" name="consumer_secret" 
                               value="<?php echo htmlspecialchars($data['config']['consumer_secret'] ?? ''); ?>" 
                               placeholder="Enter your Consumer Secret" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('consumer_secret')">
                            <i class="fas fa-eye" id="consumer_secret_icon"></i>
                        </button>
                    </div>
                    <small class="form-text">Keep this secret and secure - never share it</small>
                </div>

                <div class="form-group">
                    <label for="short_code">
                        <i class="fas fa-building"></i> Business Short Code
                    </label>
                    <input type="text" class="form-control" id="short_code" name="short_code" 
                           value="<?php echo htmlspecialchars($data['config']['short_code'] ?? ''); ?>" 
                           placeholder="e.g., 174379" required>
                    <small class="form-text">Your M-Pesa Paybill or Till Number</small>
                </div>

                <div class="form-group">
                    <label for="pass_key">
                        <i class="fas fa-shield-alt"></i> Pass Key (Lipa Na M-Pesa Online)
                    </label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="pass_key" name="pass_key" 
                               value="<?php echo htmlspecialchars($data['config']['pass_key'] ?? ''); ?>" 
                               placeholder="Enter your Pass Key" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('pass_key')">
                            <i class="fas fa-eye" id="pass_key_icon"></i>
                        </button>
                    </div>
                    <small class="form-text">Required for STK Push (Lipa Na M-Pesa) functionality</small>
                </div>

                <div class="form-group">
                    <label for="callback_url">
                        <i class="fas fa-link"></i> Callback URL
                    </label>
                    <input type="url" class="form-control" id="callback_url" name="callback_url" 
                           value="<?php echo htmlspecialchars($data['config']['callback_url'] ?? ''); ?>" 
                           placeholder="https://yourdomain.com/payment/callback" required>
                    <small class="form-text">URL to receive payment notifications (must use HTTPS)</small>
                </div>

                <div class="switch-container">
                    <label class="switch">
                        <input type="checkbox" id="is_active" name="is_active" 
                               <?php echo ($data['config']['is_active'] ?? 0) ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                    <div>
                        <strong style="display: block; color: #374151; font-size: 0.875rem;">Active Configuration</strong>
                        <small style="color: #6b7280; font-size: 0.75rem;">Enable or disable M-Pesa payments</small>
                    </div>
                </div>

                <button type="submit" class="modern-btn primary">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </form>
        </div>

        <!-- Test Connection Card -->
        <div class="modern-card">
            <h2><i class="fas fa-plug"></i> Test Connection</h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">Verify your M-Pesa configuration before going live.</p>
            
            <form method="POST" action="/admin/mpesa-config/test">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <button type="submit" class="modern-btn secondary">
                    <i class="fas fa-vial"></i> Test Connection
                </button>
            </form>
            
            <?php if (isset($data['test_result'])): ?>
                <div class="alert-modern <?php echo $data['test_result']['success'] ? 'success' : 'danger'; ?>" style="margin-top: 1rem;">
                    <i class="fas fa-<?php echo $data['test_result']['success'] ? 'check-circle' : 'times-circle'; ?>"></i>
                    <div>
                        <strong><?php echo $data['test_result']['success'] ? 'Connection Successful' : 'Connection Failed'; ?></strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                            <?php echo htmlspecialchars($data['test_result']['message']); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Information -->
    <div class="col-md-4">
        <!-- Configuration Status -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Configuration Status</h3>
            
            <?php if (!empty($data['config'])): ?>
                <div class="info-item">
                    <i class="fas fa-circle" style="color: <?php echo $data['config']['is_active'] ? '#10B981' : '#EF4444'; ?>"></i>
                    <div style="flex: 1;">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge <?php echo $data['config']['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $data['config']['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-server"></i>
                    <div style="flex: 1;">
                        <div class="info-label">Environment</div>
                        <div class="info-value">
                            <span class="status-badge <?php echo $data['config']['environment'] === 'production' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($data['config']['environment']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div style="flex: 1;">
                        <div class="info-label">Last Updated</div>
                        <div class="info-value"><?php echo date('M d, Y H:i', strtotime($data['config']['updated_at'])); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert-modern warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>No configuration found. Please set up M-Pesa credentials.</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Setup Instructions -->
        <div class="info-card">
            <h3><i class="fas fa-list-ol"></i> Setup Instructions</h3>
            <ol class="instructions-list">
                <li>Register on <a href="https://developer.safaricom.co.ke/" target="_blank" style="color: #8B5CF6; text-decoration: none;">Safaricom Daraja Portal</a></li>
                <li>Create a new app (Sandbox or Production)</li>
                <li>Copy Consumer Key and Consumer Secret</li>
                <li>Get your Business Short Code</li>
                <li>Generate Lipa Na M-Pesa Pass Key</li>
                <li>Configure callback URL (must be HTTPS)</li>
                <li>Save and test configuration</li>
                <li>Switch to Production when ready</li>
            </ol>
        </div>

        <!-- Security Notes -->
        <div class="info-card">
            <h3><i class="fas fa-shield-alt"></i> Security Notes</h3>
            <ul class="security-list">
                <li><i class="fas fa-check-circle"></i> Never share your Consumer Secret</li>
                <li><i class="fas fa-check-circle"></i> Use HTTPS for callback URLs</li>
                <li><i class="fas fa-check-circle"></i> Test thoroughly in Sandbox</li>
                <li><i class="fas fa-check-circle"></i> Monitor transactions regularly</li>
                <li><i class="fas fa-check-circle"></i> Keep credentials up to date</li>
            </ul>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation
document.getElementById('mpesaConfigForm')?.addEventListener('submit', function(e) {
    const callbackUrl = document.getElementById('callback_url').value;
    
    if (!callbackUrl.startsWith('https://')) {
        e.preventDefault();
        ShenaApp.showNotification('Callback URL must use HTTPS for security', 'warning');
        return false;
    }
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
