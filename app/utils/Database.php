<?php

namespace App\Utils;

use Leaf\Db;

class Database
{
    private static $db;

    public static function connect($config)
    {
        self::$db = new Db();
        self::$db->connect($config);
    }

    public static function connection()
    {
        return self::$db;
    }
}
