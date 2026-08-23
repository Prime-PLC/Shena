<?php
$page = 'claims';
include __DIR__ . '/../layouts/member-header.php';
$requests = $requests ?? [];
$coverages = $coverages ?? [];
$csrf_token = $csrf_token ?? '';
$activeCoverages = array_filter($coverages, fn($c) => $c['status'] === 'active');

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
</style>

<div class="inpatient-container">
    <a href="/platinum" style="display:inline-flex;align-items:center;gap:8px;color:#7F20B0;font-weight:600;font-size:0.85rem;text-decoration:none;margin-bottom:14px"><i class="fas fa-arrow-left"></i> Back to Platinum</a>
    <h1 class="page-title">Inpatient Support</h1>
    <p class="page-subtitle">Submit facility details for admin review. Approval reserves days from the covered person's calendar-year Platinum allowance.</p>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-banner alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-banner alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($activeCoverages)): ?>
        <div class="no-coverage-notice">
            <strong>No active Platinum coverage yet.</strong> Visit <a href="/platinum" style="color:#7F20B0;font-weight:600">SHENA Platinum</a> to request cover before submitting an inpatient request.
        </div>
    <?php else: ?>
    <div class="request-card">
        <h3>Submit Inpatient Request</h3>
        <p class="hint">Choose the Platinum-covered person and provide the admission facility details.</p>
        <form method="post" action="/inpatient-requests">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Covered person</label>
                    <select name="platinum_coverage_id" id="platinumCoverage" required>
                        <?php foreach ($activeCoverages as $c): ?>
                            <option value="<?php echo (int) $c['id']; ?>" data-person="<?php echo htmlspecialchars($c['covered_person_name'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($c['covered_person_name'] ?? $c['covered_person_type']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Registered patient (optional quick-fill)</label>
                    <select id="registeredPatient" onchange="document.querySelector('[name=patient_name]').value=this.value">
                        <option value="">Use registered covered person</option>
                        <?php foreach ($activeCoverages as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['covered_person_name'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($c['covered_person_name'] ?? $c['covered_person_type']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Patient name</label>
                    <input name="patient_name" required>
                </div>
                <div class="form-group">
                    <label>Facility name</label>
                    <input name="facility_name" required>
                </div>
                <div class="form-group">
                    <label>Facility location</label>
                    <input name="facility_location" required>
                </div>
                <div class="form-group">
                    <label>Facility contact</label>
                    <input name="facility_contact">
                </div>
                <div class="form-group">
                    <label>Admission date</label>
                    <input type="date" name="admission_date" required>
                </div>
                <div class="form-group">
                    <label>Requested days (1-20)</label>
                    <input type="number" name="requested_days" min="1" max="20" required>
                </div>
                <div class="form-group">
                    <label>Admission/doctor reference</label>
                    <input name="admission_reference">
                </div>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit inpatient request</button>
        </form>
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
                <p><?php echo htmlspecialchars($r['facility_name']); ?></p>
            </div>
            <div class="meta">
                <span class="days"><?php echo (int) $r['requested_days']; ?>/<?php echo $r['approved_days'] === null ? '-' : (int) $r['approved_days']; ?> days</span>
                <span class="status-badge" style="background:<?php echo $style['bg']; ?>;color:<?php echo $style['color']; ?>"><?php echo $style['label']; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>