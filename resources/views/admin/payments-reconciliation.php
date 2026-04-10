<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-header-left h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .page-header-left p {
        font-size: 13px;
        color: #9CA3AF;
        margin: 0;
    }

    .feed-badge {
        background: #10B981;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .feed-badge i {
        font-size: 14px;
        animation: pulse 2s infinite;
    }

    .header-action-btn {
        background: #7F3D9E;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .header-action-btn:hover {
        background: #6F3490;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Emergency Alert */
    .emergency-alert {
        background: linear-gradient(135deg, #7F3D9E 0%, #B91C1C 100%);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
    }

    .alert-content {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .alert-text {
        flex: 1;
    }

    .alert-badge {
        background: rgba(255, 255, 255, 0.9);
        color: #DC2626;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: inline-block;
    }

    .alert-title {
        color: white;
        font-size: 15px;
        font-weight: 700;
        margin: 0;
    }

    .alert-description {
        color: rgba(255, 255, 255, 0.9);
        font-size: 12px;
        margin: 0;
    }

    .alert-button {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .alert-button:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Stats Grid */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #E5E7EB;
        transition: all 0.2s;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .stat-label {
        font-size: 11px;
        color: #9CA3AF;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .stat-small {
        font-size: 13px;
        color: #9CA3AF;
    }

    .stat-change {
        font-size: 12px;
        color: #10B981;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.down {
        color: #EF4444;
    }

    .stat-indicator {
        color: #EF4444;
        font-size: 11px;
        font-weight: 700;
    }

    .stat-progress {
        width: 100%;
        height: 8px;
        background: #F3F4F6;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .stat-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #7F3D9E 0%, #7F3D9E 100%);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* Main Content Layout */
    .content-layout {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    /* Chart Card */
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .chart-subtitle {
        font-size: 12px;
        color: #9CA3AF;
        margin-top: 2px;
    }

    .chart-legend {
        display: flex;
        gap: 16px;
        font-size: 12px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .legend-dot.purple {
        background: #7F3D9E;
    }

    .legend-dot.gray {
        background: #D1D5DB;
    }

    .chart-container {
        height: 250px;
        position: relative;
    }

    /* Unmatched Feed */
    .feed-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .feed-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
    }

    .feed-item {
        padding: 16px;
        background: #F9FAFB;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .feed-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .feed-type {
        background: #FEF3C7;
        color: #F59E0B;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .feed-time {
        font-size: 11px;
        color: #9CA3AF;
    }

    .feed-amount {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .feed-description {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 12px;
    }

    .feed-meta {
        font-size: 11px;
        color: #9CA3AF;
        margin-bottom: 12px;
    }

    .feed-button {
        width: 100%;
        background: #7F3D9E;
        color: white;
        border: none;
        padding: 8px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .feed-button:hover {
        background: #7F3D9E;
    }

    /* Table */
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
        margin-bottom: 30px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .table-subtitle {
        font-size: 12px;
        color: #9CA3AF;
        margin-top: 2px;
    }

    .table-actions {
        display: flex;
        gap: 12px;
    }

    .table-btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .table-btn.primary {
        background: #7F3D9E;
        color: white;
        border-color: #7F3D9E;
    }

    .table-btn:hover {
        background: #F3F4F6;
    }

    .table-btn.primary:hover {
        background: #7F3D9E;
    }

    .reconciliation-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .reconciliation-table thead th {
        background: #7F3D9E;
        color: white;
        padding: 14px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .reconciliation-table thead th:first-child {
        border-radius: 8px 0 0 0;
    }

    .reconciliation-table thead th:last-child {
        border-radius: 0 8px 0 0;
    }

    .reconciliation-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: 13px;
        color: #1F2937;
    }

    .reconciliation-table tbody tr:hover {
        background: #F9FAFB;
    }

    .member-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .member-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        color: white;
    }

    .member-avatar.green {
        background: #10B981;
    }

    .member-avatar.orange {
        background: #F97316;
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 2px;
    }

    .member-number {
        font-size: 11px;
        color: #9CA3AF;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.reconciled {
        background: #D1FAE5;
        color: #10B981;
    }

    .status-badge.signaling {
        background: #FED7AA;
        color: #F97316;
    }

    .status-badge.required {
        background: #FEE2E2;
        color: #EF4444;
    }

    .action-btn {
        padding: 6px 16px;
        border-radius: 6px;
        border: none;
        background: #7F3D9E;
        color: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .action-btn:hover {
        background: #7F3D9E;
    }

    .action-btn.edit {
        background: transparent;
        color: #6B7280;
        border: 1px solid #E5E7EB;
    }

    .action-btn.edit:hover {
        background: #F3F4F6;
    }

    @media (max-width: 1200px) {
        .content-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Financial Reconciliation</h1>
        <p>Tracking Paymf b/d 60drt - SHENA Companion Welfare</p>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <div class="feed-badge">
            <i class="fas fa-circle"></i>
            LIVE M-PESA FEED ACTIVE
        </div>
        <a class="header-action-btn" href="/admin/reports?report_type=payments&filter=defaulters">
            <i class="fas fa-file-alt"></i> Defaulter Report
        </a>
    </div>
</div>

<?php
$recon_stats        = $recon_stats ?? [];
$unmatched_payments = $unmatched_payments ?? [];
$today_collections  = $today_collections ?? 0;
$defaulters_count   = $defaulters_count ?? 0;
$unmatched_count    = (int)($recon_stats['unmatched'] ?? count($unmatched_payments));
?>

<!-- Statistics Cards -->
<div class="stats-row">
    <!-- Today's Collections -->
    <div class="stat-card">
        <div class="stat-label">Today's Collections</div>
        <div class="stat-value"><?php echo number_format($today_collections, 0); ?> <span class="stat-small">KES</span></div>
        <div class="stat-change">
            <i class="fas fa-calendar-day"></i>
            AS OF TODAY
        </div>
    </div>

    <!-- Unmatched Records -->
    <div class="stat-card">
        <div class="stat-label">Unmatched Records</div>
        <div class="stat-value"><?php echo $unmatched_count; ?></div>
        <div class="stat-indicator">
            <?php if ($unmatched_count > 0): ?>
            <i class="fas fa-sync"></i> MANUAL SYNC REQUIRED
            <?php else: ?>
            <i class="fas fa-check-circle"></i> ALL MATCHED
            <?php endif; ?>
        </div>
    </div>

    <!-- Total Matched -->
    <div class="stat-card">
        <div class="stat-label">Total Matched</div>
        <div class="stat-value"><?php echo number_format((int)($recon_stats['matched'] ?? 0)); ?></div>
        <div class="stat-progress">
            <?php
            $total = max(1, (int)($recon_stats['total_payments'] ?? 1));
            $matchPct = round(((int)($recon_stats['matched'] ?? 0)) / $total * 100);
            ?>
            <div class="stat-progress-bar" style="width: <?php echo $matchPct; ?>%;"></div>
        </div>
    </div>

    <!-- Defaulters -->
    <div class="stat-card">
        <div class="stat-label">Defaulters (+60 Days)</div>
        <div class="stat-value"><?php echo $defaulters_count; ?></div>
        <div class="stat-indicator">
            <i class="fas fa-flag"></i> RECOVERY PENDING
        </div>
    </div>
</div>

<!-- Main Content Layout -->
<div class="content-layout">
    <!-- Revenue vs. Targets Chart -->
    <div>
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Revenue vs. Targets</div>
                    <div class="chart-subtitle">Monthly collection performance tracking</div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-dot purple"></div>
                        <span>Minimal</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot gray"></div>
                        <span>Target</span>
                    </div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Unmatched Feed -->
    <div>
        <div class="feed-card">
            <div class="feed-title">Unmatched Feed</div>

            <?php if (!empty($unmatched_payments)): ?>
            <?php foreach (array_slice($unmatched_payments, 0, 5) as $up): ?>
            <div class="feed-item">
                <div class="feed-header">
                    <span class="feed-type"><?php echo htmlspecialchars($up['mpesa_receipt_number'] ?? $up['transaction_reference'] ?? 'N/A'); ?></span>
                    <span class="feed-time"><?php echo date('h:i A', strtotime($up['created_at'] ?? 'now')); ?></span>
                </div>
                <div class="feed-amount">KES <?php echo number_format((float)($up['amount'] ?? 0), 2); ?></div>
                <div class="feed-description"><?php echo htmlspecialchars($up['notes'] ?? 'No account match'); ?></div>
                <div class="feed-meta"><?php echo htmlspecialchars($up['payment_method'] ?? 'M-Pesa'); ?></div>
                <a class="feed-button" href="/admin/payments/unmatched">Link</a>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div style="padding:24px; text-align:center; color:#6B7280; font-size:0.9rem;">
                <i class="fas fa-check-circle text-success me-1"></i> No unmatched payments.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Live Payment Reconciliation Table -->
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Live Payment Reconciliation</div>
            <div class="table-subtitle">Auto-match ongoing, queue for instant & manual verification</div>
        </div>
        <div class="table-actions">
            <a class="table-btn" href="/admin/payments-reconciliation?filter=1">
                <i class="fas fa-filter"></i> Filter
            </a>
            <a class="table-btn primary" href="/admin/payments-reconciliation?import=statement">
                <i class="fas fa-download"></i> Import Statement
            </a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="reconciliation-table">
            <thead>
                <tr>
                    <th>TRANSACTION CODE</th>
                    <th>SENDER / MEMBER</th>
                    <th>AMOUNT (KES)</th>
                    <th>STATUS</th>
                    <th>TIMESTAMP</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reconciled_payments)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6B7280;">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                        <p>No reconciled payments found</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($reconciled_payments as $payment): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong></td>
                        <td>
                            <div class="member-profile">
                                <div class="member-avatar <?php echo $payment['avatar_color'] ?? 'green'; ?>">
                                    <?php echo strtoupper(substr($payment['first_name'] ?? 'M', 0, 1) . substr($payment['last_name'] ?? 'M', 0, 1)); ?>
                                </div>
                                <div class="member-info">
                                    <div class="member-name"><?php echo htmlspecialchars($payment['member_name'] ?? 'N/A'); ?></div>
                                    <div class="member-number"><?php echo htmlspecialchars($payment['member_number'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><strong><?php echo number_format($payment['amount'], 2); ?></strong></td>
                        <td><span class="status-badge <?php echo $payment['status']; ?>"><?php echo strtoupper($payment['status']); ?></span></td>
                        <td><?php echo date('M d, h:i A', strtotime($payment['timestamp'])); ?></td>
                        <td>
                            <a class="action-btn edit" href="/admin/payments?search=<?php echo urlencode($payment['transaction_id'] ?? ''); ?>">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align: center; padding: 16px; color: #9CA3AF; font-size: 13px;">
        VIEWING 3,436 RECONCILED PAYMENTS
        <div style="margin-top: 12px; display: flex; gap: 8px; justify-content: center;">
            <a href="/admin/payments-reconciliation?page=prev" style="padding: 6px 12px; border: 1px solid #E5E7EB; background: white; border-radius: 6px; cursor: pointer; text-decoration: none; color: #1F2937;">
                Previous
            </a>
            <a href="/admin/payments-reconciliation?page=next" style="padding: 6px 12px; border: 1px solid #E5E7EB; background: white; border-radius: 6px; cursor: pointer; text-decoration: none; color: #1F2937;">
                Next Page
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels ?? ['Week 1', 'Week 2', 'Week 3', 'Week 4']); ?>,
            datasets: [{
                label: 'Revenue',
                data: <?php echo json_encode($chart_data ?? [0, 0, 0, 0]); ?>,
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointBackgroundColor: '#8B5CF6',
                pointBorderColor: 'white',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + (value / 1000) + 'K';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
