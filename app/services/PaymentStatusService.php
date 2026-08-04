<?php
/**
 * PaymentStatusService
 *
 * Centralizes member contribution status around the monthly payment deadline.
 */

class PaymentStatusService
{
    public const PAYMENT_DEADLINE_DAY = 7;
    private const DEFAULTED_MISSED_MONTHS = 2;

    private $db;
    private $memberColumns = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function refreshMemberPaymentStatus(int $memberId): ?array
    {
        $member = $this->getMemberPaymentRows(['member_id' => $memberId], 1, 0)[0] ?? null;
        return $member ?: null;
    }

    public function getPaymentBreakdownSummary(array $filters = []): array
    {
        $rows = $this->getMemberPaymentRows($filters, 100000, 0);
        $summary = $this->emptySummary();

        foreach ($rows as $row) {
            $group = $row['payment_group'] ?? 'unpaid_current';
            if (!isset($summary['groups'][$group])) {
                continue;
            }
            $summary['groups'][$group]['count']++;
            $summary['groups'][$group]['balance_due'] += (float)($row['balance_due'] ?? 0);
            $summary['expected_amount'] += (float)($row['expected_amount'] ?? 0);
            $summary['paid_amount'] += (float)($row['paid_amount'] ?? 0);
            $summary['balance_due'] += (float)($row['balance_due'] ?? 0);
            $summary['member_count']++;
        }

        $summary['collection_rate'] = $summary['expected_amount'] > 0
            ? round(($summary['paid_amount'] / $summary['expected_amount']) * 100, 1)
            : 0;
        $summary['payment_deadline_day'] = self::PAYMENT_DEADLINE_DAY;

        return $summary;
    }

    public function getMembersByPaymentGroup(string $group = 'all', array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $filters['payment_group'] = $group;
        return $this->getMemberPaymentRows($filters, $limit, $offset);
    }

    public function getMembersByPaymentGroupCount(string $group = 'all', array $filters = []): int
    {
        $filters['payment_group'] = $group;
        return count($this->getMemberPaymentRows($filters, 100000, 0));
    }

    public function getPaymentGroupTotals(string $group = 'all', array $filters = []): array
    {
        $filters['payment_group'] = $group;
        $rows = $this->getMemberPaymentRows($filters, 100000, 0);
        $balance = 0.0;
        foreach ($rows as $row) {
            $balance += (float)($row['balance_due'] ?? 0);
        }

        return [
            'count' => count($rows),
            'balance_due' => $balance,
        ];
    }

    public function getFinancialDashboardData(array $filters = []): array
    {
        $summary = $this->getPaymentBreakdownSummary($filters);
        $recentPayments = $this->db->fetchAll(
            "SELECT p.*, m.member_number, u.first_name, u.last_name
             FROM payments p
             LEFT JOIN members m ON p.member_id = m.id
             LEFT JOIN users u ON m.user_id = u.id
             WHERE p.status = 'completed'
             ORDER BY COALESCE(p.payment_date, p.created_at) DESC, p.id DESC
             LIMIT 8"
        );
        $recentReconciliations = $this->db->fetchAll(
            "SELECT p.*, m.member_number, u.first_name, u.last_name
             FROM payments p
             LEFT JOIN members m ON p.member_id = m.id
             LEFT JOIN users u ON m.user_id = u.id
             WHERE p.reconciled_at IS NOT NULL
             ORDER BY p.reconciled_at DESC, p.id DESC
             LIMIT 8"
        );
        $trend = $this->db->fetchAll(
            "SELECT DATE(COALESCE(payment_date, created_at)) AS payment_day,
                    COUNT(*) AS payment_count,
                    COALESCE(SUM(amount), 0) AS paid_amount
             FROM payments
             WHERE status = 'completed'
               AND payment_type = 'monthly'
               AND COALESCE(payment_date, created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(COALESCE(payment_date, created_at))
             ORDER BY payment_day DESC
             LIMIT 14"
        );

        return [
            'summary' => $summary,
            'recent_payments' => $recentPayments,
            'recent_reconciliations' => $recentReconciliations,
            'payment_trend' => $trend,
        ];
    }

    public function buildMonthlyPaymentSnapshot(array $member, array $payments, ?DateTime $asOf = null): array
    {
        $asOf = $asOf ?: new DateTime('today');
        $monthlyContribution = max(0, (float)($member['monthly_contribution'] ?? 0));
        $monthStart = $asOf->format('Y-m-01 00:00:00');
        $monthEnd = $asOf->format('Y-m-t 23:59:59');
        $deadlinePassed = (int)$asOf->format('j') >= self::PAYMENT_DEADLINE_DAY;
        $paidThisMonth = 0.0;
        $lastPaymentDate = null;
        $lastPaymentAmount = 0.0;

        foreach ($payments as $payment) {
            if (($payment['status'] ?? '') !== 'completed' || !$this->isContributionPayment($payment)) {
                continue;
            }
            $paymentDate = $payment['payment_date'] ?? $payment['created_at'] ?? null;
            if (!$paymentDate) {
                continue;
            }
            if ($lastPaymentDate === null || strtotime($paymentDate) > strtotime($lastPaymentDate)) {
                $lastPaymentDate = $paymentDate;
                $lastPaymentAmount = (float)($payment['amount'] ?? 0);
            }
            if ($paymentDate >= $monthStart && $paymentDate <= $monthEnd) {
                $paidThisMonth += (float)($payment['amount'] ?? 0);
            }
        }

        $expected = $monthlyContribution;
        $currentBalance = max(0, $expected - $paidThisMonth);
        $shortfall = $this->calculateContributionShortfall($member, $payments, $asOf);
        $missedMonths = (int)$shortfall['missed_months'];
        $arrearsAmount = (float)$shortfall['arrears_amount'];
        $paymentBalance = max($currentBalance, $arrearsAmount);
        $group = $this->classifyPaymentGroup($deadlinePassed, $paidThisMonth, $expected, $missedMonths, $paymentBalance);

        return [
            'monthly_contribution' => $monthlyContribution,
            'expected_amount' => $expected,
            'paid_amount' => $paidThisMonth,
            'current_balance_due' => $currentBalance,
            'balance_due' => $paymentBalance,
            'amount_due' => $paymentBalance,
            'arrears_amount' => $arrearsAmount,
            'missed_months' => $missedMonths,
            'last_payment_date' => $lastPaymentDate,
            'last_payment_amount' => $lastPaymentAmount,
            'payment_group' => $group,
            'payment_group_label' => $this->labelForGroup($group),
        ];
    }

    public function labelForGroup(string $group): string
    {
        $labels = [
            'paid_current' => 'Paid',
            'unpaid_current' => 'Not Paid',
            'partially_paid' => 'Partially Paid',
            'in_arrears' => 'In Arrears',
            'defaulted' => 'Defaulted',
            'pending_current_month' => 'Awaiting Deadline',
            'all' => 'All Members',
        ];

        return $labels[$group] ?? 'Members';
    }

    private function getMemberPaymentRows(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT m.*, u.first_name, u.last_name, u.email, u.phone
                FROM members m
                JOIN users u ON m.user_id = u.id
                WHERE m.status = 'active'
                  AND COALESCE(m.monthly_contribution, 0) > 0";
        $params = [];

        if (!empty($filters['member_id'])) {
            $sql .= " AND m.id = :member_id";
            $params['member_id'] = (int)$filters['member_id'];
        }
        if (!empty($filters['search'])) {
            $term = '%' . trim((string)$filters['search']) . '%';
            $sql .= " AND (m.member_number LIKE :search_member_number
                      OR m.id_number LIKE :search_id_number
                      OR u.first_name LIKE :search_first_name
                      OR u.last_name LIKE :search_last_name
                      OR u.phone LIKE :search_phone
                      OR u.email LIKE :search_email)";
            $params['search_member_number'] = $term;
            $params['search_id_number'] = $term;
            $params['search_first_name'] = $term;
            $params['search_last_name'] = $term;
            $params['search_phone'] = $term;
            $params['search_email'] = $term;
        }
        if (!empty($filters['package']) && $filters['package'] !== 'all') {
            if ($this->memberHasColumn('package_key')) {
                $sql .= " AND (m.package = :package OR m.package_key = :package_key)";
                $params['package_key'] = $filters['package'];
            } else {
                $sql .= " AND m.package = :package";
            }
            $params['package'] = $filters['package'];
        }

        $sql .= " ORDER BY u.first_name ASC, u.last_name ASC, m.member_number ASC";
        $members = $this->db->fetchAll($sql, $params);
        $rows = [];

        foreach ($members as $member) {
            $payments = $this->paymentsForMember((int)$member['id']);
            $snapshot = $this->buildMonthlyPaymentSnapshot($member, $payments);
            $coverage = $this->buildContributionCoverageSnapshot($member, $payments);
            $row = array_merge($member, $snapshot, $coverage);

            if (!$this->matchesComputedFilters($row, $filters)) {
                continue;
            }
            $rows[] = $row;
        }

        return array_slice($rows, max(0, $offset), max(1, $limit));
    }

    private function matchesComputedFilters(array $row, array $filters): bool
    {
        $group = $filters['payment_group'] ?? 'all';
        if ($group !== '' && $group !== 'all' && ($row['payment_group'] ?? '') !== $group) {
            return false;
        }
        if (isset($filters['amount_min']) && $filters['amount_min'] !== '' && (float)$row['balance_due'] < (float)$filters['amount_min']) {
            return false;
        }
        if (isset($filters['amount_max']) && $filters['amount_max'] !== '' && (float)$row['balance_due'] > (float)$filters['amount_max']) {
            return false;
        }
        if (isset($filters['missed_months']) && $filters['missed_months'] !== '' && (int)$row['missed_months'] < (int)$filters['missed_months']) {
            return false;
        }

        return true;
    }

    private function classifyPaymentGroup(bool $deadlinePassed, float $paid, float $expected, int $missedMonths, float $arrearsAmount): string
    {
        if ($missedMonths >= self::DEFAULTED_MISSED_MONTHS) {
            return 'defaulted';
        }
        if ($paid >= $expected && $arrearsAmount > 0 && $missedMonths > 0) {
            return 'in_arrears';
        }
        if ($arrearsAmount > $expected && $missedMonths > 0) {
            return 'in_arrears';
        }
        if ($paid >= $expected && $expected > 0) {
            return 'paid_current';
        }
        if ($paid > 0 && $paid < $expected) {
            return 'partially_paid';
        }
        return 'unpaid_current';
    }

    private function paymentsForMember(int $memberId): array
    {
        $sql = "SELECT * FROM payments WHERE member_id = :member_id";
        $params = ['member_id' => $memberId];

        $sql .= " ORDER BY COALESCE(payment_date, created_at) DESC";
        return $this->db->fetchAll($sql, $params);
    }

    private function calculateContributionShortfall(array $member, array $payments, DateTime $asOf): array
    {
        $monthlyContribution = max(0, (float)($member['monthly_contribution'] ?? 0));
        if ($monthlyContribution <= 0) {
            return ['missed_months' => 0, 'arrears_amount' => 0.0];
        }

        $createdAt = $member['created_at'] ?? $asOf->format('Y-m-d');
        $start = new DateTime(date('Y-m-01', strtotime($createdAt)));
        $cursor = clone $start;
        $missed = 0;
        $arrearsAmount = 0.0;

        while ($cursor <= $asOf) {
            $periodEnd = (clone $cursor)->modify('last day of this month')->setTime(23, 59, 59);
            $deadline = (clone $cursor)->setDate((int)$cursor->format('Y'), (int)$cursor->format('n'), self::PAYMENT_DEADLINE_DAY);
            if ($deadline > $asOf) {
                break;
            }

            $paid = 0.0;
            foreach ($payments as $payment) {
                if (($payment['status'] ?? '') !== 'completed' || !$this->isContributionPayment($payment)) {
                    continue;
                }
                $paymentDate = $payment['payment_date'] ?? $payment['created_at'] ?? null;
                if ($paymentDate && $paymentDate >= $cursor->format('Y-m-d 00:00:00') && $paymentDate <= $periodEnd->format('Y-m-d H:i:s')) {
                    $paid += (float)($payment['amount'] ?? 0);
                }
            }
            if ($paid < $monthlyContribution) {
                $missed++;
                $arrearsAmount += ($monthlyContribution - $paid);
            }
            $cursor->modify('first day of next month');
        }

        return [
            'missed_months' => $missed,
            'arrears_amount' => $arrearsAmount,
        ];
    }

    /**
     * Calculates reminder eligibility without changing Payment Breakdown.
     * Completed contribution payments are applied oldest-first and any excess
     * is carried forward as credit for future monthly reminders.
     */
    public function buildContributionCoverageSnapshot(array $member, array $payments, ?DateTime $asOf = null): array
    {
        $asOf = $asOf ?: new DateTime('today');
        $monthlyContribution = max(0, (float)($member['monthly_contribution'] ?? 0));
        if ($monthlyContribution <= 0) {
            return ['coverage_balance_due' => 0.0, 'contribution_credit' => 0.0, 'covered_through' => null];
        }

        $createdAt = $member['created_at'] ?? $asOf->format('Y-m-d');
        $start = new DateTime(date('Y-m-01', strtotime($createdAt)));
        $current = new DateTime($asOf->format('Y-m-01'));
        $monthsThroughCurrent = (((int)$current->format('Y') - (int)$start->format('Y')) * 12)
            + ((int)$current->format('n') - (int)$start->format('n')) + 1;
        $monthsThroughCurrent = max(1, $monthsThroughCurrent);
        $totalPaid = 0.0;
        $asOfEnd = (clone $asOf)->setTime(23, 59, 59)->getTimestamp();

        foreach ($payments as $payment) {
            if (($payment['status'] ?? '') !== 'completed' || !$this->isContributionPayment($payment)) {
                continue;
            }
            $paymentDate = $payment['payment_date'] ?? $payment['created_at'] ?? null;
            if ($paymentDate && strtotime($paymentDate) <= $asOfEnd) {
                $totalPaid += max(0, (float)($payment['amount'] ?? 0));
            }
        }

        $requiredThroughCurrent = $monthsThroughCurrent * $monthlyContribution;
        $coverageBalance = max(0, $requiredThroughCurrent - $totalPaid);
        $credit = max(0, $totalPaid - $requiredThroughCurrent);
        $fullyCoveredMonths = (int)floor($totalPaid / $monthlyContribution);
        $coveredThrough = null;
        if ($fullyCoveredMonths > 0) {
            $coveredMonth = clone $start;
            $coveredMonth->modify('+' . ($fullyCoveredMonths - 1) . ' months');
            $coveredThrough = $coveredMonth->format('Y-m-t');
        }

        return [
            'coverage_balance_due' => $coverageBalance,
            'contribution_credit' => $credit,
            'covered_through' => $coveredThrough,
        ];
    }

    private function isContributionPayment(array $payment): bool
    {
        $type = strtolower(trim((string)($payment['payment_type'] ?? '')));
        if ($type === '') {
            return true;
        }

        return in_array($type, ['monthly', 'contribution', 'monthly_contribution'], true);
    }

    private function emptySummary(): array
    {
        return [
            'member_count' => 0,
            'expected_amount' => 0.0,
            'paid_amount' => 0.0,
            'balance_due' => 0.0,
            'collection_rate' => 0,
            'groups' => [
                'paid_current' => ['label' => 'Paid', 'count' => 0, 'balance_due' => 0.0],
                'unpaid_current' => ['label' => 'Not Paid', 'count' => 0, 'balance_due' => 0.0],
                'partially_paid' => ['label' => 'Partially Paid', 'count' => 0, 'balance_due' => 0.0],
                'in_arrears' => ['label' => 'In Arrears', 'count' => 0, 'balance_due' => 0.0],
                'defaulted' => ['label' => 'Defaulted', 'count' => 0, 'balance_due' => 0.0],
                'pending_current_month' => ['label' => 'Awaiting Deadline', 'count' => 0, 'balance_due' => 0.0],
            ],
        ];
    }

    private function memberHasColumn(string $column): bool
    {
        if ($this->memberColumns === null) {
            try {
                $columns = $this->db->fetchAll('DESCRIBE members');
                $this->memberColumns = array_map(static function ($row) {
                    return $row['Field'];
                }, $columns);
            } catch (Throwable $e) {
                $this->memberColumns = [];
            }
        }

        return in_array($column, $this->memberColumns, true);
    }
}
