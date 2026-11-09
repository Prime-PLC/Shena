<?php
$r = $request ?? [];
$status = (string)($r['status'] ?? 'submitted');
$open = in_array($status, ['submitted', 'under_review'], true);
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$statusClass = in_array($status, ['approved', 'completed'], true) ? 'is-success' : ($status === 'rejected' ? 'is-danger' : 'is-pending');
include __DIR__ . '/../layouts/admin-header.php';
?>
<style>
.request-view{max-width:1100px;margin:0 auto;padding:24px}.request-back{display:inline-flex;align-items:center;gap:8px;min-height:44px;color:#7f3d9e;font-weight:700;text-decoration:none}.request-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin:12px 0 22px}.request-head h1{margin:0 0 5px;font-family:'Playfair Display',serif;font-size:28px}.request-head p{margin:0;color:#6b7280}.request-status{padding:7px 12px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase}.is-pending{background:#fff3cd;color:#8a5a00}.is-success{background:#d1fae5;color:#065f46}.is-danger{background:#fee2e2;color:#991b1b}.request-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}.request-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 4px 16px rgba(31,41,55,.05)}.request-card.wide{grid-column:span 2}.request-card h2{margin:0 0 16px;color:#4b235f;font-size:17px}.detail-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.detail span{display:block;margin-bottom:3px;color:#6b7280;font-size:12px;text-transform:uppercase}.detail strong{color:#1f2937}.document-state{display:flex;justify-content:space-between;align-items:center;gap:14px}.request-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:10px 16px;border:0;border-radius:8px;background:#7f3d9e;color:#fff;font-weight:700;text-decoration:none;cursor:pointer}.decision-form{display:grid;gap:14px}.decision-form label{display:grid;gap:6px;font-weight:700}.decision-form input[type="number"],.decision-form textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px}.override-row{display:flex!important;align-items:center;gap:9px!important;font-weight:500!important}.decision-buttons{display:flex;gap:10px;flex-wrap:wrap}.reject{background:#dc3545}.flash{margin-bottom:16px;padding:12px 14px;border-left:4px solid #7f3d9e;background:#f7effb;color:#4b235f;border-radius:8px}@media(max-width:760px){.request-view{padding:16px}.request-grid{grid-template-columns:1fr}.request-card.wide{grid-column:auto}.request-head,.document-state{flex-direction:column}.detail-list{grid-template-columns:1fr}.request-action{width:100%}}
</style>
<main class="request-view">
    <a class="request-back" href="/admin/platinum-requests?section=inpatient#inpatient-requests"><i class="fas fa-arrow-left" aria-hidden="true"></i> Hospital requests</a>
    <header class="request-head"><div><h1>Hospital request #<?= (int)$r['id'] ?></h1><p><?= $escape($r['patient_name']) ?> at <?= $escape($r['facility_name']) ?></p></div><span class="request-status <?= $statusClass ?>"><?= $escape(str_replace('_', ' ', $status)) ?></span></header>
    <?php foreach (['success','error'] as $flash): if (!empty($_SESSION[$flash])): ?><p class="flash" role="status"><?= $escape($_SESSION[$flash]) ?></p><?php unset($_SESSION[$flash]); endif; endforeach; ?>
    <div class="request-grid">
        <section class="request-card wide"><h2>Request details</h2><div class="detail-list">
            <div class="detail"><span>Member</span><strong><?= $escape(trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? ''))) ?> · <?= $escape($r['member_number']) ?></strong></div>
            <div class="detail"><span>Patient</span><strong><?= $escape($r['patient_name']) ?></strong></div>
            <div class="detail"><span>Hospital</span><strong><?= $escape($r['facility_name']) ?>, <?= $escape($r['facility_location']) ?></strong></div>
            <div class="detail"><span>Hospital contact</span><strong><?= $escape($r['facility_contact'] ?: 'Not provided') ?></strong></div>
            <div class="detail"><span>Admission date</span><strong><?= $escape(date('d M Y', strtotime($r['admission_date']))) ?></strong></div>
            <div class="detail"><span>Days</span><strong><?= (int)$r['requested_days'] ?> requested · <?= (int)$remaining_days ?> available</strong></div>
        </div></section>
        <section class="request-card"><h2>Documents</h2><div class="document-state"><div><strong><?= empty($missing_documents) ? 'Complete' : count($missing_documents).' required document missing' ?></strong><p>Due <?= $escape($deadline->format('d M Y, H:i')) ?> EAT</p></div><a class="request-action" href="/claim-documents/inpatient/<?= (int)$r['id'] ?>">Review documents</a></div></section>
        <?php if ($open): ?><section class="request-card wide"><h2>Review decision</h2><form class="decision-form" method="post" action="/admin/inpatient-requests/<?= (int)$r['id'] ?>/process">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrf_token) ?>">
            <label>Approved days<input type="number" name="approved_days" min="1" max="<?= max(1, (int)$r['requested_days']) ?>" value="<?= max(1, min((int)$r['requested_days'], max(0, (int)$remaining_days))) ?>"></label>
            <label>Admin notes<textarea name="admin_notes" rows="3" placeholder="Decision notes, rejection reason, or reason for a late-document exception"></textarea></label>
            <?php if (empty($missing_documents) && !empty($documents_late) && empty($document_review['exception_reason'])): ?>
                <label class="override-row"><input type="checkbox" name="authorize_late_documents" value="1"> Authorize the late document submission using the admin note above as the recorded reason</label>
            <?php elseif (!empty($document_review['exception_reason'])): ?>
                <p class="flash"><strong>Late documents authorized:</strong> <?= $escape($document_review['exception_reason']) ?></p>
            <?php endif; ?>
            <label class="override-row"><input type="checkbox" name="override_balance" value="1" onchange="document.getElementById('overrideReason').hidden=!this.checked"> Approve beyond the available annual balance</label><textarea id="overrideReason" name="override_reason" rows="2" placeholder="Required reason for an annual-balance override" hidden></textarea>
            <div class="decision-buttons"><button class="request-action" type="submit" name="decision" value="approve"><i class="fas fa-check" aria-hidden="true"></i> Approve request</button><button class="request-action reject" type="submit" name="decision" value="reject"><i class="fas fa-times" aria-hidden="true"></i> Reject request</button></div>
        </form></section><?php endif; ?>
        <section class="request-card"><h2>Service provider</h2><?php $providerCaseType='platinum';$providerCaseId=(int)$r['id'];$providerStage='platinum_hospital';include __DIR__.'/../partials/claim-provider.php'; ?></section>
    </div>
</main>
<?php include __DIR__ . '/../layouts/admin-footer.php'; ?>
