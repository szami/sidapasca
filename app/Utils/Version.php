<?php

namespace App\Utils;

class Version
{
    private static $version = null;

    /**
     * Get the current application version
     * @return string
     */
    public static function get(): string
    {
        if (self::$version === null) {
            $versionFile = __DIR__ . '/../../VERSION';
            if (file_exists($versionFile)) {
                self::$version = trim(file_get_contents($versionFile));
            } else {
                self::$version = 'Unknown';
            }
        }
        return self::$version;
    }

    /**
     * Get version with prefix (e.g., "v1.6.0")
     * @return string
     */
    public static function getWithPrefix(): string
    {
        return 'v' . self::get();
    }

    /**
     * Get full version info with name
     * @return string
     */
    public static function getFull(): string
    {
        return 'SINTESA ' . self::getWithPrefix();
    }
}
