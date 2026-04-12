<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell"></i> Notification Settings</h2>
                <a href="/admin/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>

            <div class="row">
                <!-- Email Fallback Configuration -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-envelope"></i> Email Fallback</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                When enabled, emails are automatically sent when SMS delivery fails.
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="emailFallbackToggle" 
                                       <?php echo isset($settings['email_fallback_enabled']) && $settings['email_fallback_enabled']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="emailFallbackToggle">
                                    <strong>Enable Email Fallback</strong>
                                </label>
                            </div>

                            <?php if (isset($settings['email_fallback_enabled'])): ?>
                            <div class="mt-3 pt-3 border-top">
                                <p class="text-muted mb-1">
                                    <small><i class="fas fa-clock"></i> Last Updated: <?php echo date('F j, Y g:i A', strtotime($settings['email_fallback_enabled']['updated_at'])); ?></small>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Test Fallback -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-vial"></i> Test Fallback</h5>
                        </div>
                        <div class="card-body">
                            <form id="testFallbackForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <div class="form-group">
                                    <label for="testPhone">Phone Number</label>
                                    <input type="text" class="form-control" id="testPhone" 
                                           placeholder="254712345678" required>
                                    <small class="text-muted">Use invalid number to force SMS failure</small>
                                </div>

                                <div class="form-group">
                                    <label for="testEmail">Email Address</label>
                                    <input type="email" class="form-control" id="testEmail" 
                                           placeholder="user@example.com" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane"></i> Send Test
                                </button>
                            </form>

                            <div id="testResult" class="mt-3" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mt-4">
                <!-- Today -->
                <div class="col-md-4 mb-4">
                    <div class="card border-left-success shadow">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Today
                                    </div>
                                    <?php
                                    $today = $notificationStats['today'];
                                    $todayTotal = ($today['sms_success'] ?? 0) + ($today['email_success'] ?? 0);
                                    ?>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $todayTotal; ?></div>
                                    <div class="mt-2">
                                        <small>
                                            <i class="fas fa-sms text-primary"></i> <?php echo $today['sms_success'] ?? 0; ?> SMS &nbsp;
                                            <i class="fas fa-envelope text-info"></i> <?php echo $today['email_success'] ?? 0; ?> Emails
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last 7 Days -->
                <div class="col-md-4 mb-4">
                    <div class="card border-left-info shadow">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Last 7 Days
                                    </div>
                                    <?php
                                    $week = $notificationStats['week'];
                                    $weekTotal = ($week['sms_success'] ?? 0) + ($week['email_success'] ?? 0);
                                    ?>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $weekTotal; ?></div>
                                    <div class="mt-2">
                                        <small>
                                            <i class="fas fa-sms text-primary"></i> <?php echo $week['sms_success'] ?? 0; ?> SMS &nbsp;
                                            <i class="fas fa-envelope text-info"></i> <?php echo $week['email_success'] ?? 0; ?> Emails
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last 30 Days -->
                <div class="col-md-4 mb-4">
                    <div class="card border-left-warning shadow">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Last 30 Days
                                    </div>
                                    <?php
                                    $month = $notificationStats['month'];
                                    $monthTotal = ($month['sms_success'] ?? 0) + ($month['email_success'] ?? 0);
                                    $fallbackRate = $monthTotal > 0 ? round((($month['email_success'] ?? 0) / $monthTotal) * 100, 1) : 0;
                                    ?>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $monthTotal; ?></div>
                                    <div class="mt-2">
                                        <small>
                                            <i class="fas fa-sms text-primary"></i> <?php echo $month['sms_success'] ?? 0; ?> SMS &nbsp;
                                            <i class="fas fa-envelope text-info"></i> <?php echo $month['email_success'] ?? 0; ?> Emails
                                        </small>
                                        <div class="text-muted mt-1">
                                            <small>Fallback Rate: <?php echo $fallbackRate; ?>%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle email fallback
document.getElementById('emailFallbackToggle').addEventListener('change', function() {
    const enabled = this.checked ? '1' : '0';
    
    fetch('/admin/settings/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'setting_key=email_fallback_enabled&setting_value=' + enabled
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('✓ Email fallback ' + (enabled === '1' ? 'enabled' : 'disabled'), 'success');
        } else {
            ShenaApp.showNotification('Error: ' + data.message, 'error');
            this.checked = !this.checked;
        }
    })
    .catch(error => {
        ShenaApp.showNotification('Failed to update setting', 'error');
        this.checked = !this.checked;
    });
});

// Test fallback
document.getElementById('testFallbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const resultDiv = document.getElementById('testResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Sending...</div>';
    
    const formData = new FormData();
    formData.append('phone', document.getElementById('testPhone').value);
    formData.append('email', document.getElementById('testEmail').value);
    formData.append('name', 'Test User');
    
    fetch('/admin/settings/test-fallback', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const method = data.method === 'sms' ? 
                '<span class="badge badge-primary">SMS</span>' : 
                '<span class="badge badge-info">EMAIL (Fallback)</span>';
            
            resultDiv.innerHTML = '<div class="alert alert-success">' +
                '<strong>✓ Success!</strong> Method: ' + method +
                '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' +
                '<strong>✗ Failed:</strong> ' + data.error +
                '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger">Request failed</div>';
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
