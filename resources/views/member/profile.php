<?php
$page = 'settings';
include __DIR__ . '/../layouts/member-header.php';

// Use member data from controller
$memberData = $member ?? [];

// Helper function to retrieve old form data
$getOldValue = function($field) {
    $value = $_SESSION['form_data'][$field] ?? '';
    if (!empty($value)) {
        unset($_SESSION['form_data'][$field]);
        return htmlspecialchars($value);
    }
    return '';
};
?>

<style>
/* Settings Page Styles */
.settings-container {
    padding: 30px 30px 40px 25px;
    background: #F8F9FA;
    min-height: calc(100vh - 80px);
}

.settings-header {
    margin-bottom: 32px;
}

.settings-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.settings-header p {
    font-size: 14px;
    color: #6B7280;
    margin: 0;
}

/* Personal Information Section */
.personal-info-section {
    background: white;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E5E7EB;
}

.section-title-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.section-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    flex-shrink: 0;
}

.section-title-content h2 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin: 0 0 4px 0;
}

.section-title-content p {
    font-size: 13px;
    color: #6B7280;
    margin: 0;
}

.btn-save-changes {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-save-changes:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

/* Form Styles */
.settings-form .form-label {
    font-size: 12px;
    font-weight: 600;
    color: #4B5563;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.settings-form .form-control,
.settings-form .form-select {
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    color: #1F2937;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.settings-form .form-control:focus,
.settings-form .form-select:focus {
    border-color: #7F20B0;
    box-shadow: 0 0 0 3px rgba(127, 32, 176, 0.1);
    outline: none;
}

.settings-form .form-control::placeholder {
    color: #9CA3AF;
}

.settings-form textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Bottom Grid */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

/* Next of Kin Card */
.next-of-kin-card,
.active-package-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header-settings {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E5E7EB;
}

.card-header-settings h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.card-header-settings p {
    font-size: 13px;
    color: #6B7280;
    margin: 4px 0 0 0;
}

/* Active Package Card */
.active-package-card {
    position: relative;
}

.package-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    flex-shrink: 0;
}

.current-plan-label {
    font-size: 11px;
    font-weight: 700;
    color: #7F20B0;
    letter-spacing: 1px;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.package-name {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 16px;
    line-height: 1.3;
}

.coverage-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #7F20B0;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
}

.coverage-badge i {
    font-size: 16px;
}

.btn-upgrade {
    background: linear-gradient(135deg, #7F20B0 0%, #5E2B7A 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    width: 100%;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-upgrade:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(127, 32, 176, 0.3);
}

/* Responsive */
@media (max-width: 992px) {
    .bottom-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .settings-container {
        padding: 20px 15px;
    }

    .personal-info-section,
    .next-of-kin-card,
    .active-package-card {
        padding: 24px;
    }

    .section-header {
        flex-direction: column;
        gap: 16px;
    }

    .btn-save-changes {
        width: 100%;
    }

    .form-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<div class="settings-container">
    <div class="settings-header">
        <h1>Account Settings</h1>
        <p>Manage your personal profile and preferences</p>
    </div>

    <!-- Personal Information Section -->
    <div class="personal-info-section">
        <div class="section-header">
            <div class="section-title-wrapper">
                <div class="section-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="section-title-content">
                    <h2>Personal Information</h2>
                    <p>Update your primary contact and identification details</p>
                </div>
            </div>
            <button type="submit" form="profileForm" class="btn-save-changes">
                Save Changes
            </button>
        </div>

        <form method="POST" action="/profile" id="profileForm" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" 
                        value="<?php $old = $getOldValue('full_name'); echo $old ? $old : htmlspecialchars(trim(($memberData['full_name'] ?? '') ?: (($memberData['first_name'] ?? '') . ' ' . ($memberData['last_name'] ?? '')))); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">National ID / Passport</label>
                    <input type="text" name="national_id" class="form-control" 
                        value="<?php $old = $getOldValue('national_id'); echo $old ? $old : htmlspecialchars($memberData['id_number'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" 
                        value="<?php $old = $getOldValue('phone'); echo $old ? $old : htmlspecialchars($memberData['phone'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" 
                        value="<?php $old = $getOldValue('email'); echo $old ? $old : htmlspecialchars($memberData['email'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Residential Address</label>
                <textarea name="address" class="form-control" rows="3" required><?php $old = $getOldValue('address'); echo $old ? $old : htmlspecialchars($memberData['address'] ?? ''); ?></textarea>
            </div>
        </form>
    </div>

    <!-- Bottom Grid: Next of Kin + Active Package -->
    <div class="bottom-grid">
        <!-- Next of Kin Card -->
        <div class="next-of-kin-card">
            <div class="card-header-settings">
                <div class="section-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div>
                    <h3>Next of Kin</h3>
                    <p>Primary Emergency Contact</p>
                </div>
            </div>

            <form method="POST" action="/profile/next-of-kin" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="next_of_kin_name" class="form-control" 
                        value="<?php $old = $getOldValue('next_of_kin_name'); echo $old ? $old : htmlspecialchars($memberData['next_of_kin'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Relationship</label>
                    <select name="next_of_kin_relationship" class="form-select" required>
                        <?php $old = $getOldValue('next_of_kin_relationship'); $selected = $old ? $old : ($memberData['next_of_kin_relationship'] ?? ''); ?>
                        <option value="Spouse" <?php echo $selected == 'Spouse' ? 'selected' : ''; ?>>Spouse</option>
                        <option value="Parent" <?php echo $selected == 'Parent' ? 'selected' : ''; ?>>Parent</option>
                        <option value="Child" <?php echo $selected == 'Child' ? 'selected' : ''; ?>>Child</option>
                        <option value="Sibling" <?php echo $selected == 'Sibling' ? 'selected' : ''; ?>>Sibling</option>
                        <option value="Friend" <?php echo $selected == 'Friend' ? 'selected' : ''; ?>>Friend</option>
                        <option value="Other" <?php echo $selected == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="next_of_kin_phone" class="form-control" 
                        value="<?php $old = $getOldValue('next_of_kin_phone'); echo $old ? $old : htmlspecialchars($memberData['next_of_kin_phone'] ?? ''); ?>" required>
                </div>

                <button type="submit" class="btn-save-changes" style="width: 100%;">Save Next of Kin</button>
            </form>
        </div>

        <!-- Active Package Card -->
        <div class="active-package-card">
            <div class="card-header-settings">
                <div class="package-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h3>Active Package</h3>
                    <p>Your current subscription</p>
                </div>
            </div>

            <div class="current-plan-label">CURRENT PLAN</div>
            <div class="package-name"><?php echo htmlspecialchars($memberData['package_name'] ?? $memberData['package'] ?? 'Basic'); ?></div>
            
            <?php if ($memberData['is_covered'] ?? true): ?>
            <div class="coverage-badge">
                <i class="fas fa-check-circle"></i>
                Fully Covered
            </div>
            <?php endif; ?>

            <button type="button" class="btn-upgrade" onclick="location.href='/member/upgrade'">
                 <i class="fas fa-arrow-up"></i>
                <i class="fas fa-arrow-up"></i>
                UPGRADE PACKAGE
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/member-footer.php'; ?>
