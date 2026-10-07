<?php
include __DIR__ . '/../layouts/admin-header.php';
$coverages = $coverages ?? [];
$inpatientRequests = $inpatientRequests ?? [];
$recentDecisions = $recentDecisions ?? [];
$platinumMembers = $platinumMembers ?? [];
$activePlatinumCount = (int) ($activePlatinumCount ?? 0);
$csrf_token = $csrf_token ?? '';



$decisionStyles = [
    'approved' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Approved'],
    'partially_approved' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'label' => 'Partially approved'],
    'rejected' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'label' => 'Rejected'],
    'completed' => ['bg' => '#E0E7FF', 'color' => '#3730A3', 'label' => 'Completed'],
    'cancelled' => ['bg' => '#F3F4F6', 'color' => '#4B5563', 'label' => 'Cancelled'],
];
?>
<style>
    .platinum-admin-container { padding: 24px; max-width: 1400px; margin: 0 auto; }

    .page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; margin-bottom: 24px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1F2937; margin: 0 0 6px 0; display: flex; align-items: center; gap: 12px; }
    .page-title .title-gem { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; }
    .page-subtitle { color: #6B7280; margin: 0; font-size: 14px; max-width: 620px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 24px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); display: flex; align-items: center; gap: 16px; }
    .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
    .stat-icon.purple { background: #F3E8FF; color: #7F20B0; }
    .stat-icon.amber { background: #FEF3C7; color: #B45309; }
    .stat-icon.green { background: #D1FAE5; color: #047857; }
    .stat-icon.blue { background: #DBEAFE; color: #1D4ED8; }
    .stat-label { font-size: 12px; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600; }
    .stat-value { font-size: 26px; font-weight: 700; color: #1F2937; line-height: 1.1; }

    .data-table-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); margin-bottom: 24px; overflow: hidden; }
    .card-header { padding: 16px 20px; background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); color: #fff; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
    .card-header.subtle { background: #F9FAFB; color: #1F2937; border-bottom: 1px solid #E5E7EB; }
    .card-title { font-size: 15px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 9px; }
    .card-hint { font-size: 12px; opacity: 0.85; }
    .table-container { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { background: #F9FAFB; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .data-table td { padding: 15px 16px; border-top: 1px solid #F3F4F6; font-size: 14px; vertical-align: middle; }
    .data-table tbody tr:hover { background: #FCFAFF; }

    .status-badge { padding: 4px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; display: inline-flex; align-items: center; gap: 5px; }
    .status-badge.verified { background: #D1FAE5; color: #065F46; }
    .status-badge.unverified { background: #FEE2E2; color: #991B1B; }

    .days-pill { padding: 4px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .days-pill.plenty { background: #D1FAE5; color: #065F46; }
    .days-pill.low { background: #FEF3C7; color: #92400E; }
    .days-pill.none { background: #FEE2E2; color: #991B1B; }

    .btn { padding: 8px 15px; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; }
    .btn-success { background: #10B981; color: #fff; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #EF4444; color: #fff; }
    .btn-danger:hover { background: #DC2626; }
    .btn-primary { background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); color: #fff; }
    .btn-primary:hover { filter: brightness(1.08); }
    .btn-secondary { background: #fff; color: #374151; border: 1px solid #D1D5DB; }
    .btn-secondary:hover { background: #F9FAFB; }
    .btn-ghost-light { background: rgba(255,255,255,0.18); color: #fff; border: 1px solid rgba(255,255,255,0.35); }
    .btn-ghost-light:hover { background: rgba(255,255,255,0.28); }
    .btn:disabled { opacity: 0.45; cursor: not-allowed; }

    .action-group { display: flex; gap: 7px; flex-wrap: wrap; }

    .empty-state { text-align: center; padding: 52px 20px; color: #9CA3AF; }
    .empty-state i { font-size: 46px; margin-bottom: 14px; opacity: 0.3; display: block; }
    .empty-state p { margin: 0; font-size: 14px; }

    .member-cell strong { display: block; color: #1F2937; font-weight: 600; }
    .member-cell small { color: #9CA3AF; font-size: 12px; }

    /* Collapsible create panel — form stays hidden behind a button instead of raw exposure */
    .collapsible-body { display: none; padding: 22px 20px; border-top: 1px solid #E5E7EB; }
    .collapsible-body.open { display: block; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    @media (max-width: 820px) { .form-grid { grid-template-columns: 1fr; } }
    .form-grid .span-2 { grid-column: 1 / -1; }
    .field label { display: block; font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
    .field input, .field select, .field textarea { width: 100%; padding: 10px 12px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff; color: #1F2937; font-family: inherit; }
    .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: #7F3D9E; box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1); }
    .field .field-hint { font-size: 12px; color: #9CA3AF; margin-top: 5px; }
    .checkbox-row { display: flex; align-items: flex-start; gap: 9px; font-size: 13px; color: #374151; }
    .checkbox-row input { width: auto; }
    .callout { background: #FFFBEB; border-left: 4px solid #F59E0B; color: #78350F; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }

    .modal-overlay { position: fixed; inset: 0; background: rgba(17, 24, 39, 0.55); z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .modal-box { background: #fff; border-radius: 16px; padding: 26px; max-width: 500px; width: 100%; box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.3); max-height: 90vh; overflow-y: auto; }
    .modal-box h3 { margin: 0 0 8px 0; font-size: 18px; color: #1F2937; display: flex; align-items: center; gap: 9px; }
    .modal-box label { display: block; font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
    .modal-box input, .modal-box textarea { width: 100%; padding: 10px 12px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 14px; box-sizing: border-box; margin-bottom: 14px; font-family: inherit; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; }
</style>

<div class="platinum-admin-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><span class="title-gem"><i class="fas fa-gem"></i></span> SHENA Platinum Desk</h1>
            <p class="page-subtitle">Approve Platinum package-group cover and allocate shared inpatient bed-cover days. Each selected Basic package group has 20 days per calendar year.</p>
        </div>
        <div class="action-group">
            <a class="btn btn-secondary" href="/admin/service-providers"><i class="fas fa-address-book"></i> Service providers</a>
            <a class="btn btn-secondary" href="/admin/members?tier=platinum"><i class="fas fa-users"></i> Platinum members</a>
            <a class="btn btn-secondary" href="/admin/payments?payment_type=platinum"><i class="fas fa-coins"></i> Platinum ledger</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon amber"><i class="fas fa-hourglass-half"></i></span>
            <div>
                <div class="stat-label">Coverage requests</div>
                <div class="stat-value"><?php echo count($coverages); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon blue"><i class="fas fa-notes-medical"></i></span>
            <div>
                <div class="stat-label">Inpatient requests</div>
                <div class="stat-value"><?php echo count($inpatientRequests); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon green"><i class="fas fa-check-circle"></i></span>
            <div>
                <div class="stat-label">Awaiting approval</div>
                <div class="stat-value"><?php echo count($coverages); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon purple"><i class="fas fa-gem"></i></span>
            <div>
                <div class="stat-label">Active Platinum cover</div>
                <div class="stat-value"><?php echo $activePlatinumCount; ?></div>
            </div>
        </div>
    </div>

    <!-- Platinum coverage approvals -->
    <div class="data-table-card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-shield-alt"></i> Platinum Coverage Requests</span>
            <span class="card-hint">Approval activates cover and starts the maturity clock</span>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Covered person</th>
                        <th>Monthly</th>
                        <th>Requested</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coverages)): ?>
                        <tr><td colspan="5"><div class="empty-state"><i class="fas fa-inbox"></i><p>No Platinum coverage requests awaiting approval.</p></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($coverages as $c): ?>
                        <tr>
                            <td class="member-cell">
                                <strong><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($c['member_number']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($c['covered_person_name']); ?></td>
                            <td><strong>KES <?php echo number_format((float) $c['monthly_contribution'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($c['requested_at'] ? date('d M Y', strtotime($c['requested_at'])) : '—'); ?></td>
                            <td>
                                <div class="action-group">
                                    <button type="button" class="btn btn-success"
                                            onclick="PlatinumAdmin.confirmCoverage(<?php echo (int) $c['id']; ?>, 'approve', <?php echo htmlspecialchars(json_encode($c['covered_person_name']), ENT_QUOTES); ?>)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-danger"
                                            onclick="PlatinumAdmin.confirmCoverage(<?php echo (int) $c['id']; ?>, 'reject', <?php echo htmlspecialchars(json_encode($c['covered_person_name']), ENT_QUOTES); ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inpatient request queue -->
    <div class="data-table-card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-notes-medical"></i> Inpatient Requests</span>
            <span class="card-hint">Approving reserves days from the covered person's annual balance</span>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Patient</th>
                        <th>Facility</th>
                        <th>Admitted</th>
                        <th>Requested</th>
                        <th>Balance</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inpatientRequests)): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="fas fa-inbox"></i><p>No inpatient requests awaiting review.</p></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($inpatientRequests as $request):
                            $remaining = (int) $request['remaining_days'];
                            $pillClass = $remaining <= 0 ? 'none' : ($remaining < (int) $request['requested_days'] ? 'low' : 'plenty');
                        ?>
                        <tr>
                            <td class="member-cell">
                                <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($request['member_number']); ?><?php echo !empty($request['admin_created']) ? ' &middot; admin-created' : ''; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($request['patient_name']); ?> <a href="/claim-documents/inpatient/<?= (int)$request['id'] ?>">Documents / deadline review</a></td>
                            <td class="member-cell">
                                <strong><?php echo htmlspecialchars($request['facility_name']); ?></strong>
                                <small><?php echo htmlspecialchars($request['facility_location']); ?><?php echo !empty($request['facility_contact']) ? ' &middot; ' . htmlspecialchars($request['facility_contact']) : ''; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($request['admission_date']))); ?></td>
                            <td><strong><?php echo (int) $request['requested_days']; ?></strong> days</td>
                            <td><span class="days-pill <?php echo $pillClass; ?>"><?php echo $remaining; ?> left this year</span></td>
                            <td>
                                <button type="button" class="btn btn-primary" onclick="PlatinumAdmin.openInpatientModal(<?php echo (int) $request['id']; ?>, <?php echo (int) $request['requested_days']; ?>, <?php echo $remaining; ?>, <?php echo htmlspecialchars(json_encode($request['patient_name']), ENT_QUOTES); ?>)"><i class="fas fa-gavel"></i> Decide</button>
                            </td>
                        </tr>
                        <tr><td colspan="7"><details><summary class="py-2"><i class="fas fa-address-book" aria-hidden="true"></i> Service provider for hospital request #<?= (int)$request['id'] ?></summary>
                        <?php $providerCaseType = 'platinum'; $providerCaseId = (int)$request['id']; $providerStage = 'platinum_hospital'; include __DIR__ . '/../partials/claim-provider.php'; ?>
                        </details></td></tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admin-created inpatient request: collapsed behind a button -->
    <div class="data-table-card" id="adminInpatientCreateCard">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-user-md"></i> Record an Inpatient Request for a Member</span>
            <button type="button" class="btn btn-ghost-light" id="createRequestToggle" onclick="PlatinumAdmin.toggleCreatePanel()" aria-expanded="false" aria-controls="createRequestPanel">
                <i class="fas fa-plus" id="createRequestToggleIcon"></i> <span id="createRequestToggleLabel">New request</span>
            </button>
        </div>
        <div class="collapsible-body" id="createRequestPanel">
            <?php if (empty($platinumMembers)): ?>
                <div class="empty-state" style="padding:30px 20px">
                    <i class="fas fa-user-slash"></i>
                    <p>No members hold Platinum cover yet, so there is nobody to record an inpatient request for.</p>
                </div>
            <?php else: ?>
                <div class="callout">
                    <i class="fas fa-info-circle"></i> Use this for phone or paper submissions the member could not enter themselves. Only members holding Platinum cover appear below. Enable the eligibility override only for genuine exceptions and always record a reason.
                </div>
                <?php renderClaimFormState('inpatient_form', 'adminInpatientForm'); ?>
                <form method="post" action="/admin/inpatient-requests/create" class="form-grid" id="adminInpatientForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="member_id" id="inpatientMemberId" value="">

                    <div class="field">
                        <label for="inpatientMemberSelect">Platinum member <span style="color:#EF4444">*</span></label>
                        <select id="inpatientMemberSelect" required onchange="PlatinumAdmin.onMemberChange()">
                            <option value="">-- select a Platinum member --</option>
                            <?php foreach ($platinumMembers as $index => $pm): ?>
                                <option value="<?php echo $index; ?>" data-member-id="<?php echo (int) $pm['member_id']; ?>">
                                    <?php echo htmlspecialchars($pm['name'] . ' (' . $pm['member_number'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field-hint">Filtered to members registered for SHENA Platinum.</div>
                    </div>

                    <div class="field">
                        <label for="inpatientCoverageSelect">Covered person <span style="color:#EF4444">*</span></label>
                        <select name="platinum_coverage_id" id="inpatientCoverageSelect" required disabled>
                            <option value="">-- select a member first --</option>
                        </select>
                        <div class="field-hint">The Platinum-covered person who was admitted.</div>
                    </div>

                    <div class="field">
                        <label for="inpatientPatientName">Patient name <span style="color:#EF4444">*</span></label>
                        <input type="text" name="patient_name" id="inpatientPatientName" required placeholder="Name as recorded at the facility">
                    </div>
                    <div class="field">
                        <label for="inpatientAdmissionDate">Admission date <span style="color:#EF4444">*</span></label>
                        <input type="date" name="admission_date" id="inpatientAdmissionDate" required>
                    </div>
                    <div class="field">
                        <label for="inpatientFacilityName">Facility name <span style="color:#EF4444">*</span></label>
                        <input type="text" name="facility_name" id="inpatientFacilityName" required placeholder="e.g. Nairobi West Hospital">
                    </div>
                    <div class="field">
                        <label for="inpatientFacilityLocation">Facility location <span style="color:#EF4444">*</span></label>
                        <input type="text" name="facility_location" id="inpatientFacilityLocation" required placeholder="Town / county / ward">
                    </div>
                    <div class="field">
                        <label for="inpatientFacilityContact">Facility contact</label>
                        <input type="text" name="facility_contact" id="inpatientFacilityContact" placeholder="Phone number for coordination">
                    </div>
                    <div class="field">
                        <label for="inpatientRequestedDays">Requested days (1&ndash;20) <span style="color:#EF4444">*</span></label>
                        <input type="number" name="requested_days" id="inpatientRequestedDays" min="1" max="20" required>
                        <div class="field-hint" id="inpatientBalanceHint"></div>
                    </div>
                    <div class="field span-2">
                        <label for="inpatientAdmissionRef">Admission / doctor reference</label>
                        <input type="text" name="admission_reference" id="inpatientAdmissionRef" placeholder="Admission number or attending doctor">
                    </div>
                    <div class="field span-2">
                        <label class="checkbox-row" style="text-transform:none;letter-spacing:0;font-size:13px;font-weight:500;color:#374151">
                            <input type="checkbox" name="override_eligibility" value="1" onchange="document.getElementById('inpatientCreateOverrideReason').style.display = this.checked ? 'block' : 'none'">
                            <span>Override eligibility checks (maturity / active status) for this exception</span>
                        </label>
                        <textarea id="inpatientCreateOverrideReason" name="override_reason" rows="2" placeholder="Reason for override (required if checked)" style="display:none;margin-top:9px"></textarea>
                    </div>
                    <div class="span-2" style="display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" class="btn btn-secondary" onclick="PlatinumAdmin.toggleCreatePanel()">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create inpatient request</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent decisions -->
    <?php if (!empty($recentDecisions)): ?>
    <div class="data-table-card">
        <div class="card-header subtle">
            <span class="card-title"><i class="fas fa-history"></i> Recent Inpatient Decisions</span>
            <span class="card-hint" style="color:#9CA3AF">Last <?php echo count($recentDecisions); ?> reviewed</span>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Patient</th>
                        <th>Facility</th>
                        <th>Days</th>
                        <th>Outcome</th>
                        <th>Reviewed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentDecisions as $d):
                        $style = $decisionStyles[$d['status']] ?? ['bg' => '#F3F4F6', 'color' => '#4B5563', 'label' => ucfirst(str_replace('_', ' ', $d['status']))];
                    ?>
                    <tr>
                        <td class="member-cell">
                            <strong><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></strong>
                            <small><?php echo htmlspecialchars($d['member_number']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($d['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($d['facility_name']); ?></td>
                        <td><?php echo $d['approved_days'] === null ? '—' : (int) $d['approved_days']; ?> / <?php echo (int) $d['requested_days']; ?></td>
                        <td><span class="status-badge" style="background:<?php echo $style['bg']; ?>;color:<?php echo $style['color']; ?>"><?php echo htmlspecialchars($style['label']); ?></span></td>
                        <td><?php echo htmlspecialchars(!empty($d['reviewed_at']) ? date('d M Y', strtotime($d['reviewed_at'])) : '—'); ?></td>
                    </tr>
                    <tr><td colspan="6"><details><summary class="py-2"><i class="fas fa-address-book" aria-hidden="true"></i> Service provider for hospital request #<?= (int)$d['id'] ?></summary>
                        <?php $providerCaseType = 'platinum'; $providerCaseId = (int)$d['id']; $providerStage = 'platinum_hospital'; include __DIR__ . '/../partials/claim-provider.php'; ?>
                        </details></td></tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// The primary Platinum desk task is recording an inpatient request. Keep that
// action immediately below the page heading instead of after both queues.
(function () {
    var createCard = document.getElementById('adminInpatientCreateCard');
    var stats = document.querySelector('.platinum-admin-container > .stats-grid');
    if (createCard && stats && stats.parentNode) {
        stats.parentNode.insertBefore(createCard, stats);
    }
}());

window.PlatinumAdmin = (function () {
    var CSRF = <?php echo json_encode($csrf_token); ?>;
    var PLATINUM_MEMBERS = <?php echo json_encode($platinumMembers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    /** Post to an endpoint via a throwaway form so CSRF and redirects behave like a normal submit. */
    function submitAction(action, fields) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = action;
        fields = Object.assign({ csrf_token: CSRF }, fields || {});
        Object.keys(fields).forEach(function (name) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = fields[name];
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }

    function feedback(message, type) {
        if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
            ShenaApp.alert(message, type);
            return;
        }
        var notice = document.createElement('div');
        notice.className = 'modal-overlay';
        notice.innerHTML = '<div class="modal-box" role="alert"><h3></h3><p style="margin:0 0 18px 0;color:#4B5563"></p><div class="modal-actions"><button type="button" class="btn btn-primary">OK</button></div></div>';
        notice.querySelector('h3').textContent = type === 'success' ? 'Completed' : 'Please review';
        notice.querySelector('p').textContent = String(message);
        notice.querySelector('button').addEventListener('click', function () { notice.remove(); });
        notice.addEventListener('click', function (event) { if (event.target === notice) notice.remove(); });
        document.body.appendChild(notice);
    }

    function confirmThen(message, options, onConfirm) {
        if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
            ShenaApp.confirmAction(message, onConfirm, null, options);
            return;
        }
        var modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = '<div class="modal-box" role="dialog" aria-modal="true"><h3></h3><p style="margin:0 0 18px 0;color:#4B5563"></p><div class="modal-actions"><button type="button" class="btn btn-secondary">Cancel</button><button type="button" class="btn btn-primary"></button></div></div>';
        modal.querySelector('h3').textContent = options.title || 'Confirm action';
        modal.querySelector('p').textContent = String(message);
        var buttons = modal.querySelectorAll('button');
        buttons[1].textContent = options.confirmText || 'Confirm';
        buttons[0].addEventListener('click', function () { modal.remove(); });
        buttons[1].addEventListener('click', function () { modal.remove(); onConfirm(); });
        modal.addEventListener('click', function (event) { if (event.target === modal) modal.remove(); });
        document.body.appendChild(modal);
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) { modal.remove(); }
    }

    return {
        feedback: feedback,

        toggleCreatePanel: function () {
            var panel = document.getElementById('createRequestPanel');
            var toggle = document.getElementById('createRequestToggle');
            var icon = document.getElementById('createRequestToggleIcon');
            var label = document.getElementById('createRequestToggleLabel');
            if (!panel) { return; }
            var open = panel.classList.toggle('open');
            if (toggle) { toggle.setAttribute('aria-expanded', open ? 'true' : 'false'); }
            if (icon) { icon.className = open ? 'fas fa-times' : 'fas fa-plus'; }
            if (label) { label.textContent = open ? 'Close' : 'New request'; }
            if (open) { panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
        },

        /** Populate the covered-person list for the chosen Platinum member. */
        onMemberChange: function () {
            var memberSelect = document.getElementById('inpatientMemberSelect');
            var coverageSelect = document.getElementById('inpatientCoverageSelect');
            var memberIdInput = document.getElementById('inpatientMemberId');
            var hint = document.getElementById('inpatientBalanceHint');
            if (!memberSelect || !coverageSelect) { return; }

            var member = memberSelect.value === '' ? null : PLATINUM_MEMBERS[Number(memberSelect.value)];
            coverageSelect.innerHTML = '';
            if (hint) { hint.textContent = ''; }

            if (!member) {
                memberIdInput.value = '';
                coverageSelect.disabled = true;
                coverageSelect.innerHTML = '<option value="">-- select a member first --</option>';
                return;
            }

            memberIdInput.value = member.member_id;
            coverageSelect.disabled = false;
            coverageSelect.innerHTML = '<option value="">-- select covered person --</option>' +
                member.coverages.map(function (coverage) {
                    var suffix = coverage.status === 'active' ? '' : ' — ' + coverage.status.replace(/_/g, ' ');
                    return '<option value="' + coverage.coverage_id + '" data-status="' + escapeHtml(coverage.status) +
                        '" data-name="' + escapeHtml(coverage.covered_person_name) + '">' +
                        escapeHtml(coverage.covered_person_name) + escapeHtml(suffix) + '</option>';
                }).join('');
        },

        confirmCoverage: function (coverageId, action, personName) {
            var approving = action === 'approve';
            confirmThen(
                approving
                    ? 'Activate SHENA Platinum cover for ' + personName + '? This starts the maturity clock and notifies the member by SMS.'
                    : 'Reject the Platinum cover request for ' + personName + '? The member will be notified by SMS and may re-apply.',
                {
                    title: approving ? 'Approve Platinum cover' : 'Reject Platinum cover',
                    confirmText: approving ? 'Yes, approve' : 'Yes, reject',
                    type: approving ? 'success' : 'danger'
                },
                function () {
                    submitAction('/admin/platinum-requests/' + coverageId + '/process', { action: action });
                }
            );
        },

        openInpatientModal: function (requestId, requestedDays, remainingDays, patientName) {
            closeModal('inpatient-modal');
            var suggested = Math.max(0, Math.min(requestedDays, remainingDays));
            var overBalance = remainingDays < requestedDays;
            var html = '' +
                '<div id="inpatient-modal" class="modal-overlay">' +
                '  <div class="modal-box" role="dialog" aria-modal="true">' +
                '    <h3><i class="fas fa-notes-medical" style="color:#7F20B0"></i> Decide: ' + escapeHtml(patientName) + '</h3>' +
                '    <p style="margin:0 0 18px 0;color:#6B7280;font-size:14px">Requested <strong>' + requestedDays + '</strong> days. Balance remaining this calendar year: <strong>' + remainingDays + '</strong> days.</p>' +
                '    <form method="post" action="/admin/inpatient-requests/' + requestId + '/process" onsubmit="return PlatinumAdmin.validateDecision(this, ' + requestedDays + ', ' + remainingDays + ')">' +
                '      <input type="hidden" name="csrf_token" value="' + escapeHtml(CSRF) + '">' +
                '      <label>Approved days (0 rejects the request)</label>' +
                '      <input type="number" name="approved_days" min="0" max="' + requestedDays + '" value="' + suggested + '" required>' +
                '      <label>Admin notes / rejection reason</label>' +
                '      <textarea name="admin_notes" rows="3" placeholder="Shared with the audit trail"></textarea>' +
                (overBalance
                    ? '      <label class="checkbox-row" style="text-transform:none;letter-spacing:0;font-weight:500;color:#374151;margin-bottom:10px">' +
                      '        <input type="checkbox" name="override_balance" value="1" style="width:auto;margin:0" onchange="document.getElementById(\'override_balance_reason\').style.display = this.checked ? \'block\' : \'none\'">' +
                      '        <span>Override the annual day balance for this request</span>' +
                      '      </label>' +
                      '      <textarea id="override_balance_reason" name="override_reason" rows="2" placeholder="Reason for exceeding the remaining balance (required)" style="display:none"></textarea>'
                    : '') +
                '      <div class="modal-actions">' +
                '        <button type="button" class="btn btn-secondary" onclick="PlatinumAdmin.closeInpatientModal()">Cancel</button>' +
                '        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save decision</button>' +
                '      </div>' +
                '    </form>' +
                '  </div>' +
                '</div>';
            document.body.insertAdjacentHTML('beforeend', html);
        },

        /** Block the obvious mistakes client-side; the service re-validates server-side regardless. */
        validateDecision: function (form, requestedDays, remainingDays) {
            var days = Number(form.approved_days.value);
            var override = form.override_balance && form.override_balance.checked;
            if (isNaN(days) || days < 0 || days > requestedDays) {
                feedback('Approved days must be between 0 and ' + requestedDays + '.', 'warning');
                return false;
            }
            if (days > remainingDays && !override) {
                feedback('Only ' + remainingDays + ' days remain this calendar year. Reduce the approved days or tick the balance override.', 'warning');
                return false;
            }
            if (override && !String(form.override_reason.value || '').trim()) {
                feedback('An override reason is required when exceeding the remaining balance.', 'warning');
                return false;
            }
            return true;
        },

        closeInpatientModal: function () { closeModal('inpatient-modal'); }
    };
})();

// Surface server flash messages through the shared modal design.
document.addEventListener('DOMContentLoaded', function () {

});

// Close modals on Escape / backdrop click.
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        PlatinumAdmin.closeInpatientModal();
    }
});
document.addEventListener('click', function (event) {
    if (event.target.classList && event.target.classList.contains('modal-overlay')) {
        event.target.remove();
    }
});
</script>

<?php include __DIR__ . '/../layouts/admin-footer.php'; ?>
