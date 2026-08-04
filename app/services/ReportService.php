<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Claim.php';
require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/PaymentStatusService.php';

class ReportService
{
    private $db;
    private $memberModel;
    private $paymentModel;
    private $claimModel;
    private $agentModel;
    private $paymentStatusService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->memberModel = new Member();
        $this->paymentModel = new Payment();
        $this->claimModel = new Claim();
        $this->agentModel = new Agent();
        $this->paymentStatusService = new PaymentStatusService();
    }

    public function getReportPayload(string $type, string $dateFrom, string $dateTo, array $filters = []): array
    {
        $type = $this->normalizeType($type);
        $payload = $this->buildPayload($type, $dateFrom, $dateTo, $filters);
        $payload['report_type'] = $type;
        $payload['available_reports'] = $this->availableReports();
        $payload['date_from'] = $dateFrom;
        $payload['date_to'] = $dateTo;
        $payload['monthlyRevenueData'] = $this->getMonthlyRevenueData();

        return $payload;
    }

    public function getExportPayload(string $type, string $dateFrom, string $dateTo, array $filters = []): array
    {
        $payload = $this->getReportPayload($type, $dateFrom, $dateTo, $filters);
        $payload['prepared_for'] = $filters['prepared_for'] ?? 'Admin';
        $payload['generated_at'] = date('Y-m-d H:i');
        $payload['date_range'] = $dateFrom . ' to ' . $dateTo;

        return $payload;
    }

    public function getMemberStatementPayload(int $memberId, string $dateFrom = '', string $dateTo = '', array $options = []): array
    {
        $member = $this->memberModel->getMemberById($memberId);
        $payments = $this->paymentModel->getMemberPayments($memberId);
        $rows = [];
        $total = 0;

        foreach ($payments as $payment) {
            $date = substr((string)($payment['payment_date'] ?? $payment['created_at'] ?? ''), 0, 10);
            if ($dateFrom && $date && $date < $dateFrom) {
                continue;
            }
            if ($dateTo && $date && $date > $dateTo) {
                continue;
            }

            $amount = (float)($payment['amount'] ?? 0);
            if (($payment['status'] ?? '') === 'completed') {
                $total += $amount;
            }

            $rows[] = [
                $date ?: 'N/A',
                $payment['payment_type'] ?? 'monthly',
                'KES ' . number_format($amount, 2),
                $payment['payment_method'] ?? 'N/A',
                $payment['status'] ?? 'pending',
                $payment['mpesa_receipt_number'] ?? $payment['transaction_id'] ?? $payment['reference'] ?? 'N/A',
            ];
        }

        $memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?: 'Member';
        $memberNumber = $member['member_number'] ?? ('member-' . $memberId);
        $memberPhone = $member['phone'] ?? '';

        return [
            'title' => $options['title'] ?? 'Member Payment Statement',
            'subtitle' => $memberName . ' | ' . $memberNumber . ($memberPhone !== '' ? ' | ' . $memberPhone : ''),
            'prepared_for' => $options['prepared_for'] ?? $memberName,
            'generated_at' => date('Y-m-d H:i'),
            'date_range' => ($dateFrom ?: 'All time') . ' to ' . ($dateTo ?: date('Y-m-d')),
            'metrics' => [
                ['label' => 'Completed Paid', 'value' => 'KES ' . number_format($total, 2)],
                ['label' => 'Payments Listed', 'value' => number_format(count($rows))],
                ['label' => 'Member No.', 'value' => $memberNumber],
                ['label' => 'Phone', 'value' => $memberPhone ?: 'N/A'],
            ],
            'tables' => [[
                'title' => 'Payment History',
                'headers' => ['Date', 'Type', 'Amount', 'Method', 'Status', 'Reference'],
                'rows' => $rows,
            ]],
            'csv_filename' => 'member-statement-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $memberNumber) . '-' . date('Ymd') . '.csv',
            'pdf_filename' => 'member-statement-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $memberNumber) . '-' . date('Ymd') . '.pdf',
        ];
    }

    public function getPaymentReceiptPayload(array $member, array $payment): array
    {
        $memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?: 'Member';
        $reference = $payment['mpesa_receipt_number'] ?? $payment['transaction_id'] ?? $payment['reference'] ?? 'N/A';

        return [
            'title' => 'Payment Receipt',
            'subtitle' => $reference,
            'prepared_for' => $memberName,
            'generated_at' => date('Y-m-d H:i'),
            'metrics' => [
                ['label' => 'Amount', 'value' => 'KES ' . number_format((float)($payment['amount'] ?? 0), 2)],
                ['label' => 'Status', 'value' => ucfirst($payment['status'] ?? 'pending')],
                ['label' => 'Reference', 'value' => $reference],
            ],
            'tables' => [[
                'title' => 'Receipt Details',
                'headers' => ['Field', 'Value'],
                'rows' => [
                    ['Member', $memberName],
                    ['Member Number', $member['member_number'] ?? $member['member_id'] ?? 'N/A'],
                    ['Phone', $member['phone'] ?? 'N/A'],
                    ['Payment Date', $payment['payment_date'] ?? $payment['created_at'] ?? 'N/A'],
                    ['Payment Type', $payment['payment_type'] ?? 'monthly'],
                    ['Payment Method', $payment['payment_method'] ?? 'N/A'],
                    ['Reference', $reference],
                ],
            ]],
            'pdf_filename' => 'receipt-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $reference) . '.pdf',
        ];
    }

    private function buildPayload(string $type, string $dateFrom, string $dateTo, array $filters): array
    {
        if ($type === 'members') {
            return $this->getMembersReport($dateFrom, $dateTo, $filters);
        }
        if ($type === 'payments') {
            return $this->getPaymentsReport($dateFrom, $dateTo, $filters);
        }
        if ($type === 'payment_breakdown') {
            return $this->getPaymentBreakdownReport($filters);
        }
        if ($type === 'claims') {
            return $this->getClaimsReport($dateFrom, $dateTo, $filters);
        }
        if ($type === 'financial') {
            return $this->getFinancialReport($dateFrom, $dateTo, $filters);
        }
        if ($type === 'agents') {
            return $this->getAgentsReport($dateFrom, $dateTo, $filters);
        }
        if ($type === 'campaigns') {
            return $this->getCampaignsReport($dateFrom, $dateTo, $filters);
        }

        return $this->getOverviewReport($dateFrom, $dateTo, $filters);
    }

    public function getOverviewReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $members = $this->memberStatusSummary();
        $payments = $this->paymentSummary($dateFrom, $dateTo);
        $claims = $this->claimSummary($dateFrom, $dateTo);
        $campaigns = $this->campaignSummary($dateFrom, $dateTo);

        return [
            'title' => 'Overview Report',
            'subtitle' => 'Operational health across members, payments, claims, and campaigns',
            'metrics' => [
                ['label' => 'Total Members', 'value' => number_format($members['total_members'])],
                ['label' => 'Active Members', 'value' => number_format($members['active_members'])],
                ['label' => 'Completed Revenue', 'value' => 'KES ' . number_format($payments['completed_amount'], 2)],
                ['label' => 'Open Claims', 'value' => number_format($claims['open_claims'])],
                ['label' => 'Campaign Sent', 'value' => number_format($campaigns['sent_count'])],
                ['label' => 'Failed Deliveries', 'value' => number_format($campaigns['failed_count'])],
            ],
            'tables' => [[
                'title' => 'Executive Summary',
                'headers' => ['Area', 'Value', 'Risk Signal'],
                'rows' => [
                    ['Membership', $members['active_members'] . ' active / ' . $members['total_members'] . ' total', $members['inactive_members'] . ' inactive'],
                    ['Payments', 'KES ' . number_format($payments['completed_amount'], 2), $payments['failed_count'] . ' failed'],
                    ['Claims', $claims['total_claims'] . ' total', $claims['open_claims'] . ' open'],
                    ['Campaigns', $campaigns['total_campaigns'] . ' campaigns', $campaigns['failed_count'] . ' failed recipients'],
                ],
            ]],
        ];
    }

    public function getMembersReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $status = $filters['status'] ?? 'all';
        $package = $filters['package'] ?? 'all';
        $rows = $this->memberModel->getAllMembersWithDetails('', $status, $package);
        $tableRows = [];

        foreach ($rows as $member) {
            $created = substr((string)($member['created_at'] ?? ''), 0, 10);
            if ($dateFrom && $created && $created < $dateFrom) {
                continue;
            }
            if ($dateTo && $created && $created > $dateTo) {
                continue;
            }

            $tableRows[] = [
                $member['member_number'] ?? '',
                $member['first_name'] ?? '',
                $member['last_name'] ?? '',
                $member['national_id'] ?? $member['id_number'] ?? '',
                $member['email'] ?? '',
                $member['phone'] ?? '',
                $member['package'] ?? '',
                $member['status'] ?? '',
                $member['registration_date'] ?? ($created ?: 'N/A'),
                $member['last_payment_date'] ?? '',
                $member['last_payment_amount'] ?? '',
            ];
        }

        return [
            'title' => 'Members Report',
            'subtitle' => 'Member registration and lifecycle view',
            'metrics' => [
                ['label' => 'Members Listed', 'value' => number_format(count($tableRows))],
                ['label' => 'Active Members', 'value' => number_format($this->memberStatusSummary()['active_members'])],
                ['label' => 'Inactive Members', 'value' => number_format($this->memberStatusSummary()['inactive_members'])],
            ],
            'tables' => [[
                'title' => 'Members',
                'headers' => [
                    'Member Number',
                    'First Name',
                    'Last Name',
                    'National ID',
                    'Email',
                    'Phone',
                    'Package',
                    'Status',
                    'Registration Date',
                    'Last Payment Date',
                    'Last Payment Amount',
                ],
                'rows' => $tableRows,
            ]],
        ];
    }

    public function getPaymentsReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $payments = $this->paymentModel->getAllPaymentsWithDetails([
            'start_date' => $dateFrom,
            'end_date' => $dateTo . ' 23:59:59',
            'status' => $filters['status'] ?? 'all',
        ]);
        $rows = [];
        $completed = 0;
        $failed = 0;
        $amount = 0;

        foreach ($payments as $payment) {
            if (($payment['status'] ?? '') === 'completed') {
                $completed++;
                $amount += (float)($payment['amount'] ?? 0);
            }
            if (($payment['status'] ?? '') === 'failed') {
                $failed++;
            }

            $rows[] = [
                substr((string)($payment['created_at'] ?? ''), 0, 10),
                $payment['member_number'] ?? '',
                trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')),
                $payment['phone'] ?? '',
                'KES ' . number_format((float)($payment['amount'] ?? 0), 2),
                $payment['payment_type'] ?? '',
                $payment['status'] ?? '',
                $payment['mpesa_receipt_number'] ?? $payment['transaction_id'] ?? $payment['reference'] ?? '',
            ];
        }

        return [
            'title' => 'Payments Report',
            'subtitle' => 'Contributions, registration fees, reactivations, and payment status',
            'metrics' => [
                ['label' => 'Payments Listed', 'value' => number_format(count($rows))],
                ['label' => 'Completed Count', 'value' => number_format($completed)],
                ['label' => 'Completed Amount', 'value' => 'KES ' . number_format($amount, 2)],
                ['label' => 'Failed Count', 'value' => number_format($failed)],
            ],
            'tables' => [[
                'title' => 'Payment Transactions',
                'headers' => ['Date', 'Member No.', 'Member', 'Phone', 'Amount', 'Type', 'Status', 'Reference'],
                'rows' => $rows,
            ]],
        ];
    }

    public function getPaymentBreakdownReport(array $filters = []): array
    {
        $group = (string)($filters['group'] ?? 'all');
        $rows = $this->paymentStatusService->getMembersByPaymentGroup($group, $filters, PHP_INT_MAX, 0);
        $tableRows = [];
        $balanceDue = 0.0;

        foreach ($rows as $member) {
            $balanceDue += (float)($member['balance_due'] ?? 0);
            $tableRows[] = [
                $member['member_number'] ?? '',
                trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')),
                $member['phone'] ?? '',
                $member['payment_group_label'] ?? $this->paymentStatusService->labelForGroup($member['payment_group'] ?? ''),
                'KES ' . number_format((float)($member['monthly_contribution'] ?? 0), 2),
                'KES ' . number_format((float)($member['paid_amount'] ?? 0), 2),
                'KES ' . number_format((float)($member['balance_due'] ?? 0), 2),
                (int)($member['missed_months'] ?? 0),
                $member['last_payment_date'] ?? 'N/A',
            ];
        }

        return [
            'title' => 'Payment Breakdown Report',
            'subtitle' => $this->paymentStatusService->labelForGroup($group) . ' member contribution status',
            'metrics' => [
                ['label' => 'Members Listed', 'value' => number_format(count($tableRows))],
                ['label' => 'Outstanding Balance', 'value' => 'KES ' . number_format($balanceDue, 2)],
            ],
            'tables' => [[
                'title' => 'Member Payment Status',
                'headers' => ['Member No.', 'Member', 'Phone', 'Group', 'Monthly Contribution', 'Paid This Month', 'Balance', 'Missed Months', 'Last Payment'],
                'rows' => $tableRows,
            ]],
        ];
    }

    public function getClaimsReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $claims = $this->claimModel->getAllClaimsWithDetails([
            'start_date' => $dateFrom,
            'end_date' => $dateTo . ' 23:59:59',
            'status' => $filters['status'] ?? 'all',
        ]);
        $rows = [];
        $open = 0;
        $approved = 0;

        foreach ($claims as $claim) {
            if (in_array($claim['status'] ?? '', ['submitted', 'under_review', 'pending'], true)) {
                $open++;
            }
            if (($claim['status'] ?? '') === 'approved') {
                $approved++;
            }
            $rows[] = [
                '#' . str_pad((string)($claim['id'] ?? ''), 4, '0', STR_PAD_LEFT),
                $claim['member_number'] ?? '',
                trim(($claim['first_name'] ?? '') . ' ' . ($claim['last_name'] ?? '')),
                $claim['phone'] ?? '',
                $claim['deceased_name'] ?? '',
                $claim['status'] ?? '',
                substr((string)($claim['created_at'] ?? ''), 0, 10),
            ];
        }

        return [
            'title' => 'Claims Report',
            'subtitle' => 'Claim submission, approval, and service-delivery monitoring',
            'metrics' => [
                ['label' => 'Claims Listed', 'value' => number_format(count($rows))],
                ['label' => 'Open Claims', 'value' => number_format($open)],
                ['label' => 'Approved Claims', 'value' => number_format($approved)],
            ],
            'tables' => [[
                'title' => 'Claims',
                'headers' => ['Claim', 'Member No.', 'Member', 'Phone', 'Deceased', 'Status', 'Submitted'],
                'rows' => $rows,
            ]],
        ];
    }

    public function getFinancialReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $summary = $this->paymentSummary($dateFrom, $dateTo);
        $methods = $this->paymentModel->getPaymentsByMethod($dateFrom, $dateTo . ' 23:59:59');
        $types = $this->paymentModel->getPaymentsByType($dateFrom, $dateTo . ' 23:59:59');
        $rows = [];

        foreach ($types as $type) {
            $rows[] = [
                ucfirst($type['payment_type'] ?? 'unknown'),
                number_format((int)($type['count'] ?? 0)),
                'KES ' . number_format((float)($type['total_amount'] ?? 0), 2),
            ];
        }
        foreach ($methods as $method) {
            $rows[] = [
                strtoupper($method['payment_method'] ?? 'unknown') . ' method',
                number_format((int)($method['count'] ?? 0)),
                'KES ' . number_format((float)($method['total_amount'] ?? 0), 2),
            ];
        }

        return [
            'title' => 'Financial Report',
            'subtitle' => 'Revenue breakdown by payment type and method',
            'metrics' => [
                ['label' => 'Completed Revenue', 'value' => 'KES ' . number_format($summary['completed_amount'], 2)],
                ['label' => 'Completed Payments', 'value' => number_format($summary['completed_count'])],
                ['label' => 'Failed Payments', 'value' => number_format($summary['failed_count'])],
            ],
            'tables' => [[
                'title' => 'Financial Breakdown',
                'headers' => ['Category', 'Count', 'Amount'],
                'rows' => $rows,
            ]],
        ];
    }

    public function getAgentsReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $agents = $this->agentModel->getAllAgents([], null, 0);
        $rows = [];
        foreach ($agents as $agent) {
            $rows[] = [
                $agent['agent_number'] ?? '',
                trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')),
                $agent['phone'] ?? '',
                $agent['status'] ?? '',
                number_format((int)($agent['total_members'] ?? 0)),
                'KES ' . number_format((float)($agent['paid_commission'] ?? 0), 2),
            ];
        }

        return [
            'title' => 'Agents Report',
            'subtitle' => 'Agent performance and commission summary',
            'metrics' => [
                ['label' => 'Agents Listed', 'value' => number_format(count($rows))],
                ['label' => 'Active Agents', 'value' => number_format($this->agentModel->getActiveAgentsCount())],
            ],
            'tables' => [[
                'title' => 'Agents',
                'headers' => ['Agent No.', 'Name', 'Phone', 'Status', 'Members', 'Paid Commission'],
                'rows' => $rows,
            ]],
        ];
    }

    public function getCampaignsReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $sql = "SELECT bm.id, bm.title, bm.message_type, bm.target_audience, bm.status, bm.created_at,
                       COUNT(bmr.id) AS total_count,
                       SUM(CASE WHEN bmr.status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS sent_count,
                       SUM(CASE WHEN bmr.status IN ('failed', 'rejected', 'undelivered', 'expired', 'unknown') THEN 1 ELSE 0 END) AS failed_count
                FROM bulk_messages bm
                LEFT JOIN bulk_message_recipients bmr ON bm.id = bmr.bulk_message_id
                WHERE bm.created_at BETWEEN :date_from AND :date_to
                GROUP BY bm.id, bm.title, bm.message_type, bm.target_audience, bm.status, bm.created_at
                ORDER BY bm.created_at DESC";
        $campaigns = $this->db->fetchAll($sql, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo . ' 23:59:59',
        ]);
        $rows = [];
        $sent = 0;
        $failed = 0;

        foreach ($campaigns as $campaign) {
            $sent += (int)($campaign['sent_count'] ?? 0);
            $failed += (int)($campaign['failed_count'] ?? 0);
            $rows[] = [
                $campaign['title'] ?? '',
                strtoupper($campaign['message_type'] ?? ''),
                $campaign['target_audience'] ?? '',
                $campaign['status'] ?? '',
                number_format((int)($campaign['total_count'] ?? 0)),
                number_format((int)($campaign['sent_count'] ?? 0)),
                number_format((int)($campaign['failed_count'] ?? 0)),
            ];
        }

        return [
            'title' => 'Campaigns Report',
            'subtitle' => 'SMS and email campaign delivery summary',
            'metrics' => [
                ['label' => 'Campaigns', 'value' => number_format(count($rows))],
                ['label' => 'Sent Recipients', 'value' => number_format($sent)],
                ['label' => 'Failed Recipients', 'value' => number_format($failed)],
            ],
            'tables' => [[
                'title' => 'Campaign Delivery',
                'headers' => ['Title', 'Channel', 'Audience', 'Status', 'Recipients', 'Sent', 'Failed'],
                'rows' => $rows,
            ]],
        ];
    }

    private function availableReports(): array
    {
        return [
            'overview' => 'Overview',
            'members' => 'Members',
            'payments' => 'Payments',
            'payment_breakdown' => 'Payment Breakdown',
            'claims' => 'Claims',
            'financial' => 'Financial',
            'agents' => 'Agents',
            'campaigns' => 'Campaigns',
        ];
    }

    private function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        return array_key_exists($type, $this->availableReports()) ? $type : 'overview';
    }

    private function memberStatusSummary(): array
    {
        $row = $this->db->fetch("SELECT
            COUNT(*) AS total_members,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_members,
            SUM(CASE WHEN status <> 'active' THEN 1 ELSE 0 END) AS inactive_members
            FROM members") ?: [];

        return [
            'total_members' => (int)($row['total_members'] ?? 0),
            'active_members' => (int)($row['active_members'] ?? 0),
            'inactive_members' => (int)($row['inactive_members'] ?? 0),
        ];
    }

    private function paymentSummary(string $dateFrom, string $dateTo): array
    {
        $row = $this->db->fetch("SELECT
            COUNT(*) AS total_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) AS completed_amount
            FROM payments
            WHERE created_at BETWEEN :date_from AND :date_to", [
            'date_from' => $dateFrom,
            'date_to' => $dateTo . ' 23:59:59',
        ]) ?: [];

        return [
            'total_count' => (int)($row['total_count'] ?? 0),
            'completed_count' => (int)($row['completed_count'] ?? 0),
            'failed_count' => (int)($row['failed_count'] ?? 0),
            'completed_amount' => (float)($row['completed_amount'] ?? 0),
        ];
    }

    private function claimSummary(string $dateFrom, string $dateTo): array
    {
        $row = $this->db->fetch("SELECT
            COUNT(*) AS total_claims,
            SUM(CASE WHEN status IN ('submitted','under_review','pending') THEN 1 ELSE 0 END) AS open_claims
            FROM claims
            WHERE created_at BETWEEN :date_from AND :date_to", [
            'date_from' => $dateFrom,
            'date_to' => $dateTo . ' 23:59:59',
        ]) ?: [];

        return [
            'total_claims' => (int)($row['total_claims'] ?? 0),
            'open_claims' => (int)($row['open_claims'] ?? 0),
        ];
    }

    private function campaignSummary(string $dateFrom, string $dateTo): array
    {
        $row = $this->db->fetch("SELECT
            COUNT(DISTINCT bm.id) AS total_campaigns,
            SUM(CASE WHEN bmr.status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS sent_count,
            SUM(CASE WHEN bmr.status IN ('failed', 'rejected', 'undelivered', 'expired', 'unknown') THEN 1 ELSE 0 END) AS failed_count
            FROM bulk_messages bm
            LEFT JOIN bulk_message_recipients bmr ON bm.id = bmr.bulk_message_id
            WHERE bm.created_at BETWEEN :date_from AND :date_to", [
            'date_from' => $dateFrom,
            'date_to' => $dateTo . ' 23:59:59',
        ]) ?: [];

        return [
            'total_campaigns' => (int)($row['total_campaigns'] ?? 0),
            'sent_count' => (int)($row['sent_count'] ?? 0),
            'failed_count' => (int)($row['failed_count'] ?? 0),
        ];
    }

    private function getMonthlyRevenueData(): array
    {
        $rows = $this->db->fetchAll("SELECT MONTH(created_at) AS month_no,
                   SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) AS amount
            FROM payments
            WHERE YEAR(created_at) = YEAR(CURDATE())
            GROUP BY MONTH(created_at)");
        $data = array_fill(0, 12, 0.0);
        foreach ($rows as $row) {
            $index = max(0, min(11, (int)$row['month_no'] - 1));
            $data[$index] = (float)$row['amount'];
        }
        return $data;
    }
}
