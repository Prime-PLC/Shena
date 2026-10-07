<?php
$page = 'claims';
include __DIR__ . '/layouts/' . ($admin ? 'admin' : 'member') . '-header.php';
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$required = ClaimEvidenceService::required($type);
$deadline = ClaimEvidenceService::deadline($type, $case);
$missing = ClaimEvidenceService::missing($type, $docs);
?>
<main class="container" style="max-width:960px;margin:30px auto;padding:24px">
<h1>Claim #<?= $id ?> — documents and next steps</h1>
<?php foreach (['success', 'error'] as $flash): if (!empty($_SESSION[$flash])): ?><p role="status"><?= $escape($_SESSION[$flash]) ?></p><?php unset($_SESSION[$flash]); endif; endforeach ?>
<?php if (!empty($_SESSION['evidence_message'])): ?><p role="alert"><?= $escape($_SESSION['evidence_message']) ?></p><?php unset($_SESSION['evidence_message']); endif ?>
<p><strong>Document deadline: <?= $escape($deadline->format('d M Y, H:i')) ?> EAT.</strong></p>
<?php if ($type === 'funeral'): ?>
<p>Call SHENA to report the loss. Your initial application can be filed without documents. An administrator will verify and accept it for review. Upload the required documents within seven days of filing; acceptance does not restart this deadline. Final approval and processing require the documents.</p>
<p>Initial verification: <strong><?= !empty($review['accepted_at']) ? 'Accepted for document review' : 'Awaiting administrator verification' ?></strong>.</p>
<?php else: ?>
<p>Upload admission proof on the day of admission. If you cannot, call 0748585067 or 0748585071 immediately for guidance. An administrator must record any deadline exception; proof is still required before cover can be approved.</p>
<?php endif ?>
<p>These requirements also apply when an administrator files on your behalf. PDF, JPG or PNG, up to 5 MB per file.</p>
<?php if (new DateTimeImmutable('now', new DateTimeZone('Africa/Nairobi')) > $deadline && !ClaimEvidenceService::submittedOnTime($type,$case,$docs)): ?>
<p role="alert"><strong>Deadline passed.</strong> Upload the missing evidence and contact the office. Late documents need an administrator-recorded exception.</p>
<?php endif ?>
<ul><?php foreach ($required as $key=>$label): ?><li><?= $escape($label) ?>: <strong><?= isset($missing[$key]) ? 'Required — missing' : 'Uploaded' ?></strong></li><?php endforeach ?></ul>
<p>Uploading documents does not itself approve the claim.</p>
<?php if (!in_array($case['status'], ['rejected','completed','paid','cancelled'], true)): ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>">
<input type="hidden" name="action" value="upload">
<label>Document type <select name="document_type"><?php foreach ($required as $key=>$label): ?><option value="<?= $escape($key) ?>"><?= $escape($label) ?></option><?php endforeach ?><?php if ($type==='funeral'): ?><option value="death_certificate">Death certificate (optional)</option><?php endif ?></select></label>
<label>File <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required></label>
<button type="submit">Upload document</button>
</form>
<?php endif ?>
<h2>Submitted documents</h2>
<ul><?php foreach ($docs as $doc): ?><li><?= $escape($doc['document_type']) ?> — <?= $escape($doc['file_name']) ?> (<?= $escape($doc['created_at']) ?>)
<?php if (isset($doc['storage_name'])): ?><a href="/claim-documents/<?= $escape($type) ?>/<?= $id ?>/download/<?= (int)$doc['id'] ?>">Download</a><?php else: ?> — available in the original claim record<?php endif ?></li><?php endforeach ?></ul>
<?php if ($admin): ?>
<h2>Administrator review</h2>
<?php if ($type==='funeral' && in_array($case['status'],['submitted','under_review'],true) && empty($review['accepted_at'])): ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="accept"><p>Confirm the initial application has been verified. This is preliminary acceptance; final approval remains blocked until documents satisfy policy.</p><button>Accept initial application</button></form>
<?php endif ?>
<?php if (!empty($review['exception_reason'])): ?><p>Deadline exception: <?= $escape($review['exception_reason']) ?> (<?= $escape($review['exception_at']) ?>)</p><?php endif ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="exception"><label>Reason for deadline exception, including contact and circumstances <textarea name="reason" minlength="15" required></textarea></label><button>Record deadline exception</button><p>This does not waive required proof or eligibility checks.</p></form>
<?php endif ?>
<a href="<?= $admin ? ($type==='funeral' ? '/admin/claims/view/'.$id : '/admin/platinum-requests') : ($type==='funeral' ? '/claims/view/'.$id : '/inpatient-requests') ?>">Back to claim</a>
</main>
<?php include __DIR__ . '/layouts/' . ($admin ? 'admin' : 'member') . '-footer.php'; ?>
