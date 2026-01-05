<?php

namespace App\Models;

use App\Utils\Database;

class Setting
{
    public static function get($key, $default = null)
    {
        $db = Database::connection();

        if (!$db) {
            return $default;
        }

        $result = $db->query("SELECT value FROM settings WHERE key_name = ?")
            ->bind($key)
            ->fetchAssoc();

        if ($result) {
            return $result['value'];
        }

        return $default;
    }

    public static function set($key, $value)
    {
        $db = Database::connection();

        if (!$db) {
            return;
        }

        $exists = $db->query("SELECT 1 FROM settings WHERE key_name = ?")
            ->bind($key)
            ->fetchAssoc();

        if ($exists) {
            $db->query("UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE key_name = ?")
                ->bind($value, $key)
                ->execute();
        } else {
            $db->query("INSERT INTO settings (key_name, value, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)")
                ->bind($key, $value)
                ->execute();
        }
    }

    // Helper to ensure table exists (Compatible with existing schema)
    public static function ensureTableExists()
    {
        $db = Database::connection();
        if (!$db) {
            return;
        }
        // If table exists, this does nothing. If it doesn't, we create it to match what we found.
        $db->query("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_name VARCHAR(255) NOT NULL,
            value TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )")->execute();
    }
}
