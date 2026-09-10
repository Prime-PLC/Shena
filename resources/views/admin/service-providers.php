<?php
include __DIR__ . '/../layouts/admin-header.php';
$providerForm = $_SESSION['provider_form'] ?? [];
unset($_SESSION['provider_form']);
$editId = (int)($_GET['edit'] ?? ($providerForm['provider_id'] ?? 0));
if (!$providerForm && $editId) foreach ($providers as $row) if ((int)$row['id'] === $editId) $providerForm = $row;
$providerForm['stage_key'] = $providerForm['stage_key'] ?? ($_GET['stage'] ?? '');
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-address-book" aria-hidden="true"></i> Service providers</h1>
    <div class="d-flex gap-2"><a class="btn btn-secondary" href="/admin/claims">Claims</a><a class="btn btn-secondary" href="/admin/platinum-requests">Platinum desk</a></div>
</div>
<?php if ($providerError): ?>
    <div class="alert alert-warning" role="alert"><?= htmlspecialchars($providerError) ?></div>
<?php else: ?>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5"><?= $editId ? 'Edit provider' : 'Add service provider' ?></h2>
    <p class="text-muted">A provider can serve several stages, including funeral and hospital care. Select the provider separately for each service on a claim.</p>
    <form method="POST" action="/admin/service-providers">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
        <input type="hidden" name="provider_id" value="<?= $editId ?>">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="providerStage">Primary service</label>
                <select class="form-select" id="providerStage" name="stage_key" required>
                    <option value="">Select a stage</option>
                    <?php foreach (ServiceProvider::STAGES as $key=>$label): ?><option value="<?= $key ?>" <?= $providerForm['stage_key'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?php foreach (['contact_name'=>['Contact person','text',120], 'business_name'=>['Company, institution or business name','text',180], 'phone'=>['Phone number (required)','tel',20], 'email'=>['Email (optional)','email',190], 'address'=>['Address or location (optional)','text',255]] as $key=>[$label,$type,$limit]): ?>
                <div class="col-md-6"><label class="form-label" for="provider-<?= $key ?>"><?= $label ?></label><input class="form-control" type="<?= $type ?>" id="provider-<?= $key ?>" name="<?= $key ?>" maxlength="<?= $limit ?>" value="<?= htmlspecialchars($providerForm[$key] ?? '', ENT_QUOTES) ?>" <?= $key === 'phone' ? 'required' : '' ?>></div>
            <?php endforeach; ?>
        </div>
        <div class="mt-3 d-flex gap-2"><button class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Save provider</button><?php if ($editId): ?><a class="btn btn-secondary" href="/admin/service-providers">Cancel edit</a><?php endif; ?></div>
    </form>
</div></div>
<div class="card"><div class="card-body"><h2 class="h5">Provider directory</h2>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Provider</th><th>Primary service</th><th>Contacts</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php if (!$providers): ?><tr><td colspan="5">No service providers yet.</td></tr><?php endif; ?>
<?php foreach ($providers as $provider): ?><tr>
    <td><?= htmlspecialchars($provider['business_name'] ?: $provider['contact_name']) ?><small class="d-block text-muted"><?= htmlspecialchars($provider['contact_name']) ?></small></td>
    <td><?= htmlspecialchars(ServiceProvider::STAGES[$provider['stage_key']] ?? $provider['stage_key']) ?></td>
    <td><?= htmlspecialchars($provider['phone']) ?><small class="d-block text-muted"><?= htmlspecialchars($provider['email']) ?></small></td>
    <td><?= $provider['active'] ? 'Active' : 'Removed' ?></td>
    <td><a class="btn btn-secondary btn-sm" href="/admin/service-providers?edit=<?= (int)$provider['id'] ?>"><i class="fas fa-pen" aria-hidden="true"></i> <?= $provider['active'] ? 'Edit' : 'Restore / edit' ?></a>
        <?php if ($provider['active']): ?><form class="d-inline" method="POST" action="/admin/service-providers"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>"><input type="hidden" name="provider_id" value="<?= (int)$provider['id'] ?>"><button class="btn btn-outline-danger btn-sm" name="decision" value="remove"><i class="fas fa-trash" aria-hidden="true"></i> Remove</button></form><?php endif; ?>
    </td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/admin-footer.php'; ?>
