<?php

namespace App\Models;

use App\Utils\Database;

class EmailTemplate
{
    public static function all()
    {
        return Database::connection()->select('email_templates')->orderBy('name', 'asc')->fetchAll();
    }

    public static function find($id)
    {
        return Database::connection()->select('email_templates')->where('id', $id)->first();
    }

    public static function create($data)
    {
        return Database::connection()->insert('email_templates')->params($data)->execute();
    }

    public static function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::connection()->update('email_templates')->params($data)->where('id', $id)->execute();
    }

    public static function delete($id)
    {
        return Database::connection()->delete('email_templates')->where('id', $id)->execute();
    }
}
