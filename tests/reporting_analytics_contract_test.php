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
            'getClaimsReport',
            'getFinancialReport',
            'getAgentsReport',
            'getCampaignsReport',
            'getMemberStatementPayload',
            'monthlyRevenueData',
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
            'ReportDocumentTemplate::render',
            "format') === 'csv'",
        ],
        'mustNotContain' => [
            'Unsupported report type.',
        ],
    ],
    [
        'file' => 'resources/views/admin/reports.php',
        'mustContain' => [
            'reports-shell',
            'report-tabs',
            'Export CSV',
            'Export PDF',
            '$payload',
            '$previewRows',
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
