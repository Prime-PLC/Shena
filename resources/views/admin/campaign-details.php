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
        min-width: 1120px;
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
    .status-pill.failed { background: #FEE2E2; color: #991B1B; }
    .status-pill.pending { background: #FEF3C7; color: #92400E; }
    .status-pill.skipped { background: #E5E7EB; color: #374151; }
</style>

<?php
$stats = $stats ?? [];
$recipients = $recipients ?? [];
$channel = $channel ?? ($campaign['message_type'] ?? 'campaign');
$backUrl = $back_url ?? '/admin/communications';
$total = (int)($stats['total'] ?? $campaign['total_recipients'] ?? 0);
$sent = (int)($stats['sent'] ?? $campaign['sent_count'] ?? 0);
$failed = (int)($stats['failed'] ?? $campaign['failed_count'] ?? 0);
$pending = (int)($stats['pending'] ?? max(0, $total - $sent - $failed));
$skipped = (int)($stats['skipped'] ?? 0);
?>

<div class="campaign-report-header">
    <div>
        <h1><i class="fas fa-chart-line"></i> <?php echo htmlspecialchars(ucfirst($channel)); ?> Campaign Report</h1>
        <p><?php echo htmlspecialchars($campaign['title'] ?? 'Campaign'); ?> · <?php echo htmlspecialchars(ucfirst($campaign['status'] ?? 'draft')); ?></p>
    </div>
    <a href="<?php echo htmlspecialchars($backUrl); ?>" class="report-btn">
        <i class="fas fa-arrow-left"></i> Back to Campaigns
    </a>
</div>

<div class="report-grid">
    <div class="report-card">
        <div class="report-label">Recipients</div>
        <div class="report-value"><?php echo number_format($total); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Sent</div>
        <div class="report-value"><?php echo number_format($sent); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Failed</div>
        <div class="report-value"><?php echo number_format($failed); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Pending</div>
        <div class="report-value"><?php echo number_format($pending); ?></div>
    </div>
    <div class="report-card">
        <div class="report-label">Skipped</div>
        <div class="report-value"><?php echo number_format($skipped); ?></div>
    </div>
</div>

<div class="report-table-card">
    <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 16px;">Recipient Delivery Log</h2>
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
                    <th>Provider Response</th>
                    <th>Sent At</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recipients)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center; color:#6B7280; padding:32px;">No recipient records found.</td>
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
                            <td title="<?php echo htmlspecialchars($recipient['provider_response'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($providerResponse ?: 'N/A'); ?></td>
                            <td><?php echo !empty($recipient['sent_at']) ? date('M j, Y H:i', strtotime($recipient['sent_at'])) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($recipient['error_message'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
