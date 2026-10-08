<?php
$page = 'claims';
include __DIR__ . '/../layouts/member-header.php';
$requests = $requests ?? [];
$coverages = $coverages ?? [];
$claimableCoverages = $claimable_coverages ?? [];
$maturityBlocked = !empty($maturity_blocked);
$nextMaturityDate = $next_maturity_date ?? null;
$eligiblePeopleByCoverage = $eligible_people_by_coverage ?? [];
$csrf_token = $csrf_token ?? '';
$hasActiveCoverage = !empty(array_filter($coverages, fn($c) => $c['status'] === 'active'));

$statusStyles = [
    'submitted' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'label' => 'SUBMITTED'],
    'under_review' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'label' => 'UNDER REVIEW'],
    'approved' => ['bg' => '#D1FAE5', 'color' => '#059669', 'label' => 'APPROVED'],
    'partially_approved' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'label' => 'PARTIALLY APPROVED'],
    'rejected' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'label' => 'REJECTED'],
];
?>
<style>
main { padding: 0 !important; margin: 0 !important; }

.inpatient-container { padding: 30px 30px 40px 25px; background: #F8F9FC; max-width: 100%; }
.page-title { font-size: 1.75rem; font-weight: 700; color: #4A1468; margin: 0 0 8px 0; }
.page-subtitle { color: #6B7280; margin: 0 0 25px 0; max-width: 640px; }

.alert-banner { padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
.alert-success { background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); border-left: 4px solid #10B981; color: #065F46; }
.alert-danger { background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); border-left: 4px solid #EF4444; color: #991B1B; }

.request-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 30px; }
.maturity-lock { position:absolute; inset:0; z-index:3; border-radius:20px; background:rgba(255,255,255,0.92); display:flex; align-items:center; justify-content:center; padding:24px; text-align:center; }
.maturity-lock-box { max-width:430px; color:#78350F; }
.request-card h3 { font-size: 1.25rem; font-weight: 700; color: #1F2937; margin: 0 0 6px 0; }
.request-card .hint { color: #6B7280; font-size: 0.9rem; margin: 0 0 22px 0; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
.form-group select, .form-group input {
    width: 100%;
    border: 1.5px solid #E5E7EB;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 0.95rem;
    background: #fff;
    color: #1F2937;
    box-sizing: border-box;
}
.form-group select:focus, .form-group input:focus { outline: none; border-color: #7F3D9E; box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1); }

.submit-btn {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 13px 28px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(127, 32, 176, 0.4); }

.section-title { font-size: 1.3rem; font-weight: 700; color: #1F2937; margin: 0 0 20px 0; }

.request-row {
    background: #fff;
    border-radius: 16px;
    padding: 20px 26px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.request-row .info h4 { font-size: 1rem; font-weight: 700; color: #1F2937; margin: 0 0 4px 0; }
.request-row .info p { font-size: 0.85rem; color: #6B7280; margin: 0; }
.request-row .meta { display: flex; align-items: center; gap: 24px; }
.request-row .days { font-size: 0.9rem; font-weight: 600; color: #1F2937; }
.status-badge { padding: 6px 16px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }

.empty-state { background: #fff; border-radius: 16px; padding: 40px; text-align: center; color: #6B7280; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.no-coverage-notice { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-left: 4px solid #F59E0B; padding: 18px 22px; border-radius: 12px; margin-bottom: 25px; color: #78350F; }

.form-legend { font-size: 0.8rem; font-weight: 700; color: #7F20B0; text-transform: uppercase; letter-spacing: 0.6px; margin: 4px 0 14px 0; display: flex; align-items: center; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid #F3E8FF; }
.form-legend:not(:first-of-type) { margin-top: 12px; }
.field-hint { display: block; font-size: 0.78rem; color: #9CA3AF; margin-top: 6px; line-height: 1.4; }
.req { color: #EF4444; }
</style>

<div class="inpatient-container">
    <a href="/platinum" style="display:inline-flex;align-items:center;gap:8px;color:#7F20B0;font-weight:600;font-size:0.85rem;text-decoration:none;margin-bottom:14px"><i class="fas fa-arrow-left"></i> Back to Platinum</a>
    <h1 class="page-title">Inpatient Support</h1>
    <p class="page-subtitle">Submit facility details for admin review. Approval reserves days from the selected Platinum package group's shared calendar-year allowance.</p>

    <?php
        $flashSuccess = $_SESSION['success'] ?? '';
        $flashError = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);
    ?>
    <?php if ($flashSuccess !== ''): ?>
        <div class="alert-banner alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== ''): ?>
        <div class="alert-banner alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>

    <?php if (!$hasActiveCoverage): ?>
        <div class="no-coverage-notice">
            <strong>No active Platinum coverage yet.</strong> Visit <a href="/platinum" style="color:#7F20B0;font-weight:600">SHENA Platinum</a> to request cover before submitting an inpatient request.
        </div>
    <?php else: ?>
    <div class="request-card" style="position:relative;">
        <h3>Submit Inpatient Request</h3>
        <p class="hint">Choose the Platinum-covered person and provide the admission facility details. After filing, upload admission proof by 23:59:59 EAT on admission day. If unavailable, call 0748585067 or 0748585071 immediately. Admin-recorded late-evidence exceptions never waive proof.</p>
        <form method="post" action="/inpatient-requests" id="inpatientForm" onsubmit="return Inpatient.validate(this)">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <h4 class="form-legend"><i class="fas fa-user-injured"></i> Who was admitted</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label for="platinumCoverage">Platinum package group <span class="req">*</span></label>
                    <select name="platinum_coverage_id" id="platinumCoverage" required onchange="Inpatient.syncPatients()">
                        <?php foreach ($claimableCoverages as $c): ?>
                            <option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars(($c['package_name'] ?? $c['covered_person_name'] ?? $c['covered_person_type']) . ' group'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-hint">Only active Platinum package groups are listed.</small>
                </div>
                <div class="form-group">
                    <label for="patientSelect">Patient covered by this group <span class="req">*</span></label>
                    <select id="patientSelect" required onchange="Inpatient.syncPatientFields()"></select>
                    <input type="hidden" name="patient_type" id="patientType">
                    <input type="hidden" name="patient_id" id="patientId">
                    <small class="field-hint">The group owner and its covered dependants share the same 20-day allowance.</small>
                </div>
            </div>

            <h4 class="form-legend"><i class="fas fa-hospital"></i> Facility details</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label for="facilityName">Facility name <span class="req">*</span></label>
                    <input name="facility_name" id="facilityName" required placeholder="e.g. Nairobi West Hospital">
                </div>
                <div class="form-group">
                    <label for="facilityLocation">Facility location <span class="req">*</span></label>
                    <input name="facility_location" id="facilityLocation" required placeholder="Town / county / ward">
                </div>
                <div class="form-group">
                    <label for="facilityContact">Facility phone contact <span class="req">*</span></label>
                    <input name="facility_contact" id="facilityContact" required placeholder="Facility contact for SHENA SMS confirmation">
                    <small class="field-hint">SHENA sends a submission confirmation here and may use it to verify admission details.</small>
                </div>
                <div class="form-group">
                    <label for="admissionReference">Admission / doctor reference</label>
                    <input name="admission_reference" id="admissionReference" placeholder="Admission number or attending doctor">
                </div>
            </div>

            <h4 class="form-legend"><i class="fas fa-calendar-check"></i> Admission &amp; days requested</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label for="admissionDate">Admission date <span class="req">*</span></label>
                    <input type="date" name="admission_date" id="admissionDate" required max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="requestedDays">Inpatient bed-cover days requested <span class="req">*</span></label>
                    <input type="number" name="requested_days" id="requestedDays" min="1" max="20" required>
                    <small class="field-hint">Up to the group's remaining shared 20 days per calendar year. Days can be split across admissions.</small>
                </div>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit inpatient request</button>
        </form>
        <?php if ($maturityBlocked): ?>
            <div class="maturity-lock" aria-live="polite">
                <div class="maturity-lock-box">
                    <i class="fas fa-hourglass-half" style="font-size:30px;color:#D97706"></i>
                    <h3 style="margin:12px 0 8px;color:#92400E">Platinum maturity period in progress</h3>
                    <p style="margin:0;line-height:1.55">Your approved Platinum cover becomes available for inpatient requests on <strong><?= htmlspecialchars(date('d M Y', strtotime($nextMaturityDate))) ?></strong>. The form will unlock automatically on that date.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h3 class="section-title">Your Requests</h3>
    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <i class="fas fa-notes-medical" style="font-size:32px;color:#D1D5DB;margin-bottom:12px;display:block"></i>
            <p style="margin:0">No inpatient requests submitted yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($requests as $r):
            $style = $statusStyles[$r['status']] ?? ['bg' => '#F3F4F6', 'color' => '#374151', 'label' => strtoupper($r['status'])];
        ?>
        <div class="request-row">
            <div class="info">
                <h4><?php echo htmlspecialchars($r['patient_name']); ?></h4>
                <p>
                    <?php echo htmlspecialchars($r['facility_name']); ?> <a href="/claim-documents/inpatient/<?= (int)$r['id'] ?>">Upload admission proof / next steps</a><?php echo !empty($r['facility_location']) ? ' &middot; ' . htmlspecialchars($r['facility_location']) : ''; ?>
                    &middot; admitted <?php echo htmlspecialchars(date('d M Y', strtotime($r['admission_date']))); ?>
                </p>
                <?php if (!empty($r['admin_notes']) || !empty($r['rejection_reason'])): ?>
                    <p style="margin-top:6px;color:#4B5563"><i class="fas fa-comment-dots"></i> <?php echo htmlspecialchars($r['rejection_reason'] ?: $r['admin_notes']); ?></p>
                <?php endif; ?>
            </div>
            <div class="meta">
                <span class="days">
                    <?php echo $r['approved_days'] === null ? 'Requested ' . (int) $r['requested_days'] : (int) $r['approved_days'] . ' of ' . (int) $r['requested_days']; ?> days
                </span>
                <span class="status-badge" style="background:<?php echo $style['bg']; ?>;color:<?php echo $style['color']; ?>"><?php echo $style['label']; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
var inpatientPeopleByCoverage = <?php echo json_encode($eligiblePeopleByCoverage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
window.Inpatient = (function () {
    function feedback(message, type) {
        if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
            ShenaApp.alert(message, type);
            return;
        }
        var banner = document.createElement('div');
        banner.className = 'alert-banner alert-danger';
        banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span></span>';
        banner.querySelector('span').textContent = String(message);
        var container = document.querySelector('.inpatient-container');
        if (container) container.insertBefore(banner, container.querySelector('.request-card') || container.firstChild);
    }

    return {
        feedback: feedback,

        syncPatients: function () {
            var select = document.getElementById('platinumCoverage');
            var patientSelect = document.getElementById('patientSelect');
            if (!select || !patientSelect) { return; }
            var people = inpatientPeopleByCoverage[String(select.value)] || [];
            patientSelect.innerHTML = '';
            people.forEach(function (person) {
                var option = document.createElement('option');
                option.value = String(person.type) + ':' + String(person.id);
                option.textContent = person.name;
                option.dataset.type = person.type;
                option.dataset.id = person.id;
                patientSelect.appendChild(option);
            });
            Inpatient.syncPatientFields();
        },

        syncPatientFields: function () {
            var select = document.getElementById('patientSelect');
            var option = select && select.options[select.selectedIndex];
            document.getElementById('patientType').value = option ? option.dataset.type : '';
            document.getElementById('patientId').value = option ? option.dataset.id : '';
        },

        validate: function (form) {
            var days = Number(form.requested_days.value);
            if (isNaN(days) || days < 1 || days > 20) {
                feedback('Request between 1 and 20 inpatient bed-cover days.', 'warning');
                return false;
            }
            if (!String(form.facility_name.value || '').trim() || !String(form.facility_location.value || '').trim() || !String(form.facility_contact.value || '').trim()) {
                feedback('Facility name, location, and a facility SMS contact are required.', 'warning');
                return false;
            }
            return true;
        }
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    Inpatient.syncPatients();
});
</script>
