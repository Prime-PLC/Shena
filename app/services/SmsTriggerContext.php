<?php
final class SmsTriggerContext
{
    /** Session identity plus an authenticated staff action route, never posted role/context. */
    public static function isStaffAction(): bool
    {
        if (empty($_SESSION['user_id']) || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return false;
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $role = $_SESSION['user_role'] ?? '';
        if (in_array($role, ['super_admin', 'manager'], true) && str_starts_with($path, '/admin/')) return true;
        if ($role === 'agent' && str_starts_with($path, '/agent/')) return true;
        return in_array($role, ['super_admin', 'manager', 'agent'], true) && str_starts_with($path, '/sms-review/');
    }
}
