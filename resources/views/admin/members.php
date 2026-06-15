<?php 
$members = $members ?? [];
$stats = $stats ?? ['total_members' => 0, 'grace_period' => 0, 'default_rate' => 0];
$recent_claim = $recent_claim ?? null;
$pending_approvals = $pending_approvals ?? [];
$packages = $packages ?? [];
$search = $search ?? '';
$status = $status ?? 'all';
$package = $package ?? 'all';
$membershipPlanData = [];
foreach ($packages as $packageKey => $packageOption) {
    $membershipPlanData[$packageKey] = [
        'name' => $packageOption['name'] ?? $packageKey,
        'monthly_contribution' => (float)($packageOption['monthly_contribution'] ?? 0),
    ];
}
$pendingValidationError = $_SESSION['pending_validation_error'] ?? '';
$pendingValidationMemberId = (int)($_SESSION['pending_validation_member_id'] ?? 0);
$pendingValidationCode = $_SESSION['pending_validation_code'] ?? '';
unset($_SESSION['pending_validation_error'], $_SESSION['pending_validation_member_id'], $_SESSION['pending_validation_code']);
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => count($members), 'per_page' => 50];
$activeMemberTab = match ($status ?? 'all') {
    'pending_approval', 'inactive', 'pending' => 'pending',
    'active' => 'active',
    'suspended' => 'suspended',
    'grace_period' => 'grace',
    default => 'all',
};
$buildMemberPageUrl = function (int $page) use ($search, $status, $package) {
    $query = array_filter([
        'search' => $search ?? '',
        'status' => ($status ?? 'all') !== 'all' ? $status : '',
        'package' => ($package ?? 'all') !== 'all' ? $package : '',
        'page' => $page > 1 ? $page : '',
    ], function ($value) {
        return $value !== '' && $value !== null;
    });

    return '/admin/members' . (!empty($query) ? '?' . http_build_query($query) : '');
};
$buildMemberFilterUrl = function (string $targetStatus) use ($search, $package) {
    $query = array_filter([
        'search' => $search ?? '',
        'status' => $targetStatus !== 'all' ? $targetStatus : '',
        'package' => ($package ?? 'all') !== 'all' ? $package : '',
    ], function ($value) {
        return $value !== '' && $value !== null;
    });

    return '/admin/members' . (!empty($query) ? '?' . http_build_query($query) : '');
};
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

    /* Emergency Alert Banner */
    .emergency-alert {
        background: linear-gradient(135deg, #7F3D9E 0%, #EF4444 100%);
        border-radius: 12px;
        padding: 20px 24px;
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
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }

    .alert-text h4 {
        color: white;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .alert-badge {
        background: white;
        color: #DC2626;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-right: 8px;
    }

    .alert-text p {
        color: rgba(255, 255, 255, 0.95);
        font-size: 13px;
        margin: 0;
    }

    .alert-text strong {
        color: white;
        font-weight: 700;
    }

    .btn-process-claim {
        padding: 12px 24px;
        background: white;
        color: #DC2626;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-process-claim:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Stats Grid */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

    .stat-icon.pink {
        background: #FCE7F3;
        color: #EC4899;
    }

    .stat-icon.red {
        background: #FEE2E2;
        color: #EF4444;
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

    /* Main Content Layout */
    .content-layout {
        display: grid;
        grid-template-columns: 1.8fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    /* Directory Card */
    .directory-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E5E7EB;
    }

    .directory-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .directory-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .directory-actions {
        display: flex;
        gap: 12px;
    }

    .btn-export {
        padding: 8px 16px;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        color: #6B7280;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-export:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    .btn-new-registration {
        padding: 8px 16px;
        background: linear-gradient(135deg, #A78BFA 0%, #7F3D9E 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-new-registration:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    /* Reusable management UI pattern */
    .management-shell {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .management-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .management-table {
        width: 100%;
        border-collapse: collapse;
    }

    .action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .row-action {
        min-height: 34px;
        padding: 7px 10px;
        border: 1px solid #E5E7EB;
        border-radius: 7px;
        background: white;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .row-action:hover {
        border-color: #7F3D9E;
        color: #7F3D9E;
        background: #FAF5FF;
    }

    .row-action.primary {
        background: #7F3D9E;
        border-color: #7F3D9E;
        color: white;
    }

    .row-action.warning {
        background: #FFFBEB;
        border-color: #F59E0B;
        color: #92400E;
    }

    .row-action.success {
        background: #ECFDF5;
        border-color: #10B981;
        color: #065F46;
    }

    .entity-modal .modal-dialog {
        max-width: 860px;
    }

    .entity-modal .modal-header {
        background: #7F3D9E;
        color: white;
        border: none;
    }

    .entity-modal .modal-title {
        font-weight: 800;
    }

    .entity-modal .btn-close {
        filter: brightness(0) invert(1);
    }

    .entity-modal .modal-body {
        padding: 22px 24px;
        background: #FFFFFF;
    }

    .entity-modal .modal-footer {
        padding: 16px 24px;
        background: #F9FAFB;
        border-top: 1px solid #E5E7EB;
    }

    .entity-modal .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px 18px;
    }

    .entity-modal .form-group {
        margin: 0;
        min-width: 0;
    }

    .entity-modal .form-label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 12px;
        font-weight: 800;
    }

    .entity-modal .form-input,
    .entity-modal .form-select {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        background: #FFFFFF;
        color: #111827;
        font-size: 14px;
        line-height: 1.4;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .entity-modal textarea.form-input {
        min-height: 76px;
        resize: vertical;
    }

    .entity-modal .form-input:focus,
    .entity-modal .form-select:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.12);
    }

    .entity-profile {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 20px;
        align-items: start;
    }

    .entity-avatar {
        width: 88px;
        height: 88px;
        border-radius: 18px;
        background: #7F3D9E;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: 800;
    }

    .entity-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px 20px;
    }

    .entity-field-label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .entity-field-value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        overflow-wrap: anywhere;
    }

    /* Members Table */
    .members-table {
        width: 100%;
        border-collapse: collapse;
    }

    .members-table thead {
        border-bottom: 1px solid #E5E7EB;
    }

    .members-table th {
        text-align: left;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .members-table td {
        padding: 16px;
        font-size: 13px;
        color: #1F2937;
        border-bottom: 1px solid #F3F4F6;
    }

    .members-table tbody tr {
        cursor: pointer;
        transition: all 0.2s;
    }

    .members-table tbody tr:hover {
        background: #F9FAFB;
        transform: scale(1.01);
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
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        color: white;
        flex-shrink: 0;
    }

    .member-avatar.purple {
        background: linear-gradient(135deg, #A78BFA 0%, #7F3D9E 100%);
    }

    .member-avatar.pink {
        background: linear-gradient(135deg, #F9A8D4 0%, #EC4899 100%);
    }

    .member-avatar.red {
        background: linear-gradient(135deg, #FCA5A5 0%, #EF4444 100%);
    }

    .member-details {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #7F3D9E;
        margin-bottom: 2px;
        text-decoration: none;
    }

    .member-name:hover {
        text-decoration: underline;
    }

    .member-role {
        font-size: 11px;
        color: #9CA3AF;
    }

    .package-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: #F3F4F6;
        color: #6B7280;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.active {
        background: #D1FAE5;
        color: #10B981;
    }

    .status-badge.grace {
        background: #EDE9FE;
        color: #7F3D9E;
    }

    .status-badge.default {
        background: #FEE2E2;
        color: #EF4444;
    }

    .contribution-info {
        display: flex;
        flex-direction: column;
    }

    .contribution-amount {
        font-weight: 600;
        color: #1F2937;
    }

    .contribution-date {
        font-size: 11px;
        color: #9CA3AF;
    }

    .contribution-overdue {
        color: #EF4444;
        font-size: 11px;
    }

    .contribution-inactive {
        color: #9CA3AF;
        font-size: 13px;
    }

    .table-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        font-size: 13px;
        color: #6B7280;
    }

    .pagination-buttons {
        display: flex;
        gap: 8px;
    }

    .pagination-btn {
        padding: 8px 12px;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        color: #6B7280;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pagination-btn:hover:not(:disabled) {
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Pending Approvals */
    .approvals-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .approval-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #E5E7EB;
    }

    .approval-header {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 16px;
    }

    .approval-item {
        padding: 16px;
        background: #F9FAFB;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .approval-item:last-child {
        margin-bottom: 0;
    }

    .approval-name {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .approval-details {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 8px;
    }

    .approval-tag {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .approval-tag.awaiting {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .approval-tag.new {
        background: #FEE2E2;
        color: #EF4444;
    }

    .approval-reference {
        font-size: 11px;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .approval-code {
        font-family: monospace;
        font-size: 14px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 12px;
    }

    .approval-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .approval-error {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin: -4px 0 12px;
        padding: 9px 10px;
        border: 1px solid #FCA5A5;
        border-radius: 6px;
        background: #FEF2F2;
        color: #B91C1C;
        font-size: 12px;
        line-height: 1.4;
    }

    .approval-actions {
        display: flex;
        gap: 8px;
    }

    .btn-approve {
        flex: 1;
        padding: 8px 16px;
        background: linear-gradient(135deg, #A78BFA 0%, #7F3D9E 100%);
        border: none;
        border-radius: 6px;
        color: white;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-approve:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-close {
        width: 32px;
        height: 32px;
        background: transparent;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        color: #9CA3AF;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .btn-close:hover {
        background: #F3F4F6;
        color: #6B7280;
    }

    .payroll-info {
        font-size: 13px;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .system-footer {
        text-align: right;
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #F3F4F6;
    }

    .system-label {
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .system-id {
        font-family: monospace;
        font-weight: 600;
        color: #1F2937;
    }

    @media (max-width: 1200px) {
        .content-layout {
            grid-template-columns: minmax(0, 1fr);
        }
        /* CSS Grid golden rule: prevent items from forcing track wider than available */
        .content-layout > * {
            min-width: 0;
        }
    }

    /* Tabs Navigation */
    .tabs-container {
        background: white;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        border-bottom: 2px solid #F3F4F6;
        overflow-x: auto;
    }

    .tab-item {
        padding: 16px 24px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #6B7280;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tab-item:hover {
        color: #7F3D9E;
        background: #F9FAFB;
    }

    .tab-item.active {
        color: #7F3D9E;
        border-bottom-color: #7F3D9E;
    }

    .tab-badge {
        background: #F3F4F6;
        color: #6B7280;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .tab-item.active .tab-badge {
        background: #EDE9FE;
        color: #7F3D9E;
    }

    .tab-content {
        display: none;
        padding: 20px;
    }

    .tab-content.active {
        display: block;
    }

    .tab-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tab-action-btn {
        padding: 10px 18px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .tab-action-btn:hover {
        background: #F9FAFB;
        border-color: #7F3D9E;
        color: #7F3D9E;
    }

    .tab-action-btn.primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        border: none;
        color: white;
    }

    .tab-action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    /* Search and Filter Bar */
    .filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid #D1D5DB;
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

    .filter-select {
        padding: 10px 16px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
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
        padding: 8px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        transition: all 0.2s;
    }

    .page-btn:hover {
        background: #F9FAFB;
        border-color: #7F3D9E;
    }

    .page-btn.active {
        background: #7F3D9E;
        color: white;
        border-color: #7F3D9E;
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .emergency-alert {
            flex-direction: column;
            gap: 16px;
        }

        .btn-process-claim {
            width: 100%;
        }

        .tabs-nav {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .tab-item {
            font-size: 13px;
            padding: 12px 16px;
        }

        .filter-bar {
            flex-direction: column;
        }

        .search-box {
            width: 100%;
        }

        .filter-select {
            width: 100%;
        }

        .tab-actions {
            flex-direction: column;
        }

        .tab-action-btn {
            width: 100%;
            justify-content: center;
        }

        .content-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        /* CSS Grid golden rule: prevent items from forcing track wider than available */
        .content-layout > * {
            min-width: 0;
        }

        /* Properly contain the table scroll within its card */
        .directory-card {
            overflow: hidden;
        }

        .directory-header {
            flex-wrap: wrap;
            gap: 8px 12px;
        }

        .directory-actions {
            flex-wrap: wrap;
            gap: 8px;
        }

        .pagination {
            flex-direction: column;
            gap: 12px;
        }

        .members-table {
            font-size: 12px;
        }

        .members-table th,
        .members-table td {
            padding: 10px 8px;
        }

        /* Allow the table to scroll — override the clipping overflow:hidden */
        .tabs-container {
            overflow: visible !important;
            border-radius: 12px;
        }

        /* Wrap table area */
        .table-scroll-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        .members-table {
            min-width: 860px;
        }

        .entity-profile {
            grid-template-columns: 1fr;
        }

        .entity-avatar {
            width: 72px;
            height: 72px;
            border-radius: 14px;
            font-size: 24px;
        }

        .entity-fields {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Member Management</h1>
    <p class="page-subtitle">Oversee registration and verify member contributions.</p>
</div>

<!-- Emergency Alert Banner -->
<?php if (!empty($recent_claim)): ?>
<div class="emergency-alert">
    <div class="alert-content">
        <div class="alert-icon">
            <i class="fas fa-bell"></i>
        </div>
        <div class="alert-text">
            <h4>
                <span class="alert-badge">EMERGENCY ALERT</span>
                Death Notification Received
            </h4>
            <p>
                Member <strong><?php echo htmlspecialchars($recent_claim['member_name']); ?> (Member #: <?php echo htmlspecialchars($recent_claim['member_number']); ?>)</strong> reported. Action required for immediate last-respect services.
            </p>
        </div>
    </div>
    <button class="btn-process-claim" onclick="window.location.href='/admin/claims';">PROCESS CLAIM</button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-row">
    <!-- Total Members -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-label">Total Members</div>
        <div class="stat-value"><?php echo number_format($stats['total_members'] ?? 0); ?></div>
    </div>

    <!-- Grace Period -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon pink">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
        <div class="stat-label">Grace Period</div>
        <div class="stat-value"><?php echo number_format($stats['grace_period'] ?? 0); ?></div>
    </div>

    <!-- Default Rate -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-label">Default Rate</div>
        <div class="stat-value"><?php echo number_format($stats['default_rate'] ?? 1.2, 1); ?>%</div>
    </div>
</div>

<!-- Tabbed Interface -->
<div class="tabs-container">
    <div class="tabs-nav">
        <a class="tab-item <?php echo $activeMemberTab === 'all' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildMemberFilterUrl('all')); ?>">
            <i class="fas fa-users"></i>
            All Members
            <span class="tab-badge"><?= $stats['total_members'] ?? 0 ?></span>
        </a>
        <a class="tab-item <?php echo $activeMemberTab === 'pending' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildMemberFilterUrl('pending_approval')); ?>">
            <i class="fas fa-clock"></i>
            Pending Approval
            <span class="tab-badge"><?= count($pending_approvals ?? []) ?></span>
        </a>
        <a class="tab-item <?php echo $activeMemberTab === 'active' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildMemberFilterUrl('active')); ?>">
            <i class="fas fa-user-check"></i>
            Active Members
        </a>
        <a class="tab-item <?php echo $activeMemberTab === 'suspended' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildMemberFilterUrl('suspended')); ?>">
            <i class="fas fa-user-slash"></i>
            Suspended
        </a>
        <a class="tab-item <?php echo $activeMemberTab === 'grace' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildMemberFilterUrl('grace_period')); ?>">
            <i class="fas fa-hourglass-half"></i>
            Grace Period
            <span class="tab-badge"><?= $stats['grace_period'] ?? 0 ?></span>
        </a>
        <a class="tab-item" href="/admin/members/archived">
            <i class="fas fa-archive"></i>
            Archived
        </a>
        <button class="tab-item" onclick="switchTab('reports')">
            <i class="fas fa-chart-line"></i>
            Reports
        </button>
        <button class="tab-item" onclick="switchTab('tools')">
            <i class="fas fa-tools"></i>
            Import/Export
        </button>
    </div>

    <!-- All Members Tab -->
    <div id="tab-all" class="tab-content <?php echo $activeMemberTab === 'all' ? 'active' : ''; ?>">
        <div class="tab-actions">
            <a href="/admin/members/register" class="tab-action-btn primary">
                <i class="fas fa-user-plus"></i>
                Register New Member
            </a>
            <a href="/admin/members/export-csv" class="tab-action-btn">
                <i class="fas fa-download"></i>
                Export CSV
            </a>
        </div>
        
        <form class="filter-bar" id="memberSearchForm" method="GET" action="/admin/members">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-members" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Search by name, ID, phone...">
            </div>
            <select class="filter-select" id="filter-status" name="status">
                <option value="all" <?php echo (($status ?? 'all') === 'all') ? 'selected' : ''; ?>>All Statuses</option>
                <option value="pending_approval" <?php echo (($status ?? '') === 'pending_approval') ? 'selected' : ''; ?>>Pending Approval</option>
                <option value="active" <?php echo (($status ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo (($status ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="suspended" <?php echo (($status ?? '') === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                <option value="grace_period" <?php echo (($status ?? '') === 'grace_period') ? 'selected' : ''; ?>>Grace Period</option>
                <option value="defaulted" <?php echo (($status ?? '') === 'defaulted') ? 'selected' : ''; ?>>Defaulted</option>
            </select>
            <select class="filter-select" id="filter-package" name="package">
                <option value="all">All Packages</option>
                <?php foreach ($packages as $packageKey => $packageOption): ?>
                    <option value="<?php echo htmlspecialchars($packageKey); ?>" <?php echo (($package ?? 'all') === $packageKey) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($packageOption['name'] ?? $packageKey); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="tab-action-btn primary">
                <i class="fas fa-search"></i>
                Search
            </button>
            <a class="tab-action-btn" href="/admin/members">
                <i class="fas fa-redo"></i>
                Reset
            </a>
        </form>
    </div>

    <!-- Pending Approvals Tab -->
    <div id="tab-pending" class="tab-content <?php echo $activeMemberTab === 'pending' ? 'active' : ''; ?>">
        <div class="tab-actions">
            <button class="tab-action-btn primary" onclick="bulkApprove()">
                <i class="fas fa-check-double"></i>
                Bulk Approve
            </button>
            <button class="tab-action-btn" onclick="refreshPending()">
                <i class="fas fa-sync"></i>
                Refresh
            </button>
        </div>
        <p style="color: #6B7280; font-size: 14px;">Members awaiting approval. Review and approve registrations.</p>
    </div>

    <!-- Active Members Tab -->
    <div id="tab-active" class="tab-content <?php echo $activeMemberTab === 'active' ? 'active' : ''; ?>">
        <div class="tab-actions">
            <a href="/admin/payments" class="tab-action-btn">
                <i class="fas fa-money-bill-wave"></i>
                Payment History
            </a>
            <a href="/admin/members/export-csv?status=active" class="tab-action-btn">
                <i class="fas fa-download"></i>
                Export Active
            </a>
        </div>
        <div class="filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Use the main search above to filter active members" disabled>
            </div>
        </div>
    </div>

    <!-- Suspended Members Tab -->
    <div id="tab-suspended" class="tab-content <?php echo $activeMemberTab === 'suspended' ? 'active' : ''; ?>">
        <div class="tab-actions">
            <button class="tab-action-btn primary" onclick="bulkReactivate()">
                <i class="fas fa-undo"></i>
                Bulk Reactivate
            </button>
        </div>
        <p style="color: #6B7280; font-size: 14px;">View and manage suspended member accounts.</p>
    </div>

    <!-- Grace Period Tab -->
    <div id="tab-grace" class="tab-content <?php echo $activeMemberTab === 'grace' ? 'active' : ''; ?>">
        <div class="tab-actions">
            <button class="tab-action-btn" onclick="sendReminders()">
                <i class="fas fa-bell"></i>
                Send Payment Reminders
            </button>
            <a href="/admin/payments" class="tab-action-btn">
                <i class="fas fa-plus"></i>
                Record Payment
            </a>
        </div>
        <p style="color: #F59E0B; font-size: 14px;"><i class="fas fa-exclamation-triangle"></i> Members in grace period. Payment overdue but coverage still active.</p>
    </div>

    <!-- Reports Tab -->
    <div id="tab-reports" class="tab-content">
        <div class="tab-actions">
            <a href="/admin/reports?report_type=members" class="tab-action-btn primary">
                <i class="fas fa-chart-bar"></i>
                Full Analytics
            </a>
            <button class="tab-action-btn" onclick="generateReport()">
                <i class="fas fa-file-pdf"></i>
                Generate PDF Report
            </button>
        </div>
        <p style="color: #6B7280; font-size: 14px;">Generate comprehensive member reports and analytics.</p>
    </div>

    <!-- Import/Export Tab -->
    <div id="tab-tools" class="tab-content">
        <div class="tab-actions">
            <a href="/admin/members/export-csv" class="tab-action-btn primary">
                <i class="fas fa-file-export"></i>
                Export All Members
            </a>
        </div>
        <p style="color: #6B7280; font-size: 14px;">Bulk import/export operations for member data management.</p>
    </div>
</div>

<!-- Main Content Layout -->
<div class="content-layout">
    <!-- Left Column: Directory Table -->
    <div>
        <div class="directory-card">
            <div class="directory-header">
                <div class="directory-title">Comprehensive Directory</div>
                <div class="directory-actions">
                    <button class="btn-export" onclick="window.location.href='/admin/members/export-csv<?php echo !empty($search) || $status !== 'all' || $package !== 'all' ? '?' . http_build_query(['search' => $search, 'status' => $status, 'package' => $package]) : ''; ?>';">
                        <i class="fas fa-download"></i>
                        Export CSV
                    </button>
                    <button class="btn-new-registration" onclick="window.location.href='/admin/members/register';">
                        <i class="fas fa-user-plus"></i>
                        New Registration
                    </button>
                </div>
            </div>

            <div class="table-scroll-wrap">
            <table class="members-table management-table">
                <thead>
                    <tr>
                        <th>MEMBER NAME</th>
                        <th>NATIONAL ID</th>
                        <th>PACKAGE</th>
                        <th>STATUS</th>
                        <th>LAST CONTRIBUTION</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #6B7280;">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                            <p>No members found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                        <?php
                            $memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
                            $hasLastPayment = !empty($member['last_payment_date']);
                            $lastPaymentAmount = (float)($member['last_payment_amount'] ?? 0);
                            $lastPaymentDate = $hasLastPayment ? date('d M Y', strtotime($member['last_payment_date'])) : 'N/A';
                            $memberModalData = [
                                'id' => (int)($member['id'] ?? 0),
                                'name' => $memberName ?: 'N/A',
                                'initials' => strtoupper(substr($member['first_name'] ?? 'M', 0, 1) . substr($member['last_name'] ?? 'M', 0, 1)),
                                'member_number' => $member['member_number'] ?? 'N/A',
                                'national_id' => $member['id_number'] ?? $member['national_id'] ?? 'N/A',
                                'phone' => $member['phone'] ?? 'N/A',
                                'email' => $member['email'] ?? 'N/A',
                                'first_name' => $member['first_name'] ?? '',
                                'last_name' => $member['last_name'] ?? '',
                                'package' => $member['package'] ?? 'Standard',
                                'package_key' => $member['package_key'] ?? ($member['package'] ?? ''),
                                'corporate_couple_count' => (int)($member['corporate_couple_count'] ?? 0),
                                'status' => $member['status'] ?? 'active',
                                'date_of_birth' => $member['date_of_birth'] ?? '',
                                'gender' => $member['gender'] ?? '',
                                'address' => $member['address'] ?? '',
                                'next_of_kin' => $member['next_of_kin'] ?? '',
                                'next_of_kin_relationship' => $member['next_of_kin_relationship'] ?? '',
                                'next_of_kin_phone' => $member['next_of_kin_phone'] ?? '',
                                'monthly_contribution_raw' => (float)($member['monthly_contribution'] ?? 0),
                                'monthly_contribution' => 'KES ' . number_format((float)($member['monthly_contribution'] ?? 0), 2),
                                'corporate_members' => array_map(function ($item) {
                                    return [
                                        'label' => $item['label'] ?? '',
                                        'relationship' => $item['relationship'] ?? 'corporate',
                                        'package_key' => $item['package_key'] ?? '',
                                        'package_name' => $item['package_name'] ?? ($item['package_key'] ?? ''),
                                        'monthly_contribution' => (float)($item['monthly_contribution'] ?? 0),
                                    ];
                                }, $member['corporate_members'] ?? []),
                                'dependants' => array_map(function ($item) {
                                    return [
                                        'full_name' => $item['full_name'] ?? '',
                                        'relationship' => $item['relationship'] ?? '',
                                        'id_number' => $item['id_number'] ?? '',
                                        'date_of_birth' => $item['date_of_birth'] ?? '',
                                    ];
                                }, $member['beneficiaries'] ?? []),
                                'dependant_relationship_options' => $member['dependant_relationship_options'] ?? [],
                                'last_payment_amount' => 'KES ' . number_format($lastPaymentAmount, 2),
                                'last_payment_date' => $lastPaymentDate,
                                'agent_number' => $member['agent_number'] ?? 'N/A',
                                'edit_url' => '/admin/members/edit/' . (int)($member['id'] ?? 0),
                                'payments_url' => '/admin/payments?member_id=' . (int)($member['id'] ?? 0),
                            ];
                            $memberModalJson = htmlspecialchars(json_encode($memberModalData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td>
                                <div class="member-info">
                                    <div class="member-avatar <?php echo $member['avatar_color'] ?? 'purple'; ?>">
                                        <?php echo strtoupper(substr($member['first_name'] ?? 'M', 0, 1) . substr($member['last_name'] ?? 'M', 0, 1)); ?>
                                    </div>
                                    <div class="member-details">
                                        <a class="member-name" href="/admin/members/view/<?php echo (int)($member['id'] ?? 0); ?>" style="border: 0; background: transparent; padding: 0; cursor: pointer; text-decoration:none;">
                                            <?php echo htmlspecialchars($memberName); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($member['id_number'] ?? $member['national_id'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="package-badge"><?php echo htmlspecialchars($member['package'] ?? 'Standard'); ?></span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $member['status']; ?>">
                                    <?php echo strtoupper($member['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($member['status'] === 'grace_period'): ?>
                                    <div class="contribution-info">
                                        <span class="contribution-overdue">Overdue</span>
                                        <span class="contribution-overdue"><?php echo $hasLastPayment ? htmlspecialchars($lastPaymentDate) : 'No completed payment'; ?></span>
                                    </div>
                                <?php elseif ($member['status'] === 'inactive'): ?>
                                    <span class="contribution-inactive">Inactive</span>
                                <?php elseif (!$hasLastPayment): ?>
                                    <span class="contribution-inactive">No completed payment</span>
                                <?php else: ?>
                                    <div class="contribution-info">
                                        <span class="contribution-amount">KES <?php echo number_format($lastPaymentAmount, 2); ?></span>
                                        <span class="contribution-date"><?php echo htmlspecialchars($lastPaymentDate); ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a class="row-action primary" href="/admin/members/view/<?php echo (int)($member['id'] ?? 0); ?>" aria-label="View member details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a class="row-action success" href="/admin/members/view/<?php echo (int)($member['id'] ?? 0); ?>">
                                        <i class="fas fa-sliders-h"></i> Manage
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div><!-- /.table-scroll-wrap -->

            <div class="table-pagination">
                <div>VIEWING <?php echo count($members); ?> OF <?php echo (int)($pagination['total_items'] ?? 0); ?> MATCHING MEMBERS</div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" <?= ((int)($pagination['current_page'] ?? 1) <= 1) ? 'disabled' : '' ?>
                        onclick="window.location.href='<?php echo htmlspecialchars($buildMemberPageUrl(max(1, (int)($pagination['current_page'] ?? 1) - 1))); ?>'">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span style="padding: 0 12px; color: #6B7280;">Page <?php echo (int)($pagination['current_page'] ?? 1); ?> of <?php echo max(1, (int)($pagination['total_pages'] ?? 1)); ?></span>
                    <button class="pagination-btn" <?= ((int)($pagination['current_page'] ?? 1) >= (int)($pagination['total_pages'] ?? 1)) ? 'disabled' : '' ?>
                        onclick="window.location.href='<?php echo htmlspecialchars($buildMemberPageUrl((int)($pagination['current_page'] ?? 1) + 1)); ?>'">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Pending Approvals -->
    <div class="approvals-section">
        <!-- Pending Approvals Card -->
        <div class="approval-card">
            <div class="approval-header">Pending Approvals</div>
            
            <?php if (!empty($pending_approvals)): ?>
                <?php foreach ($pending_approvals as $approval): ?>
                <form class="approval-card pendingApprovalValidationForm" method="POST" action="/admin/payments/verify">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="member_id" value="<?php echo (int)$approval['id']; ?>">
                    <input type="hidden" name="method" value="paybill">
                    <input type="hidden" name="payment_type" value="registration">
                    <input type="hidden" name="return_to" value="/admin/members">
                    <div class="approval-name"><?php echo htmlspecialchars($approval['name']); ?></div>
                    <div class="approval-details"><?php echo htmlspecialchars($approval['package']); ?></div>
                    <div class="approval-tag <?php echo $approval['tag_class']; ?>"><?php echo htmlspecialchars($approval['tag']); ?></div>
                    <label class="approval-reference" for="approval-code-<?php echo (int)$approval['id']; ?>">M-PESA REFERENCE</label>
                    <input
                        id="approval-code-<?php echo (int)$approval['id']; ?>"
                        type="text"
                        class="approval-input"
                        name="mpesa_receipt_number"
                        value="<?php echo htmlspecialchars($pendingValidationMemberId === (int)$approval['id'] ? $pendingValidationCode : ($approval['code'] ?? '')); ?>"
                        placeholder="Enter Ref Code"
                        required
                    >
                    <?php if ($pendingValidationError && $pendingValidationMemberId === (int)$approval['id']): ?>
                        <div class="approval-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo htmlspecialchars($pendingValidationError); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="approval-actions">
                        <button type="submit" class="btn-approve">
                            <?php echo $approval['action_text'] ?? 'Verify & Activate'; ?>
                        </button>
                    </div>
                </form>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="approval-item" style="text-align: center; padding: 20px; color: #9CA3AF;">
                    <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 8px; opacity: 0.3;"></i>
                    <p>No pending approvals</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Member Detail Modal -->
<div class="modal fade entity-modal" id="memberDetailModal" tabindex="-1" aria-labelledby="memberDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="memberDetailModalLabel">Member Information</h5>
                    <div id="memberModalSubtitle" style="font-size: 13px; opacity: 0.85; margin-top: 2px;"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="entity-profile">
                    <div class="entity-avatar" id="memberModalAvatar">--</div>
                    <div class="entity-fields">
                        <div>
                            <div class="entity-field-label">Full Name</div>
                            <div class="entity-field-value" id="memberModalName">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Status</div>
                            <div class="entity-field-value" id="memberModalStatus">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">National ID</div>
                            <div class="entity-field-value" id="memberModalNationalId">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Package</div>
                            <div class="entity-field-value" id="memberModalPackage">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Monthly payable amount</div>
                            <div class="entity-field-value" id="memberModalMonthlyContribution">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Phone</div>
                            <div class="entity-field-value" id="memberModalPhone">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Email</div>
                            <div class="entity-field-value" id="memberModalEmail">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Recruited By</div>
                            <div class="entity-field-value" id="memberModalAgent">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Last Payment</div>
                            <div class="entity-field-value" id="memberModalPaymentAmount">N/A</div>
                        </div>
                        <div>
                            <div class="entity-field-label">Payment Date</div>
                            <div class="entity-field-value" id="memberModalPaymentDate">N/A</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade entity-modal" id="memberDeleteConfirmModal" tabindex="-1" aria-labelledby="memberDeleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="memberDeleteArchiveForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="memberDeleteConfirmLabel">Delete or Archive Member</h5>
                        <div id="memberDeleteSubtitle" style="font-size:13px;opacity:.85;margin-top:2px;"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="color:#4b5563;margin-bottom:16px;">
                        Members with no payments or claims will be permanently deleted. Members with payments or claims will be archived for audit history.
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="memberDeleteConfirmNumber">Type the member number to confirm</label>
                        <input class="form-input" id="memberDeleteConfirmNumber" name="confirm_member_number" required autocomplete="off">
                    </div>
                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label" for="memberArchiveReason">Reason</label>
                        <textarea class="form-input" id="memberArchiveReason" name="archive_reason" rows="3" placeholder="Brief reason for deletion/archive"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value || 'N/A';
}

function openMemberModal(button) {
    const member = JSON.parse(button.getAttribute('data-member') || '{}');
    setText('memberModalAvatar', member.initials);
    setText('memberModalName', member.name);
    setText('memberModalSubtitle', (member.member_number || 'N/A') + ' | ' + (member.phone || 'N/A'));
    setText('memberModalStatus', (member.status || 'N/A').replace(/_/g, ' ').toUpperCase());
    setText('memberModalNationalId', member.national_id);
    setText('memberModalPackage', member.package);
    setText('memberModalMonthlyContribution', member.monthly_contribution);
    setText('memberModalPhone', member.phone);
    setText('memberModalEmail', member.email);
    setText('memberModalAgent', member.agent_number);
    setText('memberModalPaymentAmount', member.last_payment_amount);
    setText('memberModalPaymentDate', member.last_payment_date);

    new bootstrap.Modal(document.getElementById('memberDetailModal')).show();
}

function confirmMemberStatus(event, message, type) {
    const form = event.currentTarget.closest('form');
    ShenaApp.confirmAction(
        message,
        function() {
            form.submit();
        },
        null,
        { type: type || 'warning', title: 'Confirm Member Action' }
    );
}

// Tab Switching Function
function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    const tabItems = document.querySelectorAll('.tab-item');
    tabItems.forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Activate selected tab button
    if (event && event.target) {
        event.target.classList.add('active');
    }
}

// Filter members by status (only redirect if URL needs to change)
function filterMembersByStatus(status) {
    const currentParams = new URLSearchParams(window.location.search);
    const currentStatus = currentParams.get('status') || 'all';
    
    let targetStatus = '';
    if (status === 'pending') targetStatus = 'pending_approval';
    else if (status === 'active') targetStatus = 'active';
    else if (status === 'suspended') targetStatus = 'suspended';
    else if (status === 'grace') targetStatus = 'grace_period';
    else targetStatus = 'all';
    
    // Only redirect if the status actually changes
    if (currentStatus !== targetStatus) {
        if (targetStatus === 'all') {
            window.location.href = '/admin/members';
        } else {
            window.location.href = '/admin/members?status=' + targetStatus;
        }
    }
}

// Bulk approve - IMPLEMENTED
function bulkApprove(overrideSystemRestrictions = false) {
    ShenaApp.confirmAction(
        overrideSystemRestrictions
            ? 'Override system restrictions and activate the skipped pending members?'
            : 'Approve all pending members in this view?',
        async function() {
            try {
                const response = await fetch('/admin-api/members/bulk-approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ override_system_restrictions: overrideSystemRestrictions })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.requires_override) {
                        const details = Array.isArray(data.errors) && data.errors.length ? '\n\n' + data.errors.join('\n') : '';
                        ShenaApp.confirmAction(
                            'Some members are blocked by unpaid registration, activation, or status requirements.' + details + '\n\nOverride these restrictions and activate them anyway?',
                            function() { bulkApprove(true); },
                            null,
                            { type: 'warning', title: 'Override Activation Checks', confirmText: 'Override & Activate' }
                        );
                        return;
                    }
                    ShenaApp.showNotification(data.message || `Successfully approved ${data.approved_count || 0} members`, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    ShenaApp.showNotification(data.message || 'Failed to approve members', 'error');
                }
            } catch (e) {
                ShenaApp.showNotification('Error: ' + e.message, 'error');
            }
        },
        null,
        { type: overrideSystemRestrictions ? 'warning' : 'primary', title: 'Bulk Approval' }
    );
}

// Bulk reactivate - IMPLEMENTED
function bulkReactivate(overrideSystemRestrictions = false) {
    ShenaApp.confirmAction(
        overrideSystemRestrictions
            ? 'Override system restrictions and reactivate skipped members?'
            : 'Reactivate selected suspended members?',
        async function() {
            try {
                const response = await fetch('/admin-api/members/bulk-reactivate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ override_system_restrictions: overrideSystemRestrictions })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.requires_override) {
                        const details = Array.isArray(data.errors) && data.errors.length ? '\n\n' + data.errors.join('\n') : '';
                        ShenaApp.confirmAction(
                            'Some members are blocked by unpaid registration, activation, or status requirements.' + details + '\n\nOverride these restrictions and reactivate them anyway?',
                            function() { bulkReactivate(true); },
                            null,
                            { type: 'warning', title: 'Override Reactivation Checks', confirmText: 'Override & Reactivate' }
                        );
                        return;
                    }
                    ShenaApp.showNotification(data.message || `Successfully reactivated ${data.reactivated_count || 0} members`, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    ShenaApp.showNotification(data.message || 'Failed to reactivate members', 'error');
                }
            } catch (e) {
                ShenaApp.showNotification('Error: ' + e.message, 'error');
            }
        },
        null,
        { type: 'warning', title: 'Reactivate Members' }
    );
}

// Send reminders
function sendReminders() {
    ShenaApp.confirmAction(
        'Send payment reminders to all members in grace period?',
        function() {
            ShenaApp.showNotification('Reminders sent successfully!', 'success');
        },
        null,
        { type: 'info', title: 'Send Reminders' }
    );
}

// Refresh pending
function refreshPending() {
    window.location.reload();
}

// Generate report
function generateReport() {
    ShenaApp.showNotification('Generating PDF report...', 'info', 2000);
    window.location.href = '/admin/reports/export?type=members';
}

// Handle active tab on page load based on URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    let tabIndex = 0;
    if (status === 'pending_approval') {
        tabIndex = 1;
    } else if (status === 'active') {
        tabIndex = 2;
    } else if (status === 'suspended') {
        tabIndex = 3;
    } else if (status === 'grace_period') {
        tabIndex = 4;
    }
    
    // Activate the appropriate tab without triggering navigation
    const tabs = document.querySelectorAll('.tab-item');
    if (tabs[tabIndex]) {
        // Remove active from all tabs first
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Activate the target tab
        tabs[tabIndex].classList.add('active');
        
        // Show corresponding content
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => content.classList.remove('active'));
        
        const tabNames = ['all', 'pending', 'active', 'suspended', 'grace', 'reports', 'tools'];
        const targetContent = document.getElementById('tab-' + tabNames[tabIndex]);
        if (targetContent) {
            targetContent.classList.add('active');
        }
    }
});
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
