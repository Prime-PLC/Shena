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

    /* Filter Bar */
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-item {
        flex: 1;
        min-width: 200px;
    }

    .filter-label {
        display: block;
        font-size: 11px;
        color: #9CA3AF;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .filter-select {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 14px;
        color: #1F2937;
        background: white;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: #D1D5DB;
    }

    /* Notification List */
    .notification-list {
        background: white;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .notification-item {
        padding: 20px 24px;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
    }

    .notification-item:hover {
        background: #F9FAFB;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background: #F0F9FF;
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #3B82F6;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .notification-icon.info {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .notification-icon.success {
        background: #D1FAE5;
        color: #10B981;
    }

    .notification-icon.warning {
        background: #FED7AA;
        color: #F97316;
    }

    .notification-icon.error {
        background: #FEE2E2;
        color: #EF4444;
    }

    .notification-content {
        flex: 1;
    }

    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .notification-title {
        font-size: 15px;
        font-weight: 600;
        color: #1F2937;
    }

    .notification-time {
        font-size: 12px;
        color: #9CA3AF;
    }

    .notification-message {
        font-size: 13px;
        color: #6B7280;
        line-height: 1.6;
        margin-bottom: 8px;
    }

    .notification-meta {
        display: flex;
        gap: 16px;
        font-size: 12px;
        color: #9CA3AF;
    }

    .notification-meta i {
        margin-right: 4px;
    }

    .notification-actions {
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .notification-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #7F3D9E;
        text-decoration: none;
        padding: 6px 10px;
        border-radius: 999px;
        background: #F3E8FF;
    }

    .notification-action:hover {
        background: #EDE9FE;
        color: #6D28D9;
    }

    .action-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-icon-btn:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
        background: #F9FAFB;
    }

    .mark-all-btn {
        background: #F3E8FF;
        color: #7F3D9E;
        border: none;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
    }

    .mark-all-btn:hover {
        background: #EDE9FE;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
        color: #9CA3AF;
    }

    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #6B7280;
    }

    .empty-state p {
        font-size: 13px;
        color: #9CA3AF;
        margin: 0;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">System Notifications</h1>
    <p class="page-subtitle">Track communication logs, system events, and administrative alerts</p>
    <input type="hidden" id="csrfToken" value="<?php echo $csrf_token ?? ''; ?>">
    <button class="mark-all-btn" onclick="markAllAsRead()">
        <i class="fas fa-check-double"></i> Mark All Read
    </button>
</div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-item">
            <label class="filter-label">Category</label>
            <select class="filter-select" id="categoryFilter">
                <option value="all">All Categories</option>
                <option value="membership">Membership</option>
                <option value="payment">Payment</option>
                <option value="upgrades">Upgrades</option>
                <option value="commissions">Commissions</option>
                <option value="claims">Claims</option>
                <option value="email">Email</option>
                <option value="sms">SMS</option>
                <option value="system">System</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="filter-label">Type</label>
            <select class="filter-select" id="typeFilter">
                <option value="all">All Types</option>
                <option value="success">Success</option>
                <option value="warning">Warning</option>
                <option value="info">Info</option>
                <option value="error">Error</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="filter-label">Date Range</label>
            <select class="filter-select" id="dateFilter">
                <option value="today">Today</option>
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
                <option value="all">All Time</option>
            </select>
        </div>
    </div>

    <!-- Notification List -->
    <div class="notification-list">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo !empty($notification['read']) ? '' : 'unread'; ?>"
                     data-id="<?php echo (int)$notification['id']; ?>">
                    <div class="notification-icon <?= $notification['type'] ?? 'info' ?>">
                        <i class="fas fa-<?= $notification['icon'] ?? 'bell' ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <div class="notification-title"><?= htmlspecialchars($notification['title']) ?></div>
                            <div class="notification-time"><?= $notification['time'] ?></div>
                        </div>
                        <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                        <div class="notification-meta">
                            <span><i class="fas fa-user"></i><?= $notification['recipient'] ?? 'N/A' ?></span>
                            <span><i class="fas fa-tag"></i><?= $notification['category'] ?? 'General' ?></span>
                        </div>
                        <div class="notification-actions">
                            <?php if (!empty($notification['action_url']) && !empty($notification['action_text'])): ?>
                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="notification-action">
                                    <?php echo htmlspecialchars($notification['action_text']); ?>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (empty($notification['read'])): ?>
                                <button class="action-icon-btn" onclick="markAsRead(<?php echo (int)$notification['id']; ?>)" title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell"></i>
                <h3>No Notifications Yet</h3>
                <p>System notifications will appear here when activity occurs</p>
            </div>
        <?php endif; ?>
    </div>

<script>
// Filter functionality
document.getElementById('categoryFilter').addEventListener('change', filterNotifications);
document.getElementById('typeFilter').addEventListener('change', filterNotifications);
document.getElementById('dateFilter').addEventListener('change', filterNotifications);

function filterNotifications() {
    const category = document.getElementById('categoryFilter').value;
    const type = document.getElementById('typeFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    // Reload page with filters
    const params = new URLSearchParams();
    if (category !== 'all') params.append('category', category);
    if (type !== 'all') params.append('type', type);
    if (date !== 'all') params.append('date', date);
    
    const queryString = params.toString();
    window.location.href = '/admin/notifications' + (queryString ? '?' + queryString : '');
}

function postNotificationAction(url, payload = {}) {
    const csrfToken = document.getElementById('csrfToken')?.value;
    const formData = new FormData();
    formData.append('csrf_token', csrfToken || '');
    Object.keys(payload).forEach(key => formData.append(key, payload[key]));
    return fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => response.json());
}

function markAsRead(id) {
    postNotificationAction('/admin/notifications/mark-read', { id: id })
        .then(data => {
            if (!data.success) {
                ShenaApp.showNotification(data.message || 'Failed to mark notification as read.', 'error');
                return;
            }

            const item = document.querySelector(`.notification-item[data-id="${id}"]`);
            if (item) {
                item.classList.remove('unread');
                const actionBtn = item.querySelector('.action-icon-btn');
                if (actionBtn) {
                    actionBtn.remove();
                }
            }
        })
        .catch(() => ShenaApp.showNotification('Failed to mark notification as read.', 'error'));
}

function markAllAsRead() {
    postNotificationAction('/admin/notifications/mark-all-read')
        .then(data => {
            if (!data.success) {
                ShenaApp.showNotification(data.message || 'Failed to mark notifications as read.', 'error');
                return;
            }

            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const actionBtn = item.querySelector('.action-icon-btn');
                if (actionBtn) {
                    actionBtn.remove();
                }
            });
        })
        .catch(() => ShenaApp.showNotification('Failed to mark notifications as read.', 'error'));
}

document.querySelectorAll('.notification-action').forEach(link => {
    link.addEventListener('click', function (event) {
        const item = this.closest('.notification-item');
        const id = item?.dataset.id;
        if (!id || !item?.classList.contains('unread')) {
            return;
        }

        event.preventDefault();
        postNotificationAction('/admin/notifications/mark-read', { id: id })
            .finally(() => {
                window.location.href = this.getAttribute('href');
            });
    });
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
