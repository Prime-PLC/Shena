<?php
$layout = ($_SESSION['user_role'] ?? '') === 'agent' ? 'agent' : 'admin';
include __DIR__ . '/../layouts/' . $layout . '-header.php';
$smsReviewAll = true;
include __DIR__ . '/../partials/sms-review-panel.php';
include __DIR__ . '/../layouts/' . $layout . '-footer.php';
?>
