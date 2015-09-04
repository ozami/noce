<?php
use Noce\Message;

class MessageTest extends PHPUnit_Framework_TestCase
{
    
    public $messages = array(
        "en" => array("test" => "TEST", "only_in_en" => "Only in En"),
        "ja" => array("test" => "テスト"));
    /**
     * @covers Noce\Message::init
     */
    public function testInit()
    {
        Message::init($this->messages, "default", "fallback");
        $this->assertSame($this->messages, Message::$messages);
        $this->assertSame("default", Message::$language);
        $this->assertSame("fallback", Message::$fallback_language);
    }
    
    /**
     * @covers Noce\Message::get
     */
    public function testGetSuccess()
    {
        Message::init($this->messages, "en", "en");
        $this->assertSame($this->messages["en"]["test"], Message::get("test"));
    }
    
    /**
     * @covers Noce\Message::get
     */
    public function testGetSuccessForNonDefaultLanguage()
    {
        Message::init($this->messages, "ja", "en");
        $this->assertSame($this->messages["ja"]["test"], Message::get("test"));
    }
    
    /**
     * @covers Noce\Message::get
     */
    public function testGetFailsForNoLanguage()
    {
        Message::init($this->messages, "not_exists", "not_exists");
        $this->assertSame("test", Message::get("test"));
    }
    
    /**
     * @covers Noce\Message::get
     */
    public function testGetFailsForNoId()
    {
        Message::init($this->messages, "en", "en");
        $this->assertSame("not_exists", Message::get("not_exists"));
    }
    
    /**
     * @covers Noce\Message::get
     */
    public function testGetFallback()
    {
        Message::init($this->messages, "ja", "en");
        $this->assertSame($this->messages["en"]["only_in_en"], Message::get("only_in_en"));
    }
    
    /**
     * @covers Noce\Message::setMessages
     */
    public function testSetMessages()
    {
        Message::setMessages($this->messages);
        $this->assertSame($this->messages, Message::$messages);
    }
    
    /**
     * @covers Noce\Message::setLanguage
     */
    public function testSetLanguage()
    {
        Message::setLanguage("ja");
        $this->assertSame("ja", Message::$language);
    }
    
    /**
     * @covers Noce\Message::setFallbackLanguage
     */
    public function testSetFallbackLanguage()
    {
        Message::setFallbackLanguage("ja");
        $this->assertSame("ja", Message::$fallback_language);
    }
}
