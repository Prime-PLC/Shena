<?php

$root = dirname(__DIR__);
$failed = false;

$read = static function (string $path) use ($root): string {
    $full = $root . '/' . $path;
    return file_exists($full) ? file_get_contents($full) : '';
};

$assertFileExists = static function (string $path, string $message) use ($root, &$failed): void {
    if (!file_exists($root . '/' . $path)) {
        fwrite(STDERR, $message . "\nMissing file: {$path}\n");
        $failed = true;
    }
};

$assertContains = static function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, $message . "\nMissing: {$needle}\n");
        $failed = true;
    }
};

$assertNotContains = static function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, $message . "\nUnexpected: {$needle}\n");
        $failed = true;
    }
};

$paymentStatusService = $read('app/services/PaymentStatusService.php');
$paymentModel = $read('app/models/Payment.php');
$paymentController = $read('app/controllers/PaymentController.php');
$adminController = $read('app/controllers/AdminController.php');
$bulkSmsController = $read('app/controllers/BulkSmsController.php');
$bulkSmsService = $read('app/services/BulkSmsService.php');
$router = $read('app/core/Router.php');
$paymentsView = $read('resources/views/admin/payments.php');
$financialDashboardView = $read('resources/views/admin/financial-dashboard.php');
$smsCampaignsView = $read('resources/views/admin/sms-campaigns.php');
$campaignDetailsView = $read('resources/views/admin/campaign-details.php');
$adminHeader = $read('resources/views/layouts/admin-header.php');

$assertFileExists('app/services/PaymentStatusService.php', 'Shared payment deadline categorization service should exist.');
$assertContains($paymentStatusService, 'PAYMENT_DEADLINE_DAY = 7', 'Payment categorization should use the 7th as the monthly deadline.');
$assertContains($paymentStatusService, 'getPaymentBreakdownSummary', 'Payment status service should expose summary counts.');
$assertContains($paymentStatusService, 'getMembersByPaymentGroup', 'Payment status service should expose filtered group members.');
$assertContains($paymentStatusService, 'getPaymentGroupTotals', 'Payment breakdown should expose selected group totals for card/table consistency.');
$assertContains($paymentStatusService, 'isContributionPayment', 'Payment service should tolerate live contribution payment type variants.');
$assertContains($paymentStatusService, 'buildMonthlyPaymentSnapshot', 'Payment status service should calculate current month payment status.');
$assertNotContains($paymentStatusService, 'min($limit, 500)', 'Payment summaries and audiences should not silently cap live member datasets at 500 rows.');
$assertNotContains($paymentStatusService, "return 'pending_current_month';", 'Before-deadline unpaid members should remain in the visible Not Paid audience.');
$assertContains($paymentStatusService, "'paid_current'", 'Payment service should calculate paid members.');
$assertContains($paymentStatusService, "'unpaid_current'", 'Payment service should calculate members who have not paid.');
$assertContains($paymentStatusService, "'partially_paid'", 'Payment service should calculate partly paid members.');
$assertContains($paymentStatusService, "'in_arrears'", 'Payment service should calculate outstanding balances.');
$assertContains($paymentStatusService, "'defaulted'", 'Payment service should calculate defaulted members.');
$assertContains($paymentStatusService, 'missed_months', 'Payment service should calculate missed months.');
$assertContains($paymentStatusService, 'balance_due', 'Payment service should calculate payment balance.');
$assertContains($paymentStatusService, "WHERE m.status = 'active'", 'Payment breakdown should only count active payable members.');
$assertContains($paymentStatusService, 'COALESCE(m.monthly_contribution, 0) > 0', 'Payment breakdown should exclude non-payable zero-contribution records.');

$assertContains($paymentModel, 'PaymentStatusService', 'Payment confirmation should refresh shared payment status data.');
$assertContains($paymentController, 'refreshMemberPaymentStatus', 'Payment verification/reconciliation should refresh member payment grouping.');

$assertContains($adminController, 'paymentBreakdown', 'Admin controller should expose payment breakdown pages.');
$assertContains($router, '/admin/payments/breakdown', 'Router should expose payment breakdown page.');
$assertContains($paymentsView, 'Payment Breakdown', 'Payment Management should show payment breakdown cards.');
$assertContains($paymentsView, 'Paid', 'Payment breakdown should use short paid wording.');
$assertContains($paymentsView, 'Not Paid', 'Payment breakdown should use admin-friendly not paid wording.');
$assertContains($paymentsView, 'Partially Paid', 'Payment breakdown should use standard partly paid wording.');
$assertContains($paymentsView, 'In Arrears', 'Payment breakdown should use standard arrears wording.');
$assertContains($paymentsView, 'Outstanding KSh', 'Payment breakdown cards should explain that balances are outstanding amounts.');
$assertContains($paymentsView, 'Create SMS Campaign', 'Payment breakdown should expose group-level campaign creation.');
$assertContains($paymentsView, 'Send Reminder', 'Payment breakdown should expose group-level reminder wording.');
$assertContains($paymentsView, 'Send Thank You', 'Payment breakdown should expose group-level appreciation wording.');
$assertContains($paymentsView, 'View Member', 'Payment breakdown rows should keep member inspection actions.');
$assertContains($paymentsView, 'View Payments', 'Payment breakdown rows should keep payment inspection actions.');
$assertNotContains($paymentsView, 'name="search" value="<?php echo htmlspecialchars($memberRow', 'Payment breakdown rows should not create one-member campaigns.');

$assertContains($financialDashboardView, 'Payment Performance', 'Financial dashboard should show payment performance section.');
$assertContains($financialDashboardView, 'Paid vs Not Paid', 'Financial dashboard should compare paid against not paid members.');
$assertContains($financialDashboardView, 'Collection Rate', 'Financial dashboard should show collection rate.');
$assertContains($financialDashboardView, 'Recent Reconciliations', 'Financial dashboard should show recent reconciled payments.');
$assertContains($adminController, 'FROM payments', 'Financial dashboard should read payment totals from the payments table.');
$assertContains($adminController, 'FROM agent_commissions', 'Financial dashboard should read commission totals from agent commissions.');
$assertNotContains($adminController, 'FROM financial_transactions', 'Financial dashboard should not depend on secondary financial transaction rows for payment totals.');
$assertNotContains($adminController, 'vw_financial_summary', 'Financial dashboard charts should not depend on stale financial summary views.');

$assertContains($bulkSmsService, 'payment_group', 'Campaign recipient queries should support payment-aware groups.');
$assertContains($bulkSmsService, 'agent_all', 'SMS campaigns should support all agents.');
$assertContains($bulkSmsService, 'agent_active', 'SMS campaigns should support active agents.');
$assertContains($bulkSmsService, 'agent_inactive', 'SMS campaigns should support inactive agents.');
$assertContains($bulkSmsService, 'agent_with_members', 'SMS campaigns should support agents with registered members.');
$assertContains($bulkSmsService, "\$targetAudience === 'inactive'", 'Inactive member audiences should filter directly instead of becoming unfiltered custom audiences.');
$assertNotContains($bulkSmsService, "'inactive' => 'custom'", 'Inactive member audiences should not normalize to custom without a status filter.');
$assertContains($bulkSmsService, 'reuseCampaignAsDraft', 'SMS campaigns should be reusable as new drafts.');
$assertContains($bulkSmsService, 'createDraftFromPaymentGroup', 'Filtered payment groups should create draft campaigns.');
$assertContains($bulkSmsService, 'shouldRefreshRecipientsBeforeSend', 'Draft and reused campaigns should refresh recipients before sending.');
$assertContains($bulkSmsService, 'refreshRecipientsForCampaign', 'Campaign send path should rebuild recipients from current filters.');
$assertContains($bulkSmsService, "'recipient_mode' => 'refresh recipients'", 'Payment group campaign drafts should default to refreshed recipients.');
$assertContains($bulkSmsService, 'resendSingleRecipient', 'Individual failed campaign recipient resend should be supported.');
$assertNotContains($bulkSmsService, "DATE_SUB(CURDATE(), INTERVAL 60 DAY)", 'Payment campaign defaulters should no longer use rough 60-day no-payment grouping.');

$assertContains($bulkSmsController, 'createCampaignFromPaymentGroup', 'Controller should create draft campaigns from payment filters.');
$assertContains($bulkSmsController, 'reuseCampaign', 'Controller should expose campaign reuse.');
$assertContains($bulkSmsController, 'resendCampaignRecipient', 'Controller should expose individual failed SMS resend.');
$assertContains($router, '/admin/payments/create-sms-campaign', 'Router should expose create campaign from payment group.');
$assertContains($router, '/admin/communications/campaign/{id}/reuse', 'Router should expose campaign reuse.');
$assertContains($router, '/admin/communications/campaign/{id}/recipient/{recipientId}/resend', 'Router should expose individual recipient resend.');

$assertContains($smsCampaignsView, 'All Agents', 'SMS campaign UI should expose all agents audience.');
$assertContains($smsCampaignsView, 'Active Agents', 'SMS campaign UI should expose active agents audience.');
$assertContains($smsCampaignsView, 'Agents With Members', 'SMS campaign UI should expose agents with members audience.');
$assertContains($smsCampaignsView, 'Balance', 'SMS campaign UI should use short payment balance wording.');
$assertContains($smsCampaignsView, 'In Arrears', 'SMS campaign UI should use standard arrears wording.');
$assertNotContains($smsCampaignsView, 'Members Who Have Not Paid', 'SMS campaign UI should avoid long audience labels.');
$assertNotContains($smsCampaignsView, 'Agents with Registered Members', 'SMS campaign UI should avoid long agent audience labels.');
$assertContains($smsCampaignsView, 'sms-tag-picker', 'SMS campaign UI should expose a compact placeholder picker.');
$assertContains($smsCampaignsView, 'edit-sms-tag-picker', 'SMS campaign edit UI should expose the same placeholder picker.');
$assertContains($smsCampaignsView, 'Payment tags', 'SMS campaign UI should show payment placeholders only for payment audiences.');
$assertContains($smsCampaignsView, "'{monthly_contribution}'", 'SMS preview should support monthly contribution tags.');
$assertContains($smsCampaignsView, "'{missed_months}'", 'SMS preview should support missed month tags.');
$assertContains($smsCampaignsView, "'{agent_number}'", 'SMS preview should support agent tags.');
$assertContains($bulkSmsService, "'contribution'", 'SMS placeholder replacement should support contribution alias.');
$assertContains($bulkSmsService, "'balance'", 'SMS placeholder replacement should support balance alias.');

$assertContains($campaignDetailsView, 'Campaign Details', 'Campaign detail should be a full management page, not just a log.');
$assertContains($campaignDetailsView, '?edit_campaign=', 'Campaign detail edit action should open the edit workflow for that campaign.');
$assertContains($smsCampaignsView, 'openRequestedCampaignEditor', 'Campaign list should auto-open edit modal when requested from campaign details.');
$assertContains($smsCampaignsView, 'edit_campaign_to_open', 'Campaign list should receive direct edit metadata from the controller.');
$assertContains($smsCampaignsView, 'openSmsCampaignEditor', 'Campaign edit modal should open from direct campaign metadata as well as row actions.');
$assertContains($campaignDetailsView, '$currentRecipientPage > 1', 'Campaign detail pagination should not link before the first page.');
$assertContains($campaignDetailsView, '$currentRecipientPage < $totalRecipientPages', 'Campaign detail pagination should not link beyond the last page.');
$assertContains($campaignDetailsView, 'recipientStatusFilter', 'Campaign recipients should be filterable by delivery status.');
$assertContains($campaignDetailsView, 'recipientSearchInput', 'Campaign recipients should be searchable.');
$assertContains($campaignDetailsView, 'campaignRecipientPagination', 'Campaign recipients should be paginated.');
$assertContains($campaignDetailsView, 'Resend SMS', 'Failed SMS rows should expose individual resend.');
$assertContains($campaignDetailsView, 'Reuse Campaign', 'Campaign detail should expose reuse action.');
$assertContains($campaignDetailsView, 'Process Due Campaigns', 'Campaign detail should expose manual scheduled processing.');
$assertNotContains($campaignDetailsView, 'queued time', 'Campaign detail should not add unnecessary queue timing copy.');
$assertNotContains($campaignDetailsView, 'current delay', 'Campaign detail should not add unnecessary queue timing copy.');

$assertContains($adminHeader, '/admin/payments/breakdown', 'Payment breakdown should be reachable from the admin navigation.');

exit($failed ? 1 : 0);
