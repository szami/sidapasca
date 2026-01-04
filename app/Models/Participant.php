<?php

namespace App\Models;

use App\Utils\Database;

class Participant
{
    public static function find($id)
    {
        return Database::connection()->select('participants')->where('id', $id)->first();
    }

    public static function where($column, $value)
    {
        return Database::connection()->select('participants')->where($column, $value);
    }

    public static function count()
    {
        // Leaf Db v3 count might be different? 
        // fallback to fetchAll count
        return count(Database::connection()->select('participants')->fetchAll());
    }
}
