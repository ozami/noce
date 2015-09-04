<?php
namespace Noce;

class Auth
{
    public static $_id;
    public static $_table;
    public static $_id_col;
    public static $_name_col;
    public static $_password_col;
    public static $_site_password = "";

    public static function setSitePassword($site_password)
    {
        self::$_site_password = $site_password;
    }

    public static function setDatabase($table, $id_col, $name_col, $password_col)
    {
        self::$_table = $table;
        self::$_id_col = $id_col;
        self::$_name_col = $name_col;
        self::$_password_col = $password_col;
    }

    public static function linkToSession()
    {
        Session::link(__CLASS__ . "::id", self::$_id);
    }

    public static function getId()
    {
        return self::$_id;
    }

    public static function setId($id)
    {
        self::$_id = $id;
    }

    public static function checkPassword($name, $pw)
    {
        $db = new Db_Table(self::$_table);
        $account = $db->selectRow(array(
            "cols" => array(self::$_id_col, self::$_password_col),
            "where" => array(self::$_name_col => $name)));
        if (!$account) {
            return null;
        }
        if (!self::matchPasswordHash($pw, $account[self::$_password_col])) {
            return null;
        }
        return $account[self::$_id_col];
    }

    public static function updatePassword($id, $pw)
    {
        $db = new Db_Table(self::$_table);
        $hash = self::hashPassword($pw);
        $db->update(array(
            self::$_id_col => $id,
            self::$_password_col => $hash));
    }

    public static function hashPassword($pw)
    {
        $pw = hash_hmac("sha256", $pw, self::$_site_password);
        // We can safely use crypt() for Blowfish on PHP prior to 5.3.7,
        // because $pw is converted to hex string by hash_hmac().
        $salt_length = 22;
        $salt = Crypt::random($salt_length);
        $salt = str_replace("+", ".", base64_encode($salt));
        $salt = substr($salt, 0, $salt_length);
        $hash = crypt($pw, '$2a$12$' . $salt);
        if (strlen($hash) < 13) {
            throw new \Exception(); // TODO
        }
        return $hash;
    }

    public static function matchPasswordHash($pw, $hashed_pw)
    {
        $pw = hash_hmac("sha256", $pw, self::$_site_password);
        // We can safely use crypt() for Blowfish on PHP prior to 5.3.7,
        // because $pw is converted to hex string by hash_hmac().
        return crypt($pw, $hashed_pw) == $hashed_pw;
    }
}

// TODO: Login log
// TODO: Account expiration time
// TODO: Account activation time?
// TODO: Account status?
// TODO: Account assuming?
