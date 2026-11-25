<?php $page = 'commissions'; include __DIR__ . '/../layouts/agent-header.php'; ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fas fa-money-bill-wave text-success"></i> My Commissions
            </h2>
            <p class="text-muted mb-0">Track your earnings and commission history</p>
        </div>
    </div>
</div>

<!-- Commission Summary -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-success" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Total Earned
                </div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">
                    KES <?php echo number_format($total_earned ?? 0, 2); ?>
                </div>
                <small class="text-muted">All time earnings</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-warning" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                    Pending Amount
                </div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">
                    KES <?php echo number_format($pending_amount ?? 0, 2); ?>
                </div>
                <small class="text-muted">Awaiting approval</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info" style="border-left: 4px solid #17a2b8;">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                    Total Commissions
                </div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">
                    <?php echo count($commissions ?? []); ?>
                </div>
                <small class="text-muted">All records</small>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($monthly_summary)): ?>
<div class="card mb-3">
    <div class="card-header"><i class="fas fa-calendar-alt"></i> Commission by Month</div>
    <div class="card-body table-responsive">
        <table class="table table-sm" aria-label="Commission by payment month">
            <thead><tr><th>Month</th><th>Commissions</th><th>Basis amount</th><th>Commission</th><th>Pending</th><th>Approved</th><th>Paid</th></tr></thead>
            <tbody>
            <?php foreach ($monthly_summary as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['label']); ?></td>
                    <td><?php echo (int)$row['count']; ?></td>
                    <td>KES <?php echo number_format($row['base_amount'], 2); ?></td>
                    <td><strong>KES <?php echo number_format($row['commission_amount'], 2); ?></strong></td>
                    <td>KES <?php echo number_format($row['pending'], 2); ?></td>
                    <td>KES <?php echo number_format($row['approved'], 2); ?></td>
                    <td>KES <?php echo number_format($row['paid'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!empty($data_as_of)): ?><small class="text-muted">Figures as of <?php echo htmlspecialchars($data_as_of); ?>. Months follow the date the commission was earned (the member's payment date, or the registration date for registration commissions).</small><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Commission History -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history"></i> Commission History
    </div>
    <div class="card-body">
        <?php if (empty($commissions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No commissions yet</h5>
                <p class="text-muted">Register members to start earning commissions</p>
                <a href="/agent/register-member" class="btn btn-agent-primary mt-3">
                    <i class="fas fa-user-plus"></i> Register Member
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="commissionsTable">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Member</th>
                            <th>Member Number</th>
                            <th>Package</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Paid On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $commission): ?>
                        <tr>
                            <td>
                                <small><?php echo date('M d, Y', strtotime($commission['payment_date'] ?? $commission['created_at'])); ?></small>
                            </td>
                            <td>
                                <i class="fas fa-user-circle text-muted"></i>
                                <?php echo htmlspecialchars($commission['member_name'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($commission['member_number'] ?? 'N/A'); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo !empty($commission['package']) ? ucfirst($commission['package']) : 'N/A'; ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    KES <?php echo number_format($commission['commission_amount'], 2); ?>
                                <br><small class="text-muted"><?php if (($commission['commission_type'] ?? '') === 'registration'): ?>Registration commission<?php else: ?>on KES <?php echo number_format((float)($commission['amount'] ?? 0), 2); ?> @ <?php echo rtrim(rtrim(number_format((float)($commission['commission_rate'] ?? 0), 2), '0'), '.'); ?>%<?php endif; ?></small>
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $commission['status'] === 'paid' ? 'success' : 
                                         ($commission['status'] === 'pending' ? 'warning' : 
                                         ($commission['status'] === 'approved' ? 'info' : 'secondary')); 
                                ?>">
                                    <?php echo !empty($commission['status']) ? ucfirst($commission['status']) : 'Pending'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($commission['paid_at'])): ?>
                                    <small><?php echo date('M d, Y', strtotime($commission['paid_at'])); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/agent-footer.php'; ?>
