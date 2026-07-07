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

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
        height: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        box-shadow: 0 4px 8px rgba(139, 92, 246, 0.1);
        transform: translateY(-2px);
    }

    .stat-card .icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .stat-card .icon-wrapper i {
        font-size: 28px;
    }

    .stat-card.primary .icon-wrapper {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .stat-card.success .icon-wrapper {
        background: linear-gradient(135deg, #7F3D9E 0%, #059669 100%);
        color: white;
    }

    .stat-card.warning .icon-wrapper {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
    }

    .stat-card.info .icon-wrapper {
        background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        color: white;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0.5rem 0;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .stat-meta {
        color: #9ca3af;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-meta i {
        font-size: 0.875rem;
    }

    .stat-meta.positive {
        color: #10B981;
    }

    .stat-meta.negative {
        color: #EF4444;
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
        font-size: 1.25rem;
        margin: 0 0 1.5rem 0;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #f3f4f6;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 150px;
    }

    .filter-group label {
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
        background: #8B5CF6;
        color: white;
    }

    .modern-btn.primary:hover {
        background: #7C3AED;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
    }

    .modern-btn.secondary {
        background: #6b7280;
        color: white;
    }

    .modern-btn.secondary:hover {
        background: #4b5563;
    }

    .modern-btn.outline {
        background: white;
        color: #8B5CF6;
        border: 1px solid #8B5CF6;
    }

    .modern-btn.outline:hover {
        background: #8B5CF6;
        color: white;
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

    .modern-table th.text-right {
        text-align: right;
    }

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .modern-table td.text-right {
        text-align: right;
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

    .status-badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-badge.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .status-badge.info {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
    }

    .status-badge.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-state i {
        font-size: 3rem;
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
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .performance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .performance-card {
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        padding: 16px;
        background: #FFFFFF;
    }

    .performance-card span {
        display: block;
        color: #6B7280;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .performance-card strong {
        display: block;
        color: #111827;
        font-size: 24px;
        margin-top: 6px;
    }

    @media print {
        .page-header {
            background: #8B5CF6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .modern-btn {
            display: none;
        }
    }
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1><i class="fas fa-chart-line"></i> Financial Dashboard</h1>
            <p>Comprehensive financial overview and analytics</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="modern-btn outline" onclick="exportReport()">
                <i class="fas fa-download"></i> Export
            </button>
            <button class="modern-btn outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="/admin/dashboard" class="modern-btn secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="filter-card">
    <form method="GET" action="/admin/financial-dashboard" class="filter-form">
        <div class="filter-group">
            <label for="from_date">From Date</label>
            <input type="date" class="form-control" id="from_date" name="from_date" 
                   value="<?php echo htmlspecialchars($data['from_date'] ?? date('Y-m-01')); ?>">
        </div>
        <div class="filter-group">
            <label for="to_date">To Date</label>
            <input type="date" class="form-control" id="to_date" name="to_date" 
                   value="<?php echo htmlspecialchars($data['to_date'] ?? date('Y-m-d')); ?>">
        </div>
        <button type="submit" class="modern-btn primary">
            <i class="fas fa-filter"></i> Apply Filter
        </button>
        <button type="button" class="modern-btn secondary" onclick="resetFilters()">
            <i class="fas fa-redo"></i> This Month
        </button>
    </form>
</div>

<?php
$paymentPerformance = $data['payment_performance'] ?? [];
$paymentSummary = $paymentPerformance['summary'] ?? [];
$paymentGroups = $paymentSummary['groups'] ?? [];
$paidCount = (int)($paymentGroups['paid_current']['count'] ?? 0);
$notPaidCount = (int)($paymentGroups['unpaid_current']['count'] ?? 0);
$partlyPaidCount = (int)($paymentGroups['partially_paid']['count'] ?? 0);
$outstandingCount = (int)($paymentGroups['in_arrears']['count'] ?? 0);
$defaultedCount = (int)($paymentGroups['defaulted']['count'] ?? 0);
?>

<div class="modern-card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h2 style="margin-bottom:4px;">Payment Performance</h2>
            <p style="margin:0 0 16px; color:#6B7280; font-size:13px;">Paid vs Not Paid, outstanding balances, and recent payment activity from the same live breakdown used in Payment Management.</p>
        </div>
        <a class="modern-btn outline" href="/admin/payments/breakdown">Open Payment Breakdown</a>
    </div>
    <div class="performance-grid">
        <div class="performance-card"><span>Paid vs Not Paid</span><strong><?php echo number_format($paidCount); ?> / <?php echo number_format($notPaidCount); ?></strong></div>
        <div class="performance-card"><span>Partially Paid</span><strong><?php echo number_format($partlyPaidCount); ?></strong></div>
        <div class="performance-card"><span>In Arrears</span><strong><?php echo number_format($outstandingCount); ?></strong></div>
        <div class="performance-card"><span>Defaulted</span><strong><?php echo number_format($defaultedCount); ?></strong></div>
        <div class="performance-card"><span>Expected Collections</span><strong>KES <?php echo number_format((float)($paymentSummary['expected_amount'] ?? 0), 0); ?></strong></div>
        <div class="performance-card"><span>Collected Amount</span><strong>KES <?php echo number_format((float)($paymentSummary['paid_amount'] ?? 0), 0); ?></strong></div>
        <div class="performance-card"><span>Balance</span><strong>KES <?php echo number_format((float)($paymentSummary['balance_due'] ?? 0), 0); ?></strong></div>
        <div class="performance-card"><span>Collection Rate</span><strong><?php echo number_format((float)($paymentSummary['collection_rate'] ?? 0), 1); ?>%</strong></div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h3 style="font-size:16px; font-weight:800;">Recent Payments</h3>
            <table class="modern-table">
                <tbody>
                    <?php foreach (array_slice($paymentPerformance['recent_payments'] ?? [], 0, 5) as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?: ($payment['member_number'] ?? 'Member')); ?></td>
                            <td class="text-right">KES <?php echo number_format((float)($payment['amount'] ?? 0), 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h3 style="font-size:16px; font-weight:800;">Recent Reconciliations</h3>
            <table class="modern-table">
                <tbody>
                    <?php foreach (array_slice($paymentPerformance['recent_reconciliations'] ?? [], 0, 5) as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['mpesa_receipt_number'] ?? $payment['transaction_id'] ?? 'Payment'); ?></td>
                            <td class="text-right"><?php echo !empty($payment['reconciled_at']) ? date('M j, Y', strtotime($payment['reconciled_at'])) : 'Matched'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="icon-wrapper">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <p class="stat-label">Total Revenue</p>
            <p class="stat-value">KES <?php echo number_format($data['kpis']['total_revenue'] ?? 0, 0); ?></p>
            <p class="stat-meta <?php echo ($data['kpis']['revenue_change'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                <i class="fas fa-<?php echo ($data['kpis']['revenue_change'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo abs($data['kpis']['revenue_change'] ?? 0); ?>% vs last period
            </p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card success">
            <div class="icon-wrapper">
                <i class="fas fa-credit-card"></i>
            </div>
            <p class="stat-label">Payments Received</p>
            <p class="stat-value">KES <?php echo number_format($data['kpis']['total_payments'] ?? 0, 0); ?></p>
            <p class="stat-meta">
                <i class="fas fa-users"></i>
                <?php echo $data['kpis']['paying_members'] ?? 0; ?> paying members
            </p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="icon-wrapper">
                <i class="fas fa-percentage"></i>
            </div>
            <p class="stat-label">Commissions Paid</p>
            <p class="stat-value">KES <?php echo number_format($data['kpis']['total_commissions'] ?? 0, 0); ?></p>
            <p class="stat-meta">
                <i class="fas fa-user-tie"></i>
                <?php echo $data['kpis']['earning_agents'] ?? 0; ?> agents paid
            </p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card info">
            <div class="icon-wrapper">
                <i class="fas fa-chart-line"></i>
            </div>
            <p class="stat-label">Net Revenue</p>
            <p class="stat-value">KES <?php echo number_format($data['kpis']['net_revenue'] ?? 0, 0); ?></p>
            <p class="stat-meta">
                <i class="fas fa-percentage"></i>
                <?php 
                $totalRevenue = $data['kpis']['total_revenue'] ?? 0;
                $netRevenue = $data['kpis']['net_revenue'] ?? 0;
                $margin = $totalRevenue > 0 ? round(($netRevenue / $totalRevenue) * 100, 1) : 0;
                echo $margin; 
                ?>% margin
            </p>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col-md-8">
        <div class="modern-card">
            <h2><i class="fas fa-chart-area"></i> Monthly Revenue Trend</h2>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="modern-card">
            <h2><i class="fas fa-chart-pie"></i> Transaction Breakdown</h2>
            <div class="chart-container">
                <canvas id="transactionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col-md-6">
        <div class="modern-card">
            <h2><i class="fas fa-trophy"></i> Top Performing Agents</h2>
            <?php if (!empty($data['top_agents'])): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th class="text-right">Members</th>
                                <th class="text-right">Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['top_agents'] as $agent): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($agent['agent_name'] ?? 'N/A'); ?></strong></td>
                                    <td class="text-right"><?php echo number_format($agent['total_members'] ?? 0); ?></td>
                                    <td class="text-right"><strong>KES <?php echo number_format($agent['total_commissions'] ?? 0, 0); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-tie"></i>
                    <h3>No Agent Data</h3>
                    <p>No agent commission data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="modern-card">
            <h2><i class="fas fa-exchange-alt"></i> Recent Transactions</h2>
            <?php if (!empty($data['recent_transactions'])): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Member</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['recent_transactions'] as $txn): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($txn['transaction_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php 
                                            echo $txn['transaction_type'] === 'payment' ? 'success' : 
                                                 ($txn['transaction_type'] === 'commission' ? 'warning' : 
                                                 ($txn['transaction_type'] === 'upgrade' ? 'info' : 'danger')); 
                                        ?>">
                                            <?php echo ucfirst($txn['transaction_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($txn['member_name'] ?? 'N/A'); ?></td>
                                    <td class="text-right"><strong>KES <?php echo number_format($txn['amount'], 0); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h3>No Transactions</h3>
                    <p>No transactions found for the selected period</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Monthly Summary Table -->
<div class="modern-card">
    <h2><i class="fas fa-calendar-alt"></i> Monthly Financial Summary</h2>
    <?php if (!empty($data['monthly_summary'])): ?>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class="text-right">Payments</th>
                        <th class="text-right">Commissions</th>
                        <th class="text-right">Upgrades</th>
                        <th class="text-right">Refunds</th>
                        <th class="text-right">Net Revenue</th>
                        <th class="text-right">Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['monthly_summary'] as $month): ?>
                        <tr>
                            <td><strong><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></strong></td>
                            <td class="text-right" style="color: #10B981;">
                                KES <?php echo number_format($month['total_payments'], 0); ?>
                            </td>
                            <td class="text-right" style="color: #F59E0B;">
                                KES <?php echo number_format($month['total_commissions'], 0); ?>
                            </td>
                            <td class="text-right" style="color: #3B82F6;">
                                KES <?php echo number_format($month['total_upgrades'], 0); ?>
                            </td>
                            <td class="text-right" style="color: #EF4444;">
                                KES <?php echo number_format($month['total_refunds'], 0); ?>
                            </td>
                            <td class="text-right">
                                <strong style="color: #1f2937;">KES <?php 
                                    $net = $month['total_payments'] + $month['total_upgrades'] - 
                                           $month['total_commissions'] - $month['total_refunds'];
                                    echo number_format($net, 0); 
                                ?></strong>
                            </td>
                            <td class="text-right"><?php echo number_format($month['paying_members']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-chart-bar"></i>
            <h3>No Financial Data</h3>
            <p>No financial data available for the selected period</p>
        </div>
    <?php endif; ?>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Prepare data
const monthlyData = <?php echo json_encode($data['monthly_summary'] ?? []); ?>;
const kpis = <?php echo json_encode($data['kpis'] ?? []); ?>;

// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(m => {
                const date = new Date(m.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Revenue',
                data: monthlyData.map(m => parseFloat(m.total_payments)),
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Commissions',
                data: monthlyData.map(m => parseFloat(m.total_commissions)),
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: '#f3f4f6'
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

// Transaction Breakdown Chart
const transactionCtx = document.getElementById('transactionChart');
if (transactionCtx) {
    new Chart(transactionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Payments', 'Commissions', 'Upgrades', 'Refunds'],
            datasets: [{
                data: [
                    parseFloat(kpis.total_payments || 0),
                    parseFloat(kpis.total_commissions || 0),
                    parseFloat(kpis.total_upgrades || 0),
                    parseFloat(kpis.total_refunds || 0)
                ],
                backgroundColor: [
                    '#10B981',
                    '#F59E0B',
                    '#3B82F6',
                    '#EF4444'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });
}

function resetFilters() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const fromDate = firstDay.toISOString().split('T')[0];
    const toDate = today.toISOString().split('T')[0];
    
    window.location.href = `/admin/financial-dashboard?from_date=${fromDate}&to_date=${toDate}`;
}

function exportReport() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '/admin/financial-dashboard/export?' + params.toString();
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
