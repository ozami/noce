<?php
namespace Noce;

class Config
{
    public static $_data = array();

    public static function init(array $data)
    {
        self::$_data = $data;
    }

    public static function get($path = "", $default = null)
    {
        $value = ArrayX::pathGet(self::$_data, $path);
        if ($value === null) {
            $value = $default;
        }
        return $value;
    }
}
