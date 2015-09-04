<?php

use Noce\ArrayX;

class ArrayXTest extends PHPUnit_Framework_TestCase
{
    public function testPathSetWithEmptyPath()
    {
        $a = array();
        ArrayX::pathSet($a, array(), "value");
        $this->assertSame(
            array("value"),
            $a
        );
    }
    
    public function testPathSetWithEmptyPath2()
    {
        $a = array("first");
        ArrayX::pathSet($a, array(), "second");
        $this->assertSame(
            array("first", "second"),
            $a
        );
    }
    
    public function testPathSetWithDeepPath()
    {
        $a = array();
        ArrayX::pathSet($a, array("lv1", "lv2", "lv3"), "value");
        $this->assertSame(
            array("lv1" => array("lv2" => array("lv3" => "value"))),
            $a
        );
    }
    
    public function testPathSetWithOverwritingDeepPath()
    {
        $a = array("lv1" => array("lv2" => array("lv3" => "before")));
        ArrayX::pathSet($a, array("lv1", "lv2", "lv3"), "value");
        $this->assertSame(
            array("lv1" => array("lv2" => array("lv3" => "value"))),
            $a
        );
    }

    public function testPathSetWithAppendingToDeepPath()
    {
        $a = array();
        ArrayX::pathSet($a, array("lv1", "lv2", "lv3", null), "value");
        $this->assertSame(
            array("lv1" => array("lv2" => array("lv3" => array("value")))),
            $a
        );
    }
    
    //
    // group
    //
    
    public function testGroupWithEmptyArray()
    {
        $this->assertSame(
            array(),
            ArrayX::group(array(), function($item) {
                return @$item["a"];
            })
        );
    }
    
    public function testGroupWithCallbackFunction()
    {
        $a = array(
            array("a" => "a1", "id" => 1),
            array("a" => "a2", "id" => 2),
            array("a" => "a2", "id" => 3)
        );
        $this->assertSame(
            array(
                "a1" => array(
                    array("a" => "a1", "id" => 1)
                ),
                "a2" => array(
                    array("a" => "a2", "id" => 2),
                    array("a" => "a2", "id" => 3)
                )
            ),
            ArrayX::group($a, function($item) {
                return @$item["a"];
            })
        );
    }
    
    public function testGroupWithCallbackFunctionThatReturnsMultipleValues()
    {
        $a = array(
            array("a" => "a1", "b" => "b1", "id" => 1),
            array("a" => "a2", "b" => "b1", "id" => 2),
            array("a" => "a2", "b" => "b2", "id" => 3),
            array("a" => "a2", "b" => "b2", "id" => 4)
        );
        $this->assertSame(
            array(
                "a1" => array(
                    "b1" => array(
                        array("a" => "a1", "b" => "b1", "id" => 1)
                    )
                ),
                "a2" => array(
                    "b1" => array(
                        array("a" => "a2", "b" => "b1", "id" => 2)
                    ),
                    "b2" => array(
                        array("a" => "a2", "b" => "b2", "id" => 3),
                        array("a" => "a2", "b" => "b2", "id" => 4)
                    )
                )
            ),
            ArrayX::group($a, function($item) {
                return array(@$item["a"], @$item["b"]);
            })
        );
    }
    
    public function testGroupWithSingleKey()
    {
        $a = array(
            array("a" => "a1", "id" => 1),
            array("a" => "a2", "id" => 2),
            array("a" => "a2", "id" => 3)
        );
        $this->assertSame(
            array(
                "a1" => array(
                    array("a" => "a1", "id" => 1)
                ),
                "a2" => array(
                    array("a" => "a2", "id" => 2),
                    array("a" => "a2", "id" => 3)
                )
            ),
            ArrayX::group($a, "a")
        );
    }
    
    public function testGroupWithMultipleKeys()
    {
        $a = array(
            array("a" => "a1", "b" => "b1", "id" => 1),
            array("a" => "a2", "b" => "b1", "id" => 2),
            array("a" => "a2", "b" => "b2", "id" => 3),
            array("a" => "a2", "b" => "b2", "id" => 4)
        );
        $this->assertSame(
            array(
                "a1" => array(
                    "b1" => array(
                        array("a" => "a1", "b" => "b1", "id" => 1)
                    )
                ),
                "a2" => array(
                    "b1" => array(
                        array("a" => "a2", "b" => "b1", "id" => 2)
                    ),
                    "b2" => array(
                        array("a" => "a2", "b" => "b2", "id" => 3),
                        array("a" => "a2", "b" => "b2", "id" => 4)
                    )
                )
            ),
            ArrayX::group($a, array("a", "b"))
        );
    }
}

