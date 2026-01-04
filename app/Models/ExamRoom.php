<?php

namespace App\Models;

use App\Utils\Database;

class ExamRoom
{
    public static function all()
    {
        $db = Database::connection();
        return $db->query("SELECT * FROM exam_rooms ORDER BY nama_ruang ASC")->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::connection();
        return $db->query("SELECT * FROM exam_rooms WHERE id = ?")->bind($id)->first();
    }

    public static function create($data)
    {
        $db = Database::connection();
        $db->query("INSERT INTO exam_rooms (fakultas, nama_ruang, kapasitas) VALUES (?, ?, ?)")
            ->bind($data['fakultas'], $data['nama_ruang'], $data['kapasitas'])
            ->execute();
    }

    public static function update($id, $data)
    {
        $db = Database::connection();
        $db->query("UPDATE exam_rooms SET fakultas = ?, nama_ruang = ?, kapasitas = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
            ->bind($data['fakultas'], $data['nama_ruang'], $data['kapasitas'], $id)
            ->execute();
    }

    public static function delete($id)
    {
        $db = Database::connection();
        $db->query("DELETE FROM exam_rooms WHERE id = ?")->bind($id)->execute();
    }
}
