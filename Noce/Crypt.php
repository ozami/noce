<?php
namespace Noce;

class Crypt
{
    /**
     * @codeCoverageIgnore
     */
    public static function random($length)
    {
        if (function_exists("openssl_random_pseudo_bytes")) {
            return self::randomOpenSSL($length);
        }
        if (function_exists("mcrypt_create_iv") && !defined("PHALANGER")) {
            return self::randomMcrypt($length);
        }
        if (is_readable("/dev/urandom")) {
            return self::randomUrandom($length);
        }
        throw new \Exception("err_no_secure_random_source");
    }

    public static function randomOpenSSL($length)
    {
        if ($length < 0) {
            throw new \InvalidArgumentException(); // TODO
        }
        if ($length == 0) {
            return "";
        }
        $r = openssl_random_pseudo_bytes($length, $strong);
        if (strlen($r) !== $length || !$strong) {
            // @codeCoverageIgnoreStart
            throw new \Exception(); // TODO
            // @codeCoverageIgnoreEnd
        }
        return $r;
    }

    public static function randomMcrypt($length)
    {
        if ($length < 0) {
            throw new \InvalidArgumentException(); // TODO
        }
        if ($length == 0) {
            return "";
        }
        $r = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        if (strlen($r) !== $length) {
            // @codeCoverageIgnoreStart
            throw new \Exception(); // TODO
            // @codeCoverageIgnoreEnd
        }
        return $r;
    }

    public static function randomUrandom($length)
    {
        if ($length < 0) {
            throw new \InvalidArgumentException(); // TODO
        }
        if ($length == 0) {
            return "";
        }
        $r = file_get_contents("/dev/urandom", false, null, 0, $length);
        if (strlen($r) !== $length) {
            // @codeCoverageIgnoreStart
            throw new \Exception(); // TODO
            // @codeCoverageIgnoreEnd
        }
        return $r;
    }
}
