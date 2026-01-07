<?php

namespace App\Models;

use App\Utils\Database;

class News
{
    public static function all()
    {
        return Database::connection()->select('news')->orderBy('created_at', 'DESC')->fetchAll();
    }

    public static function find($id)
    {
        return Database::connection()->select('news')->where('id', $id)->first();
    }

    public static function getPublished()
    {
        return Database::connection()
            ->select('news')
            ->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->fetchAll();
    }

    public static function create($data)
    {
        return Database::connection()->insert('news')->params($data)->execute();
    }

    public static function update($id, $data)
    {
        return Database::connection()->update('news')->params($data)->where('id', $id)->execute();
    }

    public static function delete($id)
    {
        return Database::connection()->delete('news')->where('id', $id)->execute();
    }

    public static function publish($id)
    {
        return self::update($id, [
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function unpublish($id)
    {
        return self::update($id, ['is_published' => 0]);
    }
}
