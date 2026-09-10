<?php

/**
 * Helper function to retrieve old form data from session
 * Used for form persistence on validation errors
 */
function getOldValue($field, $reset = true) {
    $value = $_SESSION['form_data'][$field] ?? '';
    if (!empty($value) && $reset) {
        unset($_SESSION['form_data'][$field]);
    }
    return !empty($value) ? htmlspecialchars($value) : '';
}

/**
 * Get old value or fallback value
 * Used when form data should fall back to an alternate source
 */
function getOldValueOr($field, $fallback = '') {
    $old = $_SESSION['form_data'][$field] ?? '';
    if (!empty($old)) {
        unset($_SESSION['form_data'][$field]);
        return htmlspecialchars($old);
    }
    if (is_callable($fallback)) {
        return $fallback();
    }
    return htmlspecialchars($fallback);
}

/**
 * Check if a field has old value
 */
function hasOldValue($field) {
    return !empty($_SESSION['form_data'][$field]);
}

/**
 * Get old value without clearing it (for checkboxes, radio buttons)
 */
function isOldValueChecked($field, $value) {
    $old = $_SESSION['form_data'][$field] ?? '';
    return $old == $value ? 'checked' : '';
}

/**
 * Get old value selected for selects (for selects, dropdowns)
 */
function isOldValueSelected($field, $value) {
    $old = $_SESSION['form_data'][$field] ?? '';
    return $old == $value ? 'selected' : '';
}

/** Retain failed admin claim entries across redirects without persisting them in browser storage. */
function renderClaimFormState(string $key, string $formId): void {
    $values = $_SESSION[$key] ?? [];
    $error = $_SESSION[$key . '_error'] ?? '';
    unset($_SESSION[$key], $_SESSION[$key . '_error']);
    if (!$values && !$error) return;
    echo '<script type="application/json" data-claim-form-state="' . htmlspecialchars($formId, ENT_QUOTES) . '">' . json_encode(['values'=>$values, 'error'=>$error], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) . '</script>';
}
