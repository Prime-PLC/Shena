<?php 
$page = 'claims'; 
include __DIR__ . '/../layouts/agent-header.php';
?>

<style>
.claims-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

.claims-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
}

.claims-title-section h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.claims-title-section p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

.claims-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 32px;
}

.claim-stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.claim-stat-label {
    font-size: 12px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.claim-stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
}

.claim-stat-value.pending {
    color: #F59E0B;
}

.claim-stat-value.approved {
    color: #10B981;
}

.claim-stat-value.processing {
    color: #3B82F6;
}

.claim-stat-value.completed {
    color: #8B5CF6;
}

.claim-stat-change {
    font-size: 13px;
    color: #6B7280;
}

.claims-table-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.filter-controls {
    display: flex;
    gap: 12px;
}

.filter-select {
    padding: 8px 16px;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    font-size: 14px;
    color: #4B5563;
    background: white;
}

.claims-table {
    width: 100%;
    border-collapse: collapse;
}

.claims-table thead {
    background: #F9FAFB;
    border-bottom: 2px solid #E5E7EB;
}

.claims-table th {
    padding: 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.claims-table td {
    padding: 20px 16px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 14px;
    color: #4B5563;
}

.claims-table tbody tr {
    transition: background-color 0.2s;
    cursor: pointer;
}

.claims-table tbody tr:hover {
    background: #F9FAFB;
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
    background: linear-gradient(135deg, #7F20B0 0%, #9D3CC9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
}

.member-details h6 {
    font-weight: 600;
    font-size: 14px;
    color: #1F2937;
    margin: 0 0 2px 0;
}

.member-details p {
    font-size: 13px;
    color: #6B7280;
    margin: 0;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.approved {
    background: #D1FAE5;
    color: #065F46;
}

.status-badge.processing {
    background: #DBEAFE;
    color: #1E40AF;
}

.status-badge.completed {
    background: #EDE9FE;
    color: #5B21B6;
}

.status-badge.rejected {
    background: #FEE2E2;
    color: #991B1B;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #D1D5DB;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 600;
    color: #6B7280;
    margin: 0 0 8px 0;
}

.empty-state p {
    font-size: 14px;
    color: #9CA3AF;
    margin: 0;
}

@media (max-width: 1200px) {
    .claims-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .claims-stats-grid {
        grid-template-columns: 1fr;
    }

    .claims-table {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .claims-table thead,
    .claims-table tbody,
    .claims-table tr { min-width: 480px; }
}
</style>

<div class="claims-container">
    <div class="claims-header">
        <div class="claims-title-section">
            <h1>Claims</h1>
            <p>Monitor claims submitted by your members</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="claims-stats-grid">
        <div class="claim-stat-card">
            <div class="claim-stat-label">Pending Claims</div>
            <div class="claim-stat-value pending">
                <?php echo isset($claims) ? count(array_filter($claims, fn($c) => $c['status'] === 'pending')) : 0; ?>
            </div>
            <div class="claim-stat-change">Awaiting review</div>
        </div>

        <div class="claim-stat-card">
            <div class="claim-stat-label">Approved Claims</div>
            <div class="claim-stat-value approved">
                <?php echo isset($claims) ? count(array_filter($claims, fn($c) => $c['status'] === 'approved')) : 0; ?>
            </div>
            <div class="claim-stat-change">Ready for processing</div>
        </div>

        <div class="claim-stat-card">
            <div class="claim-stat-label">Processing</div>
            <div class="claim-stat-value processing">
                <?php echo isset($claims) ? count(array_filter($claims, fn($c) => $c['status'] === 'processing')) : 0; ?>
            </div>
            <div class="claim-stat-change">Being processed</div>
        </div>

        <div class="claim-stat-card">
            <div class="claim-stat-label">Completed</div>
            <div class="claim-stat-value completed">
                <?php echo isset($claims) ? count(array_filter($claims, fn($c) => $c['status'] === 'completed')) : 0; ?>
            </div>
            <div class="claim-stat-change">Successfully closed</div>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="claims-table-card">
        <div class="table-header">
            <h2>All Claims</h2>
            <div class="filter-controls">
                <select class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <?php if (empty($claims)): ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice"></i>
                <h3>No Claims Yet</h3>
                <p>Claims submitted by your members will appear here</p>
            </div>
        <?php else: ?>
            <table class="claims-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Claim Type</th>
                        <th>Amount</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $claim): ?>
                        <tr onclick="location.href='/agent/claim-details/<?php echo $claim['id']; ?>'">
                            <td>
                                <div class="member-info">
                                    <div class="member-avatar">
                                        <?php echo strtoupper(substr($claim['member_name'], 0, 2)); ?>
                                    </div>
                                    <div class="member-details">
                                        <h6><?php echo htmlspecialchars($claim['member_name']); ?></h6>
                                        <p><?php echo htmlspecialchars($claim['member_number']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($claim['claim_type']); ?></td>
                            <td><strong>KES <?php echo number_format($claim['amount'], 2); ?></strong></td>
                            <td><?php echo date('d M Y', strtotime($claim['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $claim['status']; ?>">
                                    <?php echo strtoupper($claim['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
