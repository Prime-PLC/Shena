<?php 
$page = 'members'; 
include __DIR__ . '/../layouts/agent-header.php';

// Process members data from controller
if (!empty($members)) {
    foreach ($members as &$member) {
        // Generate initials
        $firstName = $member['first_name'] ?? 'M';
        $lastName = $member['last_name'] ?? 'M';
        $member['initials'] = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        
        // Full name
        $member['full_name'] = trim($firstName . ' ' . $lastName);
        
        // Format status
        $status = strtoupper($member['status'] ?? 'pending');
        $member['display_status'] = $status;
        
        // Status badge class
        switch($status) {
            case 'ACTIVE':
                $member['status_class'] = 'active';
                break;
            case 'PENDING':
            case 'MATURITY':
                $member['status_class'] = 'maturity';
                break;
            case 'SUSPENDED':
            case 'DEFAULTED':
            case 'INACTIVE':
                $member['status_class'] = 'defaulted';
                break;
            default:
                $member['status_class'] = 'maturity';
        }
        
        // Format join date
        if (isset($member['created_at'])) {
            $member['join_date'] = date('d M Y', strtotime($member['created_at']));
        } else {
            $member['join_date'] = 'N/A';
        }
        
        // Format policy plan
        $package = $member['package'] ?? 'standard';
        switch($package) {
            case 'basic':
                $member['policy_plan'] = 'Basic Plan';
                break;
            case 'standard':
                $member['policy_plan'] = 'Standard Plan';
                break;
            case 'premium':
                $member['policy_plan'] = 'Premium Plan';
                break;
            default:
                $member['policy_plan'] = ucfirst($package) . ' Plan';
        }
        
        // Role/type
        $member['role'] = 'Primary Member';
    }
    unset($member); // Break reference
}
?>

<style>
/* Members Page Styles */
.members-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

.members-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
}

.members-title-section h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.members-title-section p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.btn-register-member {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
}

.btn-register-member:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
    color: white;
}

/* Portfolio Section */
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

.portfolio-controls {
    display: flex;
    gap: 16px;
}

.member-search {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #E5E7EB;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
}

.search-input-wrapper input {
    border: none;
    padding: 8px 12px;
    outline: none;
    font-size: 14px;
    min-width: 220px;
}

.search-input-wrapper .filter-btn {
    border: none;
    border-left: 1px solid #E5E7EB;
    background: transparent;
    width: 42px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6B7280;
}

.pagination-controls .page-btn {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pagination-controls .page-btn.disabled {
    pointer-events: none;
    opacity: 0.5;
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
    cursor: pointer;
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

.status-badge.maturity,
.status-badge.pending {
    background: #FEF3C7;
    color: #D97706;
}

.status-badge.defaulted,
.status-badge.suspended,
.status-badge.inactive {
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    background: #F3E8FF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7F20B0;
    font-size: 32px;
}

.empty-state h3 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 8px 0;
}

.empty-state p {
    font-size: 14px;
    color: #6B7280;
    margin: 0 0 24px 0;
}

/* Responsive */
@media (max-width: 768px) {
    .members-container {
        padding: 20px 15px;
    }

    .members-header {
        flex-direction: column;
        gap: 16px;
    }

    .btn-register-member {
        width: 100%;
        justify-content: center;
    }

    .portfolio-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .portfolio-controls {
        width: 100%;
        flex-direction: column;
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

<div class="members-container">
    <div class="members-header">
        <div class="members-title-section">
            <h1>Member Portfolio</h1>
            <p>Overview of your registered members and their status</p>
        </div>
        <a href="/agent/register-member" class="btn-register-member">
            <i class="fas fa-user-plus"></i>
            Register New Member
        </a>
    </div>

    <!-- Portfolio Section -->
    <div class="portfolio-section">
        <div class="portfolio-header">
            <h2>All Members</h2>
            <div class="portfolio-controls">
                <div class="portfolio-tabs">
                    <?php $activeStatus = $filters['status'] ?? 'all'; ?>
                    <a class="portfolio-tab <?php echo $activeStatus === 'all' ? 'active' : ''; ?>" href="/agent/members?status=all<?php echo !empty($filters['q']) ? '&q=' . urlencode($filters['q']) : ''; ?>">All Members</a>
                    <a class="portfolio-tab <?php echo $activeStatus === 'pending' ? 'active' : ''; ?>" href="/agent/members?status=pending<?php echo !empty($filters['q']) ? '&q=' . urlencode($filters['q']) : ''; ?>">Pending</a>
                </div>
                <form class="member-search" method="GET" action="/agent/members">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($activeStatus); ?>">
                    <input type="hidden" name="month" value="<?php echo htmlspecialchars($filters['month'] ?? ''); ?>">
                    <div class="search-input-wrapper">
                        <input type="text" name="q" placeholder="Search members" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>">
                        <button type="submit" class="filter-btn" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($load_error)): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($load_error); ?></div>
        <?php endif; ?>
        <?php if (!empty($monthly_summary)): ?>
        <section aria-labelledby="monthlyRegistrationsHeading" style="margin:0 0 20px;">
            <h2 id="monthlyRegistrationsHeading" style="font-size:1rem;margin:0 0 10px;">Registrations by month</h2>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php $activeMonth = $filters['month'] ?? null; ?>
                <a href="/agent/members" class="portfolio-tab <?php echo $activeMonth === null ? 'active' : ''; ?>" <?php echo $activeMonth === null ? 'aria-current="true"' : ''; ?>>All months</a>
                <?php foreach ($monthly_summary as $monthRow): ?>
                    <a href="/agent/members?month=<?php echo urlencode($monthRow['month']); ?>" class="portfolio-tab <?php echo $activeMonth === $monthRow['month'] ? 'active' : ''; ?>" <?php echo $activeMonth === $monthRow['month'] ? 'aria-current="true"' : ''; ?>>
                        <?php echo htmlspecialchars($monthRow['label']); ?>: <strong><?php echo (int)$monthRow['count']; ?></strong>
                        <small>(<?php echo (int)$monthRow['active']; ?> active<?php echo $monthRow['pending'] ? ', ' . (int)$monthRow['pending'] . ' pending' : ''; ?>)</small>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($data_as_of)): ?><small style="opacity:.7;">Figures as of <?php echo htmlspecialchars($data_as_of); ?></small><?php endif; ?>
        </section>
        <?php endif; ?>
        <?php if (empty($members)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3><?php echo !empty($filters['month']) ? 'No members registered in this month' : 'No members yet'; ?></h3>
            <p>Start building your portfolio by registering your first member</p>
            <a href="/agent/register-member" class="btn-register-member" style="display: inline-flex;">
                <i class="fas fa-user-plus"></i>
                Register Your First Member
            </a>
        </div>
        <?php else: ?>
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
                <?php foreach ($members as $member): ?>
                <tr onclick="location.href='/agent/member-details/<?php echo $member['id']; ?>'">
                    <td>
                        <div class="member-info">
                            <div class="member-avatar"><?php echo htmlspecialchars($member['initials']); ?></div>
                            <div class="member-details">
                                <h6><?php echo htmlspecialchars($member['full_name']); ?></h6>
                                <p><?php echo htmlspecialchars($member['role']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="member-number"><?php echo htmlspecialchars($member['member_number'] ?? 'N/A'); ?></td>
                    <td class="policy-plan"><?php echo htmlspecialchars($member['policy_plan']); ?></td>
                    <td class="join-date"><?php echo htmlspecialchars($member['join_date']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $member['status_class']; ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo htmlspecialchars($member['display_status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn" title="View Details" onclick="event.stopPropagation(); location.href='/agent/member-details/<?php echo $member['id']; ?>'">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing <?php echo (int)($pagination['start_item'] ?? 0); ?>-<?php echo (int)($pagination['end_item'] ?? 0); ?> of <?php echo (int)($pagination['total'] ?? 0); ?> members
            </div>
            <div class="pagination-controls">
                <?php
                $currentPage = (int)($pagination['page'] ?? 1);
                $totalPages = (int)($pagination['total_pages'] ?? 1);
                $queryBase = 'status=' . urlencode($activeStatus);
                if (!empty($filters['month'])) {
                    $queryBase .= '&month=' . urlencode($filters['month']);
                }
                if (!empty($filters['q'])) {
                    $queryBase .= '&q=' . urlencode($filters['q']);
                }
                ?>
                <a class="page-btn<?php echo $currentPage <= 1 ? ' disabled' : ''; ?>" href="/agent/members?<?php echo $queryBase; ?>&page=<?php echo max(1, $currentPage - 1); ?>" title="Previous Page">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a class="page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>" href="/agent/members?<?php echo $queryBase; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <a class="page-btn<?php echo $currentPage >= $totalPages ? ' disabled' : ''; ?>" href="/agent/members?<?php echo $queryBase; ?>&page=<?php echo min($totalPages, $currentPage + 1); ?>" title="Next Page">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
