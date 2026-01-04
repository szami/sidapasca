<?php

namespace App\Models;

use App\Utils\Database;

class ExamSession
{
    public static function all()
    {
        $db = Database::connection();
        return $db->query("
            SELECT es.*, er.nama_ruang, s.nama as nama_semester 
            FROM exam_sessions es
            JOIN exam_rooms er ON es.exam_room_id = er.id
            JOIN semesters s ON es.semester_id = s.id
            ORDER BY es.tanggal ASC, es.waktu_mulai ASC
        ")->fetchAll();
    }

    public static function findBySemester($semesterId)
    {
        $db = Database::connection();
        return $db->query("
            SELECT es.*, er.nama_ruang 
            FROM exam_sessions es
            JOIN exam_rooms er ON es.exam_room_id = er.id
            WHERE es.semester_id = ?
            ORDER BY es.tanggal ASC, es.waktu_mulai ASC
        ")->bind($semesterId)->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::connection();
        return $db->query("SELECT * FROM exam_sessions WHERE id = ?")->bind($id)->first();
    }

    public static function create($data)
    {
        $db = Database::connection();
        $db->query("INSERT INTO exam_sessions (semester_id, exam_room_id, nama_sesi, tanggal, waktu_mulai, waktu_selesai, is_active) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->bind(
                $data['semester_id'],
                $data['exam_room_id'],
                $data['nama_sesi'],
                $data['tanggal'],
                $data['waktu_mulai'],
                $data['waktu_selesai'],
                $data['is_active'] ?? 1
            )
            ->execute();
    }

    public static function update($id, $data)
    {
        $db = Database::connection();
        $db->query("UPDATE exam_sessions SET 
                   semester_id = ?, exam_room_id = ?, nama_sesi = ?, tanggal = ?, 
                   waktu_mulai = ?, waktu_selesai = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                   WHERE id = ?")
            ->bind(
                $data['semester_id'],
                $data['exam_room_id'],
                $data['nama_sesi'],
                $data['tanggal'],
                $data['waktu_mulai'],
                $data['waktu_selesai'],
                $data['is_active'] ?? 1,
                $id
            )
            ->execute();
    }

    public static function delete($id)
    {
        $db = Database::connection();
        $db->query("DELETE FROM exam_sessions WHERE id = ?")->bind($id)->execute();
    }
}
