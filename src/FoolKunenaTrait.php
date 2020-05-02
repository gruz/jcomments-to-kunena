<?php
// phpcs:ignore

namespace gruz\JCommentsToKunenaCli;

/**
 * Undocumented class
 *
 * @since 1.0.0
 */
trait FoolKunenaTrait
{
    public static $storage = [];
    public static function isClient()
    {
        return true;
    }
    
    public static function setUserState( $key, $value )
    {
        static::$storage[$key ] = $value;
        return true;
    }
    public static function getUserState( $key, $default = null )
    {
        if (isset(static::$storage[$key])) {
            return static::$storage[$key];
        }

        return $default;
    }
}
