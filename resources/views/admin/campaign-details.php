<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .campaign-report-header {
        background: #7F3D9E;
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .campaign-report-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        margin: 0 0 6px;
    }

    .campaign-report-header p {
        margin: 0;
        opacity: 0.9;
    }

    .report-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #374151;
        font-weight: 700;
        text-decoration: none;
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .report-card {
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        padding: 18px;
    }

    .report-label {
        color: #6B7280;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .report-value {
        color: #111827;
        font-size: 28px;
        font-weight: 800;
    }

    .report-table-card {
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 20px;
        overflow: hidden;
    }

    .report-table-wrap {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        min-width: 1380px;
        border-collapse: collapse;
    }

    .report-table th,
    .report-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #F3F4F6;
        text-align: left;
        font-size: 13px;
    }

    .report-table th {
        background: #F9FAFB;
        color: #374151;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pill {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        background: #F3F4F6;
        color: #374151;
    }

    .status-pill.sent { background: #D1FAE5; color: #065F46; }
    .status-pill.delivered { background: #D1FAE5; color: #065F46; }
    .status-pill.submitted { background: #DBEAFE; color: #1D4ED8; }
    .status-pill.failed { background: #FEE2E2; color: #991B1B; }
    .status-pill.undelivered,
    .status-pill.expired,
    .status-pill.rejected,
    .status-pill.unknown { background: #FEE2E2; color: #991B1B; }
    .status-pill.pending { background: #FEF3C7; color: #92400E; }
    .status-pill.skipped { background: #E5E7EB; color: #374151; }

    .workspace-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .recipient-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) minmax(160px, 220px) auto auto;
        gap: 10px;
        align-items: center;
        margin-bottom: 16px;
    }

    .recipient-filter-form input,
    .recipient-filter-form select {
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
    }

    .campaignRecipientPagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 14px;
        color: #6B7280;
        font-size: 13px;
    }

    @media (max-width: 760px) {
        .recipient-filter-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
$stats = $stats ?? [];
$recipients = $recipients ?? [];
$channel = $channel ?? ($campaign['message_type'] ?? 'campaign');
$backUrl = $back_url ?? '/admin/communications';
$total = (int)($stats['total'] ?? $campaign['total_recipients'] ?? 0);
$sent = (int)($stats['sent'] ?? $campaign['sent_count'] ?? 0);
$submitted = (int)($stats['submitted'] ?? $campaign['submitted_count'] ?? 0);
$delivered = (int)($stats['delivered'] ?? $campaign['delivered_count'] ?? $sent);
$undelivered = (int)($stats['undelivered'] ?? $campaign['undelivered_count'] ?? 0);
$failed = (int)($stats['failed'] ?? $campaign['failed_count'] ?? 0);
$pending = (int)($stats['pending'] ?? max(0, $total - $sent - $failed));
$skipped = (int)($stats['skipped'] ?? 0);
$recipientFilters = $recipient_filters ?? ['status' => 'all', 'search' => ''];
$recipientPagination = $recipient_pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => count($recipients), 'per_page' => 25];
$recipientPageUrl = function (int $page) use ($campaign, $recipientFilters) {
    return '/admin/communications/campaign/' . (int)($campaign['id'] ?? 0) . '?' . http_build_query([
        'recipient_page' => max(1, $page),
        'recipient_status' => $recipientFilters['status'] ?? 'all',
        'recipient_search' => $recipientFilters['search'] ?? '',
    ]);
};
$customFilters = [];
if (!empty($campaign['custom_filters'])) {
    $decodedFilters = json_decode((string)$campaign['custom_filters'], true);
    $customFilters = is_array($decodedFilters) ? $decodedFilters : [];
}
?>

<div class="campaign-report-header">
    <div>
        <h1><i class="fas fa-chart-line"></i> <?php echo htmlspecialchars(ucfirst($channel)); ?> Campaign Report</h1>
        <p><?php echo htmlspecialchars($campaign['title'] ?? 'Campaign'); ?> · <?php echo htmlspecialchars(ucfirst($campaign['status'] ?? 'draft')); ?></p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <?php if (($channel ?? '') === 'sms'): ?>
            <button type="button" class="report-btn" onclick="reuseCampaign(<?php echo (int)($campaign['id'] ?? 0); ?>)">
                <i class="fas fa-copy"></i> Reuse Campaign
            </button>
            <button type="button" class="report-btn" onclick="processScheduledCampaigns()">
                <i class="fas fa-clock"></i> Process Due Campaigns
            </button>
            <a href="/admin/communications/campaign/<?php echo (int)($campaign['id'] ?? 0); ?>/delivery-report" class="report-btn">
                <i class="fas fa-file-download"></i> Download Delivery Report
            </a>
            <button type="button" class="report-btn" onclick="syncDeliveryStatuses()">
                <i class="fas fa-sync"></i> Sync Delivery
            </button>
            <?php if ($pending > 0 || $failed > 0): ?>
                <button type="button" class="report-btn" onclick="resendPendingFailed(<?php echo (int)($campaign['id'] ?? 0); ?>)">
                    <i class="fas fa-redo"></i> Resend Pending/Failed
                </button>
            <?php endif; ?>
        <?php endif; ?>
        <a href="<?php echo htmlspecialchars($backUrl); ?>" class="report-btn">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>
</div>

<div class="report-table-card" style="margin-bottom:24px;">
    <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 12px;">Campaign Details</h2>
    <div class="report-grid" style="margin-bottom:0;">
        <div class="report-card">
            <div class="report-label">Audience</div>
            <div style="font-weight:800;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $campaign['target_audience'] ?? 'All Members'))); ?></div>
        </div>
        <div class="report-card">
            <div class="report-label">Schedule</div>
            <div style="font-weight:800;"><?php echo !empty($campaign['scheduled_at']) ? date('M j, Y H:i', strtotime($campaign['scheduled_at'])) : 'Not scheduled'; ?></div>
        </div>
        <div class="report-card">
            <div class="report-label">Filters</div>
            <div style="font-weight:800;"><?php echo empty($customFilters) ? 'No extra filters' : htmlspecialchars(implode(', ', array_map('strval', array_filter($customFilters)))); ?></div>
        </div>
    </div>
    <div style="margin-top:16px;">
        <div class="report-label">Message Preview</div>
        <div style="background:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; padding:14px; white-space:pre-wrap;"><?php echo htmlspecialchars($campaign['message'] ?? ''); ?></div>
    </div>
    <div class="workspace-actions">
        <?php if (in_array(($campaign['status'] ?? ''), ['draft', 'scheduled', 'paused'], true)): ?>
            <a class="report-btn" href="/admin/sms-campaigns?edit_campaign=<?php echo (int)($campaign['id'] ?? 0); ?>"><i class="fas fa-edit"></i> Edit Campaign</a>
            <button type="button" class="report-btn" onclick="sendCampaignNow(<?php echo (int)($campaign['id'] ?? 0); ?>)"><i class="fas fa-paper-plane"></i> Send Now</button>
        <?php endif; ?>
        <?php if (in_array(($campaign['status'] ?? ''), ['draft', 'scheduled', 'paused'], true)): ?>
            <button type="button" class="report-btn" onclick="cancelCampaign(<?php echo (int)($campaign['id'] ?? 0); ?>)"><i class="fas fa-ban"></i> Cancel</button>
        <?php endif; ?>
        <button type="button" class="report-btn" onclick="deleteCampaign(<?php echo (int)($campaign['id'] ?? 0); ?>)"><i class="fas fa-trash"></i> Delete</button>
    </div>
</div>

<div class="report-grid">
    <div class="report-card">
        <div class="report-label">Recipients</div>
        <div class="report-value"><?php echo number_format($total); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Submitted</div>
        <div class="report-value"><?php echo number_format($submitted); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Delivered</div>
        <div class="report-value"><?php echo number_format($delivered); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Failed / Undelivered</div>
        <div class="report-value"><?php echo number_format($failed + $undelivered); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Skipped</div>
        <div class="report-value"><?php echo number_format($skipped); ?></div>
    </div>
</div>

<div class="report-table-card">
    <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 16px;">Recipient Delivery Log</h2>
    <form class="recipient-filter-form" method="GET" action="/admin/communications/campaign/<?php echo (int)($campaign['id'] ?? 0); ?>">
        <input id="recipientSearchInput" type="search" name="recipient_search" value="<?php echo htmlspecialchars($recipientFilters['search'] ?? ''); ?>" placeholder="Search recipient, phone, member or agent number">
        <select id="recipientStatusFilter" name="recipient_status">
            <?php foreach (['all' => 'All recipients', 'pending' => 'Pending', 'submitted' => 'Submitted', 'delivered' => 'Delivered', 'failed' => 'Failed', 'undelivered' => 'Not Delivered', 'skipped' => 'Skipped / Invalid'] as $value => $label): ?>
                <option value="<?php echo $value; ?>" <?php echo (($recipientFilters['status'] ?? 'all') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="report-btn">Apply</button>
        <a class="report-btn" href="/admin/communications/campaign/<?php echo (int)($campaign['id'] ?? 0); ?>">Reset</a>
    </form>
    <div class="report-table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Member Number</th>
                    <th>Destination</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Delivery Method</th>
                    <th>Provider Ref</th>
                    <th>Provider Status</th>
                    <th>Provider Cause</th>
                    <th>Submitted At</th>
                    <th>Delivered At</th>
                    <th>DLR Checked</th>
                    <th>Error</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recipients)): ?>
                    <tr>
                        <td colspan="14" style="text-align:center; color:#6B7280; padding:32px;">No recipient records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recipients as $recipient): ?>
                        <?php
                        $providerResponse = $recipient['provider_response'] ?? '';
                        if (strlen((string)$providerResponse) > 120) {
                            $providerResponse = substr((string)$providerResponse, 0, 117) . '...';
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars(trim(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? '')) ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($recipient['member_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($recipient['recipient_value'] ?? $recipient['phone'] ?? $recipient['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($recipient['recipient_type'] ?? $channel); ?></td>
                            <td><span class="status-pill <?php echo htmlspecialchars($recipient['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars($recipient['status'] ?? 'pending'); ?></span></td>
                            <td><?php echo htmlspecialchars($recipient['delivery_method'] ?? $recipient['recipient_type'] ?? $channel); ?></td>
                            <td><?php echo htmlspecialchars($recipient['provider_message_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($recipient['provider_status'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($recipient['provider_cause'] ?? 'N/A'); ?></td>
                            <td><?php echo !empty($recipient['submitted_at']) ? date('M j, Y H:i', strtotime($recipient['submitted_at'])) : (!empty($recipient['sent_at']) ? date('M j, Y H:i', strtotime($recipient['sent_at'])) : 'N/A'); ?></td>
                            <td><?php echo !empty($recipient['delivered_at']) ? date('M j, Y H:i', strtotime($recipient['delivered_at'])) : 'N/A'; ?></td>
                            <td><?php echo !empty($recipient['dlr_checked_at']) ? date('M j, Y H:i', strtotime($recipient['dlr_checked_at'])) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($recipient['error_message'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($recipient['member_id'])): ?>
                                    <a class="report-btn" href="/admin/members/view/<?php echo (int)$recipient['member_id']; ?>">View recipient</a>
                                <?php else: ?>
                                    <span class="report-btn" style="opacity:.65;">Agent recipient</span>
                                <?php endif; ?>
                                <?php if (in_array(($recipient['status'] ?? ''), ['failed', 'undelivered', 'expired', 'rejected', 'unknown'], true)): ?>
                                    <button type="button" class="report-btn" onclick="resendCampaignRecipient(<?php echo (int)($campaign['id'] ?? 0); ?>, <?php echo (int)($recipient['id'] ?? 0); ?>)">Resend SMS</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="campaignRecipientPagination" id="campaignRecipientPagination">
        <span>Viewing <?php echo count($recipients); ?> of <?php echo (int)($recipientPagination['total_items'] ?? 0); ?> recipients</span>
        <div style="display:flex; gap:8px; align-items:center;">
            <?php $currentRecipientPage = (int)($recipientPagination['current_page'] ?? 1); ?>
            <?php $totalRecipientPages = (int)($recipientPagination['total_pages'] ?? 1); ?>
            <?php if ($currentRecipientPage > 1): ?>
                <a class="report-btn" href="<?php echo htmlspecialchars($recipientPageUrl($currentRecipientPage - 1)); ?>">Previous</a>
            <?php else: ?>
                <span class="report-btn" style="opacity:.55;">Previous</span>
            <?php endif; ?>
            <span>Page <?php echo (int)($recipientPagination['current_page'] ?? 1); ?> of <?php echo (int)($recipientPagination['total_pages'] ?? 1); ?></span>
            <?php if ($currentRecipientPage < $totalRecipientPages): ?>
                <a class="report-btn" href="<?php echo htmlspecialchars($recipientPageUrl($currentRecipientPage + 1)); ?>">Next</a>
            <?php else: ?>
                <span class="report-btn" style="opacity:.55;">Next</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (($channel ?? '') === 'sms'): ?>
<script>
function resendPendingFailed(id) {
    const proceed = () => {
        fetch('/admin/communications/campaign/' + id + '/resend-pending-failed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification(data.message || 'Resend started', 'success');
                setTimeout(() => window.location.reload(), 900);
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

function syncDeliveryStatuses() {
    fetch('/admin/communications/sync-delivery-statuses', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ limit: 100 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification(`Delivery sync checked ${data.checked_count || 0} SMS record(s); ${data.updated_count || 0} updated.`, 'success');
            setTimeout(() => window.location.reload(), 900);
        } else {
            ShenaApp.showNotification(data.message || 'Failed to sync delivery statuses', 'error');
        }
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function reuseCampaign(id) {
    fetch('/admin/communications/campaign/' + id + '/reuse', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
            return;
        }
        ShenaApp.showNotification(data.message || 'Failed to reuse campaign', data.success ? 'success' : 'error');
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function resendCampaignRecipient(campaignId, recipientId) {
    fetch('/admin/communications/campaign/' + campaignId + '/recipient/' + recipientId + '/resend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        ShenaApp.showNotification(data.message || (data.success ? 'SMS resent' : 'Failed to resend SMS'), data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => window.location.reload(), 900);
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function sendCampaignNow(id) {
    fetch('/admin/communications/send-campaign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ campaign_id: id })
    })
    .then(response => response.json())
    .then(data => {
        ShenaApp.showNotification(data.message || (data.success ? 'Campaign submitted' : 'Failed to send campaign'), data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => window.location.reload(), 900);
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function processScheduledCampaigns() {
    fetch('/admin/communications/process-scheduled-campaigns', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => ShenaApp.showNotification(data.message || 'Due campaigns processed', data.success ? 'success' : 'error'))
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function cancelCampaign(id) {
    fetch('/admin/communications/cancel-campaign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ campaign_id: id })
    })
    .then(response => response.json())
    .then(data => {
        ShenaApp.showNotification(data.message || (data.success ? 'Campaign cancelled' : 'Failed to cancel campaign'), data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => window.location.reload(), 900);
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}

function deleteCampaign(id) {
    fetch('/admin/communications/delete-campaign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ campaign_id: id, confirm_delete: true })
    })
    .then(response => response.json())
    .then(data => {
        ShenaApp.showNotification(data.message || (data.success ? 'Campaign deleted' : 'Failed to delete campaign'), data.success ? 'success' : 'error');
        if (data.success) window.location.href = '/admin/sms-campaigns';
    })
    .catch(() => ShenaApp.showNotification('Network error occurred', 'error'));
}
</script>
<?php endif; ?>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
