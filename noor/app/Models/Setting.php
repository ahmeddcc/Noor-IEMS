<?php
namespace App\Models;

use App\Core\Database;

class Setting {
    /**
     * Get a setting value by key
     * @param string $key The setting key
     * @param string $default Default value if not found
     * @return string
     */
    public static function get($key, $default = '') {
        try {
            $db = Database::getInstance();
            $result = $db->fetch("SELECT value FROM settings WHERE key = ? LIMIT 1", [$key]);
            
            if ($result && isset($result['value']) && $result['value'] !== '') {
                return $result['value'];
            }
        } catch (\Exception $e) {
            // Silently fail and return default if DB error occurs
        }
        
        return $default;
    }
}
