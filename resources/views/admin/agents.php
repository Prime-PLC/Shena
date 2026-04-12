<?php 
$latest_claim = $latest_claim ?? null;
$agents = $agents ?? [];
$stats = $stats ?? ['total_agents' => 0, 'pending_commissions' => 0, 'monthly_accounts' => 0, 'total_portfolios' => 0, 'new_agents' => 0];
$pending_commissions_data = $pending_commissions_data ?? [];
$top_performers = $top_performers ?? [];
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    /* Page Header */
    .page-header {
        margin-bottom: 24px;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .page-subtitle {
        font-size: 13px;
        color: #9CA3AF;
        margin: 0;
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
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.2);
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
    }

    .alert-button:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Tabs Styles */
    .tabs-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .tabs-nav {
        display: flex;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        padding: 4px;
        gap: 4px;
        overflow-x: auto;
    }

    .tab-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        background: transparent;
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .tab-item:hover {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .tab-item.active {
        background: white;
        color: #7F3D9E;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        background: #7F3D9E;
        color: white;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .tab-item.active .tab-badge {
        background: #7F3D9E;
    }

    .tab-content {
        padding: 24px;
    }

    .tab-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .tab-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .tab-action-btn:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(127, 61, 158, 0.1);
    }

    .tab-action-btn.primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        border-color: #7F3D9E;
    }

    .tab-action-btn.primary:hover {
        background: linear-gradient(135deg, #6D2B8C 0%, #6A28DB 100%);
        color: white;
    }

    .search-container {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 10px 12px 10px 36px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-badge.suspended {
        background: #FEE2E2;
        color: #991B1B;
    }

    .status-badge.inactive {
        background: #F3F4F6;
        color: #6B7280;
    }

    /* Stats Grid */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
        margin-bottom: 12px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-icon.purple {
        background: #EDE9FE;
        color: #7F3D9E;
    }

    .stat-icon.yellow {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .stat-icon.green {
        background: #D1FAE5;
        color: #10B981;
    }

    .stat-icon.blue {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .stat-label {
        font-size: 11px;
        color: #9CA3AF;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
    }

    .stat-change {
        font-size: 12px;
        color: #10B981;
        margin-top: 4px;
    }

    .stat-change.down {
        color: #EF4444;
    }

    /* Main Content Layout */
    .content-layout {
        display: grid;
        grid-template-columns: 1.8fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    /* Regional Growth Card */
    .growth-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .growth-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .growth-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .growth-subtitle {
        font-size: 12px;
        color: #9CA3AF;
        margin-top: 2px;
    }

    .growth-filters {
        display: flex;
        gap: 8px;
    }

    .filter-btn {
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn.active {
        background: #7F3D9E;
        color: white;
        border-color: #7F3D9E;
    }

    .map-container {
        background: #F9FAFB;
        border-radius: 12px;
        height: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .map-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: absolute;
        animation: pulse 2s infinite;
    }

    .map-dot.purple {
        background: #7F3D9E;
        top: 45%;
        left: 48%;
    }

    .map-dot.orange {
        background: #F97316;
        top: 60%;
        left: 52%;
    }

    .map-dot.yellow {
        background: #F59E0B;
        top: 30%;
        left: 45%;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.5);
            opacity: 0.5;
        }
    }

    .map-icon {
        font-size: 120px;
        color: #E5E7EB;
    }

    .growth-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .metric-item {
        text-align: center;
        padding: 12px;
        background: #F9FAFB;
        border-radius: 8px;
    }

    .metric-label {
        font-size: 10px;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .metric-value {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
    }

    /* Payout Queue */
    .payout-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .payout-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .payout-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .payout-badge {
        background: #FEF3C7;
        color: #F59E0B;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .payout-item {
        padding: 16px;
        background: #F9FAFB;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .payout-agent {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .payout-amount {
        font-size: 12px;
        color: #8B5CF6;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .payout-info {
        font-size: 11px;
        color: #6B7280;
        margin-bottom: 12px;
    }

    .payout-button {
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
    }

    .payout-button:hover {
        background: #7F3D9E;
    }

    .payout-button.audit {
        background: #F3F4F6;
        color: #6B7280;
    }

    .payout-button.audit:hover {
        background: #E5E7EB;
    }

    /* Agents Table */
    .agents-table-card {
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
    }

    .table-btn.primary {
        background: #F59E0B;
        color: white;
        border-color: #F59E0B;
    }

    .table-btn:hover {
        background: #F3F4F6;
    }

    .table-btn.primary:hover {
        background: #D97706;
    }

    .agents-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .agents-table thead th {
        background: #7F3D9E;
        color: white;
        padding: 14px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .agents-table thead th:first-child {
        border-radius: 8px 0 0 0;
    }

    .agents-table thead th:last-child {
        border-radius: 0 8px 0 0;
    }

    .agents-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #F3F4F6;
        font-size: 13px;
        color: #1F2937;
    }

    .agents-table tbody tr:hover {
        background: #FAF5FF;
        transform: translateX(2px);
    }

    .agent-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agent-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #7F3D9E;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .agent-info {
        flex: 1;
    }

    .agent-name {
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 2px;
    }

    .agent-number {
        font-size: 11px;
        color: #9CA3AF;
    }

    .portfolio-badge {
        background: #FEF3C7;
        color: #F59E0B;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 18px;
        font-weight: 700;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #10B981;
    }

    .status-indicator.down {
        color: #EF4444;
    }

    .earnings-value {
        font-weight: 700;
        color: #7F3D9E;
    }

    .action-btn {
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: #7F3D9E;
        color: white;
        border-color: #7F3D9E;
    }

    /* Top Performers */
    .performers-card {
        background: linear-gradient(135deg, #7F3D9E 0%, #7F3D9E 100%);
        border-radius: 12px;
        padding: 24px;
        color: white;
        margin-bottom: 30px;
    }

    .performers-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .performers-title {
        font-size: 20px;
        font-weight: 700;
    }

    .performers-subtitle {
        font-size: 12px;
        opacity: 0.9;
        margin-top: 2px;
    }

    .trophy-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .performers-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .performer-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .performer-rank {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .performer-name {
        font-weight: 700;
        font-size: 14px;
    }

    .performer-stat {
        font-size: 11px;
        opacity: 0.9;
    }

    @media (max-width: 1200px) {
        .content-layout {
            grid-template-columns: 1fr;
        }

        .performers-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .growth-metrics {
            grid-template-columns: 1fr;
        }

        /* Allow tables to scroll — override overflow:hidden on tabs-container */
        .tabs-container {
            overflow: visible !important;
        }

        .agents-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .agents-table thead,
        .agents-table tbody,
        .agents-table tr {
            min-width: 480px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Agent & Commission Center</h1>
    <p class="page-subtitle">Field operations & payout management</p>
</div>

<!-- Emergency Alert -->
<?php if (!empty($latest_claim)): ?>
<div class="emergency-alert">
    <div class="alert-content">
        <div class="alert-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="alert-text">
            <span class="alert-badge">SYSTEM NOTIFICATION</span>
            <div class="alert-title">Death Claim Alert: <?php echo htmlspecialchars($latest_claim['deceased_name'] ?? 'N/A'); ?></div>
            <div class="alert-description">Member: <?php echo htmlspecialchars(($latest_claim['first_name'] ?? '') . ' ' . ($latest_claim['last_name'] ?? '')); ?>. Immediate verification required for funeral payout</div>
        </div>
    </div>
    <a href="/admin/claims" class="alert-button">VIEW CLAIM</a>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-row">
    <!-- Total Field Agents -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon purple">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
        <div class="stat-label">Total Field Agents</div>
        <div class="stat-value"><?php echo number_format($stats['total_agents'] ?? 0); ?></div>
        <div class="stat-change">+<?php echo $stats['new_agents'] ?? 0; ?> New</div>
    </div>

    <!-- Pending Commissions -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
        <div class="stat-label">Pending Commissions</div>
        <div class="stat-value">KES <?php echo number_format($stats['pending_commissions'] ?? 0, 0); ?></div>
        <div class="stat-change"><?php echo count($pending_commissions_data ?? []); ?> Pending</div>
    </div>

    <!-- Monthly Accounts -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-label">Monthly Accounts</div>
        <div class="stat-value"><?php echo number_format($stats['monthly_accounts'] ?? 0); ?></div>
        <div class="stat-change">All Agents</div>
    </div>

    <!-- Assigned Portfolios -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-briefcase"></i>
            </div>
        </div>
        <div class="stat-label">Assigned Portfolios</div>
        <div class="stat-value"><?php echo number_format($stats['total_portfolios'] ?? 0); ?></div>
        <div class="stat-change">All Regions</div>
    </div>
</div>

<!-- Main Content Layout -->
<div class="content-layout">
    <!-- Regional Agent Growth -->
    <div>
        <div class="growth-card">
            <div class="growth-header">
                <div>
                    <div class="growth-title">
                        <i class="fas fa-chart-line" style="color: #F59E0B;"></i>
                        <span>Regional Agent Growth</span>
                    </div>
                    <div class="growth-subtitle">Heatmap of recruitment and active agents indexed by region</div>
                </div>
                <div class="growth-filters">
                    <button class="filter-btn active" onclick="window.location.href='/admin/agents?period=monthly';">Monthly</button>
                    <button class="filter-btn" onclick="window.location.href='/admin/agents?period=quarterly';">Quarterly</button>
                </div>
            </div>

            <div class="map-container">
                <i class="fas fa-map-marked-alt map-icon"></i>
                <div class="map-dot purple"></div>
                <div class="map-dot orange"></div>
                <div class="map-dot yellow"></div>
            </div>

            <div class="growth-metrics">
                <div class="metric-item">
                    <div class="metric-label">Top Region</div>
                    <div class="metric-value">Nairobi Makua</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Perfect Growth</div>
                    <div class="metric-value">Coast Region</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Active Regions</div>
                    <div class="metric-value">KES 843,000</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Queue -->
    <div>
        <div class="payout-card">
            <div class="payout-header">
                <div class="payout-title">Payout Queue</div>
                <span class="payout-badge">NEEDS REVIEW</span>
            </div>

            <?php if (!empty($pending_commissions_data)): ?>
                <?php foreach ($pending_commissions_data as $commission): ?>
                <div class="payout-item">
                    <div class="payout-agent"><?php echo htmlspecialchars($commission['agent_name'] ?? 'N/A'); ?></div>
                    <div class="payout-amount">KES <?php echo number_format($commission['commission_amount'] ?? 0, 2); ?></div>
                    <div class="payout-info">
                        Agent: <?php echo htmlspecialchars($commission['agent_number'] ?? 'N/A'); ?><br>
                        <?php echo number_format($commission['total_members'] ?? 0); ?> Portfolios
                    </div>
                    <button class="payout-button" onclick="window.location.href='/admin/agents/view/<?php echo $commission['agent_id']; ?>'">View Details</button>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="payout-item">
                    <div class="payout-agent">No Pending Payouts</div>
                    <div class="payout-info">All commissions have been processed</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Registered Field Agents Table -->
<div class="tabs-container">
    <div class="tabs-nav">
        <button class="tab-item active" onclick="switchTab('all', this)">
            <i class="fas fa-users"></i>
            All Agents
            <span class="tab-badge"><?= $stats['total_agents'] ?? 0 ?></span>
        </button>
        <button class="tab-item" onclick="switchTab('active', this)">
            <i class="fas fa-user-check"></i>
            Active Agents
        </button>
        <button class="tab-item" onclick="switchTab('suspended', this)">
            <i class="fas fa-user-slash"></i>
            Suspended
        </button>
        <button class="tab-item" onclick="switchTab('commissions', this)">
            <i class="fas fa-money-bill-wave"></i>
            Pending Commissions
            <span class="tab-badge"><?= count($pending_commissions_data ?? []) ?></span>
        </button>
        <button class="tab-item" onclick="switchTab('leaderboard', this)">
            <i class="fas fa-trophy"></i>
            Leaderboard
        </button>
        <button class="tab-item" onclick="switchTab('tools', this)">
            <i class="fas fa-tools"></i>
            Tools & Reports
        </button>
    </div>

    <!-- All Agents Tab -->
    <div id="tab-all" class="tab-content active">
        <div class="tab-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; gap: 12px;">
                <a href="/admin/agents/create" class="tab-action-btn primary">
                    <i class="fas fa-user-plus"></i>
                    Register New Agent
                </a>
                <a href="/admin/agents/export-csv" class="tab-action-btn">
                    <i class="fas fa-download"></i>
                    Export Data
                </a>
            </div>
            <div class="search-container" style="width: 300px;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchAgents" placeholder="Search by name, number, region..." onkeyup="filterAgents()">
            </div>
        </div>

<div class="agents-table-card">
    <div style="overflow-x: auto;">
        <table class="agents-table" id="agentsTable">
            <thead>
                <tr>
                    <th>Agent Details</th>
                    <th>Region</th>
                    <th>Active Portfolios</th>
                    <th>Total Commission</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($agents)): ?>
                    <?php 
                    $avatarColors = ['#8B5CF6', '#F59E0B', '#10B981', '#3B82F6', '#EF4444'];
                    $colorIndex = 0;
                    ?>
                    <?php foreach ($agents as $agent): ?>
                        <?php 
                        $initials = strtoupper(substr($agent['first_name'] ?? 'A', 0, 1) . substr($agent['last_name'] ?? 'G', 0, 1));
                        $avatarColor = $avatarColors[$colorIndex % count($avatarColors)];
                        $colorIndex++;
                        $status = $agent['status'] ?? 'active';
                        ?>
                        <tr data-status="<?= $status ?>" onclick="window.location.href='/admin/agents/view/<?php echo $agent['id']; ?>'" style="cursor: pointer;">
                            <td>
                                <div class="agent-profile">
                                    <div class="agent-avatar" style="background: <?php echo $avatarColor; ?>;"><?php echo $initials; ?></div>
                                    <div class="agent-info">
                                        <div class="agent-name"><?php echo htmlspecialchars(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')); ?></div>
                                        <div class="agent-number"><?php echo htmlspecialchars($agent['agent_number'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($agent['region'] ?? 'N/A'); ?></td>
                            <td><span class="portfolio-badge"><?php echo number_format($agent['total_members'] ?? 0); ?></span></td>
                            <td><span class="earnings-value">KES <?php echo number_format($agent['total_commission'] ?? 0, 2); ?></span></td>
                            <td>
                                <?php if ($status === 'active'): ?>
                                    <span class="status-badge active">Active</span>
                                <?php elseif ($status === 'suspended'): ?>
                                    <span class="status-badge suspended">Suspended</span>
                                <?php else: ?>
                                    <span class="status-badge inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td onclick="event.stopPropagation();">
                                <button class="action-btn" onclick="window.location.href='/admin/agents/view/<?php echo $agent['id']; ?>'">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #9CA3AF;">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                            <div style="font-size: 16px; font-weight: 600;">No Agents Found</div>
                            <div style="font-size: 14px; margin-top: 8px;">Start by registering field agents</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align: center; padding: 16px; color: #9CA3AF; font-size: 13px;">
        Displaying <span id="displayCount"><?php echo count($agents ?? []); ?></span> OF <?php echo number_format($stats['total_agents'] ?? 0); ?> AGENTS
    </div>
</div>
    </div>

    <!-- Active Agents Tab -->
    <div id="tab-active" class="tab-content" style="display:none;">
        <div class="tab-actions">
            <h3 style="margin: 0; color: #1F2937;">Active Agents</h3>
        </div>
        <div class="agents-table-card" id="activeAgentsContent">
            <!-- Content will be filtered from main table -->
        </div>
    </div>

    <!-- Suspended Agents Tab -->
    <div id="tab-suspended" class="tab-content" style="display:none;">
        <div class="tab-actions">
            <h3 style="margin: 0; color: #1F2937;">Suspended Agents</h3>
            <button class="tab-action-btn" onclick="bulkReactivateAgents()">
                <i class="fas fa-undo"></i>
                Reactivate Selected
            </button>
        </div>
        <div class="agents-table-card" id="suspendedAgentsContent">
            <!-- Content will be filtered from main table -->
        </div>
    </div>

    <!-- Commissions Tab -->
    <div id="tab-commissions" class="tab-content" style="display:none;">
        <div class="tab-actions">
            <h3 style="margin: 0; color: #1F2937;">Pending Commission Payouts</h3>
            <div>
                <button class="tab-action-btn" onclick="exportCommissions()">
                    <i class="fas fa-file-excel"></i>
                    Export Report
                </button>
                <button class="tab-action-btn primary" onclick="processAllCommissions()">
                    <i class="fas fa-check-double"></i>
                    Process All
                </button>
            </div>
        </div>

        <div class="payout-card">
            <?php if (!empty($pending_commissions_data)): ?>
                <?php foreach ($pending_commissions_data as $commission): ?>
                <div class="payout-item">
                    <div class="payout-agent"><?php echo htmlspecialchars($commission['agent_name'] ?? 'N/A'); ?></div>
                    <div class="payout-amount">KES <?php echo number_format($commission['commission_amount'] ?? 0, 2); ?></div>
                    <div class="payout-info">
                        Agent: <?php echo htmlspecialchars($commission['agent_number'] ?? 'N/A'); ?><br>
                        <?php echo number_format($commission['total_members'] ?? 0); ?> Portfolios
                    </div>
                    <button class="payout-button" onclick="approveCommission(<?php echo $commission['agent_id']; ?>)">
                        <i class="fas fa-check"></i> Approve Payout
                    </button>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; color: #9CA3AF;">
                    <i class="fas fa-check-circle" style="font-size: 64px; opacity: 0.3; margin-bottom: 16px;"></i>
                    <div style="font-size: 18px; font-weight: 600;">All Commissions Processed</div>
                    <div style="font-size: 14px; margin-top: 8px;">No pending commission payouts at this time</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Leaderboard Tab -->
    <div id="tab-leaderboard" class="tab-content" style="display:none;">
        <div class="tab-actions">
            <h3 style="margin: 0; color: #1F2937;">Top Performing Agents</h3>
            <a class="tab-action-btn" href="/admin/agents?tab=leaderboard&range=month">
                <i class="fas fa-calendar"></i>
                This Month
            </a>
        </div>

        <div class="row" style="margin-top: 20px;">
            <div class="col-md-12">
                <div class="leaderboard-card" style="background: white; border-radius: 12px; padding: 24px;">
                    <table class="agents-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Agent</th>
                                <th>Region</th>
                                <th>Portfolios</th>
                                <th>Total Earnings</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($top_performers)): ?>
                                <?php 
                                $rank = 1;
                                foreach ($top_performers as $performer): 
                                    $trophyIcon = '';
                                    if ($rank === 1) $trophyIcon = '🏆';
                                    elseif ($rank === 2) $trophyIcon = '🥈';
                                    elseif ($rank === 3) $trophyIcon = '🥉';
                                ?>
                                <tr>
                                    <td><span style="font-size: 24px;"><?= $trophyIcon ?: $rank ?></span></td>
                                    <td>
                                        <div class="agent-profile">
                                            <div class="agent-info">
                                                <div class="agent-name"><?= htmlspecialchars($performer['name'] ?? 'N/A') ?></div>
                                                <div class="agent-number"><?= htmlspecialchars($performer['agent_number'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($performer['region'] ?? 'N/A') ?></td>
                                    <td><span class="portfolio-badge"><?= number_format($performer['portfolios'] ?? 0) ?></span></td>
                                    <td><span class="earnings-value">KES <?= number_format($performer['earnings'] ?? 0, 2) ?></span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="flex: 1; background: #E5E7EB; height: 8px; border-radius: 4px; overflow: hidden;">
                                                <div style="background: #10B981; height: 100%; width: <?= min(100, ($performer['performance'] ?? 75)) ?>%;"></div>
                                            </div>
                                            <span style="font-weight: 600; color: #10B981;"><?= ($performer['performance'] ?? 75) ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #9CA3AF;">
                                        <i class="fas fa-chart-line" style="font-size: 48px; opacity: 0.3;"></i>
                                        <div style="margin-top: 16px;">No performance data available</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tools & Reports Tab -->
    <div id="tab-tools" class="tab-content" style="display:none;">
        <div class="tab-actions">
            <h3 style="margin: 0; color: #1F2937;">Tools & Reports</h3>
        </div>

        <div class="row" style="margin-top: 20px;">
            <div class="col-md-4">
                <div class="tool-card" style="background: white; border-radius: 12px; padding: 24px; text-align: center;">
                    <i class="fas fa-download" style="font-size: 48px; color: #7F3D9E; margin-bottom: 16px;"></i>
                    <h5>Export Agents Data</h5>
                    <p style="color: #6B7280; font-size: 14px;">Download complete agent roster with performance metrics</p>
                    <button class="tab-action-btn primary" onclick="window.location.href='/admin/agents/export-csv'">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tool-card" style="background: white; border-radius: 12px; padding: 24px; text-align: center;">
                    <i class="fas fa-chart-bar" style="font-size: 48px; color: #10B981; margin-bottom: 16px;"></i>
                    <h5>Performance Report</h5>
                    <p style="color: #6B7280; font-size: 14px;">Generate detailed agent performance analytics</p>
                    <button class="tab-action-btn primary" onclick="generatePerformanceReport()">
                        <i class="fas fa-file-pdf"></i> Generate PDF
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tool-card" style="background: white; border-radius: 12px; padding: 24px; text-align: center;">
                    <i class="fas fa-money-check-alt" style="font-size: 48px; color: #F59E0B; margin-bottom: 16px;"></i>
                    <h5>Commission Report</h5>
                    <p style="color: #6B7280; font-size: 14px;">Export commission history and pending payouts</p>
                    <button class="tab-action-btn primary" onclick="exportCommissions()">
                        <i class="fas fa-file-excel"></i> Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab Switching Function
function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.style.display = 'none';
    });

    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-item');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab content
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Add active class to clicked tab button
    if (typeof event !== 'undefined' && event && event.target) {
        const targetButton = event.target.closest('.tab-item');
        if (targetButton) {
            targetButton.classList.add('active');
        }
    } else {
        const fallbackButton = document.querySelector(`.tab-item[onclick*="'${tabName}'"]`);
        if (fallbackButton) {
            fallbackButton.classList.add('active');
        }
    }

    //  Filter agents based on tab
    if (tabName === 'active' || tabName === 'suspended') {
        filterAgentsByStatus(tabName);
    }
}

// Filter agents by status
function filterAgentsByStatus(status) {
    const rows = document.querySelectorAll('#agentsTable tbody tr[data-status]');
    const container = document.getElementById(status === 'active' ? 'activeAgentsContent' : 'suspendedAgentsContent');
    
    // Clone the table structure
    const tableClone = document.getElementById('agentsTable').cloneNode(true);
    tableClone.id = status + 'Table';
    const tbody = tableClone.querySelector('tbody');
    tbody.innerHTML = '';

    let count = 0;
    rows.forEach(row => {
        if (row.getAttribute('data-status') === status) {
            tbody.appendChild(row.cloneNode(true));
            count++;
        }
    });

    if (count === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #9CA3AF;">
                    <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                    <div style="font-size: 16px; font-weight: 600;">No ${status} agents found</div>
                </td>
            </tr>
        `;
    }

    container.innerHTML = '<div style="overflow-x: auto;"></div>';
    container.querySelector('div').appendChild(tableClone);
}

// Search/Filter agents
function filterAgents() {
    const input = document.getElementById('searchAgents');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('agentsTable');
    const tr = table.getElementsByTagName('tr');
    let visibleCount = 0;

    for (let i = 1; i < tr.length; i++) {
        const row = tr[i];
        const td = row.getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < td.length; j++) {
            const cell = td[j];
            if (cell) {
                const txtValue = cell.textContent || cell.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }

        if (found) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    }

    document.getElementById('displayCount').textContent = visibleCount;
}

// Commission approval
function approveCommission(agentId) {
    ShenaApp.confirmAction(
        'Approve commission payout for this agent?',
        function() {
            fetch(`/admin/commissions/approve/${agentId}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ShenaApp.showNotification('Commission approved successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    ShenaApp.alert('Failed to approve commission: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                ShenaApp.alert('An error occurred while processing the request', 'error');
            });
        },
        null,
        { type: 'success', title: 'Approve Commission' }
    );
}

// Bulk reactivate agents
function bulkReactivateAgents() {
    ShenaApp.confirmAction(
        'Reactivate all suspended agents?',
        function() {
            fetch('/admin/agents/reactivate-suspended', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ShenaApp.showNotification(data.message || 'Agents reactivated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    ShenaApp.alert(data.message || 'Failed to reactivate agents', 'error');
                }
            })
            .catch(() => {
                ShenaApp.alert('An error occurred while processing the request', 'error');
            });
        },
        null,
        { type: 'warning', title: 'Reactivate Agents' }
    );
}

// Process all commissions
function processAllCommissions() {
    ShenaApp.confirmAction(
        'Process all pending commission payouts?',
        function() {
            ShenaApp.showNotification('Processing all commissions...', 'info');
            fetch('/admin/commissions/approve-all', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ShenaApp.showNotification(data.message || 'All commissions approved.', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    ShenaApp.alert(data.message || 'Failed to approve commissions', 'error');
                }
            })
            .catch(() => {
                ShenaApp.alert('An error occurred while processing the request', 'error');
            });
        },
        null,
        { type: 'primary', title: 'Process Commissions' }
    );
}

// Export commissions
function exportCommissions() {
    ShenaApp.showNotification('Generating commission report...', 'info', 2000);
    setTimeout(() => {
        window.location.href = '/admin/commissions/export';
    }, 500);
}

// Generate performance report
function generatePerformanceReport() {
    ShenaApp.showNotification('Generating performance report...', 'info', 2000);
    setTimeout(() => {
        window.location.href = '/admin/agents/performance-report';
    }, 500);
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        switchTab(tab);
    }
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
