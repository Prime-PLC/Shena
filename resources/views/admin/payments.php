<?php 
$totalPayments = $totalPayments ?? 0;
$monthlyPayments = $monthlyPayments ?? 0;
$pendingReconciliation = $pendingReconciliation ?? 0;
$successRate = $successRate ?? 0;
?>

<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>
<?php include_once __DIR__ . '/modals/member-report-modal.php'; ?>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .page-subtitle {
        font-size: 14px;
        color: #9CA3AF;
    }

    .quick-actions {
        display: flex;
        gap: 12px;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #7F3D9E;
        border: 1px solid #E5E7EB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
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

    .stat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        color: #10B981;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
        color: #3B82F6;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        color: #F59E0B;
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
        color: #7F3D9E;
    }

    .stat-label {
        font-size: 13px;
        color: #9CA3AF;
        font-weight: 500;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .stat-subtext {
        font-size: 12px;
        color: #6B7280;
    }

    /* Tabs */
    .tabs-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .tabs-header {
        display: flex;
        border-bottom: 1px solid #E5E7EB;
        padding: 0;
        background: #F9FAFB;
        overflow-x: auto;
    }

    .tab-btn {
        padding: 16px 24px;
        border: none;
        background: transparent;
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: #7F3D9E;
        background: rgba(127, 61, 158, 0.05);
    }

    .tab-btn.active {
        color: #7F3D9E;
        background: white;
        border-bottom-color: #7F3D9E;
    }

    .tab-content {
        display: none;
        padding: 24px;
    }

    .tab-content.active {
        display: block;
    }

    /* Table Styles */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
        max-width: 400px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
    }

    .filter-group {
        display: flex;
        gap: 12px;
    }

    .filter-btn {
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-btn:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table thead {
        background: #F9FAFB;
    }

    .custom-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #E5E7EB;
    }

    .custom-table td {
        padding: 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: 14px;
        color: #1F2937;
    }

    .custom-table tbody tr {
        transition: background 0.2s;
    }

    .custom-table tbody tr:hover {
        background: #F9FAFB;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.success {
        background: #D1FAE5;
        color: #059669;
    }

    .status-badge.pending {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .status-badge.failed {
        background: #FEE2E2;
        color: #DC2626;
    }

    /* Reconciliation Section */
    .reconciliation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .reconciliation-card {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #E5E7EB;
    }

    .reconciliation-card h4 {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 12px;
    }

    .reconciliation-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #E5E7EB;
    }

    .reconciliation-item:last-child {
        border-bottom: none;
    }

    .reconciliation-label {
        font-size: 13px;
        color: #6B7280;
    }

    .reconciliation-value {
        font-size: 13px;
        font-weight: 600;
        color: #1F2937;
    }

    /* Financial Dashboard Cards */
    .financial-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .chart-header {
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .chart-subtitle {
        font-size: 13px;
        color: #9CA3AF;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .financial-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .reconciliation-grid {
            grid-template-columns: 1fr;
        }

        .filter-group {
            flex-wrap: wrap;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Payment Management</h1>
        <p class="page-subtitle">Track, reconcile, and manage all payment operations</p>
    </div>
    <div class="quick-actions">
        <button class="btn-action btn-primary" onclick="window.location.href='/admin/payments-reconciliation'">
            <i class="fas fa-sync-alt"></i>
            Reconcile Payments
        </button>
        <button class="btn-action btn-secondary">
            <i class="fas fa-download"></i>
            Export Report
        </button>
    </div>
</div>

<!-- Payment Analytics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-coins"></i>
            </div>
            <span class="stat-label">Total Payments</span>
        </div>
        <div class="stat-value">KSh <?php echo number_format($totalPayments); ?></div>
        <div class="stat-subtext">All-time collection</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <span class="stat-label">This Month</span>
        </div>
        <div class="stat-value">KSh <?php echo number_format($monthlyPayments); ?></div>
        <div class="stat-subtext">Current month collection</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon orange">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <span class="stat-label">Pending Reconciliation</span>
        </div>
        <div class="stat-value"><?php echo $pendingReconciliation; ?></div>
        <div class="stat-subtext">Transactions to review</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon purple">
                <i class="fas fa-check-circle"></i>
            </div>
            <span class="stat-label">Success Rate</span>
        </div>
        <div class="stat-value"><?php echo $successRate; ?>%</div>
        <div class="stat-subtext">Transaction success rate</div>
    </div>
</div>

<!-- Payment Tabs -->
<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-btn active" onclick="showTab('all')" id="tab-all">
            <i class="fas fa-list"></i>
            All Payments
        </button>
        <button class="tab-btn" onclick="showTab('mpesa')" id="tab-mpesa">
            <i class="fas fa-mobile-alt"></i>
            M-Pesa Payments
        </button>
        <button class="tab-btn" onclick="showTab('reconciliation')" id="tab-reconciliation">
            <i class="fas fa-sync-alt"></i>
            Reconciliation
        </button> 
        <button class="tab-btn" onclick="showTab('reports')" id="tab-reports">
            <i class="fas fa-file-alt"></i>
            Reports
        </button>
    </div>

    <!-- All Payments Tab -->
    <div class="tab-content active" id="content-all">
        <div class="table-header">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search payments by member, transaction ID...">
            </div>
            <div class="filter-group">
                <button class="filter-btn">
                    <i class="fas fa-calendar"></i>
                    Date Range
                </button>
                <button class="filter-btn">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                <button class="filter-btn">
                    <i class="fas fa-download"></i>
                    Export
                </button>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Member Name</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($p['transaction_id'] ?? $p['mpesa_receipt_number'] ?? '—'); ?></strong></td>
                    <td><?php echo htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?></td>
                    <td><strong>KSh <?php echo number_format($p['amount'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $p['payment_method'] ?? '—'))); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($p['created_at'])); ?></td>
                    <td><span class="status-badge <?php echo $p['status'] === 'completed' ? 'success' : ($p['status'] === 'failed' ? 'danger' : 'pending'); ?>"><?php echo ucfirst($p['status']); ?></span></td>
                    <td>
                        <a href="/admin/payment/<?php echo $p['id']; ?>" class="filter-btn" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="7" style="text-align:center; padding: 24px; color:#6B7280;">No payments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- M-Pesa Payments Tab -->
    <div class="tab-content" id="content-mpesa">
        <div class="table-header">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search M-Pesa transactions...">
            </div>
            <div class="filter-group">
                <button class="filter-btn">
                    <i class="fas fa-calendar"></i>
                    Date Range
                </button>
                <button class="filter-btn">
                    <i class="fas fa-download"></i>
                    Export
                </button>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>M-Pesa Code</th>
                    <th>Member</th>
                    <th>Phone Number</th>
                    <th>Amount</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $mpesaPayments = array_filter($payments ?? [], fn($p) => ($p['payment_method'] ?? '') === 'mpesa' && !empty($p['mpesa_receipt_number'])); ?>
                <?php if (!empty($mpesaPayments)): ?>
                <?php foreach ($mpesaPayments as $p): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($p['mpesa_receipt_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($p['sender_phone'] ?? '—'); ?></td>
                    <td><strong>KSh <?php echo number_format($p['amount'], 2); ?></strong></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($p['created_at'])); ?></td>
                    <td><span class="status-badge <?php echo $p['status'] === 'completed' ? 'success' : 'pending'; ?>"><?php echo $p['status'] === 'completed' ? 'Confirmed' : ucfirst($p['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding: 24px; color:#6B7280;">No M-Pesa transactions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Reconciliation Tab -->
    <div class="tab-content" id="content-reconciliation">
        <div class="reconciliation-grid">
            <?php
                $systemTotal   = count($payments ?? []);
                $systemSuccess = count(array_filter($payments ?? [], fn($p) => $p['status'] === 'completed'));
                $systemAmount  = array_sum(array_column(array_filter($payments ?? [], fn($p) => $p['status'] === 'completed'), 'amount'));
                $mpesaAll      = array_filter($payments ?? [], fn($p) => ($p['payment_method'] ?? '') === 'mpesa');
                $mpesaSuccess  = count(array_filter($mpesaAll, fn($p) => $p['status'] === 'completed'));
                $mpesaAmount   = array_sum(array_column(array_filter($mpesaAll, fn($p) => $p['status'] === 'completed'), 'amount'));
                $unmatched     = count(array_filter($payments ?? [], fn($p) => ($p['reconciliation_status'] ?? '') === 'unmatched'));
            ?>
            <div class="reconciliation-card">
                <h4><i class="fas fa-database"></i> System Records</h4>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Total Transactions</span>
                    <span class="reconciliation-value"><?php echo number_format($systemTotal); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Total Amount</span>
                    <span class="reconciliation-value">KSh <?php echo number_format($systemAmount, 2); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Successful</span>
                    <span class="reconciliation-value"><?php echo number_format($systemSuccess); ?></span>
                </div>
            </div>

            <div class="reconciliation-card">
                <h4><i class="fas fa-mobile-alt"></i> M-Pesa Records</h4>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Total Transactions</span>
                    <span class="reconciliation-value"><?php echo number_format(count($mpesaAll)); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Total Amount</span>
                    <span class="reconciliation-value">KSh <?php echo number_format($mpesaAmount, 2); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Confirmed</span>
                    <span class="reconciliation-value"><?php echo number_format($mpesaSuccess); ?></span>
                </div>
            </div>

            <div class="reconciliation-card">
                <h4><i class="fas fa-exclamation-triangle"></i> Discrepancies</h4>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Unmatched Payments</span>
                    <span class="reconciliation-value" style="color: #DC2626;"><?php echo number_format($unmatched); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Amount Difference</span>
                    <span class="reconciliation-value" style="color: #DC2626;">KSh <?php echo number_format($systemAmount - $mpesaAmount, 2); ?></span>
                </div>
                <div class="reconciliation-item">
                    <span class="reconciliation-label">Pending Review</span>
                    <span class="reconciliation-value" style="color: #F59E0B;"><?php echo number_format(count(array_filter($payments ?? [], fn($p) => $p['status'] === 'pending'))); ?></span>
                </div>
            </div>
        </div>

        <a class="btn-action btn-primary" style="margin-top: 20px;" href="/admin/payments-reconciliation">
            <i class="fas fa-play"></i>
            Run Reconciliation
        </a>
    </div>

    <!-- Financial Dashboard Tab -->
    <!-- <div class="tab-content" id="content-financial">
        <div class="financial-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Payment Trends</h3>
                    <p class="chart-subtitle">Monthly collection patterns</p>
                </div>
                <canvas id="paymentTrendsChart" height="300"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Payment Methods</h3>
                    <p class="chart-subtitle">Distribution by method</p>
                </div>
                <canvas id="paymentMethodsChart" height="300"></canvas>
            </div>
        </div>
    </div> -->

    <!-- Reports Tab -->
    <div class="tab-content" id="content-reports">
        <h4 style="margin-bottom: 20px; color: #1F2937;">Generate Payment Reports</h4>
        
        <div class="reconciliation-grid">
            <div class="reconciliation-card" style="cursor: pointer;" onclick="generateReport('monthly')">
                <h4><i class="fas fa-calendar-alt"></i> Monthly Report</h4>
                <p style="font-size: 13px; color: #6B7280; margin-top: 8px;">Comprehensive monthly payment summary</p>
                <button class="btn-action btn-primary" style="margin-top: 12px; width: 100%;">
                    <i class="fas fa-download"></i>
                    Generate Report
                </button>
            </div>

            <div class="reconciliation-card" style="cursor: pointer;" onclick="generateReport('member')">
                <h4><i class="fas fa-user"></i> Member Report</h4>
                <p style="font-size: 13px; color: #6B7280; margin-top: 8px;">Individual member payment history</p>
                <button class="btn-action btn-primary" style="margin-top: 12px; width: 100%;">
                    <i class="fas fa-download"></i>
                    Generate Report
                </button>
            </div>

            <div class="reconciliation-card" style="cursor: pointer;" onclick="generateReport('financial')">
                <h4><i class="fas fa-chart-line"></i> Financial Report</h4>
                <p style="font-size: 13px; color: #6B7280; margin-top: 8px;">Detailed financial analysis and trends</p>
                <button class="btn-action btn-primary" style="margin-top: 12px; width: 100%;">
                    <i class="fas fa-download"></i>
                    Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById('content-' + tabName).classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function generateReport(type) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    if (type === 'member') {
        // Show member selection modal
        const modal = new bootstrap.Modal(document.getElementById('memberReportModal'));
        document.getElementById('memberSearchInput').value = '';
        loadMemberList('');
        modal.show();
        return;
    }
    let url = '/admin/reports/export';
    let params = {};
    let filename = '';
    if (type === 'monthly') {
        params = {
            format: 'pdf',
            type: 'payments',
            date_from: yyyy + '-' + mm + '-01',
            date_to: yyyy + '-' + mm + '-' + dd
        };
        filename = 'monthly-payments-report-' + yyyy + '-' + mm + '-' + dd + '.pdf';
    } else if (type === 'financial') {
        params = {
            format: 'pdf',
            type: 'financial',
            date_from: yyyy + '-01-01',
            date_to: yyyy + '-' + mm + '-' + dd
        };
        filename = 'financial-report-' + yyyy + '-' + mm + '-' + dd + '.pdf';
    }
    if (!url) return;
    ShenaApp.showNotification('Generating report...', 'info', 2000);
    fetch(url + '?' + new URLSearchParams(params), {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to generate report');
        return response.blob();
    })
    .then(blob => {
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        ShenaApp.showNotification('Report downloaded!', 'success', 2000);
    })
    .catch(() => {
        ShenaApp.showNotification('Failed to generate report', 'error', 3000);
    });
}

// Member search and report logic
function loadMemberList(query) {
    const container = document.getElementById('memberListContainer');
    container.innerHTML = '<div class="text-center py-3">Loading...</div>';
    fetch('/admin/api/members?search=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(members => {
            if (!Array.isArray(members) || members.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted">No members found.</div>';
                return;
            }
            container.innerHTML = members.map(member => `
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="generateMemberReport(${member.id}, '${member.first_name} ${member.last_name}')">
                    <div>
                        <strong>${member.member_number}</strong> - ${member.first_name} ${member.last_name}<br>
                        <small>${member.email} | ${member.phone}</small>
                    </div>
                    <span class="badge bg-primary">Select</span>
                </div>
            `).join('');
        })
        .catch(() => {
            container.innerHTML = '<div class="text-center py-3 text-danger">Failed to load members.</div>';
        });
}

document.getElementById('memberSearchInput').addEventListener('input', function() {
    loadMemberList(this.value);
});

function generateMemberReport(memberId, memberName) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const url = '/admin/reports/export';
    const params = {
        format: 'pdf',
        type: 'member_payments',
        member_id: memberId,
        date_from: yyyy + '-01-01',
        date_to: yyyy + '-' + mm + '-' + dd
    };
    const filename = 'member-' + memberName.replace(/\s+/g, '-') + '-payments-report-' + yyyy + '-' + mm + '-' + dd + '.pdf';
    ShenaApp.showNotification('Generating report for ' + memberName + '...', 'info', 2000);
    fetch(url + '?' + new URLSearchParams(params), {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to generate report');
        return response.blob();
    })
    .then(blob => {
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        ShenaApp.showNotification('Report downloaded!', 'success', 2000);
        bootstrap.Modal.getInstance(document.getElementById('memberReportModal')).hide();
    })
    .catch(() => {
        ShenaApp.showNotification('Failed to generate report', 'error', 3000);
    });
}

// Payment Trends Chart
const ctx1 = document.getElementById('paymentTrendsChart');
if (ctx1) {
    new Chart(ctx1.getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Payments (KSh)',
                data: [380000, 420000, 445000, 410000, 480000, 450000],
                borderColor: '#7F3D9E',
                backgroundColor: 'rgba(127, 61, 158, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KSh ' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });
}

// Payment Methods Chart
const ctx2 = document.getElementById('paymentMethodsChart');
if (ctx2) {
    new Chart(ctx2.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['M-Pesa', 'Bank Transfer', 'Cash'],
            datasets: [{
                data: [85, 10, 5],
                backgroundColor: ['#7F3D9E', '#3B82F6', '#F59E0B']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
