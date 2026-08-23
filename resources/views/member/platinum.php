<?php
$page = 'claims';
include __DIR__ . '/../layouts/member-header.php';
$member = $member ?? [];
$coverages = $coverages ?? [];
$beneficiaries = $beneficiaries ?? [];
$corporateMembers = $corporate_members ?? [];
$csrf_token = $csrf_token ?? '';

$statusStyles = [
    'active' => ['bg' => '#D1FAE5', 'color' => '#059669', 'label' => 'ACTIVE'],
    'pending_payment' => ['bg' => '#DBEAFE', 'color' => '#1D4ED8', 'label' => 'AWAITING PAYMENT'],
    'pending_approval' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'label' => 'PENDING APPROVAL'],
    'rejected' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'label' => 'REJECTED'],
];
?>
<style>
main { padding: 0 !important; margin: 0 !important; }

.platinum-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FC;
    max-width: 100%;
}

.page-title { font-size: 1.75rem; font-weight: 700; color: #4A1468; margin: 0 0 8px 0; }
.page-subtitle { color: #6B7280; margin: 0 0 25px 0; max-width: 640px; }

.alert-banner { padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
.alert-success { background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); border-left: 4px solid #10B981; color: #065F46; }
.alert-danger { background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); border-left: 4px solid #EF4444; color: #991B1B; }

.platinum-hero {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 20px;
    padding: 36px 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.platinum-hero::after {
    content: '\f21e';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 110px;
    opacity: 0.12;
}

.platinum-hero h2 { font-size: 1.6rem; font-weight: 700; margin: 0 0 10px 0; position: relative; z-index: 1; }
.platinum-hero p { margin: 0; color: rgba(255,255,255,0.9); max-width: 560px; position: relative; z-index: 1; }
.platinum-hero .hero-badges { display: flex; gap: 12px; margin-top: 18px; flex-wrap: wrap; position: relative; z-index: 1; }
.hero-pill { background: rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }

.request-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 30px; }
.request-card h3 { font-size: 1.25rem; font-weight: 700; color: #1F2937; margin: 0 0 6px 0; }
.request-card .hint { color: #6B7280; font-size: 0.9rem; margin: 0 0 22px 0; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }

.form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
.form-group select {
    width: 100%;
    border: 1.5px solid #E5E7EB;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 0.95rem;
    background: #fff;
    color: #1F2937;
}
.form-group select:focus { outline: none; border-color: #7F3D9E; box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1); }

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

.coverage-card {
    background: #fff;
    border-radius: 16px;
    padding: 22px 26px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.coverage-info h4 { font-size: 1.05rem; font-weight: 700; color: #1F2937; margin: 0 0 4px 0; }
.coverage-info p { font-size: 0.85rem; color: #6B7280; margin: 0; }

.coverage-meta { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
.coverage-meta .meta-block { text-align: right; }
.coverage-meta .meta-block span { display: block; font-size: 0.75rem; color: #6B7280; margin-bottom: 4px; }
.coverage-meta .meta-block strong { font-size: 0.95rem; color: #1F2937; }

.status-badge { padding: 6px 16px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }

.inpatient-link {
    background: #F3E8FF;
    color: #7F20B0;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.inpatient-link:hover { background: #E9D5FF; }

.empty-state { background: #fff; border-radius: 16px; padding: 40px; text-align: center; color: #6B7280; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
</style>

<div class="platinum-container">
    <a href="/claims" style="display:inline-flex;align-items:center;gap:8px;color:#7F20B0;font-weight:600;font-size:0.85rem;text-decoration:none;margin-bottom:14px"><i class="fas fa-arrow-left"></i> Back to Claims</a>
    <h1 class="page-title">SHENA Platinum</h1>
    <p class="page-subtitle">Optional add-on to your Basic membership, providing up to 20 inpatient bed-cover days per covered person, per calendar year.</p>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-banner alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-banner alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="platinum-hero">
        <h2><i class="fas fa-shield-alt" style="margin-right:10px"></i>Inpatient Support Cover</h2>
        <p>Add Platinum cover for yourself, a dependant, or a corporate member. Once approved, each covered person gets up to 20 inpatient days annually, with maturity periods based on age.</p>
        <div class="hero-badges">
            <span class="hero-pill"><i class="fas fa-calendar-check"></i>&nbsp; 20 days / year</span>
            <span class="hero-pill"><i class="fas fa-hourglass-half"></i>&nbsp; Maturity: 4-7 months</span>
            <span class="hero-pill"><i class="fas fa-users"></i>&nbsp; Per-person selection</span>
        </div>
    </div>

    <div class="request-card">
        <h3>Request Platinum Cover</h3>
        <p class="hint">Select who you'd like to cover. Pricing is based on the covered person's age and applied to their monthly contribution once approved.</p>
        <form method="post" action="/platinum/request">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="coveredPersonType">Covered person type</label>
                    <select name="covered_person_type" id="coveredPersonType" required onchange="Platinum.togglePersonSelect(this.value)">
                        <option value="principal">Myself (Principal member)</option>
                        <option value="dependent">Dependant</option>
                        <option value="corporate_member">Corporate member</option>
                    </select>
                </div>
                <div class="form-group" id="personSelectGroup" style="display:none">
                    <label for="coveredPersonId">Select person</label>
                    <select name="covered_person_id" id="coveredPersonId">
                        <option value="">-- choose --</option>
                        <optgroup label="Dependants" class="opt-dependent">
                            <?php foreach ($beneficiaries as $b): ?>
                                <option value="<?php echo (int) $b['id']; ?>" data-type="dependent"><?php echo htmlspecialchars($b['full_name'] ?? 'Dependant'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Corporate members" class="opt-corporate_member">
                            <?php foreach ($corporateMembers as $c): ?>
                                <option value="<?php echo (int) $c['id']; ?>" data-type="corporate_member"><?php echo htmlspecialchars($c['label'] ?? 'Corporate member'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit for admin approval</button>
        </form>
    </div>

    <h3 class="section-title">Your Platinum coverage</h3>
    <?php if (empty($coverages)): ?>
        <div class="empty-state">
            <i class="fas fa-shield-alt" style="font-size:32px;color:#D1D5DB;margin-bottom:12px;display:block"></i>
            <p style="margin:0">You have no Platinum coverage yet. Submit a request above to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($coverages as $coverage):
            $style = $statusStyles[$coverage['status']] ?? ['bg' => '#F3F4F6', 'color' => '#374151', 'label' => strtoupper($coverage['status'])];
        ?>
        <div class="coverage-card">
            <div class="coverage-info">
                <h4><?php echo htmlspecialchars($coverage['covered_person_name'] ?? ucfirst(str_replace('_', ' ', $coverage['covered_person_type']))); ?></h4>
                <p><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $coverage['covered_person_type']))); ?> cover<?php echo $coverage['status'] === 'rejected' ? ' — you may submit a new request above.' : ''; ?></p>
            </div>
            <div class="coverage-meta">
                <div class="meta-block">
                    <span>Monthly</span>
                    <strong>KES <?php echo number_format((float) $coverage['monthly_contribution'], 2); ?></strong>
                </div>
                <div class="meta-block">
                    <span>Maturity</span>
                    <strong><?php echo htmlspecialchars($coverage['maturity_date'] ?? 'Pending approval'); ?></strong>
                </div>
                <span class="status-badge" style="background:<?php echo $style['bg']; ?>;color:<?php echo $style['color']; ?>"><?php echo $style['label']; ?></span>
                <?php if ($coverage['status'] === 'pending_payment'): ?>
                    <button type="button" class="inpatient-link" style="border:none" onclick="Platinum.payNow(<?php echo (int) $coverage['id']; ?>, <?php echo (float) $coverage['monthly_contribution']; ?>, this)"><i class="fas fa-mobile-alt"></i> Pay KES <?php echo number_format((float) $coverage['monthly_contribution'], 2); ?></button>
                <?php endif; ?>
                <?php if ($coverage['status'] === 'active'): ?>
                    <a href="/inpatient-requests" class="inpatient-link"><i class="fas fa-notes-medical"></i> Inpatient requests</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
window.Platinum = window.Platinum || {};
Platinum.togglePersonSelect = function (type) {
    var group = document.getElementById('personSelectGroup');
    var select = document.getElementById('coveredPersonId');
    if (type === 'principal') {
        group.style.display = 'none';
        select.removeAttribute('required');
        select.value = '';
        return;
    }
    group.style.display = 'block';
    select.setAttribute('required', 'required');
    Array.prototype.forEach.call(select.querySelectorAll('optgroup'), function (og) {
        og.style.display = og.classList.contains('opt-' + type) ? '' : 'none';
    });
};
document.addEventListener('DOMContentLoaded', function () {
    Platinum.togglePersonSelect(document.getElementById('coveredPersonType').value);
});

Platinum.payNow = async function (coverageId, amount, btn) {
    var phone = prompt('Enter the M-Pesa phone number to pay KES ' + amount.toFixed(2) + ' (e.g. 07XXXXXXXX):', '<?php echo htmlspecialchars($member['phone'] ?? ''); ?>');
    if (!phone) { return; }
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    try {
        var res = await fetch('/payment/initiate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_id: <?php echo (int) ($member['id'] ?? 0); ?>,
                phone_number: phone,
                amount: amount,
                payment_type: 'platinum',
                platinum_coverage_id: coverageId
            })
        });
        var data = await res.json();
        if (data.success) {
            alert(data.message || 'STK Push sent! Check your phone to complete payment.');
            Platinum.pollPayment(data.checkout_request_id, btn, originalHtml);
        } else {
            alert(data.error || 'Unable to initiate payment.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    } catch (e) {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
};

Platinum.pollPayment = function (checkoutRequestId, btn, originalHtml) {
    if (!checkoutRequestId) { btn.disabled = false; btn.innerHTML = originalHtml; return; }
    var attempts = 0;
    var interval = setInterval(async function () {
        attempts++;
        if (attempts > 30) { clearInterval(interval); btn.disabled = false; btn.innerHTML = originalHtml; return; }
        try {
            var res = await fetch('/payment/status?checkout_request_id=' + encodeURIComponent(checkoutRequestId));
            var data = await res.json();
            if (data.success && data.status && data.status.ResultCode !== undefined) {
                clearInterval(interval);
                if (String(data.status.ResultCode) === '0') {
                    location.reload();
                } else {
                    alert(data.status.ResultDesc || 'Payment failed. Please retry.');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        } catch (e) { /* keep polling */ }
    }, 1000);
};
</script>