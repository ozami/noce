<?php
use Noce\Auth;
use Noce\Crypt;

/**
 * @runTestsInSeparateProcesses
 */
class AuthTest extends PHPUnit_Framework_TestCase
{
    const SESSION_ID_KEY = "Noce\\Auth::id";

    /**
     * @covers Noce\Auth::linkToSession
     */
    public function testLinkToSession()
    {
        Auth::linkToSession();
        $this->assertSame(null, Auth::getId());
        
        Auth::setId(999);
        $this->assertSame(999, $_SESSION[self::SESSION_ID_KEY]);
        $this->assertSame(999, Auth::getId());

        Auth::setId(null);
        $this->assertSame(null, $_SESSION[self::SESSION_ID_KEY]);
        $this->assertSame(null, Auth::getId());
    }

    /**
     * @covers Noce\Auth::linkToSession
     */
    public function testLinkToSessionWithResume()
    {
        Auth::linkToSession();
        Auth::setId(999);
        Noce\Session::close();

        Auth::setId(null);
        Auth::linkToSession();
        $this->assertSame(999, $_SESSION[self::SESSION_ID_KEY]);
        $this->assertSame(999, Auth::getId());
    }

    /**
     * @covers Noce\Auth::hashPassword
     */
    public function testPasswordHashing()
    {
        $hash = Auth::hashPassword("abc");
        $this->assertStringStartsWith('$2a$12$', $hash);
        $this->assertRegExp("#^\\$2a\\$12\\$[./0-9A-Za-z]+$#", $hash);

        Auth::setSitePassword("9032803479{}kladsf;alkjzxoizxfpoiuopi34");
        $hash = Auth::hashPassword("abc");
        $this->assertStringStartsWith('$2a$12$', $hash);
        $this->assertRegExp("#^\\$2a\\$12\\$[./0-9A-Za-z]+$#", $hash);
        $this->assertTrue(Auth::matchPasswordHash("abc", $hash));

        $pw = Crypt::random(30);
        $hash = Auth::hashPassword($pw);
        $this->assertStringStartsWith('$2a$12$', $hash);
        $this->assertRegExp("#^\\$2a\\$12\\$[./0-9A-Za-z]+$#", $hash);
        $this->assertTrue(Auth::matchPasswordHash($pw, $hash));
    }

    public function testPasswordHashCompatibility()
    {
        if (!function_exists("password_hash")) {
            return;
        }
        Auth::setSitePassword("9032803479{}kladsf;alkjzxoizxfpoiuopi34");
        $pw = "89jklkdfzjlur4:kXJFKE";
        $hash = Auth::hashPassword($pw);
        $this->assertTrue(password_verify(hash_hmac("sha256", $pw, Auth::$_site_password), $hash));
        $this->assertFalse(password_verify(hash_hmac("sha256", $pw . "x", Auth::$_site_password), $hash));

        $hash = password_hash(hash_hmac("sha256", $pw, Auth::$_site_password), PASSWORD_DEFAULT);
        $this->assertTrue(Auth::matchPasswordHash($pw, $hash));
        $this->assertFalse(Auth::matchPasswordHash($pw . "X", $hash));
    }
}
