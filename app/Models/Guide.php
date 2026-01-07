<?php

namespace App\Models;

use App\Utils\Database;

class Guide
{
    public static function all()
    {
        return Database::connection()->select('guides')->orderBy('order_index', 'ASC')->fetchAll();
    }

    public static function find($id)
    {
        return Database::connection()->select('guides')->where('id', $id)->first();
    }

    public static function getByRole($role)
    {
        return Database::connection()
            ->select('guides')
            ->where('role', $role)
            ->where('is_active', 1)
            ->orderBy('order_index', 'ASC')
            ->fetchAll();
    }

    public static function create($data)
    {
        return Database::connection()->insert('guides')->params($data)->execute();
    }

    public static function update($id, $data)
    {
        return Database::connection()->update('guides')->params($data)->where('id', $id)->execute();
    }

    public static function delete($id)
    {
        return Database::connection()->delete('guides')->where('id', $id)->execute();
    }

    public static function activate($id)
    {
        return self::update($id, ['is_active' => 1]);
    }

    public static function deactivate($id)
    {
        return self::update($id, ['is_active' => 0]);
    }

    public static function reorder($id, $newOrder)
    {
        return self::update($id, ['order_index' => $newOrder]);
    }
}
