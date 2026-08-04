<?php 
$member = $member ?? [];
$packages = $packages ?? [];
$membershipPlanData = [];
foreach ($packages as $packageKey => $package) {
    $membershipPlanData[$packageKey] = [
        'name' => $package['name'] ?? $packageKey,
        'monthly_contribution' => (float)($package['monthly_contribution'] ?? 0),
    ];
}
$selectedPackageKey = $member['package_key'] ?? ($member['package'] ?? '');
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .member-edit-container {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 20px;
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        border-bottom: 1px solid #E5E7EB;
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 30px;
    }

    .form-section {
        margin-bottom: 32px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #F3F4F6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: #7F3D9E;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .required {
        color: #DC2626;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .form-select:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    .form-help {
        font-size: 12px;
        color: #6B7280;
        margin-top: 6px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 24px;
        border-top: 1px solid #E5E7EB;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #374151;
        border: 1px solid #D1D5DB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #6EE7B7;
    }

    .alert-error {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FCA5A5;
    }

    .alert i {
        font-size: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .page-header {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="member-edit-container profile-edit-shell">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-edit"></i> Edit Member - <?= htmlspecialchars($member['member_number'] ?? 'N/A') ?>
        </h1>
        <a href="/admin/members/view/<?= $member['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Details
        </a>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="/admin/members/update/<?= $member['id'] ?>" class="form-card">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-user-edit"></i> Member Information
            </h2>
        </div>
        <div class="card-body">
            <!-- Personal Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i> Personal Information
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            First Name <span class="required">*</span>
                        </label>
                        <input type="text" name="first_name" class="form-control" 
                               value="<?= htmlspecialchars($member['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input type="text" name="last_name" class="form-control" 
                               value="<?= htmlspecialchars($member['last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            National ID <span class="required">*</span>
                        </label>
                        <input type="text" name="id_number" class="form-control" 
                               value="<?= htmlspecialchars($member['id_number'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Member Number <span class="required">*</span>
                        </label>
                        <input type="text" name="member_number" class="form-control" 
                               value="<?= htmlspecialchars($member['member_number'] ?? '') ?>" required readonly>
                        <p class="form-help">Member number cannot be changed</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">File Number</label>
                        <input type="text" name="file_number" class="form-control"
                               value="<?= htmlspecialchars($member['file_number'] ?? '') ?>"
                               placeholder="Physical office file reference">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" 
                               value="<?= htmlspecialchars($member['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($member['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($member['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-address-book"></i> Contact Information
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Phone Number <span class="required">*</span>
                        </label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($member['phone'] ?? '') ?>" required
                               placeholder="254XXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                        </label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            County <span class="required">*</span>
                        </label>
                        <select name="county" class="form-select" required>
                            <option value="">Select County</option>
                            <?php 
                            $counties = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Kiambu', 'Machakos', 'Kajiado', 'Meru', 'Nyeri', 'Embu'];
                            foreach ($counties as $county): ?>
                                <option value="<?= $county ?>" <?= ($member['county'] ?? '') === $county ? 'selected' : '' ?>>
                                    <?= $county ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Membership Details -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-id-card-alt"></i> Membership Details
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Package <span class="required">*</span>
                        </label>
                        <select name="package_key" class="form-select" id="memberPackageKey" required>
                            <option value="">Select Package</option>
                            <?php foreach ($packages as $packageKey => $package): ?>
                                <option value="<?= htmlspecialchars($packageKey) ?>" data-monthly-contribution="<?= htmlspecialchars((string)($package['monthly_contribution'] ?? 0)) ?>" <?= $selectedPackageKey === $packageKey ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($package['name'] ?? $packageKey) . ' - KES ' . number_format((float)($package['monthly_contribution'] ?? 0), 0) . '/month') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Member Status</label>
                        <span class="form-help" style="display:block;margin-bottom:6px;">member status</span>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($member['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= ($member['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="grace_period" <?= ($member['status'] ?? '') === 'grace_period' ? 'selected' : '' ?>>Grace Period</option>
                            <option value="inactive" <?= ($member['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <p class="form-help">Change member status here without using the full activation page.</p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Corporate Couple Count</label>
                        <select name="corporate_couple_count" class="form-select" id="memberCorporateCoupleCount">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (int)($member['corporate_couple_count'] ?? 0) === $i ? 'selected' : '' ?>>
                                    <?= $i === 0 ? 'None' : $i . ' Additional Corporate ' . ($i === 1 ? 'Couple' : 'Couples') ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly Contribution</label>
                        <div class="form-control corporate-total-preview" id="memberContributionPreview" data-monthly-contribution="<?= htmlspecialchars((string)($member['monthly_contribution'] ?? 0)) ?>">
                            KES <?= number_format((float)($member['monthly_contribution'] ?? 0), 0) ?>/month
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next of Kin -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-users"></i> Next of Kin
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="nok_name" class="form-control" 
                               value="<?= htmlspecialchars($member['nok_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="nok_relationship" class="form-control" 
                               value="<?= htmlspecialchars($member['nok_relationship'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="nok_phone" class="form-control" 
                               value="<?= htmlspecialchars($member['nok_phone'] ?? '') ?>"
                               placeholder="254XXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">ID Number</label>
                        <input type="text" name="nok_id_number" class="form-control" 
                               value="<?= htmlspecialchars($member['nok_id_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="/admin/members/view/<?= $member['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const membershipPlanData = <?php echo json_encode($membershipPlanData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const packageSelect = document.getElementById('memberPackageKey');
    const corporateSelect = document.getElementById('memberCorporateCoupleCount');
    const preview = document.getElementById('memberContributionPreview');

    function updatePreview() {
        if (!packageSelect || !corporateSelect || !preview) return;
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        const packageKey = packageSelect.value;
        const baseAmount = Number(selectedOption?.dataset.monthlyContribution || membershipPlanData[packageKey]?.monthly_contribution || 0);
        const corporateCount = Number(corporateSelect.value || 0);
        const total = baseAmount * (1 + corporateCount);
        preview.textContent = 'KES ' + total.toLocaleString() + '/month';
    }

    packageSelect?.addEventListener('change', updatePreview);
    corporateSelect?.addEventListener('change', updatePreview);
    updatePreview();
})();
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
