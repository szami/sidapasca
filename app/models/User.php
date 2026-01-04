<?php

namespace App\Models;

use App\Utils\Database;

class User
{
    public static function where($column, $value)
    {
        return Database::connection()->select('users')->where($column, $value);
    }
}
