
<?php
// Ensure CSRF token is set for the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .page-header {
        background: linear-gradient(135deg, #7F3D9E 0%, #7F3D9E 100%);
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

    .stat-card.warning .icon-wrapper {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .stat-card.info .icon-wrapper {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
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

    .modern-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #f3f4f6;
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

    .status-badge.draft {
        background: rgba(107, 114, 128, 0.1);
        color: #6B7280;
    }

    .status-badge.scheduled {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    .status-badge.sending {
        background: rgba(59, 130, 246, 0.1);
        color: #3B82F6;
    }

    .status-badge.completed {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-badge.cancelled {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .status-badge.paused {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
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
        margin: 0 2px;
    }

    .action-btn:hover {
        background: rgba(127, 61, 158, 0.1);
        color: #7F3D9E;
    }

    .action-btn.success:hover {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .action-btn.danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .action-btn.warning:hover {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
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

    .modern-btn.info {
        background: #3B82F6;
        color: white;
    }

    .modern-btn.info:hover {
        background: #2563EB;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
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
        margin-bottom: 1.5rem;
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
        max-width: 700px;
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

    select.form-control {
        cursor: pointer;
    }

    .count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .count-badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .count-badge.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #7F3D9E 0%, #7F3D9E 100%);
        transition: width 0.3s ease;
    }
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1><i class="fas fa-sms"></i> SMS Campaigns</h1>
            <p>Create and manage bulk SMS campaigns for members</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="modern-btn primary" onclick="openModal('createCampaignModal')">
                <i class="fas fa-plus"></i> Create Campaign
            </button>
            <button class="modern-btn success" onclick="openModal('quickSMSModal')">
                <i class="fas fa-sms"></i> Quick SMS
            </button>
            <a href="/admin/communications" class="modern-btn secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="icon-wrapper">
                <i class="fas fa-broadcast-tower"></i>
            </div>
            <p class="stat-value"><?php echo $stats['active_campaigns'] ?? 0; ?></p>
            <p class="stat-label">Active Campaigns</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="icon-wrapper">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['sent_today'] ?? 0); ?></p>
            <p class="stat-label">Sent Today</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="icon-wrapper">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['queue_pending'] ?? 0); ?></p>
            <p class="stat-label">Queue Pending</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="icon-wrapper">
                <i class="fas fa-coins"></i>
            </div>
            <p class="stat-value"><?php echo number_format($stats['sms_credits'] ?? 0); ?></p>
            <p class="stat-label">SMS Credits</p>
        </div>
    </div>
</div>

<!-- Campaigns List -->
<div class="modern-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #1f2937; font-size: 1.5rem;">
            <i class="fas fa-list"></i> SMS Campaigns
        </h2>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <i class="fas fa-sms"></i>
            <h3>No SMS Campaigns Yet</h3>
            <p>Create your first SMS campaign to send bulk messages to members</p>
            <button class="modern-btn primary" onclick="openModal('createCampaignModal')">
                <i class="fas fa-plus"></i> Create First Campaign
            </button>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Audience</th>
                        <th>Recipients</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($campaign['title']); ?></strong><br>
                                <small style="color: #6b7280;"><?php echo date('M j, Y', strtotime($campaign['created_at'])); ?></small>
                            </div>
                        </td>
                        <td><?php echo ucwords(str_replace('_', ' ', $campaign['target_audience'])); ?></td>
                        <td><?php echo number_format($campaign['total_recipients']); ?></td>
                        <td>
                            <div>
                                <span class="count-badge success"><?php echo $campaign['sent_count']; ?> sent</span>
                                <span class="count-badge danger"><?php echo $campaign['failed_count']; ?> failed</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($campaign['total_recipients'] > 0) ? (($campaign['sent_count'] / $campaign['total_recipients']) * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $campaign['status']; ?>">
                                <?php echo ucfirst($campaign['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if ($campaign['scheduled_at']) {
                                echo date('M j, Y H:i', strtotime($campaign['scheduled_at']));
                            } else {
                                echo '<span style="color: #9ca3af;">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="action-btn" onclick="viewCampaign(<?php echo $campaign['id']; ?>)" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($campaign['status'] === 'draft' || $campaign['status'] === 'scheduled'): ?>
                                <button class="action-btn success" onclick="sendCampaign(<?php echo $campaign['id']; ?>)" title="Send Now">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button class="action-btn danger" onclick="cancelCampaign(<?php echo $campaign['id']; ?>)" title="Cancel">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($campaign['status'] === 'sending'): ?>
                                <button class="action-btn warning" onclick="pauseCampaign(<?php echo $campaign['id']; ?>)" title="Pause">
                                    <i class="fas fa-pause"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($campaign['status'] === 'paused'): ?>
                                <button class="action-btn success" onclick="resumeCampaign(<?php echo $campaign['id']; ?>)" title="Resume">
                                    <i class="fas fa-play"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Create Campaign Modal -->
<div class="modern-modal" id="createCampaignModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-plus"></i> Create SMS Campaign</h3>
            <button class="modal-close" onclick="closeModal('createCampaignModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/communications/create-campaign" method="POST" id="createCampaignForm">
                <div class="form-group">
                    <label for="campaign-title">Campaign Title</label>
                    <input type="text" class="form-control" id="campaign-title" name="title" required placeholder="e.g., Payment Reminder - January 2026">
                </div>

                <div class="form-group">
                    <label for="target-audience">Target Audience</label>
                    <select class="form-control" id="target-audience" name="target_audience" required>
                        <option value="">Select audience...</option>
                        <option value="all_members">All Active Members</option>
                        <option value="active_only">Active Members Only</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="defaulters">Payment Defaulters</option>
                        <option value="custom">Custom Selection</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sms-message">SMS Message</label>
                    <textarea class="form-control" id="sms-message" name="message" required placeholder="Enter your SMS message here..." maxlength="160"></textarea>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                        <span id="char-counter">0</span>/160 characters | 
                        Placeholders: {member_name}, {member_number}, {amount_due}
                    </small>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select class="form-control" id="priority" name="priority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule-type">Schedule</label>
                    <select class="form-control" id="schedule-type" name="schedule_type" required>
                        <option value="now">Send Immediately</option>
                        <option value="scheduled">Schedule for Later</option>
                        <option value="draft">Save as Draft</option>
                    </select>
                </div>

                <div class="form-group" id="schedule-datetime-field" style="display: none;">
                    <label for="schedule-datetime">Schedule Date & Time</label>
                    <input type="datetime-local" class="form-control" id="schedule-datetime" name="scheduled_at">
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('createCampaignModal')">Cancel</button>
                    <button type="submit" class="modern-btn primary">
                        <i class="fas fa-save"></i> Create Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick SMS Modal -->
<div class="modern-modal" id="quickSMSModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3><i class="fas fa-sms"></i> Send Quick SMS</h3>
            <button class="modal-close" onclick="closeModal('quickSMSModal')">&times;</button>
        </div>
        <div class="modal-body-modern">
            <form action="/admin/communications/quick-sms" method="POST" id="quickSMSForm">
                                <?php if (isset($_SESSION['csrf_token'])): ?>
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <?php endif; ?>
                <div class="form-group">
                    <label for="quick-recipient-type">Recipients</label>
                    <select class="form-control" id="quick-recipient-type" name="recipient_type" required>
                        <option value="">Select recipient type...</option>
                        <option value="all">All Members</option>
                        <option value="group">Member Group</option>
                        <option value="individual">Individual Member</option>
                    </select>
                </div>

                <div class="form-group" id="quick-group-field" style="display: none;">
                    <label for="quick-recipient-group">Member Group</label>
                    <select class="form-control" id="quick-recipient-group" name="recipient_group">
                        <option value="active">Active Members</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="pending">Pending Members</option>
                    </select>
                </div>

                <div class="form-group" id="quick-individual-field" style="display: none;">
                    <label for="quick-recipient-id">Select Member</label>
                    <select class="form-control" id="quick-recipient-id" name="recipient_id">
                        <!-- Populated via JavaScript -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="quick-message">Message</label>
                    <textarea class="form-control" id="quick-message" name="message" required placeholder="Enter your SMS message..." maxlength="160"></textarea>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                        <span id="quick-char-counter">0</span>/160 characters
                    </small>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="modern-btn secondary" onclick="closeModal('quickSMSModal')">Cancel</button>
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

// Character counters
document.getElementById('sms-message')?.addEventListener('input', function() {
    document.getElementById('char-counter').textContent = this.value.length;
});

document.getElementById('quick-message')?.addEventListener('input', function() {
    document.getElementById('quick-char-counter').textContent = this.value.length;
});

// Schedule type handler
document.getElementById('schedule-type')?.addEventListener('change', function() {
    const datetimeField = document.getElementById('schedule-datetime-field');
    datetimeField.style.display = this.value === 'scheduled' ? 'block' : 'none';
});

// Quick SMS recipient type handler
document.getElementById('quick-recipient-type')?.addEventListener('change', function() {
    const groupField = document.getElementById('quick-group-field');
    const individualField = document.getElementById('quick-individual-field');
    const recipientDropdown = document.getElementById('quick-recipient-id');
    groupField.style.display = this.value === 'group' ? 'block' : 'none';
    individualField.style.display = this.value === 'individual' ? 'block' : 'none';

    if (this.value === 'individual') {
        // Fetch members for dropdown
        recipientDropdown.innerHTML = '<option value="">Loading...</option>';
        fetch('/admin/api/members')
            .then(response => response.json())
            .then(data => {
                recipientDropdown.innerHTML = '<option value="">Select member...</option>';
                if (Array.isArray(data)) {
                    data.forEach(member => {
                        recipientDropdown.innerHTML += `<option value="${member.id}">${member.member_name} (${member.member_number})</option>`;
                    });
                }
            })
            .catch(() => {
                recipientDropdown.innerHTML = '<option value="">Failed to load members</option>';
            });
    } else {
        recipientDropdown.innerHTML = '';
    }
});

// Campaign actions
function viewCampaign(id) {
    window.location.href = '/admin/communications/campaign/' + id;
}

function sendCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/send-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign sending initiated!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to send campaign: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Are you sure you want to send this campaign now?',
            proceed,
            null,
            { type: 'primary', title: 'Send Campaign', confirmText: 'Send Now' }
        );
        return;
    }

    if (confirm('Are you sure you want to send this campaign now?')) {
        proceed();
    }
}

function cancelCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/cancel-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign cancelled successfully!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to cancel campaign: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Are you sure you want to cancel this campaign?',
            proceed,
            null,
            { type: 'warning', title: 'Cancel Campaign', confirmText: 'Cancel Campaign' }
        );
        return;
    }

    if (confirm('Are you sure you want to cancel this campaign?')) {
        proceed();
    }
}

function pauseCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/pause-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign paused!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to pause: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Pause this campaign?',
            proceed,
            null,
            { type: 'warning', title: 'Pause Campaign', confirmText: 'Pause' }
        );
        return;
    }

    if (confirm('Pause this campaign?')) {
        proceed();
    }
}

function resumeCampaign(id) {
    const proceed = () => {
        fetch('/admin/communications/send-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ campaign_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ShenaApp.showNotification('Campaign resumed!', 'success');
                location.reload();
            } else {
                ShenaApp.showNotification('Failed to resume: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ShenaApp.showNotification('Network error occurred', 'error');
        });
    };

    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Resume this campaign?',
            proceed,
            null,
            { type: 'primary', title: 'Resume Campaign', confirmText: 'Resume' }
        );
        return;
    }

    if (confirm('Resume this campaign?')) {
        proceed();
    }
}

// Form submissions
document.getElementById('createCampaignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/communications/create-campaign', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('Campaign created successfully!', 'success');
            closeModal('createCampaignModal');
            location.reload();
        } else {
            ShenaApp.showNotification('Failed to create campaign: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ShenaApp.showNotification('Network error occurred', 'error');
    });
});

document.getElementById('quickSMSForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/communications/quick-sms', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ShenaApp.showNotification('SMS sent successfully!', 'success');
            closeModal('quickSMSModal');
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
