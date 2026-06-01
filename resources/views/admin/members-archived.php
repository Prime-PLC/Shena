<?php
$members = $members ?? [];
$search = $search ?? '';
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .archive-shell {
        max-width: 1180px;
        margin: 0 auto;
    }
    .archive-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        margin-bottom: 18px;
    }
    .archive-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }
    .archive-subtitle {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 13px;
    }
    .archive-search {
        display: flex;
        gap: 8px;
        min-width: min(420px, 100%);
    }
    .archive-search input {
        flex: 1;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .archive-search button,
    .archive-back {
        border: 1px solid #7f3d9e;
        background: #7f3d9e;
        color: #fff;
        border-radius: 8px;
        padding: 10px 14px;
        text-decoration: none;
        font-weight: 600;
    }
    .archive-back {
        background: #fff;
        color: #7f3d9e;
    }
    .archive-table-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .archive-table {
        width: 100%;
        border-collapse: collapse;
    }
    .archive-table th,
    .archive-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #eef2f7;
        text-align: left;
        font-size: 13px;
    }
    .archive-table th {
        color: #6b7280;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .04em;
        background: #f9fafb;
    }
    @media (max-width: 760px) {
        .archive-toolbar {
            align-items: stretch;
            flex-direction: column;
        }
        .archive-table-card {
            overflow-x: auto;
        }
    }
</style>

<div class="archive-shell">
    <div class="archive-toolbar">
        <div>
            <h1 class="archive-title">Archived Members</h1>
            <p class="archive-subtitle">Members kept for audit because they have payment or claim history.</p>
        </div>
        <a class="archive-back" href="/admin/members"><i class="fas fa-arrow-left"></i> Back to Members</a>
    </div>

    <form class="archive-search" method="GET" action="/admin/members/archived">
        <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search archived members...">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

    <div class="archive-table-card" style="margin-top:18px;">
        <table class="archive-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Member No.</th>
                    <th>Phone</th>
                    <th>Archived At</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:#6b7280;padding:32px;">No archived members found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['member_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo !empty($member['archived_at']) ? htmlspecialchars(date('d M Y H:i', strtotime($member['archived_at']))) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($member['archive_reason'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
