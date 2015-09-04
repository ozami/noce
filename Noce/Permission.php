<?php
namespace Noce;

class Permission
{
    public static $_permissions = array();

    public static function linkToSession()
    {
        Session::link(__CLASS__ . "::permissions", self::$_permissions);
        if (self::$_permissions === null) {
            self::$_permissions = array();
        }
    }

    public static function add($perms)
    {
        $perms = (array) $perms;
        self::$_permissions = array_unique(array_merge(self::$_permissions, $perms));
    }

    public static function remove($perms)
    {
        $perms = (array) $perms;
        self::$_permissions = array_values(array_diff(self::$_permissions, $perms));
    }

    public static function clear()
    {
        self::$_permissions = array();
    }

    public static function get()
    {
        return self::$_permissions;
    }

    public static function check($perms)
    {
        if (!self::has($perms)) {
            throw new \RuntimeException("err_no_permission");
        }
    }

    public static function checkAll($perms)
    {
        if (!self::hasAll($perms)) {
            throw new \RuntimeException("err_no_permission");
        }
    }

    public static function has($perms)
    {
        $perms = array_diff((array) $perms, array(null));
        if (!$perms) {
            return true;
        }
        foreach ($perms as $p) {
            if (in_array($p, self::$_permissions)) {
                return true;
            }
        }
        return false;
    }

    public static function hasAll($perms)
    {
        $perms = array_diff((array) $perms, array(null));
        return !array_diff($perms, self::$_permissions);
    }
}
