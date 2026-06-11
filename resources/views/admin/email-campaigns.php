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

    .stat-card.info .icon-wrapper {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
    }

    .stat-card.danger .icon-wrapper {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
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

    .status-badge.completed {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-badge.cancelled {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
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

    .modern-btn.secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .modern-btn.secondary:hover {
        background: #e5e7eb;
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

    .campaign-preview-layout {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 22px;
        align-items: start;
    }

    .email-preview-card {
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(17, 24, 39, 0.10);
        overflow: hidden;
    }

    .email-preview-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        border-bottom: 1px solid #F3F4F6;
        background: #FAFAFA;
    }

    .email-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #D1D5DB;
    }

    .email-preview-header {
        padding: 18px 20px;
        border-bottom: 1px solid #F3F4F6;
    }

    .email-preview-subject {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 10px;
    }

    .email-preview-meta {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        color: #6B7280;
        font-size: 12px;
        flex-wrap: wrap;
    }

    .email-preview-body {
        padding: 22px 20px;
        min-height: 260px;
        color: #1F2937;
        font-size: 14px;
        line-height: 1.65;
        white-space: pre-wrap;
        word-break: break-word;
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
        .campaign-preview-layout {
            grid-template-columns: 1fr;
        }
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
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
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
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1><i class="fas fa-envelope-open-text"></i> Email Campaigns</h1>
            <p>Create and manage bulk email campaigns for members</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="modern-btn primary" onclick="openModal('createCampaignModal')">
                <i class="fas fa-plus"></i> Create Campaign
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
                <i class="fas fa-paper-plane"></i>
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
            <p class="stat-label">Sent Today</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="icon-wrapper">
                <i class="fas fa-envelope"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['total_sent'] ?? 0); ?></p>
            <p class="stat-label">Total Sent</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="icon-wrapper">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['failed_count'] ?? 0); ?></p>
            <p class="stat-label">Failed</p>
        </div>
    </div>
</div>

<!-- Campaigns List -->
<div class="modern-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #1f2937; font-size: 1.5rem;">
            <i class="fas fa-list"></i> Email Campaigns
        </h2>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <i class="fas fa-envelope-open-text"></i>
            <h3>No Email Campaigns Yet</h3>
            <p>Create your first email campaign to reach out to members</p>
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
                        <th>Sent/Failed</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                    <?php
                        $emailCampaignFilters = !empty($campaign['custom_filters']) ? json_decode($campaign['custom_filters'], true) : [];
                        $campaignEditJson = htmlspecialchars(json_encode([
                            'id' => (int)$campaign['id'],
                            'title' => $campaign['title'] ?? '',
                            'subject' => $emailCampaignFilters['email_subject'] ?? ($campaign['title'] ?? ''),
                            'message' => $campaign['message'] ?? '',
                            'target_audience' => $campaign['target_audience'] ?? 'all_members',
                            'custom_filters' => is_array($emailCampaignFilters) ? $emailCampaignFilters : [],
                            'scheduled_at' => !empty($campaign['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '',
                        ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG), ENT_QUOTES, 'UTF-8');
                    ?>
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
                            <span class="count-badge success"><?php echo $campaign['sent_count']; ?></span>
                            <span class="count-badge danger"><?php echo $campaign['failed_count']; ?></span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $campaign['status']; ?>">
                                <?php echo ucfirst($campaign['status']); ?>
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
                            <?php if ($campaign['status'] === 'draft' || $campaign['status'] === 'scheduled'): ?>
                                <button class="action-btn" data-campaign="<?php echo $campaignEditJson; ?>" onclick="editEmailCampaign(this)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn success" data-campaign="<?php echo $campaignEditJson; ?>" onclick="previewExistingEmailCampaign(this)" title="Send Now">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button class="action-btn danger" onclick="cancelCampaign(<?php echo $campaign['id']; ?>)" title="Cancel">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($campaign['status'] === 'completed' && $campaign['failed_count'] > 0): ?>
                                <button class="action-btn" onclick="retryFailed(<?php echo $campaign['id']; ?>)" title="Retry Failed">
                                    <i class="fas fa-redo"></i>
                                </button>
                            <?php endif; ?>
                            <button class="action-btn danger" onclick="deleteCampaign(<?php echo $campaign['id']; ?>)" title="Delete Campaign">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Create Campaign Modal -->
<div class="modern-modal" id="createCampaignModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-plus"></i> Create Email Campaign</h3>
            <button class="modal-close" onclick="closeModal('createCampaignModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/email-campaigns/create" method="POST" id="createCampaignForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="form-group">
                    <label for="campaign-title">Campaign Title</label>
                    <input type="text" class="form-control" id="campaign-title" name="title" required placeholder="e.g., Monthly Newsletter - January 2026">
                </div>

                <div class="form-group">
                    <label for="target-audience">Target Audience</label>
                    <select class="form-control" id="target-audience" name="target_audience" required>
                        <option value="">Select audience...</option>
                        <option value="all_members">All Members</option>
                        <option value="active">Active Members Only</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="pending">Pending Members</option>
                        <option value="grace_period">Grace Period Members</option>
                        <option value="defaulted">Payment Defaulters</option>
                        <option value="custom">Custom Selection</option>
                    </select>
                </div>

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
                    <label for="email-subject">Email Subject</label>
                    <input type="text" class="form-control" id="email-subject" name="subject" required placeholder="Enter email subject line">
                </div>

                <div class="form-group">
                    <label for="email-body">Email Content</label>
                    <textarea class="form-control" id="email-body" name="message" required placeholder="Enter your email message here..."></textarea>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                        You can use placeholders: {member_name}, {first_name}, {last_name}, {member_number}, {package}, {status}, {amount_due}
                    </small>
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

<div class="modern-modal" id="emailCampaignPreviewModal">
    <div class="modal-content-modern" style="max-width: 940px;">
        <div class="modal-header-modern">
            <h3><i class="fas fa-envelope-open-text"></i> Preview Email Campaign</h3>
            <button class="modal-close" onclick="closeModal('emailCampaignPreviewModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <div class="campaign-preview-layout">
                <div class="email-preview-card">
                    <div class="email-preview-toolbar">
                        <span class="email-dot"></span>
                        <span class="email-dot"></span>
                        <span class="email-dot"></span>
                    </div>
                    <div class="email-preview-header">
                        <div class="email-preview-subject" id="emailPreviewSubject"></div>
                        <div class="email-preview-meta">
                            <span>From: SHENA Companion</span>
                            <span id="emailPreviewTime">Today</span>
                        </div>
                    </div>
                    <div class="email-preview-body" id="emailPreviewBody"></div>
                </div>
                <div class="preview-summary">
                    <div class="preview-summary-row"><strong>Campaign</strong><span id="emailPreviewTitle"></span></div>
                    <div class="preview-summary-row"><strong>Audience</strong><span id="emailPreviewAudience"></span></div>
                    <div class="preview-summary-row"><strong>Preview recipient</strong><span id="emailPreviewRecipient"></span></div>
                    <div class="preview-summary-row"><strong>Schedule</strong><span id="emailPreviewSchedule"></span></div>
                    <div class="preview-summary-row"><strong>Body length</strong><span id="emailPreviewLength"></span></div>
                    <div style="margin-top:16px;color:#6B7280;font-size:13px;line-height:1.5;">
                        This preview uses the first matching recipient when available. Confirm only after checking subject, body, placeholders, audience, and schedule.
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;flex-wrap:wrap;">
                <button type="button" class="modern-btn secondary" onclick="closeModal('emailCampaignPreviewModal')">Back to Edit</button>
                <button type="button" class="modern-btn primary" id="confirmEmailCampaignSubmit">
                    <i class="fas fa-paper-plane"></i> Confirm Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modern-modal" id="editCampaignModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-edit"></i> Edit Email Campaign</h3>
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
                        <option value="pending">Pending Members</option>
                        <option value="grace_period">Grace Period Members</option>
                        <option value="defaulted">Payment Defaulters</option>
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
                                <option value="pending">Pending</option>
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
                    <label for="edit-campaign-subject">Email Subject</label>
                    <input type="text" class="form-control" id="edit-campaign-subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="edit-campaign-message">Message</label>
                    <textarea class="form-control" id="edit-campaign-message" name="message" rows="6" required></textarea>
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

<script>
// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

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
});

document.getElementById('edit-target-audience')?.addEventListener('change', function () {
    const panel = document.getElementById('edit-custom-filters-panel');
    if (panel) {
        panel.style.display = this.value === 'custom' ? '' : 'none';
    }
});

function setEditFilterValues(filters = {}) {
    const status = filters.member_status || filters.status || '';
    document.getElementById('edit-filter-status').value = status;
    document.getElementById('edit-filter-package').value = filters.package || '';
    document.getElementById('edit-filter-joined-after').value = filters.joined_after || '';
    document.getElementById('edit-filter-joined-before').value = filters.joined_before || '';
}

// Campaign actions
function viewCampaign(id) {
    window.location.href = '/admin/email-campaigns/campaign/' + id;
}

function editEmailCampaign(button) {
    const campaign = JSON.parse(button.getAttribute('data-campaign') || '{}');
    document.getElementById('edit-campaign-id').value = campaign.id || '';
    document.getElementById('edit-campaign-title').value = campaign.title || '';
    document.getElementById('edit-campaign-subject').value = campaign.subject || campaign.title || '';
    document.getElementById('edit-campaign-message').value = campaign.message || '';
    document.getElementById('edit-target-audience').value = campaign.target_audience || 'all_members';
    setEditFilterValues(campaign.custom_filters || {});
    document.getElementById('edit-target-audience').dispatchEvent(new Event('change'));
    document.getElementById('edit-campaign-scheduled-at').value = campaign.scheduled_at || '';
    openModal('editCampaignModal');
}

document.getElementById('editCampaignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/admin/email-campaigns/edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
            campaign_id: formData.get('campaign_id'),
            title: formData.get('title'),
            subject: formData.get('subject'),
            message: formData.get('message'),
            target_audience: formData.get('target_audience') || 'all_members',
            filter_status: formData.get('filter_status') || '',
            filter_package: formData.get('filter_package') || '',
            filter_joined_after: formData.get('filter_joined_after') || '',
            filter_joined_before: formData.get('filter_joined_before') || '',
            scheduled_at: formData.get('scheduled_at') || null
        })
    })
    .then(response => response.json())
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
        fetch('/admin/email-campaigns/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification(data.message || 'Campaign sent successfully!', 'success');
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
        fetch('/admin/email-campaigns/cancel', {
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
        fetch('/admin/email-campaigns/delete', {
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

function retryFailed(id) {
    const proceed = () => {
        fetch('/admin/email-campaigns/retry-failed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Retry initiated successfully!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to retry: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Retry sending to all failed recipients?',
            proceed,
            null,
            { type: 'warning', title: 'Retry Failed', confirmText: 'Retry' }
        );
        return;
    }

    if (confirm('Retry sending to all failed recipients?')) {
        proceed();
    }
}

function recipientPreviewValues(recipient = null) {
    const amountDue = recipient && recipient.amount_due !== undefined && recipient.amount_due !== null
        ? Number(recipient.amount_due).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : '850.00';

    return {
        '{member_name}': recipient ? `${recipient.first_name || ''} ${recipient.last_name || ''}`.trim() || 'Member' : 'Wycliffe Omondi',
        '{first_name}': recipient?.first_name || 'Wycliffe',
        '{last_name}': recipient?.last_name || 'Omondi',
        '{member_number}': recipient?.member_number || 'SH-550407',
        '{package}': recipient?.package || 'Family',
        '{status}': recipient?.status || recipient?.member_status || 'Active',
        '{amount_due}': `KES ${amountDue}`
    };
}

function emailPreviewSample(message, recipient = null) {
    const sample = recipientPreviewValues(recipient);
    return Object.keys(sample).reduce((text, key) => text.split(key).join(sample[key]), message || '');
}

function previewRecipientLabel(recipient = null) {
    if (!recipient) return 'Resolving first recipient...';
    const name = `${recipient.first_name || ''} ${recipient.last_name || ''}`.trim() || 'Member';
    return [name, recipient.member_number, recipient.email].filter(Boolean).join(' | ');
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

function loadEmailFirstRecipient(form, subject, body) {
    fetch(`/admin/email-campaigns/preview-recipients?${campaignPreviewParams(form).toString()}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.ok ? response.json() : null)
    .then(data => {
        const recipient = data?.sample?.[0] || null;
        document.getElementById('emailPreviewRecipient').textContent = recipient
            ? previewRecipientLabel(recipient)
            : 'No matching recipient found';
        if (recipient) {
            document.getElementById('emailPreviewSubject').textContent = emailPreviewSample(subject, recipient);
            document.getElementById('emailPreviewBody').textContent = emailPreviewSample(body, recipient) || 'Email body preview';
        }
    })
    .catch(() => {
        document.getElementById('emailPreviewRecipient').textContent = 'Unable to resolve recipient preview';
    });
}

function loadEmailCampaignFirstRecipient(campaignId, subject, body) {
    if (!campaignId) return;
    fetch(`/admin/email-campaigns/campaign/${campaignId}/preview-recipient`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.ok ? response.json() : null)
    .then(data => {
        const recipient = data?.recipient || null;
        document.getElementById('emailPreviewRecipient').textContent = recipient
            ? previewRecipientLabel(recipient)
            : 'No stored recipient found';
        if (recipient) {
            document.getElementById('emailPreviewSubject').textContent = emailPreviewSample(subject, recipient);
            document.getElementById('emailPreviewBody').textContent = emailPreviewSample(body, recipient) || 'Email body preview';
        }
    })
    .catch(() => {
        document.getElementById('emailPreviewRecipient').textContent = 'Unable to resolve recipient preview';
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

let pendingEmailCampaignForm = null;
let pendingEmailCampaignAction = null;

function openEmailCampaignPreview(form) {
    const formData = new FormData(form);
    const body = formData.get('message') || '';
    const subject = formData.get('subject') || 'Email campaign preview';
    const targetSelect = form.querySelector('[name="target_audience"]');

    document.getElementById('emailPreviewSubject').textContent = emailPreviewSample(subject);
    document.getElementById('emailPreviewBody').textContent = emailPreviewSample(body) || 'Email body preview';
    document.getElementById('emailPreviewTitle').textContent = formData.get('title') || 'Untitled campaign';
    document.getElementById('emailPreviewAudience').textContent = friendlySelectText(targetSelect);
    document.getElementById('emailPreviewRecipient').textContent = 'Resolving first recipient...';
    document.getElementById('emailPreviewSchedule').textContent = describeCampaignSchedule(formData);
    document.getElementById('emailPreviewLength').textContent = `${body.length} characters`;
    document.getElementById('emailPreviewTime').textContent = new Date().toLocaleString([], { weekday: 'short', hour: 'numeric', minute: '2-digit' });

    pendingEmailCampaignForm = form;
    pendingEmailCampaignAction = null;
    openModal('emailCampaignPreviewModal');
    loadEmailFirstRecipient(form, subject, body);
}

function openEmailCampaignPreviewFromData(campaign, confirmAction) {
    const body = campaign.message || '';
    const subject = campaign.subject || campaign.title || 'Email campaign preview';

    document.getElementById('emailPreviewSubject').textContent = emailPreviewSample(subject);
    document.getElementById('emailPreviewBody').textContent = emailPreviewSample(body) || 'Email body preview';
    document.getElementById('emailPreviewTitle').textContent = campaign.title || 'Untitled campaign';
    document.getElementById('emailPreviewAudience').textContent = String(campaign.target_audience || 'all_members').replace(/_/g, ' ');
    document.getElementById('emailPreviewRecipient').textContent = 'Resolving first recipient...';
    document.getElementById('emailPreviewSchedule').textContent = campaign.scheduled_at ? new Date(campaign.scheduled_at).toLocaleString() : 'Send immediately';
    document.getElementById('emailPreviewLength').textContent = `${body.length} characters`;
    document.getElementById('emailPreviewTime').textContent = new Date().toLocaleString([], { weekday: 'short', hour: 'numeric', minute: '2-digit' });

    pendingEmailCampaignForm = null;
    pendingEmailCampaignAction = confirmAction;
    openModal('emailCampaignPreviewModal');
    loadEmailCampaignFirstRecipient(campaign.id, subject, body);
}

function previewExistingEmailCampaign(button) {
    const campaign = JSON.parse(button.getAttribute('data-campaign') || '{}');
    openEmailCampaignPreviewFromData(campaign, () => sendCampaign(campaign.id, true));
}

function submitEmailCampaignForm(form) {
    const formData = new FormData(form);
    fetch('/admin/email-campaigns/create', {
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

document.getElementById('confirmEmailCampaignSubmit')?.addEventListener('click', function() {
    closeModal('emailCampaignPreviewModal');
    if (pendingEmailCampaignForm) {
        submitEmailCampaignForm(pendingEmailCampaignForm);
        return;
    }
    if (typeof pendingEmailCampaignAction === 'function') {
        pendingEmailCampaignAction();
    }
});

// Form submission
document.getElementById('createCampaignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    openEmailCampaignPreview(this);
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
