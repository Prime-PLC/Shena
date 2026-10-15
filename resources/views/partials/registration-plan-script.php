<?php
require_once __DIR__ . '/../../../app/services/PlatinumPricingService.php';
$registrationPlans = [];
foreach (($packages ?? $GLOBALS['membership_packages'] ?? []) as $key => $plan) {
    if (!empty($plan['legacy_alias'])) continue;
    $registrationPlans[$key] = ['name'=>$plan['name'], 'basic'=>(float)$plan['monthly_contribution'], 'platinum'=>(new PlatinumPricingService())->packageAmount($key)];
}
?>
<script>window.shenaRegistrationPlans = <?= json_encode($registrationPlans, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="/public/js/registration-plan.js"></script>
