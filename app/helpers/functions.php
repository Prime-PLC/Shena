<?php
/**
 * Helper Functions - Global utility functions
 */

/**
 * Escape HTML output
 */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get session flash message
 */
function getFlashMessage($key, $default = null)
{
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return $default;
}

/**
 * Set session flash message
 */
function setFlashMessage($key, $message)
{
    $_SESSION[$key] = $message;
}

/**
 * Collect all legacy and current flash-message keys once.
 */
function collectFlashMessages()
{
    $messages = [];
    $legacyKeys = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'success_message' => 'success',
        'error_message' => 'error',
        'warning_message' => 'warning',
        'info_message' => 'info',
    ];

    foreach ($legacyKeys as $key => $type) {
        if (!empty($_SESSION[$key])) {
            $messages[] = ['type' => $type, 'message' => (string)$_SESSION[$key]];
            unset($_SESSION[$key]);
        }
    }

    if (!empty($_SESSION['flash_message'])) {
        $messages[] = [
            'type' => $_SESSION['flash_type'] ?? 'info',
            'message' => (string)$_SESSION['flash_message'],
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }

    return $messages;
}

/**
 * Render queued flash messages immediately in the current layout.
 */
function renderFlashMessagesScript($duration = 5000)
{
    $messages = collectFlashMessages();
    if (empty($messages)) {
        return;
    }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = <?php echo json_encode($messages); ?>;
            window.__shenaFlashDispatched = window.__shenaFlashDispatched || {};

            flashMessages.forEach(function(flash) {
                const key = flash.type + ':' + flash.message;
                if (window.__shenaFlashDispatched[key]) return;
                window.__shenaFlashDispatched[key] = true;

                if (window.ShenaApp && typeof ShenaApp.showNotification === 'function') {
                    ShenaApp.showNotification(flash.message, flash.type, <?php echo (int)$duration; ?>);
                    return;
                }
                if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
                    ShenaApp.alert(flash.message, flash.type);
                    return;
                }
                console.warn(flash.message);
            });
        });
    </script>
    <?php
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'KES')
{
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M j, Y')
{
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A')
{
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

/**
 * Calculate time ago
 */
function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'just now';
    } elseif ($time < 3600) {
        return floor($time / 60) . ' minutes ago';
    } elseif ($time < 86400) {
        return floor($time / 3600) . ' hours ago';
    } elseif ($time < 2592000) {
        return floor($time / 86400) . ' days ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 10)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has role
 */
function hasRole($roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles);
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return hasRole(['super_admin', 'manager']);
}

/**
 * Get current user ID
 */
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Redirect if not logged in
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin()
{
    requireLogin();
    
    if (!isAdmin()) {
        header('Location: /error/403');
        exit;
    }
}

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: {$url}");
    exit;
}

/**
 * Generate URL
 */
function url($path = '')
{
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 */
function asset($path)
{
    return APP_URL . '/public/' . ltrim($path, '/');
}

/**
 * Include CSS file
 */
function css($file)
{
    echo '<link rel="stylesheet" href="' . asset('css/' . $file) . '">';
}

/**
 * Include JS file
 */
function js($file)
{
    echo '<script src="' . asset('js/' . $file) . '"></script>';
}

/**
 * Validate Kenyan phone number
 */
function validateKenyanPhone($phone)
{
    // Accept formats: 07XXXXXXXX, +2547XXXXXXXX, 2547XXXXXXXX, or 7XXXXXXXX
    $pattern = '/^(\+254|254|0)?(7[0-9]{8})$/';
    return preg_match($pattern, $phone) === 1;
}

/**
 * Format Kenyan phone number
 */
function formatKenyanPhone($phone)
{
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Handle different formats
    // Normalize to MSISDN without plus: 2547XXXXXXXX
    if (substr($phone, 0, 3) === '254') {
        return $phone;
    } elseif (substr($phone, 0, 1) === '0') {
        return '254' . substr($phone, 1);
    } elseif (strlen($phone) === 9) {
        return '254' . $phone;
    }

    return $phone;
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dateOfBirth)
{
    $today = new DateTime();
    $dob = new DateTime($dateOfBirth);
    return $today->diff($dob)->y;
}

/**
 * Check if age is eligible for membership (18-100 years)
 * 
 * @param int $age Member's age
 * @return bool True if age is within eligible range
 */
function isAgeEligible($age)
{
    return $age >= 18 && $age <= 100;
}

/**
 * Get maturity period in months based on age
 * Maturity period is the waiting period before coverage becomes active
 * 
 * @param int $age Member's age
 * @return int Maturity period in months (4 or 5)
 */
function getMaturityPeriodMonths($age)
{
    // 5 months for members aged 81-100 years
    // 4 months for members aged 1-80 years
    return ($age >= 81 && $age <= 100) ? MATURITY_PERIOD_80_AND_ABOVE : MATURITY_PERIOD_UNDER_80;
}

/**
 * Get grace period in months (allowance for late payment)
 * Grace period is the window to pay late without losing benefits
 * 
 * @return int Grace period in months
 */
function getGracePeriodMonths()
{
    return GRACE_PERIOD_MONTHS; // 2 months max
}

/**
 * Get membership package details
 */
function getPackageDetails($package)
{
    global $membership_packages;
    return $membership_packages[$package] ?? null;
}

/**
 * Check if a death cause is excluded from coverage
 * 
 * @param string $cause Death cause code
 * @return bool True if cause is excluded
 */
function isExcludedCause($cause)
{
    $excludedCauses = [
        'self_medication',
        'drug_abuse',
        'substance_abuse',
        'criminal_activity',
        'civil_commotion',
        'riots',
        'war',
        'terrorism',
        'hazardous_activities'
    ];
    
    return in_array($cause, $excludedCauses);
}

/**
 * Get list of excluded death causes
 * 
 * @return array Excluded causes with descriptions
 */
function getExcludedCauses()
{
    return [
        'self_medication' => 'Deaths from self-medication without proper medical supervision',
        'drug_abuse' => 'Deaths resulting from drug use and substance abuse',
        'substance_abuse' => 'Deaths from addiction-related causes',
        'criminal_activity' => 'Deaths while engaged in criminal acts',
        'civil_commotion' => 'Deaths during civil unrest, riots or mass demonstrations',
        'riots' => 'Deaths caused by riots or mass violence',
        'war' => 'Deaths from military action, war or combat',
        'terrorism' => 'Deaths caused by terrorist attacks or acts of terrorism',
        'hazardous_activities' => 'Deaths during participation in extremely hazardous activities (skydiving, BASE jumping, etc.)'
    ];
}

/**
 * Validate claim eligibility for a member
 * Checks all policy conditions before claim can be approved
 * 
 * @param array $member Member details
 * @param array $claim Claim details
 * @return array Status and message [success => bool, message => string]
 */
function validateClaimEligibility($member, $claim)
{
    // Check if member is registered
    if (!$member) {
        return [
            'success' => false,
            'message' => 'Deceased is not a registered member'
        ];
    }
    
    // Check if member is in good standing (not defaulted for more than 2 months)
    if ($member['status'] === 'defaulted') {
        return [
            'success' => false,
            'message' => 'Member is in default status. Membership must be reactivated.'
        ];
    }
    
    // Check if maturity period has been completed
    if (!empty($member['maturity_ends']) && strtotime($member['maturity_ends']) > time()) {
        $daysRemaining = ceil((strtotime($member['maturity_ends']) - time()) / 86400);
        return [
            'success' => false,
            'message' => "Maturity period not yet completed. {$daysRemaining} days remaining."
        ];
    }
    
    // Check if claim is submitted before coverage ends
    if (!empty($member['coverage_ends']) && strtotime($claim['date_of_death']) > strtotime($member['coverage_ends'])) {
        return [
            'success' => false,
            'message' => 'Claim date is beyond membership coverage period'
        ];
    }
    
    // Check if death cause is excluded
    if (!empty($claim['cause_of_death']) && isExcludedCause($claim['cause_of_death'])) {
        $excludedCauses = getExcludedCauses();
        $reason = $excludedCauses[$claim['cause_of_death']] ?? 'Excluded cause of death';
        return [
            'success' => false,
            'message' => "Claim cannot be approved: {$reason}"
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Member is eligible for claim'
    ];
}

/**
 * Calculate mortuary bill payment (capped at 14 days)
 * 
 * @param int $daysOfPreservation Days of mortuary preservation
 * @param float $dailyRate Daily mortuary rate
 * @return float Amount to be paid (capped at 14 days)
 */
if (!defined('MORTUARY_DAYS_COVERED')) {
    define('MORTUARY_DAYS_COVERED', 14);
}
function calculateMortuaryBillPayment($daysOfPreservation, $dailyRate)
{
    $daysToCharge = min($daysOfPreservation, MORTUARY_DAYS_COVERED);
    return $daysToCharge * $dailyRate;
}

/**
 * Check if file upload is valid
 */
function isValidUpload($file, $allowedTypes = null)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $allowedTypes = $allowedTypes ?? ALLOWED_FILE_TYPES;
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    return in_array($fileExt, $allowedTypes);
}

/**
 * Upload file securely
 */
function uploadFile($file, $directory, $allowedTypes = null)
{
    if (!isValidUpload($file, $allowedTypes)) {
        return false;
    }
    
    $uploadDir = UPLOADS_PATH . '/' . trim($directory, '/') . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = generateRandomString(20) . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'mime_type' => $file['type']
        ];
    }
    
    return false;
}

/**
 * Log activity
 */
function logActivity($action, $details = '', $userId = null)
{
    try {
        $userId = $userId ?? getCurrentUserId();
        
        if ($userId) {
            $db = Database::getInstance();
            $db->insert('activity_logs', [
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
    } catch (Exception $e) {
        error_log('Activity logging error: ' . $e->getMessage());
    }
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status)
{
    $classes = [
        'active' => 'badge-success',
        'inactive' => 'badge-secondary',
        'pending' => 'badge-warning',
        'defaulted' => 'badge-danger',
        'grace_period' => 'badge-info',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        'submitted' => 'badge-primary',
        'completed' => 'badge-success',
        'failed' => 'badge-danger'
    ];
    
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate pagination HTML
 */
function pagination($currentPage, $totalPages, $baseUrl)
{
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav aria-label="Page navigation"><ul class="pagination">';

    // Previous button
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $prevPage . '">Previous</a></li>';
    }

    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $nextPage . '">Next</a></li>';
    }

    $html .= '</ul></nav>';

    return $html;
}

/**
 * Get system setting from database
 *
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value or default
 */
function get_system_setting($key, $default = null)
{
    try {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = :key", ['key' => $key]);
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        error_log('Error getting system setting: ' . $e->getMessage());
        return $default;
    }
}
