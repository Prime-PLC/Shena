<?php 
$page = 'dashboard'; 
include __DIR__ . '/../layouts/agent-header.php';

// Get dynamic data from controller (passed by AgentDashboardController)
// Expected variables: $agent, $members, $stats
$stats = $stats ?? [
    'total_members' => 0,
    'members_growth' => 0,
    'active_policies' => 0,
    'policies_growth' => 0,
    'monthly_commission' => 0,
    'commission_growth' => 0,
    'agent_rank' => 0,
    'rank_progress' => 0
];

// Process members data
$processed_members = [];
if (!empty($members)) {
    $package_names = [
        'individual' => 'Individual Plan',
        'couple' => 'Family Plan',
        'family' => 'Family Plan',
        'extended_family_1' => 'Extended Family 1 Plan',
        'extended_family_2' => 'Extended Family 2 Plan',
        'executive' => 'Executive Plan'
    ];
    
    $status_map = [
        'active' => ['label' => 'ACTIVE', 'class' => 'active'],
        'pending' => ['label' => 'PENDING', 'class' => 'maturity'],
        'matured' => ['label' => 'MATURITY', 'class' => 'maturity'],
        'defaulted' => ['label' => 'DEFAULTED', 'class' => 'defaulted'],
        'suspended' => ['label' => 'SUSPENDED', 'class' => 'defaulted']
    ];
    
    foreach (array_slice($members, 0, 4) as $member) {
        $names = explode(' ', trim($member['first_name'] . ' ' . $member['last_name']));
        $initials = '';
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }
        
        $status_info = $status_map[strtolower($member['status'] ?? 'pending')] ?? ['label' => 'PENDING', 'class' => 'maturity'];
        
        $processed_members[] = [
            'initials' => $initials,
            'name' => $member['first_name'] . ' ' . $member['last_name'],
            'role' => 'Primary Member',
            'member_number' => $member['member_number'] ?? 'N/A',
            'policy_plan' => $package_names[$member['package'] ?? 'individual'] ?? 'Individual Plan',
            'join_date' => !empty($member['created_at']) ? date('d M Y', strtotime($member['created_at'])) : 'N/A',
            'status' => $status_info['label'],
            'status_class' => $status_info['class'],
            'member_id' => $member['id']
        ];
    }
}
?>

<style>
/* Agent Dashboard Styles */
.agent-dashboard-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
}

.dashboard-title-section h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.dashboard-title-section p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.dashboard-actions {
    display: flex;
    gap: 12px;
}

.btn-export {
    background: white;
    color: #4B5563;
    border: 1px solid #D1D5DB;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-export:hover {
    background: #F9FAFB;
    border-color: #9CA3AF;
}

.btn-new-registration {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-new-registration:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card-agent {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card-agent:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card-agent.rank-card {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
}

.stat-icon-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.stat-icon {
    font-size: 20px;
    color: #9CA3AF;
}

.stat-label {
    font-size: 11px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rank-card .stat-icon {
    color: rgba(255, 255, 255, 0.8);
}

.rank-card .stat-label {
    color: rgba(255, 255, 255, 0.9);
}

.stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 700;
    color: #1F2937;
    margin: 8px 0;
}

.rank-card .stat-value {
    color: white;
    font-size: 48px;
}

.stat-growth {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
    color: #059669;
}

.stat-growth i {
    font-size: 12px;
}

.rank-card .stat-growth {
    color: rgba(255, 255, 255, 0.9);
}

.stat-description {
    font-size: 12px;
    color: #9CA3AF;
    margin-top: 4px;
}

.rank-card .stat-description {
    color: rgba(255, 255, 255, 0.8);
}

.rank-progress {
    margin-top: 16px;
    height: 6px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
    overflow: hidden;
}

.rank-progress-bar {
    height: 100%;
    background: white;
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Member Portfolio Section */
.portfolio-section {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.portfolio-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E5E7EB;
}

.portfolio-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.portfolio-tabs {
    display: flex;
    gap: 16px;
}

.portfolio-tab {
    font-size: 14px;
    font-weight: 600;
    color: #6B7280;
    padding: 8px 16px;
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.portfolio-tab.active {
    color: #7F20B0;
    background: #F3E8FF;
}

.portfolio-tab:hover:not(.active) {
    color: #4B5563;
    background: #F9FAFB;
}

.filter-btn {
    background: white;
    border: 1px solid #D1D5DB;
    padding: 8px 12px;
    border-radius: 8px;
    color: #6B7280;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn:hover {
    border-color: #9CA3AF;
    color: #4B5563;
}

/* Member Table */
.member-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.member-table thead th {
    font-size: 11px;
    font-weight: 700;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border-bottom: 1px solid #E5E7EB;
    text-align: left;
}

.member-table tbody tr {
    transition: background-color 0.2s;
}

.member-table tbody tr:hover {
    background: #F9FAFB;
}

.member-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #F3F4F6;
}

.member-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.member-details h6 {
    font-size: 14px;
    font-weight: 600;
    color: #1F2937;
    margin: 0 0 2px 0;
}

.member-details p {
    font-size: 12px;
    color: #9CA3AF;
    margin: 0;
}

.member-number {
    font-size: 13px;
    color: #4B5563;
}

.policy-plan {
    font-size: 13px;
    color: #4B5563;
}

.join-date {
    font-size: 13px;
    color: #6B7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.3px;
}

.status-badge.active {
    background: #D1FAE5;
    color: #059669;
}

.status-badge.maturity {
    background: #FEF3C7;
    color: #D97706;
}

.status-badge.defaulted {
    background: #FEE2E2;
    color: #DC2626;
}

.status-badge i {
    font-size: 8px;
}

.action-btns {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #E5E7EB;
    background: white;
    color: #6B7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
    color: #4B5563;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #E5E7EB;
}

.pagination-info {
    font-size: 14px;
    color: #6B7280;
}

.pagination-controls {
    display: flex;
    gap: 8px;
}

.page-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid #D1D5DB;
    background: white;
    color: #4B5563;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.page-btn:hover:not(.active) {
    background: #F9FAFB;
    border-color: #9CA3AF;
}

.page-btn.active {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border-color: transparent;
}

/* Responsive */
@media (max-width: 1400px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .agent-dashboard-container {
        padding: 20px 15px;
    }

    .dashboard-header {
        flex-direction: column;
        gap: 16px;
    }

    .dashboard-actions {
        width: 100%;
        flex-direction: column;
    }

    .btn-export,
    .btn-new-registration {
        width: 100%;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .portfolio-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .portfolio-tabs {
        width: 100%;
        overflow-x: auto;
    }

    .member-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="agent-dashboard-container">
    <div class="dashboard-header">
        <div class="dashboard-title-section">
            <h1>Agent Performance</h1>
            <p>Overview of your activity and member portfolio</p>
        </div>
        <div class="dashboard-actions">
            <button class="btn-export">
                <i class="fas fa-download"></i>
                Export Data
            </button>
            <button class="btn-new-registration" onclick="location.href='/agent/register-member'">
                <i class="fas fa-user-plus"></i>
                New Registration
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <!-- Total Members -->
        <div class="stat-card-agent">
            <div class="stat-icon-wrapper">
                <i class="fas fa-users stat-icon"></i>
                <span class="stat-label">Total Members</span>
            </div>
            <div class="stat-value"><?php echo number_format($stats['total_members']); ?></div>
            <div class="stat-growth">
                <i class="fas fa-arrow-up"></i>
                +<?php echo number_format($stats['members_growth'], 1); ?>%
            </div>
            <div class="stat-description">Active registrations this year</div>
        </div>

        <!-- Active Policies -->
        <div class="stat-card-agent">
            <div class="stat-icon-wrapper">
                <i class="fas fa-shield-alt stat-icon"></i>
                <span class="stat-label">Active Policies</span>
            </div>
            <div class="stat-value"><?php echo number_format($stats['active_policies']); ?></div>
            <div class="stat-growth">
                <i class="fas fa-arrow-up"></i>
                +<?php echo number_format($stats['policies_growth'], 1); ?>%
            </div>
            <div class="stat-description">Current premium-paying members</div>
        </div>

        <!-- Monthly Commission -->
        <div class="stat-card-agent">
            <div class="stat-icon-wrapper">
                <i class="fas fa-coins stat-icon"></i>
                <span class="stat-label">Monthly Comm.</span>
            </div>
            <div class="stat-value">KES <?php echo number_format($stats['monthly_commission'], 2); ?></div>
            <div class="stat-growth">
                <i class="fas fa-arrow-up"></i>
                +<?php echo number_format($stats['commission_growth'], 1); ?>%
            </div>
            <div class="stat-description">Next payout: 28th of month</div>
        </div>

        <!-- Agent Rank -->
        <div class="stat-card-agent rank-card">
            <div class="stat-icon-wrapper">
                <i class="fas fa-trophy stat-icon"></i>
                <span class="stat-label">Agent Rank</span>
            </div>
            <div class="stat-value">#<?php echo $stats['agent_rank'] > 0 ? $stats['agent_rank'] : 'N/A'; ?></div>
            <div class="rank-progress">
                <div class="rank-progress-bar" style="width: <?php echo $stats['rank_progress']; ?>%;"></div>
            </div>
            <div class="stat-description"><?php echo $stats['rank_progress']; ?>% to next tier</div>
        </div>
    </div>

    <!-- Member Portfolio -->
    <div class="portfolio-section">
        <div class="portfolio-header">
            <h2>Member Portfolio</h2>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="portfolio-tabs">
                    <button class="portfolio-tab active">All Members</button>
                    <button class="portfolio-tab">Pending</button>
                    <button class="portfolio-tab">Claims</button>
                </div>
                <button class="filter-btn">
                    <i class="fas fa-sliders-h"></i>
                </button>
            </div>
        </div>

        <table class="member-table">
            <thead>
                <tr>
                    <th>MEMBER</th>
                    <th>ID NUMBER</th>
                    <th>POLICY PLAN</th>
                    <th>JOIN DATE</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($processed_members)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px;">
                        <i class="fas fa-users" style="font-size: 48px; color: #D1D5DB; margin-bottom: 16px;"></i>
                        <p style="color: #9CA3AF; font-size: 14px;">No members registered yet</p>
                        <button class="btn-new-registration" onclick="location.href='/agent/register-member'" style="margin-top: 16px;">
                            <i class="fas fa-user-plus"></i>
                            Register First Member
                        </button>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($processed_members as $member): ?>
                <tr>
                    <td>
                        <div class="member-info">
                            <div class="member-avatar"><?php echo $member['initials']; ?></div>
                            <div class="member-details">
                                <h6><?php echo htmlspecialchars($member['name']); ?></h6>
                                <p><?php echo htmlspecialchars($member['role']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="member-number"><?php echo htmlspecialchars($member['member_number']); ?></td>
                    <td class="policy-plan"><?php echo htmlspecialchars($member['policy_plan']); ?></td>
                    <td class="join-date"><?php echo htmlspecialchars($member['join_date']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $member['status_class']; ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo $member['status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn" title="View Details" onclick="location.href='/agent/member-details/<?php echo $member['member_id']; ?>'">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn" title="More Actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <?php echo count($processed_members); ?> of <?php echo count($members ?? []); ?> members</div>
            <div class="pagination-controls">
                <button class="page-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
