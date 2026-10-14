<?php
$page = 'claims';
include __DIR__ . '/layouts/' . ($admin ? 'admin' : 'member') . '-header.php';
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$required = ClaimEvidenceService::required($type);
$deadline = ClaimEvidenceService::deadline($type, $case);
$missing = ClaimEvidenceService::missing($type, $docs);
$deadlinePassed = new DateTimeImmutable('now', new DateTimeZone('Africa/Nairobi')) > $deadline
    && !ClaimEvidenceService::submittedOnTime($type, $case, $docs);
$evidenceMessage = (string) ($_SESSION['evidence_message'] ?? '');
$evidenceMessageType = str_starts_with($evidenceMessage, 'Saved.') ? 'info' : 'error';
unset($_SESSION['evidence_message']);
$backUrl = $admin
    ? ($type === 'funeral' ? '/admin/claims/view/' . $id : '/admin/inpatient-requests/view/' . $id)
    : ($type === 'funeral' ? '/claims/view/' . $id : '/inpatient-requests');
?>
<style>
.evidence-page { max-width: 960px; margin: 0 auto; padding: 24px; color: #1f2937; }
.evidence-back { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; margin-bottom: 12px; color: #7f3d9e; font-weight: 700; text-decoration: none; }
.evidence-back:hover { color: #642d7d; }
.evidence-title { margin: 0 0 6px; color: #1a1a1a; font-family: 'Playfair Display', serif; font-size: clamp(1.65rem, 4vw, 2.25rem); }
.evidence-subtitle { margin: 0; color: #6b7280; }
.evidence-status { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin: 24px 0; }
.evidence-status-card, .evidence-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; box-shadow: 0 4px 16px rgba(31, 41, 55, .05); }
.evidence-status-card { padding: 16px; }
.evidence-status-card span { display: block; margin-bottom: 4px; color: #6b7280; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.evidence-status-card strong { color: #4b235f; }
.evidence-card { margin-bottom: 18px; padding: 22px; }
.evidence-card h2 { margin: 0 0 16px; color: #4b235f; font-size: 1.1rem; }
.evidence-alert { margin: 0 0 16px; padding: 12px 14px; border-left: 4px solid #7f3d9e; border-radius: 8px; background: #f7effb; color: #4b235f; }
.evidence-alert.is-warning { border-color: #b45309; background: #fff7ed; color: #7c2d12; }
.evidence-checklist, .evidence-files { margin: 0; padding: 0; list-style: none; }
.evidence-checklist li, .evidence-files li { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eef0f3; }
.evidence-checklist li:last-child, .evidence-files li:last-child { border-bottom: 0; }
.evidence-checklist i { margin-top: 3px; color: #7f3d9e; }
.evidence-checklist .is-missing { color: #b42318; }
.evidence-file-meta { min-width: 0; overflow-wrap: anywhere; }
.evidence-file-meta small { display: block; color: #6b7280; }
.evidence-form { display: grid; gap: 14px; }
.evidence-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.evidence-form label { display: grid; gap: 7px; color: #374151; font-weight: 700; }
.evidence-form select, .evidence-form input[type="file"], .evidence-form textarea { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #1f2937; font: inherit; }
.evidence-form textarea { min-height: 100px; resize: vertical; }
.evidence-form select:focus, .evidence-form input:focus, .evidence-form textarea:focus { outline: 3px solid rgba(127, 61, 158, .18); border-color: #7f3d9e; }
.evidence-button, .evidence-download { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; border-radius: 8px; font-weight: 700; text-decoration: none; }
.evidence-button { width: fit-content; border: 0; padding: 10px 18px; background: #7f3d9e; color: #fff; cursor: pointer; }
.evidence-button:hover { background: #642d7d; }
.evidence-download { flex: 0 0 auto; padding: 8px 12px; color: #7f3d9e; }
.evidence-empty { margin: 0; color: #6b7280; }
@media (max-width: 640px) {
    .evidence-page { padding: 16px; }
    .evidence-status, .evidence-form-grid { grid-template-columns: 1fr; }
    .evidence-card { padding: 18px 16px; }
    .evidence-checklist li, .evidence-files li { align-items: stretch; flex-direction: column; }
    .evidence-download, .evidence-button { width: 100%; }
}
</style>

<div class="evidence-page">
    <a class="evidence-back" href="<?= $escape($backUrl) ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to claim</a>
    <h1 class="evidence-title"><?= $type === 'funeral' ? 'Claim' : 'Inpatient request' ?> #<?= (int)$id ?> documents</h1>
    <p class="evidence-subtitle">Upload and review supporting documents.</p>

    <?php foreach (['success', 'error'] as $flash): if (!empty($_SESSION[$flash])): ?>
        <p class="evidence-alert" role="status"><?= $escape($_SESSION[$flash]) ?></p>
    <?php unset($_SESSION[$flash]); endif; endforeach ?>
    <section class="evidence-status" aria-label="Document status">
        <div class="evidence-status-card"><span>Deadline</span><strong><?= $escape($deadline->format('d M Y, H:i')) ?> EAT</strong></div>
        <div class="evidence-status-card"><span>Status</span><strong><?= $missing ? count($missing) . ' required document' . (count($missing) === 1 ? '' : 's') . ' missing' : 'Required documents complete' ?></strong></div>
    </section>

    <?php if ($deadlinePassed): ?>
        <p class="evidence-alert is-warning" role="alert"><strong>Deadline passed.</strong> Upload the missing documents and contact the office.</p>
    <?php elseif ($type === 'inpatient'): ?>
        <p class="evidence-alert">Admission proof is due on the admission date. For help, call 0748585067 or 0748585071.</p>
    <?php endif ?>

    <section class="evidence-card">
        <h2>Required documents</h2>
        <ul class="evidence-checklist">
            <?php foreach ($required as $key => $label): $isMissing = isset($missing[$key]); ?>
                <li><span><i class="fas <?= $isMissing ? 'fa-circle-exclamation is-missing' : 'fa-circle-check' ?>" aria-hidden="true"></i> <?= $escape($label) ?></span><strong class="<?= $isMissing ? 'is-missing' : '' ?>"><?= $isMissing ? 'Missing' : 'Uploaded' ?></strong></li>
            <?php endforeach ?>
        </ul>
    </section>

    <?php if (!in_array($case['status'], ['rejected', 'completed', 'paid', 'cancelled'], true)): ?>
    <section class="evidence-card">
        <h2>Upload document</h2>
        <form class="evidence-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>">
            <input type="hidden" name="action" value="upload">
            <div class="evidence-form-grid">
                <label for="document_type">Document type
                    <select id="document_type" name="document_type">
                        <?php foreach ($required as $key => $label): ?><option value="<?= $escape($key) ?>"><?= $escape($label) ?></option><?php endforeach ?>
                        <?php if ($type === 'funeral'): ?><option value="death_certificate">Death certificate (optional)</option><?php endif ?>
                    </select>
                </label>
                <label for="document">File
                    <input id="document" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                </label>
            </div>
            <button class="evidence-button" type="submit"><i class="fas fa-upload" aria-hidden="true"></i> Upload document</button>
        </form>
    </section>
    <?php endif ?>

    <section class="evidence-card">
        <h2>Submitted documents</h2>
        <?php if (!$docs): ?><p class="evidence-empty">No documents uploaded yet.</p><?php else: ?>
        <ul class="evidence-files">
            <?php foreach ($docs as $doc): ?>
                <li><span class="evidence-file-meta"><strong><?= $escape(ucwords(str_replace('_', ' ', $doc['document_type']))) ?></strong><small><?= $escape($doc['file_name']) ?> · <?= $escape($doc['created_at']) ?></small></span>
                <?php if (isset($doc['storage_name'])): ?><a class="evidence-download" href="/claim-documents/<?= $escape($type) ?>/<?= (int)$id ?>/download/<?= (int)$doc['id'] ?>"><i class="fas fa-download" aria-hidden="true"></i> Download</a><?php endif ?></li>
            <?php endforeach ?>
        </ul>
        <?php endif ?>
    </section>

    <?php if ($admin): ?>
    <section class="evidence-card">
        <h2>Administrator review</h2>
        <?php if ($type === 'funeral' && in_array($case['status'], ['submitted', 'under_review'], true) && empty($review['accepted_at'])): ?>
            <form class="evidence-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="accept">
                <button class="evidence-button" type="submit"><i class="fas fa-check" aria-hidden="true"></i> Accept for document review</button>
            </form>
        <?php endif ?>
        <?php if (!empty($review['exception_reason'])): ?><p class="evidence-alert"><strong>Deadline exception:</strong> <?= $escape($review['exception_reason']) ?></p><?php endif ?>
        <form class="evidence-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="exception">
            <label for="exception_reason">Deadline exception reason
                <textarea id="exception_reason" name="reason" minlength="15" required placeholder="Include the contact and circumstances."></textarea>
            </label>
            <button class="evidence-button" type="submit"><i class="fas fa-calendar-check" aria-hidden="true"></i> Record exception</button>
        </form>
    </section>
    <?php endif ?>
</div>
<?php if ($evidenceMessage !== ''): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.ShenaApp && typeof window.ShenaApp.feedback === 'function') {
        window.ShenaApp.feedback([{
            type: <?= json_encode($evidenceMessageType) ?>,
            message: <?= json_encode($evidenceMessage, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        }]);
    }
});
</script>
<?php endif; ?>
<?php include __DIR__ . '/layouts/' . ($admin ? 'admin' : 'member') . '-footer.php'; ?>
