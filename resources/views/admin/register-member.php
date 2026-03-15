<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    .registration-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .form-header {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px 12px 0 0;
        margin-bottom: 0;
    }

    .form-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .form-header p {
        margin: 0;
        opacity: 0.9;
    }

    .registration-form {
        background: white;
        border-radius: 0 0 12px 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #E5E7EB;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-input,
    .form-select {
        padding: 0.75rem;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #E5E7EB;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #6B7280;
        border: 1px solid #D1D5DB;
    }

    .btn-secondary:hover {
        background: #F9FAFB;
    }

    .required {
        color: #EF4444;
    }
</style>

<div class="registration-container">
    <div class="form-header">
        <h1><i class="fas fa-user-plus"></i> Register New Member</h1>
        <p>Complete the form below to register a new member</p>
    </div>

    <form class="registration-form" action="/admin/members/register" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
        <!-- Personal Information -->
        <div class="form-section">
            <h2 class="section-title">Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">National ID <span class="required">*</span></label>
                    <input type="text" name="id_number" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth <span class="required">*</span></label>
                    <input type="date" name="date_of_birth" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender <span class="required">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Select Status</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="form-section">
            <h2 class="section-title">Contact Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone Number <span class="required">*</span></label>
                    <input type="tel" name="phone" class="form-input" placeholder="0712345678" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Physical Address</label>
                    <input type="text" name="address" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">County <span class="required">*</span></label>
                    <input type="text" name="county" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Sub-County</label>
                    <input type="text" name="sub_county" class="form-input">
                </div>
            </div>
        </div>

        <!-- Membership Details -->
        <div class="form-section">
            <h2 class="section-title">Membership Details</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Package <span class="required">*</span></label>
                    <select name="package" class="form-select" required>
                        <option value="">Select Package</option>
                        <?php foreach (($packages ?? []) as $packageKey => $package): ?>
                            <option value="<?php echo htmlspecialchars($packageKey); ?>">
                                <?php echo htmlspecialchars(($package['name'] ?? $packageKey) . ' - KES ' . number_format((float)($package['monthly_contribution'] ?? 0), 0) . '/month'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Corporate Couple Count</label>
                    <select name="corporate_couple_count" class="form-select">
                        <option value="0" selected>None</option>
                        <option value="1">1 Additional Couple (+KES 150)</option>
                        <option value="2">2 Additional Couples (+KES 300)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Referred By (Agent Number)</label>
                    <input type="text" name="agent_number" class="form-input" placeholder="Optional">
                </div>
            </div>
        </div>

        <!-- Next of Kin -->
        <div class="form-section">
            <h2 class="section-title">Next of Kin Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="next_of_kin_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Relationship <span class="required">*</span></label>
                    <input type="text" name="next_of_kin_relationship" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number <span class="required">*</span></label>
                    <input type="tel" name="next_of_kin_phone" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">National ID</label>
                    <input type="text" name="next_of_kin_id" class="form-input">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='/admin/members';">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Register Member
            </button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
