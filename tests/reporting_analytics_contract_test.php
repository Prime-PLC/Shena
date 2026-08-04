<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'app/services/ReportService.php',
        'mustContain' => [
            'class ReportService',
            'getReportPayload',
            'getExportPayload',
            'getOverviewReport',
            'getMembersReport',
            'getPaymentsReport',
            'getPaymentBreakdownReport',
            'getClaimsReport',
            'getFinancialReport',
            'getAgentsReport',
            'getCampaignsReport',
            'getMemberStatementPayload',
            'monthlyRevenueData',
            "'Phone'",
            '$this->agentModel->getAllAgents([], null, 0)',
            'getMembersByPaymentGroup($group, $filters, PHP_INT_MAX, 0)',
            "'National ID'",
            "'Last Payment Date'",
            "'Last Payment Amount'",
        ],
    ],
    [
        'file' => 'app/helpers/ReportDocumentTemplate.php',
        'mustContain' => [
            'class ReportDocumentTemplate',
            'public static function render',
            'SHENA Companion',
            'shena-logo.png',
            '#7F3D9E',
            '#10B981',
            'Prepared for',
        ],
    ],
    [
        'file' => 'app/controllers/AdminController.php',
        'mustContain' => [
            'require_once __DIR__ . \'/../services/ReportService.php\';',
            'require_once __DIR__ . \'/../helpers/ReportDocumentTemplate.php\';',
            '$reportService = new ReportService();',
            '$payload = $reportService->getReportPayload',
            '$payload = $reportService->getExportPayload',
            'streamCsvReport',
            'streamExcelReport',
            'ReportDocumentTemplate::render',
            "format') === 'csv'",
            "format') === 'excel'",
            'application/vnd.ms-excel',
            'fwrite($output, "\xEF\xBB\xBF")',
            '"\r\n"',
            "\$dateFrom = \$hasDateFrom ? (string)\$_GET['date_from'] : '1900-01-01'",
            'Select both the start date and end date before exporting.',
            'private function isValidReportDate',
            'private function isSpreadsheetIdentifierHeader',
            'private function formatCsvIdentifier',
            'xmlns:x="urn:schemas-microsoft-com:office:excel"',
            '<td x:str',
        ],
        'mustNotContain' => [
            'Unsupported report type.',
            "', '\\\\', '')",
            'class="metrics"',
            'table.data tr:nth-child(even)',
            'table.meta',
            'class="letterhead"',
        ],
    ],
    [
        'file' => 'resources/views/admin/payments.php',
        'mustContain' => [
            "'type' => 'payment_breakdown'",
            'Export Breakdown',
            "if (\$selectedPaymentGroup === 'all')",
        ],
    ],
    [
        'file' => 'resources/views/admin/reports.php',
        'mustContain' => [
            'reports-shell',
            'report-tabs',
            'Export CSV',
            'Export Excel',
            'Export PDF',
            '$payload',
            '$previewRows',
            '$hasExplicitPeriod',
            'Apply Period',
            'Select and apply a reporting period to export',
            'required',
        ],
        'mustNotContain' => [
            'financialReports',
            'monthlyRevenueData ?? array_fill',
            "exportReport('excel')",
        ],
    ],
    [
        'file' => 'app/controllers/MemberController.php',
        'mustContain' => [
            'ReportDocumentTemplate::render',
            'getMemberStatementPayload',
            'Payment Receipt',
        ],
    ],
    [
        'file' => 'app/controllers/AgentDashboardController.php',
        'mustContain' => [
            'ReportDocumentTemplate::render',
            'getMemberStatementPayload',
            'member-statement-',
            "format') === 'csv'",
            '"\r\n"',
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    if (!file_exists($path)) {
        fwrite(STDERR, $check['file'] . " does not exist\n");
        $failed = true;
        continue;
    }

    $contents = file_get_contents($path);

    foreach ($check['mustContain'] ?? [] as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, $check['file'] . " is missing expected marker: {$needle}\n");
            $failed = true;
        }
    }

    foreach ($check['mustNotContain'] ?? [] as $needle) {
        if (strpos($contents, $needle) !== false) {
            fwrite(STDERR, $check['file'] . " still contains forbidden marker: {$needle}\n");
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
