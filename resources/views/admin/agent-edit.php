<?php 
$agent = $agent ?? [];
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .agent-edit-container {
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

<div class="agent-edit-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-edit"></i> Edit Agent - <?= htmlspecialchars($agent['agent_number'] ?? 'N/A') ?>
        </h1>
        <a href="/admin/agents/view/<?= $agent['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Details
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" action="/admin/agents/update/<?= $agent['id'] ?>" class="form-card">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-user-edit"></i> Agent Information
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
                               value="<?= htmlspecialchars($agent['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input type="text" name="last_name" class="form-control" 
                               value="<?= htmlspecialchars($agent['last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            National ID <span class="required">*</span>
                        </label>
                        <input type="text" name="national_id" class="form-control" 
                               value="<?= htmlspecialchars($agent['national_id'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Agent Number <span class="required">*</span>
                        </label>
                        <input type="text" name="agent_number" class="form-control" 
                               value="<?= htmlspecialchars($agent['agent_number'] ?? '') ?>" required readonly>
                        <p class="form-help">Agent number cannot be changed</p>
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
                               value="<?= htmlspecialchars($agent['phone'] ?? '') ?>" required
                               placeholder="254XXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Email Address <span class="required">*</span>
                        </label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($agent['email'] ?? '') ?>" required>
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
                            $counties = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Kiambu', 'Machakos', 'Kajiado'];
                            foreach ($counties as $county): ?>
                                <option value="<?= $county ?>" <?= ($agent['county'] ?? '') === $county ? 'selected' : '' ?>>
                                    <?= $county ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($agent['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= ($agent['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="inactive" <?= ($agent['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($agent['address'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Commission Settings -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-percentage"></i> Commission Settings
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Commission Rate (%) <span class="required">*</span>
                        </label>
                        <input type="number" name="commission_rate" class="form-control" 
                               value="<?= htmlspecialchars($agent['commission_rate'] ?? '5') ?>" 
                               min="0" max="100" step="0.01" required>
                        <p class="form-help">Percentage of recruitment fees earned as commission</p>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-university"></i> Bank Details
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" 
                               value="<?= htmlspecialchars($agent['bank_name'] ?? '') ?>"
                               placeholder="e.g., Equity Bank">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="bank_account" class="form-control" 
                               value="<?= htmlspecialchars($agent['bank_account'] ?? '') ?>"
                               placeholder="Account number">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Branch</label>
                    <input type="text" name="bank_branch" class="form-control" 
                           value="<?= htmlspecialchars($agent['bank_branch'] ?? '') ?>"
                           placeholder="e.g., Nairobi Branch">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="/admin/agents/view/<?= $agent['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
