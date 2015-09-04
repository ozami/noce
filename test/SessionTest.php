<?php
use Noce\Session;

/**
 * @runTestsInSeparateProcesses
 */
class SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Noce\Session::start
     */
    public function testStart()
    {
        $this->assertSame(true, Session::start());
        $this->assertNotEmpty(session_id());
        $this->assertSame(true, Session::$started);
    }
    
    /**
     * @covers Noce\Session::start
     * @covers Noce\Session::close
     */
    public function testResume()
    {
        Session::start();
        $_SESSION["test"] = "TEST";
        Session::close();
        $this->assertSame(false, Session::$started);
        $this->assertSame(false, isset($_SESSION["test"]));
        Session::start();
        $this->assertSame("TEST", $_SESSION["test"]);
        $this->assertSame(true, Session::$started);
    }
    
    /**
     * @covers Noce\Session::start
     */
    public function testStartWhileAlreadyStarted()
    {
        $this->assertSame(true, Session::start());
        $this->assertSame(true, Session::$started);
        $this->assertSame(false, Session::start());
        $this->assertSame(true, Session::$started);
    }
    
    /**
     * @covers Noce\Session::close
     * @expectedException RuntimeException
     * @expectedExceptionMessage err_session_not_started
     */
    public function testCloseWithoutStart()
    {
        Session::close();
        $this->assertSame(false, Session::$started);
    }
    
    /**
     * @covers Noce\Session::destroy
     */
    public function testDestroy()
    {
        Session::start();
        $_SESSION["test"] = "TEST";
        Session::destroy();
        $this->assertSame(false, Session::$started);
        $this->assertSame(false, isset($_SESSION["test"]));
        Session::start();
        $this->assertSame(false, isset($_SESSION["test"]));
    }
    
    /**
     * @covers Noce\Session::destroy
     * @expectedException RuntimeException
     * @expectedExceptionMessage err_session_not_started
     */
    public function testDestroyWithoutStart()
    {
        Session::destroy();
        $this->assertSame(false, Session::$started);
    }
    
    /**
     * @covers Noce\Session::renew
     */
    public function testRenew()
    {
        Session::start();
        $_SESSION["test"] = "TEST";
        $old_id = session_id();
        Session::renew();
        $new_id = session_id();
        $this->assertNotEmpty($new_id);
        $this->assertNotEquals($old_id, $new_id);
        $this->assertSame("TEST", $_SESSION["test"]);
        $this->assertSame(true, Session::$started);
    }
    
    /**
     * @covers Noce\Session::renew
     * @expectedException RuntimeException
     * @expectedExceptionMessage err_session_not_started
     */
    public function testRenewWithoutStart()
    {
        Session::renew();
        $this->assertSame(false, Session::$started);
    }
    
    /**
     * @covers Noce\Session::link
     */
    public function testLink()
    {
        $test = "TEST";
        Session::link("link_test", $test);
        $this->assertSame(true, Session::$started);
        $this->assertSame(null, $test);
        $test = "UPDATED";
        $this->assertSame("UPDATED", $_SESSION["link_test"]);
    }
    
    /**
     * @covers Noce\Session::link
     */
    public function testLinkWhenResumed()
    {
        Session::start();
        $test = "TEST";
        Session::link("link_test", $test);
        $test = "UPDATED";
        Session::close();
        $this->assertSame("UPDATED", $test);
        $test = "UPDATED AGAIN";
        Session::start();
        Session::link("link_test", $test);
        $this->assertSame("UPDATED", $_SESSION["link_test"]);
    }
    
    /**
     * @covers Noce\Session::unlink
     */
    public function testUnlink()
    {
        Session::start();
        $test = "TEST";
        Session::link("link_test", $test);
        $test = "UPDATED";
        Session::unlink("link_test");
        $this->assertSame("UPDATED", $test);
        $this->assertSame(false, isset($_SESSION["link_test"]));
        
        $test = "UPDATED AGAIN";
        $this->assertSame("UPDATED AGAIN", $test);
        $this->assertSame(false, isset($_SESSION["link_test"]));
        
        $_SESSION["link_test"] = "UPDATED SESSION";
        $this->assertSame("UPDATED AGAIN", $test);
    }
    
    /**
     * @covers Noce\Session::checkConfig
     */
    public function testCheckConfig()
    {
        ini_set("session.use_only_cookies", "1");
        ini_set("session.cookie_httponly", "1");
        ini_set("session.entropy_file", "/dev/urandom");
        ini_set("session.entropy_length", "20");
        ini_set("session.use_trans_sid", "0");
        Session::checkConfig();
        $this->assertTrue(true);
    }

    /**
     * @covers Noce\Session::checkConfig
     */
    public function testCheckConfigCanBeDisabled()
    {
        define("NOCE_SESSION_DISABLE_CONFIG_CHECK", 1);
        ini_set("session.use_only_cookies", "0");
        Session::checkConfig();
        $this->assertTrue(true);
    }
    
    /**
     * @covers Noce\Session::checkConfig
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCheckConfigForUseOnlyCookies()
    {
        ini_set("session.use_only_cookies", "0");
        Session::checkConfig();
    }
    
    /**
     * @covers Noce\Session::checkConfig
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCheckConfigForCookieHttponly()
    {
        ini_set("session.cookie_httponly", "0");
        Session::checkConfig();
    }
    
    /**
     * @covers Noce\Session::checkConfig
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCheckConfigForEntropyFile()
    {
        ini_set("session.entropy_file", "");
        Session::checkConfig();
    }
    
    /**
     * @covers Noce\Session::checkConfig
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCheckConfigForEntropyLength()
    {
        ini_set("session.entropy_length", "0");
        Session::checkConfig();
    }
    
    /**
     * @covers Noce\Session::checkConfig
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCheckConfigForUseTransSid()
    {
        ini_set("session.use_trans_sid", "1");
        Session::checkConfig();
    }
}
