<?php
namespace Noce;

class Cache
{
    static public $_enabled = true;
    static public $_dir = "/tmp/";
    static public $_lifeTime = 3600;
    static public $_autoVacuum = 0;

    public static function setEnabled($enabled)
    {
        self::$_enabled = (bool) $enabled;
    }

    public static function setAutoVacuum($autoVacuum)
    {
        $autoVacuum = (int) $autoVacuum;
        if ($autoVacuum < 0) {
            throw new \Exception();
        }
        self::$_autoVacuum = $autoVacuum;
    }

    public static function setDirectory($dir)
    {
        self::$_dir = rtrim($dir, "/") . "/";
    }

    public static function setLifeTime($lifeTime)
    {
        self::$_lifeTime = (int) $lifeTime;
    }

    public static function get($id, $callback = null, $args = array(), $lifeTime = null)
    {
        if (!self::$_enabled) {
            return call_user_func_array($callback, $args);
        }
        self::autoVacuum();
        if (!$lifeTime) {
            $lifeTime = self::$_lifeTime;
        }
        $cache = self::read($id);
        if ($cache && self::isValid($cache, $lifeTime)) {
            return $cache["data"];
        }
        if (!$callback) {
            return null;
        }
        return self::set($id, call_user_func_array($callback, $args), $lifeTime);
    }

    public static function set($id, $value, $lifeTime = null)
    {
        if (!self::checkId($id)) {
            return $value;
        }
        if (!$lifeTime) {
            $lifeTime = self::$_lifeTime;
        }
        $cache = array(
            "created" => time(),
            "expires" => time() + $lifeTime,
            "data" => $value);
        @file_put_contents(self::$_dir . $id, serialize($cache));
        return $value;
    }

    public static function remove($id)
    {
        if (!self::checkId($id)) {
            return;
        }
        @unlink(self::$_dir . $id);
    }

    public static function vacuum()
    {
        $dir = @opendir(self::$_dir);
        if ($dir === false) {
            return;
        }
        $expired = array();
        while (($id = @readdir($dir)) !== false) {
            if ($id[0] == ".") {
                continue;
            }
            $cache = self::read($id);
            if (!$cache) {
                continue;
            }
            if (self::isValid($cache)) {
                continue;
            }
            $expired[] = $id;
        }
        @closedir($dir);
        foreach ($expired as $id) {
            @unlink(self::$_dir . $id);
        }
    }

    public static function flush()
    {
        $dir = @opendir(self::$_dir);
        if ($dir === false) {
            return;
        }
        $ids = array();
        while (($id = @readdir($dir)) !== false) {
            if ($id[0] == ".") {
                continue;
            }
            $ids[] = $id;
        }
        @closedir($dir);
        foreach ($ids as $id) {
            @unlink(self::$_dir . $id);
        }
    }

    public static function autoVacuum()
    {
        if (!self::$_autoVacuum) {
            return;
        }
        if (mt_rand(1, self::$_autoVacuum) == 1) {
            self::vacuum();
        }
    }

    public static function checkId($id)
    {
        return preg_match("/^[a-zA-Z0-9_]+$/", $id);
    }

    public static function read($id)
    {
        if (!self::checkId($id)) {
            return false;
        }
        return @unserialize(@file_get_contents(self::$_dir . $id));
    }

    public static function isValid($cache, $lifeTime = null)
    {
        if ($cache["expires"] <= time()) {
            return false;
        }
        if ($lifeTime && $cache["created"] + $lifeTime <= time()) {
            return false;
        }
        return true;
    }
}
