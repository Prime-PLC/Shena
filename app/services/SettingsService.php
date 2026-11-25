<?php
/**
 * Settings Service - Manages Admin-Editable System Configuration
 */
class SettingsService
{
    private $db;
    private static $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get a setting value by key, with fallback default
     */
    public function get($key, $default = null)
    {
        // Check cache first to avoid DB spam
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $sql = "SELECT setting_value FROM system_settings WHERE setting_key = :key";
        $result = $this->db->fetch($sql, ['key' => $key]);

        if ($result) {
            self::$cache[$key] = $result['setting_value'];
            return $result['setting_value'];
        }

        return $default;
    }

    /**
     * Update or Create a setting
     */
    public function set($key, $value, $description = null, $updatedBy = null)
    {
        // Invalidate cache
        unset(self::$cache[$key]);

        // Check if exists
        $sql = "SELECT id FROM system_settings WHERE setting_key = :key";
        $exists = $this->db->fetch($sql, ['key' => $key]);

        if ($exists) {
            return $this->db->update('system_settings', 
                ['setting_value' => $value] + ($updatedBy ? ['updated_by' => (int)$updatedBy] : []), 
                "setting_key = :key", 
                ['key' => $key]
            );
        } else {
            return $this->db->insert('system_settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $description
            ] + ($updatedBy ? ['updated_by' => (int)$updatedBy] : []));
        }
    }
    
    /**
     * Get all settings as associative array
     */
    public function getAll()
    {
        $sql = "SELECT setting_key, setting_value FROM system_settings";
        $results = $this->db->fetchAll($sql);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
}
