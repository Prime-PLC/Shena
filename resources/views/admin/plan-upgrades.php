<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Plan Upgrade Management</h1>
            <p class="page-subtitle">Monitor and manage member plan upgrade requests</p>
        </div>
        <div>
            <button class="btn-export" onclick="exportToCSV()">
                <i class="fas fa-download"></i>
                Export CSV
            </button>
        </div>
    </div>
</div>

<style>
    .page-header {
        background: white;
        padding: 24px 30px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .page-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }

    .page-subtitle {
        font-size: 14px;
        color: #6B7280;
        margin: 4px 0 0 0;
    }

    .btn-export {
        padding: 10px 20px;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        color: #1F2937;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-export:hover {
        background: #F9FAFB;
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%);
    }

    .stat-card.warning::before {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    }

    .stat-card.success::before {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }

    .stat-card.danger::before {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    }

    .stat-card-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-card-info h3 {
        font-size: 32px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .stat-card-info p {
        font-size: 14px;
        color: #6B7280;
        margin: 0;
        font-weight: 500;
    }

    .stat-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        opacity: 0.2;
    }

    .stat-card.warning .stat-card-icon {
        background: #FEF3C7;
        color: #F59E0B;
        opacity: 1;
    }

    .stat-card.success .stat-card-icon {
        background: #D1FAE5;
        color: #10B981;
        opacity: 1;
    }

    .stat-card.danger .stat-card-icon {
        background: #FEE2E2;
        color: #EF4444;
        opacity: 1;
    }

    .stat-card.primary .stat-card-icon {
        background: #EDE9FE;
        color: #7F3D9E;
        opacity: 1;
    }

    /* Filters Card */
    .filters-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .filters-form {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: end;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
        color: #1F2937;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    .btn-filter {
        padding: 10px 20px;
        background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-filter:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-reset {
        padding: 10px 20px;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        color: #6B7280;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-reset:hover {
        background: #F9FAFB;
        color: #1F2937;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .table-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #E5E7EB;
    }

    .table-card-header h5 {
        font-size: 18px;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #F9FAFB;
    }

    .data-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #E5E7EB;
    }

    .data-table td {
        padding: 16px;
        font-size: 14px;
        color: #1F2937;
        border-bottom: 1px solid #F3F4F6;
    }

    .data-table tbody tr:hover {
        background: #F9FAFB;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .member-link {
        color: #7F3D9E;
        text-decoration: none;
        font-weight: 600;
    }

    .member-link:hover {
        text-decoration: underline;
    }

    .member-number {
        display: block;
        font-size: 12px;
        color: #9CA3AF;
        margin-top: 2px;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-secondary {
        background: #F3F4F6;
        color: #6B7280;
    }

    .badge-primary {
        background: #EDE9FE;
        color: #7F3D9E;
    }

    .badge-warning {
        background: #FEF3C7;
        color: #D97706;
    }

    .badge-success {
        background: #D1FAE5;
        color: #059669;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #DC2626;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
    }

    .btn-action.success {
        background: #D1FAE5;
        color: #059669;
    }

    .btn-action.success:hover {
        background: #10B981;
        color: white;
    }

    .btn-action.danger {
        background: #FEE2E2;
        color: #DC2626;
    }

    .btn-action.danger:hover {
        background: #EF4444;
        color: white;
    }

    .btn-action.info {
        background: #EDE9FE;
        color: #7F3D9E;
    }

    .btn-action.info:hover {
        background: #7F3D9E;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9CA3AF;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 16px;
        margin: 0;
    }

    /* Alert Styles */
    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .alert-success {
        background: #D1FAE5;
        color: #065F46;
        border-left: 4px solid #10B981;
    }

    .alert-danger {
        background: #FEE2E2;
        color: #991B1B;
        border-left: 4px solid #EF4444;
    }

    .alert button {
        background: none;
        border: none;
        font-size: 20px;
        color: currentColor;
        cursor: pointer;
        opacity: 0.6;
        padding: 0;
        width: 24px;
        height: 24px;
    }

    .alert button:hover {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .filters-form, .filter-row { flex-direction: column !important; gap: 8px; }
        .filters-form > *, .filter-row > * { width: 100% !important; }
    }
</style>

<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = [
                <?php if (isset($_SESSION['success'])): ?>{ type: 'success', message: <?php echo json_encode($_SESSION['success']); ?> },<?php unset($_SESSION['success']); endif; ?>
                <?php if (isset($_SESSION['error'])): ?>{ type: 'error', message: <?php echo json_encode($_SESSION['error']); ?> },<?php unset($_SESSION['error']); endif; ?>
            ];

            flashMessages.forEach(function(flash) {
                if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                    ShenaApp.alert(flash.message, flash.type);
                    return;
                }
                if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                    ShenaApp.showNotification(flash.message, flash.type, 5000);
                    return;
                }
                console.warn(flash.message);
            });
        });
    </script>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card warning">
        <div class="stat-card-content">
            <div class="stat-card-info">
                <h3><?php echo $data['stats']['pending'] ?? 0; ?></h3>
                <p>Pending Requests</p>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-card-content">
            <div class="stat-card-info">
                <h3><?php echo $data['stats']['completed'] ?? 0; ?></h3>
                <p>Completed</p>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-card-content">
            <div class="stat-card-info">
                <h3><?php echo $data['stats']['cancelled'] ?? 0; ?></h3>
                <p>Cancelled</p>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card primary">
        <div class="stat-card-content">
            <div class="stat-card-info">
                <h3>KES <?php echo number_format($data['stats']['total_revenue'] ?? 0, 0); ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="/admin/plan-upgrades" class="filters-form">
        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <div class="form-group">
            <label for="from_date">From Date</label>
            <input type="date" class="form-control" id="from_date" name="from_date" 
                   value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="to_date">To Date</label>
            <input type="date" class="form-control" id="to_date" name="to_date" 
                   value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
        </div>
        <button type="submit" class="btn-filter">
            <i class="fas fa-filter"></i> Filter
        </button>
        <a href="/admin/plan-upgrades" class="btn-reset">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>
</div>

<!-- Upgrades Table -->
<div class="table-card">
    <div class="table-card-header">
        <h5>Upgrade Requests</h5>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Member</th>
                    <th>From Package</th>
                    <th>To Package</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['upgrades'])): ?>
                    <?php foreach ($data['upgrades'] as $upgrade): ?>
                        <?php $paymentStatus = isset($upgrade['payment_status']) && $upgrade['payment_status'] !== null && $upgrade['payment_status'] !== '' ? $upgrade['payment_status'] : 'pending'; ?>
                        <?php $reqStatus = isset($upgrade['status']) && $upgrade['status'] !== null && $upgrade['status'] !== '' ? $upgrade['status'] : 'pending'; ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($upgrade['id']); ?></strong></td>
                            <td>
                                <a href="/admin/member/<?php echo $upgrade['member_id']; ?>" class="member-link">
                                    <?php echo htmlspecialchars($upgrade['member_name']); ?>
                                </a>
                                <span class="member-number"><?php echo htmlspecialchars($upgrade['member_number'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?php echo htmlspecialchars($upgrade['from_package']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($upgrade['to_package']); ?>
                                </span>
                            </td>
                            <td><strong>KES <?php echo number_format($upgrade['prorated_amount'], 2); ?></strong></td>
                            <td>
                                <?php $paymentStatus = isset($upgrade['payment_status']) && $upgrade['payment_status'] !== null && $upgrade['payment_status'] !== '' ? $upgrade['payment_status'] : 'pending'; ?>
                                <span class="badge badge-<?php 
                                    echo $paymentStatus === 'completed' ? 'success' : 
                                         ($paymentStatus === 'pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo htmlspecialchars(ucfirst((string)$paymentStatus)); ?>
                                </span>
                            </td>
                            <td>
                                <?php $reqStatus = isset($upgrade['status']) && $upgrade['status'] !== null && $upgrade['status'] !== '' ? $upgrade['status'] : 'pending'; ?>
                                <span class="badge badge-<?php 
                                    echo $reqStatus === 'completed' ? 'success' : 
                                         ($reqStatus === 'pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo htmlspecialchars(ucfirst((string)$reqStatus)); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($upgrade['requested_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php // Dropdown action button with inline quick actions ?>
                                    <?php $upgradeJson = htmlspecialchars(json_encode($upgrade), ENT_QUOTES, 'UTF-8'); ?>
                                    <div class="action-dropdown-root" data-upgrade='<?php echo $upgradeJson; ?>'>
                                            <?php $isPending = (isset($upgrade['status']) && trim(strtolower($upgrade['status'])) === 'pending'); ?>
                                            <button class="btn-action success btn-quick" title="Approve" <?php echo $isPending ? '' : 'disabled'; ?> onclick="if(!this.disabled) processUpgrade(<?php echo $upgrade['id']; ?>, 'approve')">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                            <button class="btn-action danger btn-quick" title="Reject" <?php echo $isPending ? '' : 'disabled'; ?> onclick="if(!this.disabled) processUpgrade(<?php echo $upgrade['id']; ?>, 'reject')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <button class="btn-action info dropdown-toggle" aria-expanded="false" title="More actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-dropdown-menu" role="menu">
                                            <button type="button" class="action-item" onclick="openQuickView(this)">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            <?php if ($reqStatus === 'pending'): ?>
                                                <button type="button" class="action-item" onclick="processUpgrade(<?php echo $upgrade['id']; ?>, 'approve')">
                                                    <i class="fas fa-thumbs-up"></i> Approve
                                                </button>
                                                <button type="button" class="action-item" onclick="processUpgrade(<?php echo $upgrade['id']; ?>, 'reject')">
                                                    <i class="fas fa-ban"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($reqStatus === 'pending' && $paymentStatus === 'completed'): ?>
                                                <button type="button" class="action-item" onclick="processUpgrade(<?php echo $upgrade['id']; ?>, 'complete')">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                                <button type="button" class="action-item" onclick="processUpgrade(<?php echo $upgrade['id']; ?>, 'cancel')">
                                                    <i class="fas fa-times"></i> Cancel & Refund
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No upgrade requests found</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function processUpgrade(id, action) {
    const actionLabels = {
        complete: 'complete',
        cancel: 'cancel and refund',
        approve: 'approve',
        reject: 'reject'
    };

    const actionText = actionLabels[action] || action;
    const confirmMsg = `Are you sure you want to ${actionText} this upgrade request?`;

    const proceed = (extraFields = {}) => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/plan-upgrades/${action}/${id}`;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
        form.appendChild(csrfInput);

        // append any extra fields (e.g., rejection reason)
        for (const k in extraFields) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = k;
            inp.value = extraFields[k];
            form.appendChild(inp);
        }

        document.body.appendChild(form);
        form.submit();
    };

    // If the app has a nicer confirmation UI, use it
    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        // For reject, ask for reason using prompt fallback
        if (action === 'reject') {
            ShenaApp.prompt(
                'Rejection reason (optional)',
                '',
                function(reason) { proceed({ reason: reason || '' }); }
            );
            return;
        }

        ShenaApp.confirmAction(
            confirmMsg,
            function() { proceed(); },
            null,
            { type: 'warning', title: 'Confirm Action', confirmText: 'Proceed' }
        );
        return;
    }

    if (action === 'reject') {
        const reason = prompt('Please enter a reason for rejection (optional):', '');
        if (confirm(confirmMsg)) proceed({ reason: reason || '' });
        return;
    }

    if (confirm(confirmMsg)) {
        proceed();
    }
}

function exportToCSV() {
    window.location.href = '/admin/plan-upgrades/export?<?php echo http_build_query($_GET); ?>';
}
</script>

<!-- Quick View Modal -->
<div id="upgradeQuickViewModal" class="modal" style="display:none;">
    <div class="modal-content">
        <button class="modal-close" onclick="closeQuickView()">&times;</button>
        <h3 id="quickViewTitle">Upgrade Details</h3>
        <div id="quickViewBody"></div>
    </div>
</div>

<style>
/* Dropdown menu */
.action-dropdown-root{position:relative;display:inline-block}
.action-dropdown-root .action-dropdown-menu{display:none;position:absolute;right:0;top:36px;background:#fff;border:1px solid #E5E7EB;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:180px;z-index:50;padding:8px}
.action-dropdown-root .action-dropdown-menu .action-item{display:flex;align-items:center;gap:8px;width:100%;padding:8px;border:none;background:transparent;text-align:left;cursor:pointer;border-radius:6px}
.action-dropdown-root .action-dropdown-menu .action-item:hover{background:#F3F4F6}
.action-dropdown-root.show .action-dropdown-menu{display:block}

/* Quick small buttons next to dropdown */
.btn-quick{width:32px;height:32px;padding:0;margin-right:6px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px}
.btn-action[disabled]{opacity:0.5;cursor:not-allowed}
.btn-action[disabled] .fas{opacity:0.6}
.btn-quick .fas{font-size:13px}

/* Simple modal */
#upgradeQuickViewModal{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:60}
.modal-content{background:#fff;padding:20px;border-radius:12px;max-width:680px;width:90%;position:relative}
.modal-close{position:absolute;right:12px;top:12px;border:none;background:transparent;font-size:22px;cursor:pointer}
#quickViewBody p{margin:8px 0}
</style>

<script>
// Toggle dropdowns
document.addEventListener('click', function(e){
    // close any open dropdown if clicked outside
    document.querySelectorAll('.action-dropdown-root').forEach(function(root){
        if (!root.contains(e.target)) root.classList.remove('show');
    });
});

document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.action-dropdown-root .dropdown-toggle').forEach(function(btn){
        btn.addEventListener('click', function(ev){
            ev.stopPropagation();
            const root = btn.closest('.action-dropdown-root');
            // toggle
            const isOpen = root.classList.contains('show');
            // close others
            document.querySelectorAll('.action-dropdown-root').forEach(r=>r.classList.remove('show'));
            if (!isOpen) root.classList.add('show');
        });
    });
});

function openQuickView(button){
    const root = button.closest('.action-dropdown-root');
    const raw = root.getAttribute('data-upgrade') || '{}';
    let data = {};
    try { data = JSON.parse(raw); } catch(e) { data = {}; }

    const body = document.getElementById('quickViewBody');
    body.innerHTML = '';
    const rows = [
        ['ID', data.id],
        ['Member', data.member_name + ' (' + (data.member_number || 'N/A') + ')'],
        ['From', data.from_package],
        ['To', data.to_package],
        ['Prorated amount', 'KES ' + (parseFloat(data.prorated_amount || 0).toFixed(2))],
        ['Payment status', data.payment_status || 'N/A'],
        ['Status', data.status || 'N/A'],
        ['Requested', data.requested_at]
    ];

    rows.forEach(function(r){
        const p = document.createElement('p');
        p.innerHTML = '<strong>' + r[0] + ':</strong> ' + (r[1] === null || r[1] === undefined ? 'N/A' : r[1]);
        body.appendChild(p);
    });

    document.getElementById('upgradeQuickViewModal').style.display = 'flex';
}

function closeQuickView(){
    document.getElementById('upgradeQuickViewModal').style.display = 'none';
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
