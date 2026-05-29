<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<?php
$unmatched_payments = $unmatched_payments ?? [];
$recon_stats = $recon_stats ?? [];
$audit_logs = $audit_logs ?? [];
$reconciliation_filters = $reconciliation_filters ?? [];
$unmatchedAmount = 0;
foreach ($unmatched_payments as $payment) {
    $unmatchedAmount += (float)($payment['amount'] ?? 0);
}

function recon_filter_value($key, $filters) {
    return htmlspecialchars((string)($filters[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<style>
    .unmatched-shell {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .unmatched-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .unmatched-title {
        margin: 0;
        color: #111827;
        font-size: 24px;
        font-weight: 800;
    }

    .unmatched-subtitle {
        margin: 6px 0 0;
        max-width: 820px;
        color: #6B7280;
        font-size: 14px;
        line-height: 1.45;
    }

    .unmatched-actions,
    .row-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .unmatched-btn {
        border: 1px solid #D1D5DB;
        background: #FFFFFF;
        color: #374151;
        border-radius: 8px;
        padding: 9px 13px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .unmatched-btn.primary {
        border-color: #7F3D9E;
        background: #7F3D9E;
        color: #FFFFFF;
    }

    .unmatched-btn.success {
        border-color: #10B981;
        background: #10B981;
        color: #FFFFFF;
    }

    .unmatched-btn.ghost {
        border-color: transparent;
        background: transparent;
    }

    .unmatched-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .unmatched-stat,
    .filter-card,
    .audit-feed,
    .unmatched-table-card {
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        border-radius: 8px;
    }

    .unmatched-stat {
        padding: 14px;
    }

    .unmatched-stat span {
        display: block;
        color: #6B7280;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .unmatched-stat strong {
        display: block;
        margin-top: 6px;
        color: #111827;
        font-size: 24px;
        font-weight: 800;
    }

    .filter-card {
        padding: 14px;
    }

    .filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.4fr) repeat(4, minmax(130px, 1fr)) auto auto;
        gap: 10px;
        align-items: end;
    }

    .filter-field label {
        display: block;
        margin-bottom: 6px;
        color: #4B5563;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-field input {
        width: 100%;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 9px 10px;
        color: #111827;
        font-size: 13px;
    }

    .unmatched-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 16px;
        align-items: start;
    }

    .unmatched-table-card {
        overflow: hidden;
    }

    .unmatched-table-wrap {
        overflow-x: auto;
    }

    .unmatched-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .unmatched-table th,
    .unmatched-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #F3F4F6;
        text-align: left;
        vertical-align: top;
        font-size: 13px;
    }

    .unmatched-table th {
        background: #F9FAFB;
        color: #374151;
        font-weight: 800;
    }

    .muted {
        color: #6B7280;
        font-size: 12px;
    }

    .match-panel {
        display: none;
        padding: 12px 14px;
        background: #FAFAFA;
        border-top: 1px solid #E5E7EB;
    }

    .match-panel.active {
        display: block;
    }

    .match-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #E5E7EB;
    }

    .match-row:last-child {
        border-bottom: 0;
    }

    .confidence {
        color: #047857;
        font-weight: 800;
    }

    .notes-input {
        width: 100%;
        min-height: 58px;
        margin-top: 8px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 9px 10px;
        resize: vertical;
        font-size: 13px;
    }

    .empty-state {
        padding: 40px 18px;
        color: #6B7280;
        text-align: center;
    }

    .audit-feed {
        overflow: hidden;
    }

    .audit-feed h2 {
        margin: 0;
        padding: 14px;
        border-bottom: 1px solid #E5E7EB;
        color: #111827;
        font-size: 15px;
    }

    .audit-item {
        padding: 12px 14px;
        border-bottom: 1px solid #F3F4F6;
    }

    .audit-item:last-child {
        border-bottom: 0;
    }

    .audit-item strong {
        display: block;
        color: #111827;
        font-size: 13px;
    }

    .manual-search-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1050;
        background: rgba(17, 24, 39, 0.52);
        padding: 18px;
    }

    .manual-search-modal.active {
        display: flex;
    }

    .manual-modal-card {
        width: min(720px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        background: #FFFFFF;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    }

    .manual-modal-head,
    .manual-modal-body {
        padding: 16px;
    }

    .manual-modal-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #E5E7EB;
    }

    .manual-modal-head h2 {
        margin: 0;
        color: #111827;
        font-size: 18px;
    }

    .manual-result {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #E5E7EB;
    }

    @media (max-width: 1100px) {
        .filter-form,
        .unmatched-grid {
            grid-template-columns: 1fr;
        }

        .unmatched-actions,
        .row-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 760px) {
        .unmatched-header,
        .manual-modal-head {
            flex-direction: column;
        }
    }
</style>

<div class="unmatched-shell">
    <div class="unmatched-header">
        <div>
            <h1 class="unmatched-title">Unmatched Payments</h1>
            <p class="unmatched-subtitle">
                Review Paybill payments that could not be linked automatically. Find the correct member, add a clear note, then reconcile.
            </p>
        </div>
        <div class="unmatched-actions">
            <a class="unmatched-btn" href="/admin/payments-reconciliation">Back to reconciliation</a>
            <a class="unmatched-btn primary" href="/admin/payments">All payments</a>
        </div>
    </div>

    <section class="unmatched-stats">
        <div class="unmatched-stat">
            <span>Queue</span>
            <strong><?= number_format(count($unmatched_payments)) ?></strong>
        </div>
        <div class="unmatched-stat">
            <span>Amount</span>
            <strong>KES <?= number_format($unmatchedAmount, 2) ?></strong>
        </div>
        <div class="unmatched-stat">
            <span>Matched</span>
            <strong><?= number_format((int)($recon_stats['matched'] ?? 0)) ?></strong>
        </div>
        <div class="unmatched-stat">
            <span>Manual</span>
            <strong><?= number_format((int)($recon_stats['manual'] ?? 0)) ?></strong>
        </div>
    </section>

    <section class="filter-card">
        <form class="filter-form" method="GET" action="/admin/payments/unmatched">
            <div class="filter-field">
                <label for="search">Search</label>
                <input id="search" name="search" value="<?= recon_filter_value('search', $reconciliation_filters) ?>" placeholder="Receipt, account, sender, phone">
            </div>
            <div class="filter-field">
                <label for="date_from">From</label>
                <input id="date_from" type="date" name="date_from" value="<?= recon_filter_value('date_from', $reconciliation_filters) ?>">
            </div>
            <div class="filter-field">
                <label for="date_to">To</label>
                <input id="date_to" type="date" name="date_to" value="<?= recon_filter_value('date_to', $reconciliation_filters) ?>">
            </div>
            <div class="filter-field">
                <label for="amount_min">Min KES</label>
                <input id="amount_min" type="number" min="0" step="0.01" name="amount_min" value="<?= recon_filter_value('amount_min', $reconciliation_filters) ?>">
            </div>
            <div class="filter-field">
                <label for="amount_max">Max KES</label>
                <input id="amount_max" type="number" min="0" step="0.01" name="amount_max" value="<?= recon_filter_value('amount_max', $reconciliation_filters) ?>">
            </div>
            <button class="unmatched-btn primary" type="submit">Apply</button>
            <a class="unmatched-btn" href="/admin/payments/unmatched">Reset</a>
        </form>
    </section>

    <div class="unmatched-grid">
        <section class="unmatched-table-card">
            <div class="unmatched-table-wrap">
                <table class="unmatched-table">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Account / Sender</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($unmatched_payments)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">No unmatched payments match the current filters.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($unmatched_payments as $payment): ?>
                                <?php
                                    $paymentId = (int)($payment['id'] ?? 0);
                                    $receipt = $payment['mpesa_receipt_number'] ?? 'N/A';
                                    $account = $payment['paybill_account'] ?? 'No account';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($receipt) ?></strong>
                                        <div class="muted">Payment #<?= $paymentId ?></div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($account) ?>
                                        <div class="muted">
                                            <?= htmlspecialchars($payment['sender_name'] ?? 'Unknown sender') ?>
                                            <?php if (!empty($payment['sender_phone'])): ?>
                                                - <?= htmlspecialchars($payment['sender_phone']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><strong>KES <?= number_format((float)($payment['amount'] ?? 0), 2) ?></strong></td>
                                    <td><?= htmlspecialchars(substr((string)($payment['transaction_date'] ?? $payment['created_at'] ?? ''), 0, 16)) ?></td>
                                    <td>
                                        <div class="row-actions">
                                            <button class="unmatched-btn primary" type="button" onclick="loadMatches(<?= $paymentId ?>)">Find matches</button>
                                            <button class="unmatched-btn" type="button" onclick="openManualSearch(<?= $paymentId ?>, <?= htmlspecialchars(json_encode($receipt), ENT_QUOTES, 'UTF-8') ?>)">Search member manually</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <div class="match-panel" id="matches-<?= $paymentId ?>">
                                            <div class="muted">Matches will appear here.</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="audit-feed">
            <h2>Recent reconciliation audit</h2>
            <?php if (empty($audit_logs)): ?>
                <div class="audit-item">
                    <div class="muted">No recent manual reconciliation activity.</div>
                </div>
            <?php else: ?>
                <?php foreach ($audit_logs as $log): ?>
                    <div class="audit-item">
                        <strong><?= htmlspecialchars($log['mpesa_receipt_number'] ?? ('Payment #' . ($log['payment_id'] ?? ''))) ?></strong>
                        <div class="muted">
                            <?= htmlspecialchars(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'Member') ?>
                            <?php if (!empty($log['member_number'])): ?>
                                - <?= htmlspecialchars($log['member_number']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="muted">
                            <?= htmlspecialchars($log['notes'] ?? $log['reconciliation_notes'] ?? 'No notes captured') ?>
                        </div>
                        <div class="muted">
                            <?= htmlspecialchars(substr((string)($log['created_at'] ?? $log['reconciled_at'] ?? ''), 0, 16)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>
    </div>
</div>

<div class="manual-search-modal" id="manual-search-modal" aria-hidden="true">
    <div class="manual-modal-card">
        <div class="manual-modal-head">
            <div>
                <h2>Search member manually</h2>
                <div class="muted" id="manual-payment-context">Select the correct member for reconciliation.</div>
            </div>
            <button class="unmatched-btn ghost" type="button" onclick="closeManualSearch()">Close</button>
        </div>
        <div class="manual-modal-body">
            <div class="filter-field">
                <label for="manual-member-query">Member search</label>
                <input id="manual-member-query" placeholder="Name, member number, ID number, phone">
            </div>
            <textarea class="notes-input" id="manual-reconciliation-notes" placeholder="reconciliation_notes: add why this member was selected"></textarea>
            <div style="margin-top: 12px;">
                <button class="unmatched-btn primary" type="button" onclick="searchMembersForReconciliation()">Search</button>
            </div>
            <div id="manual-search-results" style="margin-top: 12px;">
                <div class="muted">Search results will appear here.</div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedManualPaymentId = 0;

async function loadMatches(paymentId) {
    const panel = document.getElementById(`matches-${paymentId}`);
    if (!panel) return;

    panel.classList.add('active');
    panel.innerHTML = '<div class="muted">Checking member records...</div>';

    try {
        const response = await fetch(`/admin/payments/${paymentId}/matches`, {
            headers: {'Accept': 'application/json'}
        });
        const data = await response.json();

        if (!data.success || !data.matches || data.matches.length === 0) {
            panel.innerHTML = `
                <div class="muted">No suggested member found.</div>
                <div style="margin-top:8px;">
                    <button class="unmatched-btn" type="button" onclick="openManualSearch(${paymentId}, 'Payment #${paymentId}')">Search member manually</button>
                </div>
            `;
            return;
        }

        panel.innerHTML = `
            <textarea class="notes-input" id="notes-${paymentId}" placeholder="reconciliation_notes: why this match is correct"></textarea>
            ${data.matches.map(match => renderMatchRow(paymentId, match)).join('')}
        `;
    } catch (error) {
        panel.innerHTML = '<div class="muted">Could not load matches. Refresh and try again.</div>';
    }
}

function renderMatchRow(paymentId, match) {
    const name = `${match.first_name || ''} ${match.last_name || ''}`.trim() || match.name || 'Member';
    const number = match.member_number || match.member_id || 'N/A';
    const confidence = match.confidence || 0;
    const matchType = match.match_type || 'manual';
    return `
        <div class="match-row">
            <div>
                <strong>${escapeHtml(name)}</strong>
                <div class="muted">${escapeHtml(number)} - ${escapeHtml(matchType)}</div>
            </div>
            <div class="confidence">${confidence}%</div>
            <button class="unmatched-btn success" type="button" onclick="manualReconcile(${paymentId}, ${Number(match.id)}, document.getElementById('notes-${paymentId}')?.value || '')">Reconcile</button>
        </div>
    `;
}

function openManualSearch(paymentId, receipt) {
    selectedManualPaymentId = paymentId;
    document.getElementById('manual-payment-context').textContent = `Payment: ${receipt}`;
    document.getElementById('manual-member-query').value = '';
    document.getElementById('manual-reconciliation-notes').value = '';
    document.getElementById('manual-search-results').innerHTML = '<div class="muted">Search results will appear here.</div>';
    document.getElementById('manual-search-modal').classList.add('active');
    setTimeout(() => document.getElementById('manual-member-query')?.focus(), 0);
}

function closeManualSearch() {
    document.getElementById('manual-search-modal').classList.remove('active');
}

async function searchMembersForReconciliation() {
    const query = document.getElementById('manual-member-query').value.trim();
    const results = document.getElementById('manual-search-results');
    if (query.length < 2) {
        results.innerHTML = '<div class="muted">Enter at least 2 characters.</div>';
        return;
    }

    results.innerHTML = '<div class="muted">Searching members...</div>';

    try {
        const response = await fetch(`/admin/payments/search-members?q=${encodeURIComponent(query)}`, {
            headers: {'Accept': 'application/json'}
        });
        const data = await response.json();
        const members = data.results || [];

        if (members.length === 0) {
            results.innerHTML = '<div class="muted">No members found for that search.</div>';
            return;
        }

        results.innerHTML = members.map(member => `
            <div class="manual-result">
                <div>
                    <strong>${escapeHtml(member.name || 'Member')}</strong>
                    <div class="muted">${escapeHtml(member.member_number || 'N/A')} - ${escapeHtml(member.id_number || 'No ID')} - ${escapeHtml(member.phone || 'No phone')}</div>
                </div>
                <button class="unmatched-btn success" type="button" onclick="manualReconcile(selectedManualPaymentId, ${Number(member.id)}, document.getElementById('manual-reconciliation-notes').value)">Reconcile</button>
            </div>
        `).join('');
    } catch (error) {
        results.innerHTML = '<div class="muted">Could not search members. Refresh and try again.</div>';
    }
}

document.getElementById('manual-member-query')?.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchMembersForReconciliation();
    }
});

document.getElementById('manual-reconciliation-notes')?.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
        event.preventDefault();
        searchMembersForReconciliation();
    }
});

async function manualReconcile(paymentId, memberId, notes) {
    const finalNotes = (notes || '').trim() || 'Manual reconciliation from unmatched payments page';
    if (!confirm('Reconcile this payment with the selected member?')) return;

    const response = await fetch('/admin/payments/reconcile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            payment_id: paymentId,
            member_id: memberId,
            notes: finalNotes
        })
    });

    const data = await response.json();
    if (data.success) {
        window.location.reload();
        return;
    }

    alert(data.message || data.error || 'Failed to reconcile payment.');
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
