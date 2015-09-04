<?php

use Noce\File;

class FileTest extends PHPUnit_Framework_TestCase
{
    public $_test_dir;

    public function setUp()
    {
        $temp_dir = sys_get_temp_dir();
        $trial = 5;
        while ($trial) {
            $this->_test_dir = $temp_dir . DIRECTORY_SEPARATOR . "NoceTest-" . mt_rand();
            if (mkdir($this->_test_dir)) {
                return;
            }
            --$trial;
        }
        throw new Exception();
    }

    public function test1()
    {
        //$this->assertEquals(array(), $sess[$space]["permissions"]);
    }
}
