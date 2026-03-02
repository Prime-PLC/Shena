<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .page-subtitle {
        font-size: 13px;
        color: #9CA3AF;
        margin: 0;
    }

    .btn-back {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: #F9FAFB;
        color: #1F2937;
        transform: translateY(-1px);
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 32px;
        border: 1px solid #E5E7EB;
        margin: 0 auto;
        max-width: 1000px;
    }

    /* Section Headers */
    .section-header {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #F3F4F6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-header i {
        color: #7F3D9E;
        font-size: 18px;
    }

    /* Form Controls */
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: block;
    }

    .form-label .required {
        color: #DC2626;
        margin-left: 4px;
    }

    .form-control {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #7F3D9E;
        box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1);
    }

    .form-control::placeholder {
        color: #9CA3AF;
    }

    .form-text {
        font-size: 12px;
        color: #9CA3AF;
        margin-top: 4px;
        display: block;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    /* Input Group */
    .input-group {
        position: relative;
        display: flex;
    }

    .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .btn-toggle {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        border-left: none;
        background: white;
        color: #7F3D9E;
        cursor: pointer;
        transition: all 0.2s;
    }

    .input-group .btn-toggle:hover {
        background: #F9FAFB;
        color: #1F2937;
    }

    /* Form Actions */
    .form-actions {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 20px 24px;
        margin-top: 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-info {
        font-size: 13px;
        color: #6B7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-buttons {
        display: flex;
        gap: 12px;
    }

    .btn-reset {
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #E5E7EB;
        background: white;
        color: #6B7280;
        transition: all 0.2s;
    }

    .btn-reset:hover {
        background: #F9FAFB;
        color: #1F2937;
    }

    .btn-submit {
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: #7F3D9E;
        color: white;
        transition: all 0.2s;
    }

    .btn-submit:hover {
        background: #7F3D9E;
        transform: translateY(-1px);
    }

    .btn-submit:disabled {
        background: #9CA3AF;
        cursor: not-allowed;
        transform: none;
    }

    /* Section Spacing */
    .form-section {
        margin-bottom: 32px;
    }

    .form-section:last-of-type {
        margin-bottom: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-card {
            padding: 20px;
        }

        .form-actions {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }

        .form-buttons {
            width: 100%;
        }

        .btn-reset,
        .btn-submit {
            flex: 1;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Register New Agent</h1>
        <p class="page-subtitle">Add a new agent to the system</p>
    </div>
    <a href="/admin/agents" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Back to Agents
    </a>
</div>

<!-- Registration Form -->
<div class="form-card">
    <form method="POST" action="/admin/agents/store" id="agentForm">
        <!-- Personal Information Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-user"></i>
                Personal Information
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">
                        First Name <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           placeholder="Enter first name" value="<?= htmlspecialchars($_SESSION['old_input']['first_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">
                        Last Name <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           placeholder="Enter last name" value="<?= htmlspecialchars($_SESSION['old_input']['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="id_number" class="form-label">
                        National ID Number <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" id="id_number" name="id_number" 
                           placeholder="Enter ID number" pattern="[0-9]{7,8}" value="<?= htmlspecialchars($_SESSION['old_input']['id_number'] ?? '') ?>" required>
                    <small class="form-text">7 or 8 digits</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">
                        Date of Birth <span class="required">*</span>
                    </label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>" value="<?= htmlspecialchars($_SESSION['old_input']['date_of_birth'] ?? '') ?>" required>
                    <small class="form-text">Must be at least 18 years old</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">
                        Gender <span class="required">*</span>
                    </label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="">Select gender</option>
                        <option value="male" <?= (($_SESSION['old_input']['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= (($_SESSION['old_input']['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= (($_SESSION['old_input']['gender'] ?? '') === 'other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-address-card"></i>
                Contact Information
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">
                        Phone Number <span class="required">*</span>
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           placeholder="0712345678" pattern="^(\+254|0)[17][0-9]{8}$" value="<?= htmlspecialchars($_SESSION['old_input']['phone'] ?? '') ?>" required>
                    <small class="form-text">Format: 07XXXXXXXX or +254XXXXXXXXX</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">
                        Email Address <span class="required">*</span>
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="agent@example.com" value="<?= htmlspecialchars($_SESSION['old_input']['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="county" class="form-label">
                        County <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" id="county" name="county" 
                           placeholder="Enter county" value="<?= htmlspecialchars($_SESSION['old_input']['county'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sub_county" class="form-label">
                        Sub-County
                    </label>
                    <input type="text" class="form-control" id="sub_county" name="sub_county" 
                           placeholder="Enter sub-county" value="<?= htmlspecialchars($_SESSION['old_input']['sub_county'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">
                    Physical Address
                </label>
                <textarea class="form-control" id="address" name="address" rows="2" 
                          placeholder="Enter physical address"><?= htmlspecialchars($_SESSION['old_input']['address'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Account Information Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-key"></i>
                Account Information
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">
                        Password <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password" minlength="8" required>
                        <button class="btn-toggle" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-text">Minimum 8 characters</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">
                        Confirm Password <span class="required">*</span>
                    </label>
                    <input type="password" class="form-control" id="password_confirmation" 
                           name="password_confirmation" placeholder="Confirm password" 
                           minlength="8" required>
                </div>
            </div>
        </div>

        <!-- Commission Information Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-percentage"></i>
                Commission Information
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="commission_rate" class="form-label">
                        Commission Rate (%) <span class="required">*</span>
                    </label>
                    <input type="number" class="form-control" id="commission_rate" 
                           name="commission_rate" placeholder="10" min="0" max="100" 
                           step="0.01" value="<?= htmlspecialchars($_SESSION['old_input']['commission_rate'] ?? '10') ?>" required>
                    <small class="form-text">Percentage of contribution (default: 10%)</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bank_account" class="form-label">
                        Bank Account Number
                    </label>
                    <input type="text" class="form-control" id="bank_account" name="bank_account" 
                           placeholder="Enter bank account number" value="<?= htmlspecialchars($_SESSION['old_input']['bank_account'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="bank_name" class="form-label">
                        Bank Name
                    </label>
                    <input type="text" class="form-control" id="bank_name" name="bank_name" 
                           placeholder="Enter bank name" value="<?= htmlspecialchars($_SESSION['old_input']['bank_name'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bank_branch" class="form-label">
                        Bank Branch
                    </label>
                    <input type="text" class="form-control" id="bank_branch" name="bank_branch" 
                           placeholder="Enter bank branch" value="<?= htmlspecialchars($_SESSION['old_input']['bank_branch'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Status Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-toggle-on"></i>
                Account Status
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        Status <span class="required">*</span>
                    </label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="active" <?= (($_SESSION['old_input']['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($_SESSION['old_input']['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= (($_SESSION['old_input']['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                    <small class="form-text">Set initial account status</small>
                </div>
            </div>
        </div>

        <!-- Additional Notes -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-sticky-note"></i>
                Additional Information
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">
                    Notes (Optional)
                </label>
                <textarea class="form-control" id="notes" name="notes" rows="3" 
                          placeholder="Add any additional notes about this agent"><?= htmlspecialchars($_SESSION['old_input']['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="form-info">
                <i class="fas fa-info-circle"></i>
                Fields marked with <span class="required">*</span> are required
            </div>
            <div class="form-buttons">
                <button type="reset" class="btn-reset">
                    <i class="fas fa-undo"></i> Reset Form
                </button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Register Agent
                </button>
            </div>
        </div>
    </form>
    <?php 
        // Clear old input after displaying
        unset($_SESSION['old_input']); 
    ?>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Validate password confirmation
document.getElementById('agentForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    
    if (password !== confirmation) {
        e.preventDefault();
        ShenaApp.showNotification('Passwords do not match. Please check and try again.', 'warning');
        document.getElementById('password_confirmation').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
});

// Format phone number
document.getElementById('phone').addEventListener('blur', function() {
    let phone = this.value.replace(/\s+/g, '');
    if (phone.startsWith('0') && phone.length === 10) {
        // Valid format
    } else if (phone.startsWith('+254') || phone.startsWith('254')) {
        // Convert to local format
        phone = '0' + phone.replace(/^\+?254/, '');
        this.value = phone;
    }
});

// Validate ID number
document.getElementById('id_number').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Set max date for date of birth (18 years ago)
const eighteenYearsAgo = new Date();
eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);
document.getElementById('date_of_birth').max = eighteenYearsAgo.toISOString().split('T')[0];
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
