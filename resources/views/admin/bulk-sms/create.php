<?php include __DIR__ . '/../../layouts/admin-header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create SMS Campaign</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/bulk-sms">Bulk SMS</a></li>
                    <li class="breadcrumb-item active">Create Campaign</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Form Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Campaign Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/bulk-sms/store" id="campaignForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <!-- Campaign Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Campaign Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= old('title') ?>" required>
                            <small class="text-muted">Internal name for this campaign</small>
                        </div>

                        <!-- Message Content -->
                        <div class="mb-3">
                            <label for="message" class="form-label">Message Content *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" 
                                      maxlength="480" required><?= old('message') ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Maximum 480 characters (3 SMS segments)</small>
                                <small id="charCount" class="text-muted">0 / 480</small>
                            </div>
                            <div id="smsSegments" class="mt-1"></div>
                        </div>

                        <!-- Target Audience -->
                        <div class="mb-3">
                            <label for="target_audience" class="form-label">Target Audience *</label>
                            <select class="form-select" id="target_audience" name="target_audience" required>
                                <option value="">Select target audience...</option>
                                <option value="all_members" <?= old('target_audience') === 'all_members' ? 'selected' : '' ?>>
                                    All Members (<?= number_format($stats['total_members'] ?? 0) ?>)
                                </option>
                                <option value="active" <?= old('target_audience') === 'active' ? 'selected' : '' ?>>
                                    Active Members Only (<?= number_format($stats['active_count'] ?? 0) ?>)
                                </option>
                                <option value="grace_period" <?= old('target_audience') === 'grace_period' ? 'selected' : '' ?>>
                                    Grace Period Members (<?= number_format($stats['grace_period_count'] ?? 0) ?>)
                                </option>
                                <option value="defaulted" <?= old('target_audience') === 'defaulted' ? 'selected' : '' ?>>
                                    Defaulted Members (<?= number_format($stats['defaulted_count'] ?? 0) ?>)
                                </option>
                                <option value="custom" <?= old('target_audience') === 'custom' ? 'selected' : '' ?>>
                                    Custom Filter...
                                </option>
                            </select>
                        </div>

                        <!-- Custom Filters (shown when 'custom' is selected) -->
                        <div id="customFilters" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Custom Filters</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="filter_package" class="form-label">Package</label>
                                            <select class="form-select" id="filter_package" name="filter_package">
                                                <option value="">All Packages</option>
                                                <option value="individual">Individual</option>
                                                <option value="family">Family</option>
                                                <option value="extended_family_1">Extended Family 1</option>
                                                <option value="extended_family_2">Extended Family 2</option>
                                                <option value="executive">Executive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="filter_status" class="form-label">Status</label>
                                            <select class="form-select" id="filter_status" name="filter_status">
                                                <option value="">All Statuses</option>
                                                <option value="active">Active</option>
                                                <option value="grace_period">Grace Period</option>
                                                <option value="defaulted">Defaulted</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="filter_joined_after" class="form-label">Joined After</label>
                                            <input type="date" class="form-control" id="filter_joined_after" name="filter_joined_after">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="filter_joined_before" class="form-label">Joined Before</label>
                                            <input type="date" class="form-control" id="filter_joined_before" name="filter_joined_before">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info" id="previewBtn">
                                        <i class="fas fa-search"></i> Preview Recipients
                                    </button>
                                    <div id="previewResult" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Option -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="scheduleCheckbox">
                                <label class="form-check-label" for="scheduleCheckbox">
                                    Schedule for later
                                </label>
                            </div>
                        </div>

                        <div id="scheduleInput" class="mb-3" style="display: none;">
                            <label for="scheduled_at" class="form-label">Schedule Date & Time</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Campaign
                            </button>
                            <a href="/admin/bulk-sms" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Member Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Members:</span>
                            <strong><?= number_format($stats['total_members'] ?? 0) ?></strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-success">Active:</span>
                            <span><?= number_format($stats['active_count'] ?? 0) ?></span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-warning">Grace Period:</span>
                            <span><?= number_format($stats['grace_period_count'] ?? 0) ?></span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Defaulted:</span>
                            <span><?= number_format($stats['defaulted_count'] ?? 0) ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>SMS Enabled:</span>
                            <strong class="text-primary"><?= number_format($stats['sms_enabled_count'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Keep messages clear and concise</li>
                        <li>Each SMS segment = 160 characters</li>
                        <li>Include contact info for queries</li>
                        <li>Review recipient count before sending</li>
                        <li>Schedule campaigns during business hours</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Character counter for SMS
const messageTextarea = document.getElementById('message');
const charCountSpan = document.getElementById('charCount');
const smsSegmentsDiv = document.getElementById('smsSegments');

messageTextarea.addEventListener('input', function() {
    const length = this.value.length;
    charCountSpan.textContent = `${length} / 480`;
    
    // Calculate SMS segments (160 chars per segment)
    const segments = Math.ceil(length / 160) || 0;
    if (segments > 0) {
        smsSegmentsDiv.innerHTML = `<small class="text-info"><i class="fas fa-info-circle"></i> This will use ${segments} SMS segment${segments > 1 ? 's' : ''}</small>`;
    } else {
        smsSegmentsDiv.innerHTML = '';
    }
});

// Show/hide custom filters
document.getElementById('target_audience').addEventListener('change', function() {
    const customFilters = document.getElementById('customFilters');
    if (this.value === 'custom') {
        customFilters.style.display = 'block';
    } else {
        customFilters.style.display = 'none';
    }
});

// Show/hide schedule input
document.getElementById('scheduleCheckbox').addEventListener('change', function() {
    const scheduleInput = document.getElementById('scheduleInput');
    scheduleInput.style.display = this.checked ? 'block' : 'none';
});

// Preview recipients
document.getElementById('previewBtn').addEventListener('click', function() {
    const targetAudience = document.getElementById('target_audience').value;
    const filterPackage = document.getElementById('filter_package').value;
    const filterStatus = document.getElementById('filter_status').value;
    
    const params = new URLSearchParams({
        target_audience: targetAudience,
        filter_package: filterPackage,
        filter_status: filterStatus
    });
    
    const previewResult = document.getElementById('previewResult');
    previewResult.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...';
    
    fetch(`/admin/bulk-sms/preview-recipients?${params}`)
        .then(response => response.json())
        .then(data => {
            previewResult.innerHTML = `<div class="alert alert-info">
                <strong>${data.count}</strong> recipients match your criteria
            </div>`;
        })
        .catch(error => {
            previewResult.innerHTML = '<div class="alert alert-danger">Failed to load preview</div>';
        });
});

// Initialize on page load
if (document.getElementById('target_audience').value === 'custom') {
    document.getElementById('customFilters').style.display = 'block';
}
</script>

<?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
