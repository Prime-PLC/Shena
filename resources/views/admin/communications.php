<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .page-header {
        background: linear-gradient(135deg, #7F3D9E 0%, #7F3D9E 100%);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 6px rgba(139, 92, 246, 0.1);
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

    .modern-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #f3f4f6;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
        height: 100%;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 8px rgba(127, 61, 158, 0.1);
        transform: translateY(-2px);
    }

    .stat-card .icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .stat-card .icon-wrapper i {
        font-size: 24px;
    }

    .stat-card.primary .icon-wrapper {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .stat-card.success .icon-wrapper {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .stat-card.info .icon-wrapper {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
    }

    .stat-card.warning .icon-wrapper {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid #f3f4f6;
        padding: 0;
        list-style: none;
    }

    .modern-tabs .tab-item {
        padding: 1rem 1.5rem;
        cursor: pointer;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        margin-bottom: -2px;
    }

    .modern-tabs .tab-item:hover {
        color: #7F3D9E;
    }

    .modern-tabs .tab-item.active {
        color: #7F3D9E;
        border-bottom-color: #7F3D9E;
    }

    .tab-content-panel {
        display: none;
    }

    .tab-content-panel.active {
        display: block;
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

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
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

    .status-badge.sent {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-badge.failed {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .status-badge.pending {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }

    .type-badge.email {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .type-badge.sms {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .action-btn {
        background: none;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .action-btn:hover {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
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
    }

    .modern-btn.primary {
        background: #7F3D9E;
        color: white;
    }

    .modern-btn.primary:hover {
        background: #7F3D9E;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(127, 61, 158, 0.3);
    }

    .modern-btn.success {
        background: #10B981;
        color: white;
    }

    .modern-btn.success:hover {
        background: #059669;
    }

    .modern-btn.secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .modern-btn.secondary:hover {
        background: #e5e7eb;
    }

    .modern-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .modern-modal.active {
        display: flex;
    }

    .modal-content-modern {
        background: white;
        border-radius: 12px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header-modern {
        background: linear-gradient(135deg, #7F3D9E 0%, #7F3D9E 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header-modern h3 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .modal-body-modern {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #9ca3af;
    }

    .filter-group {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .select-control {
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .select-control:hover {
        border-color: #7F3D9E;
    }

    .select-control:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1><i class="fas fa-comments"></i> Communications Hub</h1>
            <p>Manage email and SMS communications with members</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="modern-btn primary" onclick="openModal('emailModal')">
                <i class="fas fa-envelope"></i> Quick Email
            </button>
            <button class="modern-btn success" onclick="openModal('smsModal')">
                <i class="fas fa-sms"></i> Quick SMS
            </button>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="icon-wrapper">
                <i class="fas fa-envelope"></i>
            </div>
            <p class="stat-value"><?php echo count(array_filter($communications ?? [], fn($c) => $c['type'] === 'email')); ?></p>
            <p class="stat-label">Emails Sent</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="icon-wrapper">
                <i class="fas fa-sms"></i>
            </div>
            <p class="stat-value"><?php echo count(array_filter($communications ?? [], fn($c) => $c['type'] === 'sms')); ?></p>
            <p class="stat-label">SMS Sent</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="icon-wrapper">
                <i class="fas fa-calendar"></i>
            </div>
            <p class="stat-value">
                <?php 
                    $thisMonth = array_filter($communications ?? [], fn($c) => 
                        date('Y-m', strtotime($c['sent_at'] ?? 'now')) === date('Y-m')
                    );
                    echo count($thisMonth);
                ?>
            </p>
            <p class="stat-label">This Month</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="icon-wrapper">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p class="stat-value"><?php echo count(array_filter($communications ?? [], fn($c) => ($c['status'] ?? 'sent') === 'failed')); ?></p>
            <p class="stat-label">Failed</p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="modern-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #1f2937; font-size: 1.5rem;">
            Communication History
        </h2>
        <div class="filter-group">
            <select class="select-control" onchange="filterCommunications(this.value)">
                <option value="all">All Types</option>
                <option value="email">Email Only</option>
                <option value="sms">SMS Only</option>
            </select>
            <select class="select-control" onchange="filterByStatus(this.value)">
                <option value="all">All Status</option>
                <option value="sent">Sent</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
            </select>
        </div>
    </div>

    <?php if (empty($communications)): ?>
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h3>No Communications Yet</h3>
            <p>Start sending emails or SMS messages to members</p>
            <button class="modern-btn primary" onclick="openModal('emailModal')" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Send First Message
            </button>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Recipients</th>
                        <th>Subject/Message</th>
                        <th>Status</th>
                        <th>Sent Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($communications as $comm): ?>
                    <tr data-type="<?php echo $comm['type']; ?>" data-status="<?php echo $comm['status'] ?? 'sent'; ?>">
                        <td>
                            <span class="type-badge <?php echo $comm['type']; ?>">
                                <i class="fas fa-<?php echo $comm['type'] === 'email' ? 'envelope' : 'sms'; ?>"></i>
                                <?php echo strtoupper($comm['type']); ?>
                            </span>
                        </td>
                        <td>
                            <div>
                                <strong>
                                    <?php 
                                        if (($comm['recipient_type'] ?? 'individual') === 'all') {
                                            echo 'All Members';
                                        } elseif (($comm['recipient_type'] ?? 'individual') === 'group') {
                                            echo ucfirst($comm['recipient_group'] ?? 'Group') . ' Members';
                                        } else {
                                            echo htmlspecialchars($comm['recipient_name'] ?? 'Individual Member');
                                        }
                                    ?>
                                </strong>
                                <br>
                                <small style="color: #6b7280;"><?php echo $comm['recipient_count'] ?? 1; ?> recipient(s)</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($comm['subject'] ?? 'No Subject'); ?></strong><br>
                                <small style="color: #6b7280;">
                                    <?php echo htmlspecialchars(substr($comm['message'] ?? '', 0, 50) . '...'); ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $comm['status'] ?? 'sent'; ?>">
                                <?php echo ucfirst($comm['status'] ?? 'Sent'); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y H:i', strtotime($comm['sent_at'] ?? 'now')); ?></td>
                        <td>
                            <button class="action-btn" onclick="viewCommunication(<?php echo $comm['id']; ?>)" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Email Modal -->
<div class="modern-modal" id="emailModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-envelope"></i> Send Quick Email</h3>
            <button class="modal-close" onclick="closeModal('emailModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/communications/send-email" method="POST" id="emailForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="form-group">
                    <label for="email-recipient-type">Recipients</label>
                    <select class="form-control" id="email-recipient-type" name="recipient_type" required>
                        <option value="">Select recipient type...</option>
                        <option value="all">All Members</option>
                        <option value="group">Member Group</option>
                        <option value="individual">Individual Member</option>
                    </select>
                </div>

                <div class="form-group" id="email-group-field" style="display: none;">
                    <label for="email-recipient-group">Member Group</label>
                    <select class="form-control" id="email-recipient-group" name="recipient_group">
                        <option value="active">Active Members</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="pending">Pending Members</option>
                    </select>
                </div>

                <div class="form-group" id="email-individual-field" style="display: none;">
                    <label for="email-recipient-id">Select Member</label>
                    <select class="form-control" id="email-recipient-id" name="recipient_id">
                        <!-- Populated via JavaScript -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="email-subject">Subject</label>
                    <input type="text" class="form-control" id="email-subject" name="subject" required placeholder="Enter email subject">
                </div>

                <div class="form-group">
                    <label for="email-message">Message</label>
                    <textarea class="form-control" id="email-message" name="message" required placeholder="Enter your message here..." rows="6"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('emailModal')">Cancel</button>
                    <button type="submit" class="modern-btn primary">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick SMS Modal -->
<div class="modern-modal" id="smsModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-sms"></i> Send Quick SMS</h3>
            <button class="modal-close" onclick="closeModal('smsModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/communications/send-sms" method="POST" id="smsForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="form-group">
                    <label for="sms-recipient-type">Recipients</label>
                    <select class="form-control" id="sms-recipient-type" name="recipient_type" required>
                        <option value="">Select recipient type...</option>
                        <option value="all">All Members</option>
                        <option value="group">Member Group</option>
                        <option value="individual">Individual Member</option>
                    </select>
                </div>

                <div class="form-group" id="sms-group-field" style="display: none;">
                    <label for="sms-recipient-group">Member Group</label>
                    <select class="form-control" id="sms-recipient-group" name="recipient_group">
                        <option value="active">Active Members</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="pending">Pending Members</option>
                    </select>
                </div>

                <div class="form-group" id="sms-individual-field" style="display: none;">
                    <label for="sms-recipient-id">Select Member</label>
                    <select class="form-control" id="sms-recipient-id" name="recipient_id">
                        <!-- Populated via JavaScript -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="sms-message">Message</label>
                    <textarea class="form-control" id="sms-message" name="message" required placeholder="Enter your SMS message..." rows="4" maxlength="160"></textarea>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                        <span id="sms-counter">0</span>/160 characters
                    </small>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('smsModal')">Cancel</button>
                    <button type="submit" class="modern-btn success">
                        <i class="fas fa-paper-plane"></i> Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Close modal on outside click
document.querySelectorAll('.modern-modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modern-modal.active').forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Email recipient type handler
document.getElementById('email-recipient-type')?.addEventListener('change', function() {
    const groupField = document.getElementById('email-group-field');
    const individualField = document.getElementById('email-individual-field');
    
    groupField.style.display = this.value === 'group' ? 'block' : 'none';
    individualField.style.display = this.value === 'individual' ? 'block' : 'none';
});

// SMS recipient type handler
document.getElementById('sms-recipient-type')?.addEventListener('change', function() {
    const groupField = document.getElementById('sms-group-field');
    const individualField = document.getElementById('sms-individual-field');
    
    groupField.style.display = this.value === 'group' ? 'block' : 'none';
    individualField.style.display = this.value === 'individual' ? 'block' : 'none';
});

// SMS character counter
document.getElementById('sms-message')?.addEventListener('input', function() {
    document.getElementById('sms-counter').textContent = this.value.length;
});

// Filter communications by type
function filterCommunications(type) {
    const rows = document.querySelectorAll('.modern-table tbody tr');
    rows.forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter communications by status
function filterByStatus(status) {
    const rows = document.querySelectorAll('.modern-table tbody tr');
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// View communication details (placeholder)
function viewCommunication(id) {
    ShenaApp.showNotification('Communication ID: ' + id, 'info');
}

// Form submission handlers
document.getElementById('emailForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/communications/send-email', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('Email sent successfully!', 'success');
            closeModal('emailModal');
            location.reload();
        } else {
            ShenaApp.showNotification('Failed to send email: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ShenaApp.showNotification('Network error occurred', 'error');
    });
});

document.getElementById('smsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/communications/send-sms', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('SMS sent successfully!', 'success');
            closeModal('smsModal');
            location.reload();
        } else {
            ShenaApp.showNotification('Failed to send SMS: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ShenaApp.showNotification('Network error occurred', 'error');
    });
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
