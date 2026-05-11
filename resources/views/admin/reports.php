<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<?php
$payload = $payload ?? [];
$availableReports = $availableReports ?? ($payload['available_reports'] ?? []);
$reportType = $reportType ?? ($payload['report_type'] ?? 'overview');
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$metrics = $metrics ?? ($payload['metrics'] ?? []);
$previewRows = $previewRows ?? ($payload['tables'][0]['rows'] ?? []);
$previewHeaders = $previewHeaders ?? ($payload['tables'][0]['headers'] ?? []);
$summary = $payload['summary'] ?? 'Select a report and date range to review system performance.';
?>

<style>
    .reports-shell {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .reports-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .reports-title {
        margin: 0;
        color: #1F2937;
        font-size: 24px;
        font-weight: 700;
    }

    .reports-summary {
        margin: 6px 0 0;
        max-width: 760px;
        color: #6B7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .report-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .report-btn {
        border: 1px solid #D1D5DB;
        background: #FFFFFF;
        color: #374151;
        border-radius: 8px;
        padding: 9px 13px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .report-btn.primary {
        background: #7F3D9E;
        border-color: #7F3D9E;
        color: #FFFFFF;
    }

    .reports-filters {
        display: grid;
        grid-template-columns: 1.1fr repeat(2, minmax(150px, 190px)) auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
    }

    .report-field label {
        display: block;
        margin-bottom: 6px;
        color: #4B5563;
        font-size: 12px;
        font-weight: 700;
    }

    .report-field select,
    .report-field input {
        width: 100%;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 10px 11px;
        color: #111827;
        font-size: 14px;
    }

    .report-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .report-tab {
        flex: 0 0 auto;
        border: 1px solid #E5E7EB;
        border-radius: 999px;
        padding: 8px 13px;
        color: #4B5563;
        background: #FFFFFF;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .report-tab.active {
        border-color: #7F3D9E;
        background: #F5ECFA;
        color: #7F3D9E;
    }

    .report-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .report-metric {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 14px;
    }

    .report-metric span {
        display: block;
        color: #6B7280;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .report-metric strong {
        display: block;
        margin-top: 6px;
        color: #111827;
        font-size: 22px;
        font-weight: 800;
    }

    .report-table-panel {
        overflow: hidden;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
    }

    .report-table-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #E5E7EB;
    }

    .report-table-head h2 {
        margin: 0;
        color: #111827;
        font-size: 16px;
    }

    .report-table-head p {
        margin: 4px 0 0;
        color: #6B7280;
        font-size: 12px;
    }

    .report-table-wrap {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }

    .report-table th,
    .report-table td {
        padding: 11px 14px;
        border-bottom: 1px solid #F3F4F6;
        text-align: left;
        font-size: 13px;
    }

    .report-table th {
        color: #374151;
        background: #F9FAFB;
        font-weight: 800;
    }

    .report-empty {
        padding: 28px 16px;
        color: #6B7280;
        text-align: center;
    }

    @media (max-width: 900px) {
        .reports-header,
        .report-table-head {
            flex-direction: column;
        }

        .report-actions {
            justify-content: flex-start;
        }

        .reports-filters {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="reports-shell">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Reports & Analytics</h1>
            <p class="reports-summary"><?= htmlspecialchars($summary) ?></p>
        </div>
        <div class="report-actions">
            <a class="report-btn" href="<?= htmlspecialchars('/admin/reports/export?' . http_build_query(array_merge($_GET, ['type' => $reportType, 'format' => 'csv']))) ?>">Export CSV</a>
            <a class="report-btn primary" href="<?= htmlspecialchars('/admin/reports/export?' . http_build_query(array_merge($_GET, ['type' => $reportType, 'format' => 'pdf']))) ?>">Export PDF</a>
        </div>
    </div>

    <form class="reports-filters" method="GET" action="/admin/reports">
        <div class="report-field">
            <label for="report_type">Report</label>
            <select id="report_type" name="report_type">
                <?php foreach ($availableReports as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= $key === $reportType ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="report-field">
            <label for="date_from">From</label>
            <input id="date_from" type="date" name="date_from" value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div class="report-field">
            <label for="date_to">To</label>
            <input id="date_to" type="date" name="date_to" value="<?= htmlspecialchars($endDate) ?>">
        </div>
        <button class="report-btn primary" type="submit">Apply</button>
    </form>

    <nav class="report-tabs" aria-label="Report sections">
        <?php foreach ($availableReports as $key => $label): ?>
            <?php $tabUrl = '/admin/reports?' . http_build_query(array_merge($_GET, ['report_type' => $key])); ?>
            <a class="report-tab <?= $key === $reportType ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl) ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="report-metrics" aria-label="Report metrics">
        <?php foreach ($metrics as $metric): ?>
            <div class="report-metric">
                <span><?= htmlspecialchars($metric['label'] ?? '') ?></span>
                <strong><?= htmlspecialchars((string)($metric['value'] ?? '0')) ?></strong>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="report-table-panel">
        <div class="report-table-head">
            <div>
                <h2><?= htmlspecialchars($payload['table_title'] ?? 'Report Preview') ?></h2>
                <p><?= htmlspecialchars(($payload['date_range'] ?? ($startDate . ' to ' . $endDate))) ?></p>
            </div>
            <div><?= count($previewRows) ?> rows</div>
        </div>
        <div class="report-table-wrap">
            <?php if (!empty($previewHeaders) && !empty($previewRows)): ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <?php foreach ($previewHeaders as $header): ?>
                                <th><?= htmlspecialchars((string)$header) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewRows as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= htmlspecialchars((string)$cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="report-empty">No rows match the selected report and date range.</div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
