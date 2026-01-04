<?php

namespace App\Models;

use App\Utils\Database;

class EmailReminder
{
    public static function all($semesterId = null)
    {
        $db = Database::connection();
        $sql = "
            SELECT er.*, 
                   s.nama as semester_nama, 
                   s.kode as semester_kode
            FROM email_reminders er
            LEFT JOIN semesters s ON er.semester_id = s.id
        ";

        if ($semesterId) {
            $sql .= " WHERE er.semester_id = ?";
            return $db->query($sql)->bind($semesterId)->all();
        }

        $sql .= " ORDER BY er.created_at DESC";
        return $db->query($sql)->all();
    }

    public static function find($id)
    {
        $db = Database::connection();
        $sql = "
            SELECT er.*, 
                   s.nama as semester_nama, 
                   s.kode as semester_kode
            FROM email_reminders er
            LEFT JOIN semesters s ON er.semester_id = s.id
            WHERE er.id = ?
        ";

        $result = $db->query($sql)->bind($id)->all();
        return $result[0] ?? null;
    }

    public static function create($data)
    {
        $db = Database::connection();
        $db->insert('email_reminders')->params($data)->execute();
        return $db->lastInsertId();
    }

    public static function updateStatus($id, $status, $counts = [])
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (isset($counts['recipient_count']))
            $data['recipient_count'] = $counts['recipient_count'];
        if (isset($counts['sent_count']))
            $data['sent_count'] = $counts['sent_count'];
        if (isset($counts['failed_count']))
            $data['failed_count'] = $counts['failed_count'];
        if ($status === 'sent')
            $data['sent_at'] = date('Y-m-d H:i:s');

        return Database::connection()->update('email_reminders')->params($data)->where('id', $id)->execute();
    }

    public static function getLogs($reminderId)
    {
        $db = Database::connection();
        return $db->query("
            SELECT el.*, p.nama_lengkap, p.nomor_peserta 
            FROM email_logs el
            LEFT JOIN participants p ON el.participant_id = p.id
            WHERE el.reminder_id = ?
            ORDER BY el.created_at DESC
        ")->bind($reminderId)->all();
    }

    public static function createLog($data)
    {
        $db = Database::connection();
        $db->insert('email_logs')->params($data)->execute();
        return $db->lastInsertId();
    }

    public static function updateLog($id, $status, $errorMessage = null)
    {
        $data = [
            'status' => $status,
            'sent_at' => date('Y-m-d H:i:s')
        ];

        if ($errorMessage) {
            $data['error_message'] = $errorMessage;
        }

        return Database::connection()->update('email_logs')->params($data)->where('id', $id)->execute();
    }
}
