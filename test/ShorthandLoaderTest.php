<?php
use Noce\ShorthandLoader;

/**
 * @runTestsInSeparateProcesses
 */
class ShorthandLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Noce\ShorthandLoader::load
     */
    public function testLoadAll()
    {
        ShorthandLoader::load();
    }

    /**
     * @covers Noce\ShorthandLoader::load
     */
    public function testLoadFunction()
    {
        $this->assertFalse(function_exists("p"));
        ShorthandLoader::load("Html::p");
        $this->assertTrue(function_exists("p"));
    }

    /**
     * @covers Noce\ShorthandLoader::load
     */
    public function testLoadClass()
    {
        $this->assertFalse(function_exists("p"));
        ShorthandLoader::load("Html");
        $this->assertTrue(function_exists("p"));
    }
    
    /**
     * @covers Noce\ShorthandLoader::load
     */
    public function testLoadFunctions()
    {
        $this->assertFalse(function_exists("d"));
        $this->assertFalse(function_exists("p"));
        ShorthandLoader::load(array("Html::p", "Debug::d"));
        $this->assertTrue(function_exists("d"));
        $this->assertTrue(function_exists("p"));
    }
    
    /**
     * @covers Noce\ShorthandLoader::load
     */
    public function testLoadClasses()
    {
        $this->assertFalse(function_exists("p"));
        $this->assertFalse(function_exists("d"));
        $this->assertFalse(function_exists("dd"));
        ShorthandLoader::load(array("Html", "Debug"));
        $this->assertTrue(function_exists("p"));
        $this->assertTrue(function_exists("d"));
        $this->assertTrue(function_exists("dd"));
    }
    
    public function testLoadNonExistantFunction()
    {
        try {
            ShorthandLoader::load(array("Html::nonexistent"));
            $this->fail();
        }
        catch (Exception $e) {
        }
    }

    public function testLoadNonExistantClass()
    {
        try {
            ShorthandLoader::load(array("NonExistent"));
            $this->fail();
        }
        catch (Exception $e) {
        }
    }
}
