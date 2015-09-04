<?php
namespace Noce;

class AutoLoad
{
    public static $prefixes = array();
    public static $registered = false;

    public static function init($prefixes = array())
    {
        self::$prefixes = array();
        foreach ($prefixes as $prefix => $base_dir) {
            self::addPrefix($prefix, $base_dir);
        }
        if (!self::$registered) {
            spl_autoload_register(array(__CLASS__, "load"));
            self::$registered = true;
        }
    }

    public static function initMono()
    {
        self::init(array("" => dirname(__DIR__)));
    }

    public static function addPrefix($prefix, $base_dir)
    {
        // normalize
        $prefix = trim($prefix, "\\");
        if ($prefix != "") {
            $prefix .= "\\";
        }
        $base_dir = realpath($base_dir);
        if ($base_dir === false) {
            throw new \RuntimeException("err_directory_not_found");
        }
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        self::$prefixes[$prefix] = $base_dir;
    }

    public static function load($class)
    {
        if (preg_match("/[^a-zA-Z0-9_\\\\]/", $class)) {
            return;
        }
        foreach (self::$prefixes as $prefix => $base_dir) {
            if ($prefix != "" && strpos($class, $prefix) !== 0) {
                continue;
            }
            $src = substr($class, strlen($prefix));
            $src = str_replace("\\", DIRECTORY_SEPARATOR, $src);
            $src = $base_dir . $src . ".php";
            if (!file_exists($src)) {
                continue;
            }
            include $src;
            return;
        }
    }
}
