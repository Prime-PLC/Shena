<?php

class ReportDocumentTemplate
{
    public static function render(array $payload): string
    {
        $title = htmlspecialchars($payload['title'] ?? 'Report', ENT_QUOTES);
        $subtitle = htmlspecialchars($payload['subtitle'] ?? '', ENT_QUOTES);
        $preparedFor = htmlspecialchars($payload['prepared_for'] ?? 'SHENA Companion', ENT_QUOTES);
        $generatedAt = htmlspecialchars($payload['generated_at'] ?? date('Y-m-d H:i'), ENT_QUOTES);
        $dateRange = htmlspecialchars($payload['date_range'] ?? '', ENT_QUOTES);
        $metrics = $payload['metrics'] ?? [];
        $tables = $payload['tables'] ?? [];
        $notes = $payload['notes'] ?? [];
        $logoPath = self::logoDataUri();

        ob_start();
        ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px 24px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 11px; margin: 0; }
        .letterhead { border-bottom: 4px solid #7F3D9E; padding-bottom: 12px; margin-bottom: 18px; }
        .brand-row { width: 100%; border-collapse: collapse; }
        .brand-row td { vertical-align: middle; border: 0; padding: 0; }
        .logo-cell { width: 72px; }
        .logo { width: 56px; height: 56px; object-fit: contain; }
        .brand-name { color: #7F3D9E; font-size: 21px; font-weight: 800; letter-spacing: 0; margin: 0; }
        .brand-sub { color: #10B981; font-size: 11px; font-weight: 700; margin-top: 2px; }
        .meta { text-align: right; color: #6B7280; font-size: 9px; line-height: 1.5; }
        h1 { color: #111827; font-size: 20px; margin: 0 0 3px; }
        .subtitle { color: #6B7280; margin: 0 0 14px; }
        .metrics { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 8px -8px 18px; }
        .metric { border: 1px solid #E5E7EB; border-radius: 8px; padding: 10px; background: #F9FAFB; }
        .metric-label { color: #6B7280; font-size: 9px; text-transform: uppercase; font-weight: 800; }
        .metric-value { color: #111827; font-size: 16px; font-weight: 800; margin-top: 4px; }
        .section-title { color: #7F3D9E; font-size: 13px; font-weight: 800; margin: 14px 0 8px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th { background: #7F3D9E; color: #fff; font-size: 9px; text-transform: uppercase; padding: 7px; border: 1px solid #6f328b; text-align: left; }
        table.data td { padding: 7px; border: 1px solid #E5E7EB; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #F9FAFB; }
        .notes { margin-top: 12px; padding: 10px; border-left: 4px solid #10B981; background: #ECFDF5; color: #065F46; }
        .footer { position: fixed; left: 0; right: 0; bottom: -10px; border-top: 1px solid #E5E7EB; padding-top: 6px; color: #6B7280; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="letterhead">
        <table class="brand-row">
            <tr>
                <td class="logo-cell">
                    <?php if ($logoPath): ?>
                        <img class="logo" src="<?php echo $logoPath; ?>" alt="SHENA Logo">
                    <?php endif; ?>
                </td>
                <td>
                    <div class="brand-name">SHENA Companion</div>
                    <div class="brand-sub">Welfare Association</div>
                </td>
                <td class="meta">
                    <div>Prepared for: <?php echo $preparedFor; ?></div>
                    <div>Generated: <?php echo $generatedAt; ?></div>
                    <?php if ($dateRange): ?><div>Period: <?php echo $dateRange; ?></div><?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <h1><?php echo $title; ?></h1>
    <?php if ($subtitle): ?><p class="subtitle"><?php echo $subtitle; ?></p><?php endif; ?>

    <?php if (!empty($metrics)): ?>
        <table class="metrics">
            <tr>
                <?php foreach (array_values($metrics) as $index => $metric): ?>
                    <?php if ($index > 0 && $index % 4 === 0): ?></tr><tr><?php endif; ?>
                    <td class="metric">
                        <div class="metric-label"><?php echo htmlspecialchars($metric['label'] ?? '', ENT_QUOTES); ?></div>
                        <div class="metric-value"><?php echo htmlspecialchars((string)($metric['value'] ?? '0'), ENT_QUOTES); ?></div>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    <?php endif; ?>

    <?php foreach ($tables as $table): ?>
        <div class="section-title"><?php echo htmlspecialchars($table['title'] ?? 'Details', ENT_QUOTES); ?></div>
        <table class="data">
            <thead>
                <tr>
                    <?php foreach (($table['headers'] ?? []) as $header): ?>
                        <th><?php echo htmlspecialchars($header, ENT_QUOTES); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($table['rows'] ?? []) as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?php echo htmlspecialchars((string)$value, ENT_QUOTES); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($table['rows'])): ?>
                    <tr><td colspan="<?php echo max(1, count($table['headers'] ?? [])); ?>">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <?php if (!empty($notes)): ?>
        <div class="notes">
            <?php foreach ($notes as $note): ?>
                <div><?php echo htmlspecialchars($note, ENT_QUOTES); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="footer">SHENA Companion Welfare Association | Official system-generated document</div>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    private static function logoDataUri(): string
    {
        $path = defined('ROOT_PATH') ? ROOT_PATH . '/public/images/shena-logo.png' : __DIR__ . '/../../public/images/shena-logo.png';
        if (!file_exists($path)) {
            return '';
        }

        $data = base64_encode(file_get_contents($path));
        return 'data:image/png;base64,' . $data;
    }
}
