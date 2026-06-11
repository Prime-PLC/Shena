<?php

$root = dirname(__DIR__);

$assertContains = function (string $haystack, string $needle, string $message): void {
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "Assertion failed: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
};

$smsView = file_get_contents($root . '/resources/views/admin/sms-campaigns.php');
$emailView = file_get_contents($root . '/resources/views/admin/email-campaigns.php');
$router = file_get_contents($root . '/app/core/Router.php');
$smsController = file_get_contents($root . '/app/controllers/BulkSmsController.php');
$emailController = file_get_contents($root . '/app/controllers/BulkEmailController.php');
$emailService = file_get_contents($root . '/app/services/BulkEmailService.php');

$assertContains($smsView, 'smsCampaignPreviewModal', 'SMS campaigns should show a preview modal before create/send.');
$assertContains($smsView, 'sms-phone-frame', 'SMS preview should use a mobile phone emulator.');
$assertContains($smsView, 'smsPreviewBubble', 'SMS preview should render the personalized first-recipient message.');
$assertContains($smsView, 'smsPreviewRecipient', 'SMS preview should show which first recipient is being emulated.');
$assertContains($smsView, '/admin/bulk-sms/preview-recipients', 'SMS preview should resolve the first matching recipient.');
$assertContains($smsView, 'loadSmsCampaignFirstRecipient', 'Existing SMS campaign previews should resolve stored first recipient data.');
$assertContains($smsView, 'previewExistingSmsCampaign', 'SMS send-now action should preview existing campaign content first.');
$assertContains($smsView, 'confirmSmsCampaignSubmit', 'SMS preview should have a single confirm action.');
$assertContains($smsView, 'sendCampaign(campaign.id, true)', 'SMS preview confirmation should send without a second browser confirmation.');
$assertContains($smsController, 'extractCustomFilters($_GET)', 'SMS recipient preview should use the current campaign filters.');

$assertContains($emailView, 'emailCampaignPreviewModal', 'Email campaigns should show a preview modal before create/send.');
$assertContains($emailView, 'email-preview-card', 'Email preview should render an inbox-style message card.');
$assertContains($emailView, 'emailPreviewBody', 'Email preview should render the personalized first-recipient body.');
$assertContains($emailView, 'emailPreviewRecipient', 'Email preview should show which first recipient is being emulated.');
$assertContains($emailView, '/admin/email-campaigns/preview-recipients', 'Email preview should resolve the first matching recipient.');
$assertContains($emailView, 'previewExistingEmailCampaign', 'Email send-now action should preview existing campaign content first.');
$assertContains($emailView, 'confirmEmailCampaignSubmit', 'Email preview should have a single confirm action.');
$assertContains($emailView, 'sendCampaign(campaign.id, true)', 'Email preview confirmation should send without a second browser confirmation.');
$assertContains($router, '/admin/email-campaigns/preview-recipients', 'Email recipient preview route should be registered.');
$assertContains($router, '/admin/communications/campaign/{id}/preview-recipient', 'SMS stored-recipient preview route should be registered.');
$assertContains($router, '/admin/email-campaigns/campaign/{id}/preview-recipient', 'Email stored-recipient preview route should be registered.');
$assertContains($emailController, 'previewRecipients', 'Email controller should expose a recipient preview endpoint.');
$assertContains($emailView, 'loadEmailCampaignFirstRecipient', 'Existing email campaign previews should resolve stored first recipient data.');
$assertContains($router, '/admin/email-campaigns/delete', 'Email campaign deletion route should be registered.');
$assertContains($emailController, 'deleteCampaign', 'Email controller should expose campaign deletion.');
$assertContains($emailService, 'deleteCampaign', 'Email service should delete campaign records.');
$assertContains($emailView, 'deleteCampaign(', 'Email campaigns should expose delete with confirmation.');

echo "Campaign preview modal regression checks passed.\n";
