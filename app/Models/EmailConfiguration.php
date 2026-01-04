<?php

namespace App\Models;

use App\Utils\Database;

class EmailConfiguration
{
    public static function get()
    {
        return Database::connection()->select('email_configurations')->where('is_active', 1)->first();
    }

    public static function create($data)
    {
        // Deactivate all first
        Database::connection()->query("UPDATE email_configurations SET is_active = 0")->execute();

        return Database::connection()->insert('email_configurations')->params($data)->execute();
    }

    public static function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::connection()->update('email_configurations')->params($data)->where('id', $id)->execute();
    }
}
