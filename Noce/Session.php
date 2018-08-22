<?php
namespace Noce;

class Session
{
    const INIT_KEY = "Noce\\Session::initialized";
    
    public static $started = false;
    
    public static function start()
    {
        if (self::$started) {
            return false;
        }
        self::checkConfig();
        if (!@session_start()) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("err_session_start");
            // @codeCoverageIgnoreEnd
        }
        self::$started = true;
        // protect against session adoption attack
        $new_session = false;
        if (!isset($_SESSION[self::INIT_KEY])) {
            $_SESSION = array(self::INIT_KEY => true);
            self::renew();
            $new_session = true;
        }
        return $new_session;
    }
    
    public static function close()
    {
        if (!self::$started) {
            throw new \RuntimeException("err_session_not_started");
        }
        session_write_close();
        unset($_SESSION);
        self::$started = false;
    }
    
    public static function destroy()
    {
        if (!self::$started) {
            throw new \RuntimeException("err_session_not_started");
        }
        session_destroy();
        unset($_SESSION);
        self::$started = false;
    }
    
    public static function renew()
    {
        if (!self::$started) {
            throw new \RuntimeException("err_session_not_started");
        }
        if (!session_regenerate_id(true)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("err_session_renew");
            // @codeCoverageIgnoreEnd
        }
    }
    
    public static function &get($name)
    {
        self::start();
        return $_SESSION[$name];
    }
    
    public static function link($name, &$var)
    {
        self::start();
        $backup = null;
        if (isset($_SESSION[$name])) {
            $backup = $_SESSION[$name];
        }
        self::unlink($name);
        $_SESSION[$name] = null;
        $_SESSION[$name] =& $var;
        $_SESSION[$name] = $backup;
    }
    
    public static function unlink($name)
    {
        self::start();
        unset($_SESSION[$name]);
    }
    
    public static function checkConfig()
    {
        if (defined("NOCE_SESSION_DISABLE_CONFIG_CHECK")) {
            return;
        }
        if (ini_get("session.use_only_cookies") == "0") {
            trigger_error("session.use_only_cookies is off.", E_USER_WARNING);
        } // @codeCoverageIgnore
        if (ini_get("session.cookie_httponly") == "0") {
            trigger_error("session.cookie_httponly is off.", E_USER_WARNING);
        } // @codeCoverageIgnore
        if (ini_get("session.entropy_file") == "") {
            trigger_error("session.entropy_file is not set.", E_USER_WARNING);
        } // @codeCoverageIgnore
        if (ini_get("session.entropy_length") == "0") {
            trigger_error("session.entropy_length is zero.", E_USER_WARNING);
        } // @codeCoverageIgnore
        if (ini_get("session.use_trans_sid") == "1") {
            trigger_error("session.use_trans_sid is on.", E_USER_WARNING);
        } // @codeCoverageIgnore
    }
}
