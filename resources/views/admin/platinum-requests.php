<?php
include __DIR__ . '/../layouts/admin-header.php';
$coverages = $coverages ?? [];
$inpatientRequests = $inpatientRequests ?? [];
$csrf_token = $csrf_token ?? '';
?>
<style>
    .platinum-admin-container { padding: 20px; max-width: 1400px; margin: 0 auto; }

    .page-header { margin-bottom: 24px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1F2937; margin: 0 0 6px 0; }
    .page-subtitle { color: #6B7280; margin: 0; font-size: 14px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
    .stat-label { font-size: 13px; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .stat-value { font-size: 28px; font-weight: 700; background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .data-table-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 30px; }
    .card-header { padding: 16px 20px; background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); color: white; display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0; }
    .card-title { font-size: 16px; font-weight: 700; margin: 0; }
    .table-container { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { background: #F9FAFB; padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; }
    .data-table td { padding: 16px; border-top: 1px solid #E5E7EB; font-size: 14px; vertical-align: middle; }

    .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; white-space: nowrap; }
    .status-badge.verified { background: #D1FAE5; color: #065F46; }
    .status-badge.unverified { background: #FEE2E2; color: #991B1B; }

    .days-pill { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 700; }
    .days-pill.plenty { background: #D1FAE5; color: #065F46; }
    .days-pill.low { background: #FEF3C7; color: #92400E; }
    .days-pill.none { background: #FEE2E2; color: #991B1B; }

    .btn { padding: 8px 16px; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .btn-success { background: #10B981; color: white; }
    .btn-danger { background: #EF4444; color: white; }
    .btn-primary { background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); color: white; }
    .btn-secondary { background: white; color: #374151; border: 1px solid #D1D5DB; }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .action-group { display: flex; gap: 8px; flex-wrap: wrap; }

    .empty-state { text-align: center; padding: 60px 20px; color: #9CA3AF; }
    .empty-state i { font-size: 64px; margin-bottom: 16px; opacity: 0.3; }

    .member-cell strong { display: block; color: #1F2937; }
    .member-cell small { color: #9CA3AF; }

    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; }
    .modal-box { background: white; border-radius: 12px; padding: 24px; max-width: 480px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    .modal-box h3 { margin: 0 0 16px 0; font-size: 18px; color: #1F2937; }
    .modal-box label { display: block; font-size: 12px; font-weight: 600; color: #6B7280; margin-bottom: 6px; text-transform: uppercase; }
    .modal-box input, .modal-box textarea { width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; box-sizing: border-box; margin-bottom: 16px; }
    .modal-actions { display: flex; gap: 12px; justify-content: flex-end; }
</style>

<div class="platinum-admin-container">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-hospital"></i> Platinum Approvals</h1>
        <p class="page-subtitle">Review Platinum coverage requests and inpatient day allocations before activation.</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="stat-card" style="border-left:4px solid #10B981;margin-bottom:20px;color:#065F46"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="stat-card" style="border-left:4px solid #EF4444;margin-bottom:20px;color:#991B1B"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Pending Coverage Requests</div>
            <div class="stat-value"><?php echo count($coverages); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Inpatient Requests</div>
            <div class="stat-value"><?php echo count($inpatientRequests); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Payment Verified</div>
            <div class="stat-value" style="color:#10B981;-webkit-text-fill-color:#10B981"><?php echo count(array_filter($coverages, fn($c) => !empty($c['payment_verified']))); ?></div>
        </div>
    </div>

    <div class="data-table-card">
        <div class="card-header"><span class="card-title"><i class="fas fa-shield-alt"></i> Platinum Coverage Requests</span></div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Covered person</th>
                        <th>Monthly</th>
                        <th>Requested</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coverages)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>No pending Platinum requests</p></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($coverages as $c): ?>
                        <tr>
                            <td class="member-cell">
                                <strong><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($c['member_number']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($c['covered_person_name']); ?></td>
                            <td>KES <?php echo number_format((float) $c['monthly_contribution'], 2); ?></td>
                            <td><?php echo htmlspecialchars($c['requested_at'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($c['payment_verified'])): ?>
                                    <span class="status-badge verified"><i class="fas fa-check-circle"></i> Verified</span>
                                <?php else: ?>
                                    <span class="status-badge unverified"><i class="fas fa-exclamation-circle"></i> Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <form method="post" action="/admin/platinum-requests/<?php echo (int) $c['id']; ?>/process" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success" <?php echo empty($c['payment_verified']) ? 'disabled title="Requires a completed Platinum contribution"' : ''; ?>><i class="fas fa-check"></i> Approve</button>
                                    </form>
                                    <form method="post" action="/admin/platinum-requests/<?php echo (int) $c['id']; ?>/process" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Reject</button>
                                    </form>
                                    <?php if (empty($c['payment_verified'])): ?>
                                        <button type="button" class="btn btn-secondary" onclick="openOverrideModal(<?php echo (int) $c['id']; ?>, '<?php echo htmlspecialchars($c['covered_person_name'], ENT_QUOTES); ?>')"><i class="fas fa-unlock"></i> Override</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="data-table-card">
        <div class="card-header"><span class="card-title"><i class="fas fa-notes-medical"></i> Inpatient Requests</span></div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Patient</th>
                        <th>Facility</th>
                        <th>Requested</th>
                        <th>Remaining balance</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inpatientRequests)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>No pending inpatient requests</p></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($inpatientRequests as $request):
                            $remaining = (int) $request['remaining_days'];
                            $pillClass = $remaining <= 0 ? 'none' : ($remaining < (int) $request['requested_days'] ? 'low' : 'plenty');
                        ?>
                        <tr>
                            <td class="member-cell">
                                <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($request['member_number']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['facility_name'] . ' (' . $request['facility_location'] . ')'); ?></td>
                            <td><?php echo (int) $request['requested_days']; ?> days</td>
                            <td><span class="days-pill <?php echo $pillClass; ?>"><?php echo $remaining; ?> days left this year</span></td>
                            <td>
                                <button type="button" class="btn btn-primary" onclick="openInpatientModal(<?php echo (int) $request['id']; ?>, <?php echo (int) $request['requested_days']; ?>, <?php echo $remaining; ?>, '<?php echo htmlspecialchars($request['patient_name'], ENT_QUOTES); ?>')"><i class="fas fa-cog"></i> Decide</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="data-table-card">
        <div class="card-header"><span class="card-title"><i class="fas fa-user-md"></i> Create Inpatient Request for a Member</span></div>
        <div style="padding:20px">
            <p style="color:#6B7280;font-size:13px;margin:0 0 16px 0">Use this for phone/paper submissions the member could not enter themselves. Enable the eligibility override only for genuine exceptions (e.g. maturity not yet reached) and always record a reason.</p>
            <form method="post" action="/admin/inpatient-requests/create" style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div><label>Member ID</label><input type="number" name="member_id" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Platinum Coverage ID</label><input type="number" name="platinum_coverage_id" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Patient name</label><input type="text" name="patient_name" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Admission date</label><input type="date" name="admission_date" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Facility name</label><input type="text" name="facility_name" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Facility location</label><input type="text" name="facility_location" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Facility contact</label><input type="text" name="facility_contact" style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div><label>Requested days (1-20)</label><input type="number" name="requested_days" min="1" max="20" required style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div style="grid-column:1/-1"><label>Admission / doctor reference</label><input type="text" name="admission_reference" style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px"></div>
                <div style="grid-column:1/-1">
                    <label style="display:flex;align-items:center;gap:8px;text-transform:none"><input type="checkbox" name="override_eligibility" value="1" onchange="document.getElementById('inpatientCreateOverrideReason').style.display=this.checked?'block':'none'" style="width:auto"> Override eligibility checks (maturity / status) for this exception</label>
                    <textarea id="inpatientCreateOverrideReason" name="override_reason" rows="2" placeholder="Reason for override (required if checked)" style="display:none;width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:6px;margin-top:8px"></textarea>
                </div>
                <div style="grid-column:1/-1"><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create inpatient request</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function openInpatientModal(requestId, requestedDays, remainingDays, patientName) {
    const suggested = Math.max(0, Math.min(requestedDays, remainingDays));
    const modalHtml = `
        <div id="inpatient-modal" class="modal-overlay">
            <div class="modal-box">
                <h3><i class="fas fa-notes-medical" style="color:#7F20B0"></i> Decide: ${patientName}</h3>
                <p style="margin:0 0 16px 0;color:#6B7280;font-size:14px">Requested <strong>${requestedDays}</strong> days. Balance remaining this calendar year: <strong>${remainingDays}</strong> days.</p>
                <form method="post" action="/admin/inpatient-requests/${requestId}/process">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <label>Approved days (0 = reject)</label>
                    <input type="number" name="approved_days" min="0" max="${requestedDays}" value="${suggested}">
                    <label>Admin notes</label>
                    <textarea name="admin_notes" rows="3" placeholder="Notes or rejection reason"></textarea>
                    ${remainingDays < requestedDays ? `
                    <label style="display:flex;align-items:center;gap:8px;text-transform:none;margin-top:8px"><input type="checkbox" id="override_balance_chk" name="override_balance" value="1" onchange="document.getElementById('override_balance_reason').style.display=this.checked?'block':'none'" style="width:auto"> Override annual day balance</label>
                    <textarea id="override_balance_reason" name="override_reason" rows="2" placeholder="Reason for exceeding the remaining balance (required)" style="display:none"></textarea>
                    ` : ''}
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeInpatientModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save decision</button>
                    </div>
                </form>
            </div>
        </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeInpatientModal() {
    const modal = document.getElementById('inpatient-modal');
    if (modal) modal.remove();
}

function openOverrideModal(coverageId, personName) {
    const modalHtml = `
        <div id="override-modal" class="modal-overlay">
            <div class="modal-box">
                <h3><i class="fas fa-unlock" style="color:#EF4444"></i> Override approval: ${personName}</h3>
                <p style="margin:0 0 16px 0;color:#6B7280;font-size:14px">This activates Platinum cover without a verified contribution payment. A reason is required for the audit trail.</p>
                <form method="post" action="/admin/platinum-requests/${coverageId}/process">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="override" value="1">
                    <label>Override reason</label>
                    <textarea name="override_reason" rows="3" placeholder="e.g. Payment confirmed via cash receipt #123, pending reconciliation" required></textarea>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeOverrideModal()">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-unlock"></i> Approve with override</button>
                    </div>
                </form>
            </div>
        </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeOverrideModal() {
    const modal = document.getElementById('override-modal');
    if (modal) modal.remove();
}
</script>
