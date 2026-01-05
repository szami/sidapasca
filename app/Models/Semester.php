<?php

namespace App\Models;

use App\Utils\Database;

class Semester
{
    public static function all()
    {
        return Database::connection()->select('semesters')->orderBy('kode', 'desc')->fetchAll();
    }

    public static function find($id)
    {
        return Database::connection()->select('semesters')->where('id', $id)->fetchAssoc();
    }

    public static function getActive()
    {
        return Database::connection()->select('semesters')->where('is_active', 1)->fetchAssoc();
    }

    public static function create($data)
    {
        return Database::connection()->insert('semesters')->params($data)->execute();
    }

    public static function update($id, $data)
    {
        return Database::connection()->update('semesters')->params($data)->where('id', $id)->execute();
    }

    public static function delete($id)
    {
        return Database::connection()->delete('semesters')->where('id', $id)->execute();
    }

    public static function setActive($id)
    {
        $db = Database::connection();
        // Deactivate all
        $db->query("UPDATE semesters SET is_active = 0")->execute();
        // Activate one
        $db->update('semesters')->params(['is_active' => 1])->where('id', $id)->execute();
    }
}
