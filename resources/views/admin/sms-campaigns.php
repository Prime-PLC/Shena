
<?php
// Ensure CSRF token is set for the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$quickFilters = $quick_filters ?? ['status' => 'all', 'search' => '', 'date_from' => '', 'date_to' => ''];
$quickPagination = $quick_pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => count($queue_items ?? []), 'per_page' => 25];
$quickPageUrl = function (int $page) use ($quickFilters) {
    return '/admin/sms-campaigns?' . http_build_query([
        'sms_tab' => 'quick',
        'quick_page' => $page,
        'quick_status' => $quickFilters['status'] ?? 'all',
        'quick_search' => $quickFilters['search'] ?? '',
        'quick_date_from' => $quickFilters['date_from'] ?? '',
        'quick_date_to' => $quickFilters['date_to'] ?? '',
    ]);
};
$editCampaignToOpen = $edit_campaign_to_open ?? null;
$editCampaignToOpenFilters = !empty($editCampaignToOpen['custom_filters']) ? json_decode((string)$editCampaignToOpen['custom_filters'], true) : [];
$editCampaignToOpenJson = $editCampaignToOpen ? json_encode([
    'id' => (int)$editCampaignToOpen['id'],
    'title' => $editCampaignToOpen['title'] ?? '',
    'message' => $editCampaignToOpen['message'] ?? '',
    'target_audience' => $editCampaignToOpen['target_audience'] ?? 'all_members',
    'custom_filters' => is_array($editCampaignToOpenFilters) ? $editCampaignToOpenFilters : [],
    'scheduled_at' => !empty($editCampaignToOpen['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($editCampaignToOpen['scheduled_at'])) : '',
], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) : 'null';
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .page-header {
        background: #7F3D9E;
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

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
        height: 100%;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 8px rgba(127, 61, 158, 0.1);
        transform: translateY(-2px);
    }

    .stat-card .icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .stat-card .icon-wrapper i {
        font-size: 24px;
    }

    .stat-card.primary .icon-wrapper {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .stat-card.success .icon-wrapper {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .stat-card.warning .icon-wrapper {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .stat-card.info .icon-wrapper {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #f3f4f6;
    }

    .sms-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #E5E7EB;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .sms-tab {
        border: 0;
        background: transparent;
        color: #4B5563;
        padding: 10px 14px;
        font-weight: 700;
        border-bottom: 3px solid transparent;
        cursor: pointer;
    }

    .sms-tab.active {
        color: #7F3D9E;
        border-bottom-color: #7F3D9E;
    }

    .sms-tab-panel {
        display: none;
    }

    .sms-tab-panel.active {
        display: block;
    }

    .filter-strip {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) repeat(3, minmax(140px, 180px)) auto;
        gap: 10px;
        align-items: end;
        margin-bottom: 16px;
    }

    .quick-sms-results {
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        margin-top: 8px;
        max-height: 220px;
        overflow: auto;
        display: none;
    }

    .quick-sms-results.show {
        display: block;
    }

    .quick-sms-result {
        width: 100%;
        border: 0;
        background: #fff;
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid #F3F4F6;
        cursor: pointer;
    }

    .quick-sms-result:hover {
        background: #F9FAFB;
    }

    .quick-sms-result strong {
        display: block;
        color: #111827;
    }

    .quick-sms-result span {
        color: #6B7280;
        font-size: 12px;
    }

    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pagination-info {
        color: #6B7280;
        font-size: 13px;
    }

    .pagination-controls {
        display: flex;
        gap: 8px;
    }

    .pagination-btn {
        border: 1px solid #D1D5DB;
        background: #fff;
        color: #374151;
        border-radius: 8px;
        padding: 8px 12px;
        cursor: pointer;
    }

    .pagination-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead {
        background: #f9fafb;
    }

    .modern-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }

    .modern-table tbody tr:hover {
        background: #f9fafb;
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

    .status-badge.draft {
        background: rgba(107, 114, 128, 0.1);
        color: #6B7280;
    }

    .status-badge.scheduled {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .status-badge.sending {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
    }

    .status-badge.submitted {
        background: rgba(59, 130, 246, 0.1);
        color: #2563EB;
    }

    .status-badge.partially_delivered {
        background: rgba(245, 158, 11, 0.1);
        color: #92400E;
    }

    .status-badge.completed {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-badge.cancelled {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .status-badge.paused {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .action-btn {
        background: none;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
    }

    .action-btn:hover {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .action-btn.success:hover {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .action-btn.danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .action-btn.warning:hover {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
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
        background: #7F3D9E;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(127, 61, 158, 0.3);
    }

    .modern-btn.success {
        background: #10B981;
        color: white;
    }

    .modern-btn.success:hover {
        background: #059669;
    }

    .modern-btn.secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .modern-btn.secondary:hover {
        background: #e5e7eb;
    }

    .modern-btn.info {
        background: #3B82F6;
        color: white;
    }

    .modern-btn.info:hover {
        background: #2563EB;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #6b7280;
        margin-bottom: 0.5rem;
        font-family: 'Playfair Display', serif;
    }

    .empty-state p {
        color: #9ca3af;
        margin-bottom: 1.5rem;
    }

    .modern-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .modern-modal.active {
        display: flex;
    }

    .modal-content-modern {
        background: white;
        border-radius: 12px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header-modern {
        background: #7F3D9E;
        color: white;
        padding: 1.5rem;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header-modern h3 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .modal-body-modern {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    select.form-control {
        cursor: pointer;
    }

    .count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .count-badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .count-badge.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        background: #7F3D9E;
        transition: width 0.3s ease;
    }

    .campaign-preview-layout {
        display: grid;
        grid-template-columns: minmax(240px, 320px) 1fr;
        gap: 22px;
        align-items: start;
    }

    .sms-phone-frame {
        width: min(100%, 292px);
        margin: 0 auto;
        border: 10px solid #101820;
        border-radius: 34px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(17, 24, 39, 0.18);
        overflow: hidden;
    }

    .sms-phone-screen {
        min-height: 460px;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .sms-phone-top {
        height: 54px;
        display: grid;
        grid-template-columns: 32px 1fr 32px;
        align-items: center;
        border-bottom: 1px solid #F3F4F6;
        padding: 0 12px;
        color: #111827;
    }

    .sms-phone-sender {
        text-align: center;
        font-weight: 800;
        font-size: 12px;
        letter-spacing: 0;
    }

    .sms-phone-body {
        flex: 1;
        padding: 30px 24px;
    }

    .sms-phone-meta {
        text-align: center;
        font-size: 10px;
        color: #6B7280;
        margin-bottom: 10px;
        line-height: 1.35;
    }

    .sms-bubble {
        background: #F4F4F5;
        border: 1px solid #D4D4D8;
        border-radius: 12px;
        padding: 12px 14px;
        color: #111827;
        font-size: 13px;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .sms-phone-compose {
        display: grid;
        grid-template-columns: 1fr 34px;
        border-top: 1px solid #F3F4F6;
        background: #FAFAFA;
        padding: 10px;
        gap: 8px;
        color: #9CA3AF;
        font-size: 12px;
    }

    .preview-summary {
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        padding: 16px;
        background: #fff;
    }

    .preview-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid #F3F4F6;
        font-size: 13px;
    }

    .preview-summary-row:last-child {
        border-bottom: 0;
    }

    .preview-summary-row strong {
        color: #111827;
    }

    .preview-summary-row span {
        color: #4B5563;
        text-align: right;
    }

    @media (max-width: 768px) {
        .campaigns-container { padding: 16px !important; }
        .modern-card { padding: 16px !important; }
        .modern-card > div[style] { flex-wrap: wrap !important; gap: 8px !important; }
        .modern-btn { font-size: 13px; padding: 8px 14px; }
        .campaign-preview-layout { grid-template-columns: 1fr; }
        .filter-strip { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1><i class="fas fa-sms"></i> SMS Campaigns</h1>
            <p>Create and manage bulk SMS campaigns for members</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <button class="modern-btn primary" onclick="openModal('createCampaignModal')">
                <i class="fas fa-plus"></i> Create Campaign
            </button>
            <button class="modern-btn secondary" onclick="processScheduledCampaigns()">
                <i class="fas fa-clock"></i> Process Due
            </button>
            <button class="modern-btn secondary" onclick="syncDeliveryStatuses()">
                <i class="fas fa-sync"></i> Sync Delivery
            </button>
            <button class="modern-btn success" onclick="openModal('quickSMSModal')">
                <i class="fas fa-sms"></i> Quick SMS
            </button>
            <a href="/admin/communications" class="modern-btn secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="icon-wrapper">
                <i class="fas fa-broadcast-tower"></i>
            </div>
            <p class="stat-value"><?php echo $stats['active_campaigns'] ?? 0; ?></p>
            <p class="stat-label">Active Campaigns</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="icon-wrapper">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['sent_today'] ?? 0); ?></p>
            <p class="stat-label">Delivered Today</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="icon-wrapper">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['queue_pending'] ?? 0); ?></p>
            <p class="stat-label">Queue Pending</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="icon-wrapper">
                <i class="fas fa-coins"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['sms_credits'] ?? 0); ?></p>
            <p class="stat-label">SMS Credits</p>
        </div>
    </div>
</div>

<!-- Campaigns List -->
<div class="modern-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #1f2937; font-size: 1.5rem;">
            <i class="fas fa-list"></i> SMS Activity
        </h2>
    </div>

    <div class="sms-tabs">
        <button type="button" class="sms-tab active" data-sms-tab="campaigns" onclick="switchSmsTab('campaigns')">Campaigns</button>
        <button type="button" class="sms-tab" data-sms-tab="quick" onclick="switchSmsTab('quick')">Quick SMS</button>
    </div>

    <div class="sms-tab-panel active" id="sms-tab-campaigns">
    <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <i class="fas fa-sms"></i>
            <h3>No SMS Campaigns Yet</h3>
            <p>Create your first SMS campaign to send bulk messages to members</p>
            <button class="modern-btn primary" onclick="openModal('createCampaignModal')">
                <i class="fas fa-plus"></i> Create First Campaign
            </button>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Audience</th>
                        <th>Recipients</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                    <?php
                    $smsCampaignFilters = !empty($campaign['custom_filters']) ? json_decode($campaign['custom_filters'], true) : [];
                    $campaignEditJson = htmlspecialchars(json_encode([
                        'id' => (int)$campaign['id'],
                        'title' => $campaign['title'] ?? '',
                        'message' => $campaign['message'] ?? '',
                        'target_audience' => $campaign['target_audience'] ?? 'all_members',
                        'custom_filters' => is_array($smsCampaignFilters) ? $smsCampaignFilters : [],
                        'scheduled_at' => !empty($campaign['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '',
                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG), ENT_QUOTES, 'UTF-8'); ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($campaign['title']); ?></strong><br>
                                <small style="color: #6b7280;"><?php echo date('M j, Y', strtotime($campaign['created_at'])); ?></small>
                            </div>
                        </td>
                        <td><?php echo ucwords(str_replace('_', ' ', $campaign['target_audience'])); ?></td>
                        <td><?php echo number_format($campaign['total_recipients']); ?></td>
                        <td>
                            <div>
                                <span class="count-badge success"><?php echo (int)($campaign['delivered_count'] ?? $campaign['sent_count'] ?? 0); ?> delivered</span>
                                <span class="count-badge"><?php echo (int)($campaign['submitted_count'] ?? 0); ?> submitted</span>
                                <span class="count-badge danger"><?php echo (int)($campaign['failed_count'] ?? 0) + (int)($campaign['undelivered_count'] ?? 0); ?> failed</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($campaign['total_recipients'] > 0) ? ((((int)($campaign['delivered_count'] ?? $campaign['sent_count'] ?? 0)) / $campaign['total_recipients']) * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $campaign['status']; ?>">
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $campaign['status']))); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            if ($campaign['scheduled_at']) {
                                echo date('M j, Y H:i', strtotime($campaign['scheduled_at']));
                            } else {
                                echo '<span style="color: #9ca3af;">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="action-btn" onclick="viewCampaign(<?php echo $campaign['id']; ?>)" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if (in_array($campaign['status'], ['draft', 'scheduled', 'paused'], true)): ?>
                                <button class="action-btn" data-campaign="<?php echo $campaignEditJson; ?>" onclick="editSmsCampaign(this)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn success" data-campaign="<?php echo $campaignEditJson; ?>" onclick="previewExistingSmsCampaign(this)" title="Send Now">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button class="action-btn danger" onclick="cancelCampaign(<?php echo $campaign['id']; ?>)" title="Cancel">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($campaign['status'] === 'sending'): ?>
                                <button class="action-btn warning" onclick="pauseCampaign(<?php echo $campaign['id']; ?>)" title="Pause Sending">
                                    <i class="fas fa-pause"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    </div>

    <div class="sms-tab-panel" id="sms-tab-quick">
        <form class="filter-strip" method="GET" action="/admin/sms-campaigns">
            <input type="hidden" name="sms_tab" value="quick">
            <div>
                <label class="form-label">Search</label>
                <input class="form-control" type="search" name="quick_search" value="<?php echo htmlspecialchars($quickFilters['search'] ?? ''); ?>" placeholder="Recipient, phone, member number, message">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select class="form-control" name="quick_status">
                    <?php foreach (['all' => 'All statuses', 'pending' => 'Pending', 'submitted' => 'Submitted', 'delivered' => 'Delivered', 'failed' => 'Failed', 'undelivered' => 'Undelivered', 'expired' => 'Expired', 'rejected' => 'Rejected', 'unknown' => 'Unknown'] as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo ($quickFilters['status'] ?? 'all') === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input class="form-control" type="date" name="quick_date_from" value="<?php echo htmlspecialchars($quickFilters['date_from'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">To</label>
                <input class="form-control" type="date" name="quick_date_to" value="<?php echo htmlspecialchars($quickFilters['date_to'] ?? ''); ?>">
            </div>
            <button class="modern-btn secondary" type="submit"><i class="fas fa-filter"></i> Filter</button>
        </form>

        <?php if (empty($queue_items)): ?>
            <div class="empty-state">
                <i class="fas fa-sms"></i>
                <h3>No Quick SMS Entries</h3>
                <p>Tracked quick SMS entries will appear here after submission.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Delivered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queue_items as $item): ?>
                            <?php
                                $queueMessage = (string)($item['message'] ?? '');
                                $queuePreview = strlen($queueMessage) > 90 ? substr($queueMessage, 0, 90) . '...' : $queueMessage;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')) ?: 'Direct recipient'); ?></strong><br>
                                    <small style="color:#6B7280;"><?php echo htmlspecialchars(($item['member_number'] ?? '') ?: $item['phone_number']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($queuePreview); ?></td>
                                <td><span class="status-badge <?php echo htmlspecialchars($item['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $item['status'] ?? 'pending'))); ?></span></td>
                                <td><?php echo !empty($item['submitted_at']) ? date('M j, Y H:i', strtotime($item['submitted_at'])) : '<span style="color:#9CA3AF;">-</span>'; ?></td>
                                <td><?php echo !empty($item['delivered_at']) ? date('M j, Y H:i', strtotime($item['delivered_at'])) : '<span style="color:#9CA3AF;">-</span>'; ?></td>
                                <td>
                                    <?php if (($item['status'] ?? '') === 'pending'): ?>
                                        <button class="action-btn success" onclick="sendQueueItem(<?php echo (int)$item['id']; ?>)" title="Submit SMS"><i class="fas fa-paper-plane"></i></button>
                                    <?php endif; ?>
                                    <?php if (($item['status'] ?? '') === 'failed'): ?>
                                        <button class="action-btn warning" onclick="retryQueueItem(<?php echo (int)$item['id']; ?>)" title="Retry"><i class="fas fa-redo"></i></button>
                                    <?php endif; ?>
                                    <button class="action-btn danger" onclick="deleteQueueItem(<?php echo (int)$item['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination" style="margin-top:16px;">
                <div class="pagination-info">Viewing <?php echo count($queue_items); ?> of <?php echo (int)($quickPagination['total_items'] ?? 0); ?> quick SMS entries</div>
                <div class="pagination-controls">
                    <button class="pagination-btn" <?php echo ((int)($quickPagination['current_page'] ?? 1) <= 1) ? 'disabled' : ''; ?> onclick="window.location.href='<?php echo htmlspecialchars($quickPageUrl(max(1, (int)($quickPagination['current_page'] ?? 1) - 1))); ?>'">Previous</button>
                    <button class="pagination-btn" <?php echo ((int)($quickPagination['current_page'] ?? 1) >= (int)($quickPagination['total_pages'] ?? 1)) ? 'disabled' : ''; ?> onclick="window.location.href='<?php echo htmlspecialchars($quickPageUrl((int)($quickPagination['current_page'] ?? 1) + 1)); ?>'">Next</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modern-modal" id="createCampaignModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-plus"></i> Create SMS Campaign</h3>
            <button class="modal-close" onclick="closeModal('createCampaignModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/communications/create-campaign" method="POST" id="createCampaignForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="form-group">
                    <label for="campaign-title">Campaign Title</label>
                    <input type="text" class="form-control" id="campaign-title" name="title" required placeholder="e.g., Payment Reminder - January 2026">
                </div>

                <div class="form-group">
                    <label for="target-audience">Target Audience</label>
                    <select class="form-control" id="target-audience" name="target_audience" required>
                        <option value="">Select audience...</option>
                        <option value="all_members">All Members</option>
                        <option value="active">Active Members Only</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="grace_period">Grace Period Members</option>
                        <option value="payment_all">All Payment Groups</option>
                        <option value="payment_paid_current">Paid</option>
                        <option value="payment_unpaid_current">Not Paid</option>
                        <option value="payment_partially_paid">Partially Paid</option>
                        <option value="payment_in_arrears">In Arrears</option>
                        <option value="payment_defaulted">Defaulted</option>
                        <option value="agent_all">All Agents</option>
                        <option value="agent_active">Active Agents</option>
                        <option value="agent_inactive">Inactive Agents</option>
                        <option value="agent_with_members">Agents With Members</option>
                        <option value="custom">Custom Selection</option>
                    </select>
                </div>

                <!-- Custom filters — shown only when "Custom Selection" is chosen -->
                <div id="custom-filters-panel" style="display:none; background:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; padding:16px 18px; margin-bottom:16px;">
                    <p style="font-size:0.82rem; font-weight:600; color:#7F3D9E; margin:0 0 12px;">Custom Filters <span style="font-weight:400; color:#6B7280;">(leave blank to skip a filter)</span></p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Membership Status</label>
                            <select class="form-control form-control-sm" name="filter_status">
                                <option value="">Any status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="grace_period">Grace Period</option>
                                <option value="suspended">Suspended</option>
                                <option value="defaulted">Defaulted</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Package Type</label>
                            <select class="form-control form-control-sm" name="filter_package">
                                <option value="">Any package</option>
                                <option value="individual">Individual</option>
                                <option value="family">Family / Couple</option>
                                <option value="extended_family_1">Extended Family 1</option>
                                <option value="extended_family_2">Extended Family 2</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Joined After</label>
                            <input type="date" class="form-control form-control-sm" name="filter_joined_after">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Joined Before</label>
                            <input type="date" class="form-control form-control-sm" name="filter_joined_before">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sms-message">SMS Message</label>
                    <textarea class="form-control" id="sms-message" name="message" required placeholder="Enter your SMS message here..." maxlength="160"></textarea>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:0.5rem;">
                        <small style="color:#6b7280;"><span id="char-counter">0</span>/160 characters</small>
                        <select class="form-control form-control-sm" id="sms-tag-picker" style="max-width:220px;">
                            <option value="">Insert tag...</option>
                        </select>
                        <small id="sms-tag-help" style="color:#6b7280;">Tags change by audience.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select class="form-control" id="priority" name="priority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule-type">Schedule</label>
                    <select class="form-control" id="schedule-type" name="schedule_type" required>
                        <option value="now">Send Immediately</option>
                        <option value="scheduled">Schedule for Later</option>
                        <option value="draft">Save as Draft</option>
                    </select>
                </div>

                <div class="form-group" id="schedule-datetime-field" style="display: none;">
                    <label for="schedule-datetime">Schedule Date & Time</label>
                    <input type="datetime-local" class="form-control" id="schedule-datetime" name="scheduled_at">
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('createCampaignModal')">Cancel</button>
                    <button type="submit" class="modern-btn primary">
                        <i class="fas fa-save"></i> Create Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modern-modal" id="smsCampaignPreviewModal">
    <div class="modal-content-modern" style="max-width: 860px;">
        <div class="modal-header-modern">
            <h3><i class="fas fa-mobile-alt"></i> Preview SMS Campaign</h3>
            <button class="modal-close" onclick="closeModal('smsCampaignPreviewModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <div class="campaign-preview-layout">
                <div class="sms-phone-frame">
                    <div class="sms-phone-screen">
                        <div class="sms-phone-top">
                            <i class="fas fa-chevron-left"></i>
                            <div class="sms-phone-sender"><?php echo htmlspecialchars(HOSTPINNACLE_SENDER_ID ?? 'SHENA'); ?></div>
                            <i class="fas fa-ellipsis-v"></i>
                        </div>
                        <div class="sms-phone-body">
                            <div class="sms-phone-meta">
                                Text Message<br>
                                <span id="smsPreviewTime">Today, 3:10 PM</span>
                            </div>
                            <div class="sms-bubble" id="smsPreviewBubble"></div>
                        </div>
                        <div class="sms-phone-compose">
                            <span>Text message</span>
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
                <div class="preview-summary">
                    <div class="preview-summary-row"><strong>Campaign</strong><span id="smsPreviewTitle"></span></div>
                    <div class="preview-summary-row"><strong>Audience</strong><span id="smsPreviewAudience"></span></div>
                    <div class="preview-summary-row"><strong>Preview recipient</strong><span id="smsPreviewRecipient"></span></div>
                    <div class="preview-summary-row"><strong>Schedule</strong><span id="smsPreviewSchedule"></span></div>
                    <div class="preview-summary-row"><strong>Characters</strong><span id="smsPreviewCharacters"></span></div>
                    <div style="margin-top:16px;color:#6B7280;font-size:13px;line-height:1.5;">
                        This preview uses the first matching recipient when available. Confirm only after checking spelling, placeholders, audience, and schedule.
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;flex-wrap:wrap;">
                <button type="button" class="modern-btn secondary" onclick="closeModal('smsCampaignPreviewModal')">Back to Edit</button>
                <button type="button" class="modern-btn primary" id="confirmSmsCampaignSubmit">
                    <i class="fas fa-paper-plane"></i> Confirm Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modern-modal" id="editCampaignModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-edit"></i> Edit SMS Campaign</h3>
            <button class="modal-close" onclick="closeModal('editCampaignModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form id="editCampaignForm">
                <input type="hidden" id="edit-campaign-id" name="campaign_id">
                <div class="form-group">
                    <label for="edit-campaign-title">Campaign Title</label>
                    <input type="text" class="form-control" id="edit-campaign-title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit-target-audience">Target Audience</label>
                    <select class="form-control" id="edit-target-audience" name="target_audience" required>
                        <option value="all_members">All Members</option>
                        <option value="active">Active Members Only</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="grace_period">Grace Period Members</option>
                        <option value="payment_all">All Payment Groups</option>
                        <option value="payment_paid_current">Paid</option>
                        <option value="payment_unpaid_current">Not Paid</option>
                        <option value="payment_partially_paid">Partially Paid</option>
                        <option value="payment_in_arrears">In Arrears</option>
                        <option value="payment_defaulted">Defaulted</option>
                        <option value="agent_all">All Agents</option>
                        <option value="agent_active">Active Agents</option>
                        <option value="agent_inactive">Inactive Agents</option>
                        <option value="agent_with_members">Agents With Members</option>
                        <option value="custom">Custom Selection</option>
                    </select>
                </div>
                <div id="edit-custom-filters-panel" style="display:none; background:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; padding:16px 18px; margin-bottom:16px;">
                    <p style="font-size:0.82rem; font-weight:600; color:#7F3D9E; margin:0 0 12px;">Custom Filters <span style="font-weight:400; color:#6B7280;">(leave blank to skip a filter)</span></p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Membership Status</label>
                            <select class="form-control form-control-sm" id="edit-filter-status" name="filter_status">
                                <option value="">Any status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="grace_period">Grace Period</option>
                                <option value="suspended">Suspended</option>
                                <option value="defaulted">Defaulted</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Package Type</label>
                            <select class="form-control form-control-sm" id="edit-filter-package" name="filter_package">
                                <option value="">Any package</option>
                                <option value="individual">Individual</option>
                                <option value="family">Family / Couple</option>
                                <option value="extended_family_1">Extended Family 1</option>
                                <option value="extended_family_2">Extended Family 2</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Joined After</label>
                            <input type="date" class="form-control form-control-sm" id="edit-filter-joined-after" name="filter_joined_after">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.82rem;">Joined Before</label>
                            <input type="date" class="form-control form-control-sm" id="edit-filter-joined-before" name="filter_joined_before">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-campaign-message">Message</label>
                    <textarea class="form-control" id="edit-campaign-message" name="message" rows="5" required></textarea>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:0.5rem;">
                        <select class="form-control form-control-sm" id="edit-sms-tag-picker" style="max-width:220px;">
                            <option value="">Insert tag...</option>
                        </select>
                        <small id="edit-sms-tag-help" style="color:#6b7280;">Tags change by audience.</small>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-campaign-scheduled-at">Scheduled Date & Time</label>
                    <input type="datetime-local" class="form-control" id="edit-campaign-scheduled-at" name="scheduled_at">
                    <small style="color:#6b7280;">Leave blank to keep the campaign as a draft.</small>
                </div>
                <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:2rem;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('editCampaignModal')">Cancel</button>
                    <button type="submit" class="modern-btn primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick SMS Modal -->
<div class="modern-modal" id="quickSMSModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-sms"></i> Send Quick SMS</h3>
            <button class="modal-close" onclick="closeModal('quickSMSModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/communications/quick-sms" method="POST" id="quickSMSForm">
                                <?php if (isset($_SESSION['csrf_token'])): ?>
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <?php endif; ?>
                <div class="form-group">
                    <label for="quick-recipient-type">Recipients</label>
                    <select class="form-control" id="quick-recipient-type" name="recipient_type" required>
                        <option value="">Select recipient type...</option>
                        <option value="all">All Members</option>
                        <option value="group">Member Group</option>
                        <option value="individual">Individual Member</option>
                    </select>
                </div>

                <div class="form-group" id="quick-group-field" style="display: none;">
                    <label for="quick-recipient-group">Member Group</label>
                    <select class="form-control" id="quick-recipient-group" name="recipient_group">
                        <option value="active">Active Members</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="pending">Pending Members</option>
                    </select>
                </div>

                <div class="form-group" id="quick-individual-field" style="display: none;">
                    <label for="quick-member-search">Search Member</label>
                    <input class="form-control" id="quick-member-search" type="search" placeholder="Search by name, ID, member number or phone">
                    <input type="hidden" id="quick-recipient-id" name="recipient_id">
                    <div class="quick-sms-results" id="quick-sms-results"></div>
                    <small id="quick-selected-member" style="display:block;color:#6B7280;margin-top:8px;">Type to find and select a member.</small>
                </div>

                <div class="form-group">
                    <label for="quick-message">Message</label>
                    <textarea class="form-control" id="quick-message" name="message" required placeholder="Enter your SMS message..." maxlength="160"></textarea>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                        <span id="quick-char-counter">0</span>/160 characters
                    </small>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('quickSMSModal')">Cancel</button>
                    <button type="submit" class="modern-btn success">
                        <i class="fas fa-paper-plane"></i> Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function switchSmsTab(tab) {
    document.querySelectorAll('.sms-tab').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.smsTab === tab);
    });
    document.querySelectorAll('.sms-tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.id === `sms-tab-${tab}`);
    });
    const url = new URL(window.location.href);
    url.searchParams.set('sms_tab', tab);
    window.history.replaceState({}, '', url);
}

document.addEventListener('DOMContentLoaded', function() {
    const activeTab = new URLSearchParams(window.location.search).get('sms_tab');
    if (activeTab === 'quick') {
        switchSmsTab('quick');
    }
});

// Close modal on outside click
document.querySelectorAll('.modern-modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modern-modal.active').forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Character counters
document.getElementById('sms-message')?.addEventListener('input', function() {
    document.getElementById('char-counter').textContent = this.value.length;
});

document.getElementById('quick-message')?.addEventListener('input', function() {
    document.getElementById('quick-char-counter').textContent = this.value.length;
});

const smsTagGroups = {
    member: [
        ['{first_name}', 'First Name'],
        ['{member_number}', 'Member No.'],
        ['{package}', 'Package']
    ],
    payment: [
        ['{first_name}', 'First Name'],
        ['{member_number}', 'Member No.'],
        ['{monthly_contribution}', 'Contribution'],
        ['{amount_due}', 'Balance'],
        ['{missed_months}', 'Missed Months']
    ],
    agent: [
        ['{first_name}', 'First Name'],
        ['{agent_number}', 'Agent No.'],
        ['{total_members}', 'Members']
    ]
};

function smsAudienceTagGroup(audience) {
    if ((audience || '').startsWith('payment_')) return 'payment';
    if ((audience || '').startsWith('agent_')) return 'agent';
    return 'member';
}

function populateSmsTagPicker(pickerId, helpId, audience) {
    const picker = document.getElementById(pickerId);
    const help = document.getElementById(helpId);
    if (!picker) return;

    const group = smsAudienceTagGroup(audience);
    const labels = { member: 'Member tags', payment: 'Payment tags', agent: 'Agent tags' };
    picker.innerHTML = '<option value="">Insert tag...</option>';
    smsTagGroups[group].forEach(([value, label]) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = `${label} ${value}`;
        picker.appendChild(option);
    });
    if (help) help.textContent = labels[group] + ' shown for this audience.';
}

function updateSmsTagPicker() {
    populateSmsTagPicker('sms-tag-picker', 'sms-tag-help', document.getElementById('target-audience')?.value || '');
}

function updateEditSmsTagPicker() {
    populateSmsTagPicker('edit-sms-tag-picker', 'edit-sms-tag-help', document.getElementById('edit-target-audience')?.value || '');
}

function insertSmsTag(tag, textareaId = 'sms-message', counterId = 'char-counter') {
    const textarea = document.getElementById(textareaId);
    if (!textarea || !tag) return;

    const start = textarea.selectionStart || 0;
    const end = textarea.selectionEnd || 0;
    textarea.value = textarea.value.slice(0, start) + tag + textarea.value.slice(end);
    textarea.focus();
    textarea.setSelectionRange(start + tag.length, start + tag.length);
    const counter = document.getElementById(counterId);
    if (counter) counter.textContent = textarea.value.length;
}

document.getElementById('sms-tag-picker')?.addEventListener('change', function() {
    insertSmsTag(this.value);
    this.value = '';
});

document.getElementById('edit-sms-tag-picker')?.addEventListener('change', function() {
    insertSmsTag(this.value, 'edit-campaign-message');
    this.value = '';
});

async function parseJsonResponse(response) {
    const text = await response.text();
    let data = {};

    try {
        data = text ? JSON.parse(text) : {};
    } catch (error) {
        data = {
            success: false,
            message: 'Server returned an unexpected response. Please retry or check logs.'
        };
    }

    if (!response.ok && data.success !== false) {
        data.success = false;
        data.message = data.message || 'Request failed with HTTP ' + response.status;
    }

    return data;
}

// Schedule type handler
document.getElementById('schedule-type')?.addEventListener('change', function() {
    const datetimeField = document.getElementById('schedule-datetime-field');
    datetimeField.style.display = this.value === 'scheduled' ? 'block' : 'none';
});

document.getElementById('target-audience')?.addEventListener('change', function () {
    const panel = document.getElementById('custom-filters-panel');
    if (panel) {
        panel.style.display = this.value === 'custom' ? '' : 'none';
    }
    updateSmsTagPicker();
});

updateSmsTagPicker();

document.getElementById('edit-target-audience')?.addEventListener('change', function () {
    const panel = document.getElementById('edit-custom-filters-panel');
    if (panel) {
        panel.style.display = this.value === 'custom' ? '' : 'none';
    }
    updateEditSmsTagPicker();
});

updateEditSmsTagPicker();

function setEditFilterValues(filters = {}) {
    const status = filters.member_status || filters.status || '';
    document.getElementById('edit-filter-status').value = status;
    document.getElementById('edit-filter-package').value = filters.package || '';
    document.getElementById('edit-filter-joined-after').value = filters.joined_after || '';
    document.getElementById('edit-filter-joined-before').value = filters.joined_before || '';
}

function loadQuickSmsMembers(searchTerm = '') {
    const recipientInput = document.getElementById('quick-recipient-id');
    const resultsPanel = document.getElementById('quick-sms-results');
    const selectedLabel = document.getElementById('quick-selected-member');
    if (!recipientInput || !resultsPanel) return;

    if (searchTerm.trim().length < 2) {
        recipientInput.value = '';
        resultsPanel.classList.remove('show');
        resultsPanel.innerHTML = '';
        if (selectedLabel) selectedLabel.textContent = 'Type at least 2 characters to search members.';
        return;
    }

    resultsPanel.classList.add('show');
    resultsPanel.innerHTML = '<div class="quick-sms-result"><span>Searching...</span></div>';
    fetch('/admin/api/members?search=' + encodeURIComponent(searchTerm.trim()))
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                resultsPanel.innerHTML = '<div class="quick-sms-result"><span>No matching member found</span></div>';
                return;
            }

            resultsPanel.innerHTML = data.map(member => {
                const memberName = member.member_name || `${member.first_name || ''} ${member.last_name || ''}`.trim() || 'Member';
                const idNumber = member.id_number ? ` | ID ${member.id_number}` : '';
                const phone = member.phone ? ` | ${member.phone}` : '';
                const subtitle = `${member.member_number || 'No member number'}${idNumber}${phone}`;
                return `<button type="button" class="quick-sms-result" onclick="selectQuickSmsMember('${member.id}', '${escapeJs(memberName)}', '${escapeJs(subtitle)}')"><strong>${escapeHtml(memberName)}</strong><span>${escapeHtml(subtitle)}</span></button>`;
            }).join('');
        })
        .catch(() => {
            resultsPanel.innerHTML = '<div class="quick-sms-result"><span>Failed to load members</span></div>';
        });
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function escapeJs(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

function selectQuickSmsMember(id, name, subtitle) {
    document.getElementById('quick-recipient-id').value = id;
    document.getElementById('quick-member-search').value = name;
    document.getElementById('quick-selected-member').textContent = `${name} | ${subtitle}`;
    document.getElementById('quick-sms-results').classList.remove('show');
}

let quickSmsSearchTimer = null;
document.getElementById('quick-member-search')?.addEventListener('input', function() {
    clearTimeout(quickSmsSearchTimer);
    const value = this.value;
    quickSmsSearchTimer = setTimeout(() => loadQuickSmsMembers(value), 250);
});

// Quick SMS recipient type handler
document.getElementById('quick-recipient-type')?.addEventListener('change', function() {
    const groupField = document.getElementById('quick-group-field');
    const individualField = document.getElementById('quick-individual-field');
    const recipientInput = document.getElementById('quick-recipient-id');
    const resultsPanel = document.getElementById('quick-sms-results');
    groupField.style.display = this.value === 'group' ? 'block' : 'none';
    individualField.style.display = this.value === 'individual' ? 'block' : 'none';

    if (this.value === 'individual') {
        document.getElementById('quick-member-search').value = '';
        recipientInput.value = '';
        resultsPanel.innerHTML = '';
        resultsPanel.classList.remove('show');
        document.getElementById('quick-selected-member').textContent = 'Type to find and select a member.';
    } else {
        recipientInput.value = '';
    }
});

// Campaign actions
function viewCampaign(id) {
    window.location.href = '/admin/communications/campaign/' + id;
}

function downloadDeliveryReport(id) {
    window.location.href = '/admin/communications/campaign/' + id + '/delivery-report';
}

function processScheduledCampaigns() {
    fetch('/admin/communications/process-scheduled-campaigns', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(parseJsonResponse)
    .then(data => {
        if (data.success) {
            const processed = data.processed_count || 0;
            const campaigns = data.campaign_count || 0;
            ShenaApp.showNotification(`Processed ${processed} SMS recipient(s) across ${campaigns} campaign(s).`, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            ShenaApp.showNotification(data.message || 'Failed to process due campaigns', 'error');
        }
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function syncDeliveryStatuses() {
    fetch('/admin/communications/sync-delivery-statuses', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ limit: 100 })
    })
    .then(parseJsonResponse)
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification(`Delivery sync checked ${data.checked_count || 0} SMS record(s); ${data.updated_count || 0} updated.`, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            ShenaApp.showNotification(data.message || 'Failed to sync delivery statuses', 'error');
        }
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function resendPendingFailed(id) {
    const proceed = () => {
        fetch('/admin/communications/campaign/' + id + '/resend-pending-failed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        })
        .then(parseJsonResponse)
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification(data.message || 'Resend started', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                ShenaApp.showNotification(data.message || 'Failed to resend pending/failed SMS', 'error');
            }
        })
        .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Resend this campaign to every pending or failed recipient?',
            proceed,
            null,
            { type: 'warning', title: 'Resend SMS', confirmText: 'Resend' }
        );
        return;
    }

    if (confirm('Resend this campaign to every pending or failed recipient?')) {
        proceed();
    }
}

function editSmsCampaign(button) {
    const campaign = JSON.parse(button.getAttribute('data-campaign') || '{}');
    openSmsCampaignEditor(campaign);
}

function openSmsCampaignEditor(campaign) {
    document.getElementById('edit-campaign-id').value = campaign.id || '';
    document.getElementById('edit-campaign-title').value = campaign.title || '';
    document.getElementById('edit-campaign-message').value = campaign.message || '';

    const audienceSelect = document.getElementById('edit-target-audience');
    const storedAudience = campaign.target_audience || 'all_members';
    // Defensive: if the stored audience has no matching option (e.g. a payment group
    // added after this dropdown was last updated), preserve it instead of silently
    // losing the selection, which previously caused campaigns to fall back to all_members.
    const hasMatchingOption = Array.from(audienceSelect.options).some((opt) => opt.value === storedAudience);
    if (!hasMatchingOption) {
        const preservedOption = document.createElement('option');
        preservedOption.value = storedAudience;
        preservedOption.textContent = String(storedAudience).replace(/_/g, ' ');
        audienceSelect.appendChild(preservedOption);
    }
    audienceSelect.value = storedAudience;

    setEditFilterValues(campaign.custom_filters || {});
    audienceSelect.dispatchEvent(new Event('change'));
    document.getElementById('edit-campaign-scheduled-at').value = campaign.scheduled_at || '';
    openModal('editCampaignModal');
}

function openRequestedCampaignEditor() {
    const requestedCampaign = <?php echo $editCampaignToOpenJson ?: 'null'; ?>;
    if (requestedCampaign && requestedCampaign.id) {
        openSmsCampaignEditor(requestedCampaign);
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const requestedId = params.get('edit_campaign');
    if (!requestedId) return;

    const requestedNumber = Number(requestedId);
    const campaignButtons = document.querySelectorAll('[data-campaign]');
    for (const button of campaignButtons) {
        try {
            const campaign = JSON.parse(button.getAttribute('data-campaign') || '{}');
            if (Number(campaign.id) === requestedNumber) {
                editSmsCampaign(button);
                break;
            }
        } catch (error) {
            // Ignore malformed campaign metadata on unrelated buttons.
        }
    }
}

openRequestedCampaignEditor();

document.getElementById('editCampaignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const targetAudience = formData.get('target_audience');
    // Never silently widen a campaign's audience: if nothing is selected, block the
    // save instead of defaulting to all_members (this previously caused filtered
    // payment-breakdown campaigns to be sent to the entire membership).
    if (!targetAudience) {
        ShenaApp.showNotification('Please select a target audience before saving.', 'error');
        return;
    }
    fetch('/admin/communications/edit-campaign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            campaign_id: formData.get('campaign_id'),
            title: formData.get('title'),
            message: formData.get('message'),
            target_audience: targetAudience,
            filter_status: formData.get('filter_status') || '',
            filter_package: formData.get('filter_package') || '',
            filter_joined_after: formData.get('filter_joined_after') || '',
            filter_joined_before: formData.get('filter_joined_before') || '',
            scheduled_at: formData.get('scheduled_at') || null
        })
    })
    .then(parseJsonResponse)
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification(data.message || 'Campaign updated', 'success');
            location.reload();
        } else {
            ShenaApp.showNotification(data.message || 'Failed to update campaign', 'error');
        }
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
});

function sendCampaign(id, skipConfirm = false) {
    const proceed = () => {
        fetch('/admin/communications/send-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification(data.message || 'Campaign submitted.', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to send campaign: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (skipConfirm) {
        proceed();
        return;
    }

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Are you sure you want to send this campaign now?',
            proceed,
            null,
            { type: 'primary', title: 'Send Campaign', confirmText: 'Send Now' }
        );
        return;
    }

    if (confirm('Are you sure you want to send this campaign now?')) {
        proceed();
    }
}

function cancelCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/cancel-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign cancelled successfully!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to cancel campaign: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Are you sure you want to cancel this campaign?',
            proceed,
            null,
            { type: 'warning', title: 'Cancel Campaign', confirmText: 'Cancel Campaign' }
        );
        return;
    }

    if (confirm('Are you sure you want to cancel this campaign?')) {
        proceed();
    }
}

function deleteCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/delete-campaign', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ campaign_id: id, confirm_delete: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification(data.message || 'Campaign deleted', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification(data.message || 'Failed to delete campaign', 'error');
            }
        })
        .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Delete this campaign and its recipient records?',
            proceed,
            null,
            { type: 'danger', title: 'Delete Campaign', confirmText: 'Delete' }
        );
        return;
    }

    if (confirm('Delete this campaign and its recipient records?')) {
        proceed();
    }
}

function sendQueueItem(id) {
    fetch('/admin/communications/send-queue-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: id })
    })
    .then(response => response.json())
    .then(data => {
        ShenaApp.showNotification(data.message || (data.success ? 'SMS submitted' : 'Failed to submit SMS'), data.success ? 'success' : 'error');
        if (data.success) location.reload();
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function retryQueueItem(id) {
    fetch('/admin/communications/retry-queue-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: id })
    })
    .then(response => response.json())
    .then(data => {
        ShenaApp.showNotification(data.message || (data.success ? 'SMS queued for retry' : 'Failed to retry SMS'), data.success ? 'success' : 'error');
        if (data.success) location.reload();
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function deleteQueueItem(id) {
    const proceed = () => {
        fetch('/admin/communications/delete-queue-item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: id })
        })
        .then(response => response.json())
        .then(data => {
            ShenaApp.showNotification(data.message || (data.success ? 'Quick SMS deleted' : 'Failed to delete quick SMS'), data.success ? 'success' : 'error');
            if (data.success) location.reload();
        })
        .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Delete this quick SMS entry?',
            proceed,
            null,
            { type: 'danger', title: 'Delete Quick SMS', confirmText: 'Delete' }
        );
        return;
    }

    if (confirm('Delete this quick SMS entry?')) {
        proceed();
    }
}

function pauseCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/pause-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign paused!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to pause: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Pause this campaign?',
            proceed,
            null,
            { type: 'warning', title: 'Pause Campaign', confirmText: 'Pause' }
        );
        return;
    }

    if (confirm('Pause this campaign?')) {
        proceed();
    }
}

function resumeCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/send-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign resumed!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to resume: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Resume this campaign?',
            proceed,
            null,
            { type: 'primary', title: 'Resume Campaign', confirmText: 'Resume' }
        );
        return;
    }

    if (confirm('Resume this campaign?')) {
        proceed();
    }
}

function recipientPreviewValues(recipient = null) {
    const money = (value, fallback = 0) => {
        const numeric = Number(value ?? fallback);
        return numeric.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    const amountDue = money(recipient?.amount_due, 850);
    const monthlyContribution = money(recipient?.monthly_contribution ?? recipient?.amount_due, 1000);
    const paidAmount = money(recipient?.paid_amount, 0);
    const balanceDue = money(recipient?.balance_due ?? recipient?.amount_due, 850);
    const arrearsAmount = money(recipient?.arrears_amount ?? recipient?.amount_due, 850);

    return {
        '{member_name}': recipient ? `${recipient.first_name || ''} ${recipient.last_name || ''}`.trim() || 'Member' : 'Wycliffe Omondi',
        '{first_name}': recipient?.first_name || 'Test',
        '{last_name}': recipient?.last_name || 'Member',
        '{member_number}': recipient?.member_number || 'SH-550407',
        '{phone}': recipient?.phone || recipient?.recipient_value || '254700000000',
        '{email}': recipient?.email || 'member@example.com',
        '{package}': recipient?.package || 'Family',
        '{status}': recipient?.status || recipient?.member_status || 'Active',
        '{amount_due}': `KES ${amountDue}`,
        '{monthly_contribution}': `KES ${monthlyContribution}`,
        '{paid_amount}': `KES ${paidAmount}`,
        '{balance_due}': `KES ${balanceDue}`,
        '{arrears_amount}': `KES ${arrearsAmount}`,
        '{missed_months}': String(recipient?.missed_months ?? 0),
        '{last_payment_date}': recipient?.last_payment_date || 'N/A',
        '{agent_number}': recipient?.agent_number || recipient?.member_number || 'AG20260001',
        '{total_members}': String(recipient?.total_members ?? 0)
    };
}

function previewSampleMessage(message, recipient = null) {
    const sample = recipientPreviewValues(recipient);
    return Object.keys(sample).reduce((text, key) => text.split(key).join(sample[key]), message || '');
}

function previewRecipientLabel(recipient = null, channel = 'sms') {
    if (!recipient) return 'Resolving first recipient...';
    const name = `${recipient.first_name || ''} ${recipient.last_name || ''}`.trim() || 'Member';
    const contact = channel === 'email' ? recipient.email : recipient.phone;
    return [name, recipient.member_number, contact].filter(Boolean).join(' | ');
}

function campaignPreviewParams(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams();
    ['target_audience', 'filter_status', 'filter_package', 'filter_joined_after', 'filter_joined_before'].forEach((key) => {
        const value = formData.get(key);
        if (value) params.set(key, value);
    });
    return params;
}

function loadSmsFirstRecipient(form, message) {
    fetch(`/admin/bulk-sms/preview-recipients?${campaignPreviewParams(form).toString()}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.ok ? response.json() : null)
    .then(data => {
        const recipient = data?.sample?.[0] || null;
        document.getElementById('smsPreviewRecipient').textContent = recipient
            ? previewRecipientLabel(recipient, 'sms')
            : 'No matching recipient found';
        if (recipient) {
            document.getElementById('smsPreviewBubble').textContent = previewSampleMessage(message, recipient) || 'Message preview';
        }
    })
    .catch(() => {
        document.getElementById('smsPreviewRecipient').textContent = 'Unable to resolve recipient preview';
    });
}

function loadSmsCampaignFirstRecipient(campaignId, message) {
    if (!campaignId) return;
    fetch(`/admin/communications/campaign/${campaignId}/preview-recipient`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.ok ? response.json() : null)
    .then(data => {
        const recipient = data?.recipient || null;
        document.getElementById('smsPreviewRecipient').textContent = recipient
            ? previewRecipientLabel(recipient, 'sms')
            : 'No stored recipient found';
        if (recipient) {
            document.getElementById('smsPreviewBubble').textContent = previewSampleMessage(message, recipient) || 'Message preview';
        }
    })
    .catch(() => {
        document.getElementById('smsPreviewRecipient').textContent = 'Unable to resolve recipient preview';
    });
}

function friendlySelectText(select) {
    if (!select || select.selectedIndex < 0) return 'N/A';
    return select.options[select.selectedIndex].text || select.value || 'N/A';
}

function describeCampaignSchedule(formData) {
    const scheduleType = formData.get('schedule_type') || 'now';
    if (scheduleType === 'draft') return 'Save as draft';
    if (scheduleType === 'scheduled') {
        const scheduledAt = formData.get('scheduled_at') || '';
        return scheduledAt ? new Date(scheduledAt).toLocaleString() : 'Schedule time not selected';
    }
    return 'Send immediately';
}

let pendingSmsCampaignForm = null;
let pendingSmsCampaignAction = null;

function openSmsCampaignPreview(form) {
    const formData = new FormData(form);
    const message = formData.get('message') || '';
    const previewText = previewSampleMessage(message);
    const targetSelect = form.querySelector('[name="target_audience"]');

    document.getElementById('smsPreviewBubble').textContent = previewText || 'Message preview';
    document.getElementById('smsPreviewTitle').textContent = formData.get('title') || 'Untitled campaign';
    document.getElementById('smsPreviewAudience').textContent = friendlySelectText(targetSelect);
    document.getElementById('smsPreviewRecipient').textContent = 'Resolving first recipient...';
    document.getElementById('smsPreviewSchedule').textContent = describeCampaignSchedule(formData);
    document.getElementById('smsPreviewCharacters').textContent = `${message.length}/160 (${Math.ceil(Math.max(message.length, 1) / 160)} segment)`;
    document.getElementById('smsPreviewTime').textContent = new Date().toLocaleString([], { weekday: 'short', hour: 'numeric', minute: '2-digit' });

    pendingSmsCampaignForm = form;
    pendingSmsCampaignAction = null;
    openModal('smsCampaignPreviewModal');
    loadSmsFirstRecipient(form, message);
}

function openSmsCampaignPreviewFromData(campaign, confirmAction) {
    const message = campaign.message || '';
    const previewText = previewSampleMessage(message);

    document.getElementById('smsPreviewBubble').textContent = previewText || 'Message preview';
    document.getElementById('smsPreviewTitle').textContent = campaign.title || 'Untitled campaign';
    document.getElementById('smsPreviewAudience').textContent = String(campaign.target_audience || 'all_members').replace(/_/g, ' ');
    document.getElementById('smsPreviewRecipient').textContent = 'Resolving first recipient...';
    document.getElementById('smsPreviewSchedule').textContent = campaign.scheduled_at ? new Date(campaign.scheduled_at).toLocaleString() : 'Send immediately';
    document.getElementById('smsPreviewCharacters').textContent = `${message.length}/160 (${Math.ceil(Math.max(message.length, 1) / 160)} segment)`;
    document.getElementById('smsPreviewTime').textContent = new Date().toLocaleString([], { weekday: 'short', hour: 'numeric', minute: '2-digit' });

    pendingSmsCampaignForm = null;
    pendingSmsCampaignAction = confirmAction;
    openModal('smsCampaignPreviewModal');
    loadSmsCampaignFirstRecipient(campaign.id, message);
}

function previewExistingSmsCampaign(button) {
    const campaign = JSON.parse(button.getAttribute('data-campaign') || '{}');
    openSmsCampaignPreviewFromData(campaign, () => sendCampaign(campaign.id, true));
}

function submitSmsCampaignForm(form) {
    const formData = new FormData(form);
    fetch('/admin/communications/create-campaign', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('Campaign created successfully!', 'success');
            closeModal('createCampaignModal');
            location.reload();
        } else {
            ShenaApp.showNotification('Failed to create campaign: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ShenaApp.showNotification('Network error occurred', 'error');
    });
}

document.getElementById('confirmSmsCampaignSubmit')?.addEventListener('click', function() {
    closeModal('smsCampaignPreviewModal');
    if (pendingSmsCampaignForm) {
        submitSmsCampaignForm(pendingSmsCampaignForm);
        return;
    }
    if (typeof pendingSmsCampaignAction === 'function') {
        pendingSmsCampaignAction();
    }
});

// Form submissions
document.getElementById('createCampaignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    openSmsCampaignPreview(this);
});

document.getElementById('quickSMSForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/communications/quick-sms', {
        method: 'POST',
        body: formData
    })
    .then(response => parseJsonResponse(response))
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification(data.message || 'SMS submitted.', 'success');
            closeModal('quickSMSModal');
            location.reload();
        } else {
            ShenaApp.showNotification('Failed to send SMS: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ShenaApp.showNotification('Network error occurred', 'error');
    });
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
