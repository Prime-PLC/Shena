<?php
require_once __DIR__ . '/../../../app/models/ServiceProvider.php';
try {
    if (!isset($providerDirectory)) $providerDirectory = (new ServiceProvider())->directory();
    $providerCaseKey = $providerCaseType . ':' . $providerCaseId;
    if (!isset($providerCaseAssignments[$providerCaseKey])) $providerCaseAssignments[$providerCaseKey] = (new ServiceProvider())->assignments($providerCaseType, $providerCaseId);
    $providerAssignment = $providerCaseAssignments[$providerCaseKey][$providerStage] ?? null;
} catch (Throwable $e) {
    if (empty($providerSetupNotice)) echo '<p class="text-muted">Provider setup is unavailable. Apply the service-provider migration to enable it.</p>';
    $providerSetupNotice = true;
    return;
}
$providerControlId = 'provider-' . $providerCaseType . '-' . (int)$providerCaseId . '-' . $providerStage;
?>
<div class="mt-3 pt-3 border-top">
    <form method="POST" action="/admin/claims/provider">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
        <input type="hidden" name="case_type" value="<?= htmlspecialchars($providerCaseType, ENT_QUOTES) ?>">
        <input type="hidden" name="case_id" value="<?= (int)$providerCaseId ?>">
        <input type="hidden" name="stage_key" value="<?= htmlspecialchars($providerStage, ENT_QUOTES) ?>">
        <label class="form-label" for="<?= htmlspecialchars($providerControlId, ENT_QUOTES) ?>"><i class="fas fa-address-book" aria-hidden="true"></i> <?= htmlspecialchars(ServiceProvider::STAGES[$providerStage]) ?> provider</label>
        <select class="form-select mb-2" name="provider_id" id="<?= htmlspecialchars($providerControlId, ENT_QUOTES) ?>">
            <option value="">Select a service provider</option>
            <?php foreach ($providerDirectory as $provider): ?>
                <?php if ((!$provider['active'] && (int)($providerAssignment['provider_id'] ?? 0) !== (int)$provider['id'])) continue; ?>
                <option value="<?= (int)$provider['id'] ?>" <?= (int)($providerAssignment['provider_id'] ?? 0) === (int)$provider['id'] ? 'selected' : '' ?>><?= htmlspecialchars(($provider['business_name'] ?: $provider['contact_name']) . ' - ' . $provider['phone'] . (!$provider['active'] ? ' (removed)' : ''), ENT_QUOTES) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm" name="decision" value="assign"><i class="fas fa-save" aria-hidden="true"></i> Save provider</button>
        <a class="btn btn-outline-secondary btn-sm" href="/admin/service-providers?stage=<?= urlencode($providerStage) ?>"><i class="fas fa-plus" aria-hidden="true"></i> Add or manage providers</a>
    </form>
    <?php if (!empty($providerAssignment['provider_id'])): ?>
        <p class="mt-2 mb-2 text-muted">Assigned: <?= htmlspecialchars(trim($providerAssignment['contact_name'] . ' ' . $providerAssignment['business_name']), ENT_QUOTES) ?>
            <a href="tel:<?= htmlspecialchars($providerAssignment['phone'], ENT_QUOTES) ?>"><?= htmlspecialchars($providerAssignment['phone'], ENT_QUOTES) ?></a>
            <?php if ($providerAssignment['email']): ?><a href="mailto:<?= htmlspecialchars($providerAssignment['email'], ENT_QUOTES) ?>"><?= htmlspecialchars($providerAssignment['email'], ENT_QUOTES) ?></a><?php endif; ?>
        </p>
        <form method="POST" action="/admin/claims/provider" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
            <input type="hidden" name="case_type" value="<?= htmlspecialchars($providerCaseType, ENT_QUOTES) ?>">
            <input type="hidden" name="case_id" value="<?= (int)$providerCaseId ?>">
            <input type="hidden" name="stage_key" value="<?= htmlspecialchars($providerStage, ENT_QUOTES) ?>">
            <button class="btn btn-secondary btn-sm" name="decision" value="notify" <?= !$providerAssignment['provider_active'] ? 'disabled' : '' ?>><i class="fas fa-comment-sms" aria-hidden="true"></i> Notify or contact service provider</button>
            <button class="btn btn-outline-danger btn-sm" name="decision" value="remove"><i class="fas fa-user-minus" aria-hidden="true"></i> Remove provider</button>
        </form>
    <?php endif; ?>
</div>
