<?php
use \Noce\AutoLoad;

class AutoLoadTest extends PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcesses
     */
    public function testInit()
    {
        chdir(__DIR__);
        $abs_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . "AutoLoadTest") . DIRECTORY_SEPARATOR;

        AutoLoad::init();
        $this->assertSame(array(), AutoLoad::$prefixes);

        AutoLoad::init(array("" => "AutoLoadTest"));
        $this->assertSame(array("" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("Prefix" => "AutoLoadTest"));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("\\Prefix" => "AutoLoadTest"));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("\\Prefix\\" => "AutoLoadTest"));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("Prefix" => "AutoLoadTest" . DIRECTORY_SEPARATOR));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("Prefix" => "." . DIRECTORY_SEPARATOR . "AutoLoadTest"));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("Prefix" => "." . DIRECTORY_SEPARATOR . "AutoLoadTest" . DIRECTORY_SEPARATOR));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("Prefix" => $abs_dir));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);

        AutoLoad::init(array("Prefix" => "AutoLoadTest", "\\Prefix\\" => "AutoLoadTest"));
        $this->assertSame(array("Prefix\\" => $abs_dir), AutoLoad::$prefixes);
    }
    
    /**
     * @covers Noce\AutoLoad::addPrefix
     */
    public function testAddPrefix()
    {
        try {
            AutoLoad::addPrefix("Prefix", "/never_existing_path");
            $this->fail();
        }
        catch (RuntimeException $e) {
            $this->assertSame("err_directory_not_found", $e->getMessage());
        }
    }

    public function testInitMono()
    {
        AutoLoad::initMono();
        $this->assertSame(array("" => dirname(__DIR__) . DIRECTORY_SEPARATOR), AutoLoad::$prefixes);
    }
}
