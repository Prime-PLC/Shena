<?php include __DIR__ . '/../../layouts/admin-header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Bulk SMS Campaigns</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/bulk-sms/create" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Create New Campaign
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/bulk-sms" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="sending" <?= ($filters['status'] ?? '') === 'sending' ? 'selected' : '' ?>>Sending</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Target Audience</th>
                            <th>Recipients</th>
                            <th>Delivered</th>
                            <th>Failed</th>
                            <th>Success Rate</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campaigns)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <p class="text-muted">No campaigns found</p>
                                    <a href="/admin/bulk-sms/create" class="btn btn-primary mt-2">
                                        Create Your First Campaign
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($campaign['title']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($campaign['message'], 0, 50)) ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= ucwords(str_replace('_', ' ', $campaign['target_audience'])) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($campaign['total_recipients']) ?></td>
                                    <td>
                                        <span class="badge bg-success"><?= number_format($campaign['delivered_count'] ?? $campaign['sent_count']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger"><?= number_format($campaign['failed_count']) ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $successRate = $campaign['success_rate'] ?? 0;
                                        $rateClass = $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $rateClass ?>">
                                            <?= number_format($successRate, 1) ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'draft' => 'secondary',
                                            'scheduled' => 'info',
                                            'sending' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger'
                                        ][$campaign['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= ucfirst($campaign['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('M d, Y', strtotime($campaign['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <a href="/admin/bulk-sms/view/<?= $campaign['id'] ?>" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($campaign['status'] === 'draft'): ?>
                                            <form method="POST" action="/admin/bulk-sms/send/<?= $campaign['id'] ?>" 
                                                  style="display: inline;" 
                                                  onsubmit="return handleConfirmSubmit(event, 'Send this campaign now?', 'primary', 'Send Campaign', 'Send Now')">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Send Now">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="/admin/bulk-sms/delete/<?= $campaign['id'] ?>" 
                                                  style="display: inline;" 
                                                  onsubmit="return handleConfirmSubmit(event, 'Delete this campaign?', 'danger', 'Delete Campaign', 'Delete')">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function handleConfirmSubmit(event, message, type = 'warning', title = 'Confirm Action', confirmText = 'Confirm') {
    event.preventDefault();

    const form = event.target;
    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            message,
            function() { form.submit(); },
            null,
            { type: type, title: title, confirmText: confirmText }
        );
        return false;
    }

    if (confirm(message)) {
        form.submit();
    }

    return false;
}
</script>

<?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
