<?php
use Noce\Crypt;

class CryptTest extends PHPUnit_Framework_TestCase
{
    public function testRandom()
    {
        $this->assertSame("", Crypt::random(0));
        $this->assertSame(1, strlen(Crypt::random(1)));
        $this->assertSame(100, strlen(Crypt::random(100)));
        $this->assertSame(1024 * 1024, strlen(Crypt::random(1024 * 1024)));
        try {
            Crypt::random(-1);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }
    }
    
    /**
     * @requires extension openssl
     */
    public function testRandomOpenssl()
    {
        $this->assertSame("", Crypt::randomOpenSSL(0));
        $this->assertSame(1, strlen(Crypt::randomOpenSSL(1)));
        $this->assertSame(100, strlen(Crypt::randomOpenSSL(100)));
        $this->assertSame(1024 * 1024, strlen(Crypt::randomOpenSSL(1024 * 1024)));
        try {
            Crypt::randomOpenSSL(-1);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }
    }

    /**
     * @requires extension mcrypt
     */
    public function testRandomMcrypt()
    {
        $this->assertSame("", Crypt::randomMcrypt(0));
        $this->assertSame(1, strlen(Crypt::randomMcrypt(1)));
        $this->assertSame(100, strlen(Crypt::randomMcrypt(100)));
        $this->assertSame(1024 * 1024, strlen(Crypt::randomMcrypt(1024 * 1024)));
        try {
            Crypt::randomMcrypt(-1);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }
    }

    public function testRandomUrandom()
    {
        if (!is_readable("/dev/urandom")) {
            return;
        }
        $this->assertSame("", Crypt::randomUrandom(0));
        $this->assertSame(1, strlen(Crypt::randomUrandom(1)));
        $this->assertSame(100, strlen(Crypt::randomUrandom(100)));
        $this->assertSame(1024 * 1024, strlen(Crypt::randomUrandom(1024 * 1024)));
        try {
            Crypt::randomUrandom(-1);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }
    }
}
