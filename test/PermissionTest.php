<?php

use Noce\Permission;

/**
 * @runTestsInSeparateProcesses
 */
class PermissionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Permission::clear();
    }

    const SESSION_PERMISSION_KEY = "Noce\\Permission::permissions";

    public function testLinkToSession()
    {
        Permission::linkToSession();
        $this->assertSame(array(), Permission::$_permissions);
        Permission::add("p1");
        $this->assertSame(array("p1"), $_SESSION[self::SESSION_PERMISSION_KEY]);
        $this->assertSame(array("p1"), Permission::get());

        Permission::clear();
        $this->assertSame(array(), $_SESSION[self::SESSION_PERMISSION_KEY]);
        $this->assertSame(array(), Permission::get());
    }

    public function testLinkToSessionWithResume()
    {
        Permission::linkToSession();
        Permission::add("p1");
        Permission::add("p2");
        Noce\Session::close();

        Permission::clear();
        Permission::linkToSession();
        $this->assertSame(array("p1", "p2"), $_SESSION[self::SESSION_PERMISSION_KEY]);
        $this->assertSame(array("p1", "p2"), Permission::get());
    }

    public function testAdd()
    {
        Permission::add("p1");
        $this->assertSame(array("p1"), Permission::get());
        Permission::add("p2");
        $this->assertSame(array("p1", "p2"), Permission::get());
        Permission::add("p1");
        $this->assertSame(array("p1", "p2"), Permission::get());
        Permission::add(null);
        $this->assertSame(array("p1", "p2"), Permission::get());
    }

    public function testMultipleAdd()
    {
        Permission::add(array("p1"));
        $this->assertSame(array("p1"), Permission::get());

        Permission::add(array("p2"));
        $this->assertSame(array("p1", "p2"), Permission::get());

        Permission::add(array("p1"));
        $this->assertSame(array("p1", "p2"), Permission::get());

        Permission::add(array());
        $this->assertSame(array("p1", "p2"), Permission::get());

        Permission::add(array("p3", "p4"));
        $this->assertSame(array("p1", "p2", "p3", "p4"), Permission::get());

        Permission::add(array("p3", "p4"));
        $this->assertSame(array("p1", "p2", "p3", "p4"), Permission::get());
    }

    public function testRemove()
    {
        Permission::add(array("p1", "p2", "p3", "p4"));
        Permission::remove("p3");
        $this->assertSame(array("p1", "p2", "p4"), Permission::get());

        Permission::remove("p4");
        $this->assertSame(array("p1", "p2"), Permission::get());

        Permission::remove("p1");
        $this->assertSame(array("p2"), Permission::get());

        Permission::remove("unknown");
        $this->assertSame(array("p2"), Permission::get());

        Permission::remove(null);
        $this->assertSame(array("p2"), Permission::get());

        Permission::remove("p2");
        $this->assertSame(array(), Permission::get());

        Permission::remove("p0");
        $this->assertSame(array(), Permission::get());
    }

    public function testMultipleRemove()
    {
        Permission::add(array("p1", "p2", "p3", "p4"));
        Permission::remove(array("p3"));
        $this->assertSame(array("p1", "p2", "p4"), Permission::get());

        Permission::remove(array("p4"));
        $this->assertSame(array("p1", "p2"), Permission::get());

        Permission::remove(array("p1"));
        $this->assertSame(array("p2"), Permission::get());

        Permission::remove(array("unknown"));
        $this->assertSame(array("p2"), Permission::get());

        Permission::remove(array());
        $this->assertSame(array("p2"), Permission::get());

        Permission::remove(array("p2"));
        $this->assertSame(array(), Permission::get());

        Permission::remove(array("p0"));
        $this->assertSame(array(), Permission::get());
    }

    public function testHas()
    {
        $this->assertFalse(Permission::has("p1"));
        $this->assertTrue(Permission::has(null));

        $this->assertFalse(Permission::has(array("p1")));
        $this->assertFalse(Permission::has(array("p1", "p2")));
        $this->assertTrue(Permission::has(array()));
        $this->assertTrue(Permission::has(array(null, null)));

        Permission::add(array("p1", "p2", "p3"));
        $this->assertTrue(Permission::has("p1"));
        $this->assertTrue(Permission::has("p2"));
        $this->assertTrue(Permission::has("p3"));
        $this->assertTrue(Permission::has(null));
        $this->assertFalse(Permission::has("p4"));

        $this->assertTrue(Permission::has(array("p1")));
        $this->assertTrue(Permission::has(array("p2")));
        $this->assertTrue(Permission::has(array("p3")));
        $this->assertTrue(Permission::has(array()));
        $this->assertFalse(Permission::has(array("p4")));

        $this->assertTrue(Permission::has(array("p1", "p1")));
        $this->assertTrue(Permission::has(array("p1", "p2", "p3")));
        $this->assertTrue(Permission::has(array("px", "p1")));
        $this->assertTrue(Permission::has(array("p1", "px")));
        $this->assertTrue(Permission::has(array("p1", null)));
        $this->assertTrue(Permission::has(array(null, "p1")));
        $this->assertTrue(Permission::has(array(null, null)));
        $this->assertFalse(Permission::has(array("px", "px")));
    }

    public function testHasAll()
    {
        $this->assertFalse(Permission::hasAll("p1"));
        $this->assertTrue(Permission::hasAll(null));

        $this->assertFalse(Permission::hasAll(array("p1")));
        $this->assertFalse(Permission::hasAll(array("p1", "p2")));
        $this->assertTrue(Permission::hasAll(array()));
        $this->assertTrue(Permission::hasAll(array(null, null)));

        Permission::add(array("p1", "p2", "p3"));
        $this->assertTrue(Permission::hasAll("p1"));
        $this->assertTrue(Permission::hasAll("p2"));
        $this->assertTrue(Permission::hasAll("p3"));
        $this->assertTrue(Permission::hasAll(null));
        $this->assertFalse(Permission::hasAll("p4"));

        $this->assertTrue(Permission::hasAll(array("p1")));
        $this->assertTrue(Permission::hasAll(array("p2")));
        $this->assertTrue(Permission::hasAll(array("p3")));
        $this->assertTrue(Permission::hasAll(array()));
        $this->assertFalse(Permission::hasAll(array("p4")));

        $this->assertTrue(Permission::hasAll(array("p1", "p1")));
        $this->assertTrue(Permission::hasAll(array("p1", "p2", "p3")));
        $this->assertFalse(Permission::hasAll(array("px", "p1")));
        $this->assertFalse(Permission::hasAll(array("p1", "px")));
        $this->assertTrue(Permission::hasAll(array("p1", null)));
        $this->assertTrue(Permission::hasAll(array(null, null)));
        $this->assertFalse(Permission::hasAll(array("px", "px")));
    }

    public function testCheck()
    {
        Permission::add(array("p1", "p2", "p3"));
        Permission::check("p1");
        Permission::check(null);
        Permission::check(array("p1"));
        Permission::check(array());
        try {
            Permission::check(array("p0"));
            $this->fail();
        }
        catch (RuntimeException $e) {
            $this->assertSame("err_no_permission", $e->getMessage());
        }
        try {
            Permission::check("p0");
            $this->fail();
        }
        catch (RuntimeException $e) {
            $this->assertSame("err_no_permission", $e->getMessage());
        }
    }

    public function testCheckAll()
    {
        Permission::add(array("p1", "p2", "p3"));
        Permission::checkAll("p1");
        Permission::checkAll(null);
        Permission::checkAll(array("p1"));
        Permission::checkAll(array());
        try {
            Permission::checkAll(array("p0"));
            $this->fail();
        }
        catch (RuntimeException $e) {
            $this->assertSame("err_no_permission", $e->getMessage());
        }
        try {
            Permission::checkAll("p0");
            $this->fail();
        }
        catch (RuntimeException $e) {
            $this->assertSame("err_no_permission", $e->getMessage());
        }
    }
}
