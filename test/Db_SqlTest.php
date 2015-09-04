<?php
use Noce\Db_Sql;
use Noce\Db;
use Noce\Db_Param;

class Db_SqlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Noce\Db_Sql::__construct
     */
    public function testConstructionWithNoArguments()
    {
        $sql = new Db_Sql();
        $this->assertSame("", $sql->sql);
        $this->assertSame(array(), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::__construct
     */
    public function testConstructionWithArguments()
    {
        $sql = new Db_Sql("test", array("param1", 2));
        $this->assertSame("test", $sql->sql);
        $this->assertSame(array("param1", 2), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::__construct
     */
    public function testConstructionWithAssociativeArguments()
    {
        $sql = new Db_Sql("test", array("param1" => 1, "second" => "two"));
        $this->assertSame("test", $sql->sql);
        $this->assertSame(array(1, "two"), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::__construct
     */
    public function testConstructionWithNumber()
    {
        $sql = new Db_Sql(1);
        $this->assertSame("1", $sql->sql);
        $this->assertSame(array(), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::isEmpty
     */
    public function testIsEmpty()
    {
        $sql = new Db_Sql();
        $this->assertSame(true, $sql->isEmpty());
        
        $sql = new Db_Sql(" \t\n\r ");
        $this->assertSame(true, $sql->isEmpty());
    }
    
    /**
     * @covers Noce\Db_Sql::isEmpty
     */
    public function testIsNotEmpty()
    {
        $sql = new Db_Sql("test");
        $this->assertSame(false, $sql->isEmpty());
    }
    
    /**
     * @covers Noce\Db_Sql::isEmpty
     */
    public function testIsEmptyAreNotAffectedByParam()
    {
        $sql = new Db_Sql("", array("param1"));
        $this->assertSame(true, $sql->isEmpty());
    }
    
    /**
     * @covers Noce\Db_Sql::append
     */
    public function testAppend()
    {
        $sql = new Db_Sql();
        $r = $sql->append(new Db_Sql());
        $this->assertSame($sql, $r);
        $this->assertSame("", $sql->sql);
        $this->assertSame(array(), $sql->params);
        
        $r = $sql->append(new Db_Sql("test"));
        $this->assertSame($sql, $r);
        $this->assertSame("test", $sql->sql);
        $this->assertSame(array(), $sql->params);
        
        $r = $sql->append(new Db_Sql(" test"));
        $this->assertSame($sql, $r);
        $this->assertSame("test test", $sql->sql);
        $this->assertSame(array(), $sql->params);
        
        $r = $sql->append(new Db_Sql(""));
        $this->assertSame($sql, $r);
        $this->assertSame("test test", $sql->sql);
        $this->assertSame(array(), $sql->params);
        
        $r = $sql->append(new Db_Sql(" test", array(1)));
        $this->assertSame($sql, $r);
        $this->assertSame("test test test", $sql->sql);
        $this->assertSame(array(1), $sql->params);
        
        $r = $sql->append(new Db_Sql("", array(2)));
        $this->assertSame($sql, $r);
        $this->assertSame("test test test", $sql->sql);
        $this->assertSame(array(1, 2), $sql->params);
        
        $r = $sql->append(new Db_Sql("", array(3, 4)));
        $this->assertSame($sql, $r);
        $this->assertSame("test test test", $sql->sql);
        $this->assertSame(array(1, 2, 3, 4), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::appendString
     */
    public function testAppendString()
    {
        $sql = new Db_Sql();
        $r = $sql->appendString("");
        $this->assertSame($sql, $r);
        $this->assertSame("", $sql->sql);
        $this->assertSame(array(), $sql->params);

        $r = $sql->appendString("test");
        $this->assertSame($sql, $r);
        $this->assertSame("test", $sql->sql);
        $this->assertSame(array(), $sql->params);
        
        $r = $sql->append(new Db_Sql(" test", array(1, 2)));
        $this->assertSame($sql, $r);
        $this->assertSame("test test", $sql->sql);
        $this->assertSame(array(1, 2), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::paren
     */
    public function testParen()
    {
        $sql = new Db_Sql();
        $r = $sql->paren();
        $this->assertSame($sql, $r);
        $this->assertSame("", $sql->sql);
        $this->assertSame(array(), $sql->params);
        
        $sql = new Db_Sql("test", array(1, 2, 3));
        $r = $sql->paren();
        $this->assertSame($sql, $r);
        $this->assertSame("(test)", $sql->sql);
        $this->assertSame(array(1, 2, 3), $sql->params);
    }
    
    /**
     * @covers Noce\Db_Sql::appendHelper
     */
    public function testAppendHelper()
    {
        $left = new Db_Sql("left", array(1, 2));
        $right = new Db_Sql("right", array(3, 4, 5));
        $r = $left->appendHelper("test", $right);
        $this->assertSame(null, $r);
        $this->assertSame("left test right", $left->sql);
        $this->assertSame(array(1, 2, 3, 4, 5), $left->params);
        
        $left = new Db_Sql("", array(1, 2));
        $right = new Db_Sql("right", array(3, 4, 5));
        $r = $left->appendHelper("test", $right);
        $this->assertSame(null, $r);
        $this->assertSame("right", $left->sql);
        $this->assertSame(array(1, 2, 3, 4, 5), $left->params);
        
        $left = new Db_Sql("left", array(1, 2));
        $right = new Db_Sql("", array(3, 4, 5));
        $r = $left->appendHelper("test", $right);
        $this->assertSame(null, $r);
        $this->assertSame("left", $left->sql);
        $this->assertSame(array(1, 2, 3, 4, 5), $left->params);
        
        $left = new Db_Sql("", array(1, 2));
        $right = new Db_Sql("", array(3, 4, 5));
        $r = $left->appendHelper("test", $right);
        $this->assertSame(null, $r);
        $this->assertSame("", $left->sql);
        $this->assertSame(array(1, 2, 3, 4, 5), $left->params);
    }
    
    /**
     * @covers Noce\Db_Sql::appendAnd
     */
    public function testAppendAnd()
    {
        $left = new Db_Sql("left", array(1, 2));
        $right = new Db_Sql("right", array(3, 4, 5));
        $r = $left->appendAnd($right);
        $this->assertSame($left, $r);
        $this->assertSame("left and right", $left->sql);
        $this->assertSame(array(1, 2, 3, 4, 5), $left->params);
    }
    
    /**
     * @covers Noce\Db_Sql::appendOr
     */
    public function testAppendOr()
    {
        $left = new Db_Sql("left", array(1, 2));
        $right = new Db_Sql("right", array(3, 4, 5));
        $r = $left->appendOr($right);
        $this->assertSame($left, $r);
        $this->assertSame("left or right", $left->sql);
        $this->assertSame(array(1, 2, 3, 4, 5), $left->params);
    }
    
    /**
     * @covers Noce\Db_Sql::combine
     */
    public function testCombine()
    {
        Db::init("pgsql:dbname=noce", "ozawa", "ozawa");
        $sql = new Db_Sql();
        $combined = $sql->combine();
        $this->assertSame("", $combined);

        $sql = new Db_Sql("?");
        $combined = $sql->combine();
        $this->assertSame("?", $combined);

        $sql = new Db_Sql("?", array(1));
        $combined = $sql->combine();
        $this->assertSame("'1'", $combined);

        $sql = new Db_Sql("??", array(1));
        $combined = $sql->combine();
        $this->assertSame("'1'?", $combined);

        $sql = new Db_Sql("? ?", array(1, 2));
        $combined = $sql->combine();
        $this->assertSame("'1' '2'", $combined);

        $sql = new Db_Sql("test ? test", array(new Db_Param(-1, \PDO::PARAM_INT)));
        $combined = $sql->combine();
        $this->assertSame("test '-1' test", $combined);

        $sql = new Db_Sql("test ? test", array(new Db_Param("hello", \PDO::PARAM_STR)));
        $combined = $sql->combine();
        $this->assertSame("test 'hello' test", $combined);
    }
    
    /**
     * @covers Noce\Db_Sql::cat
     */
    public function testCat()
    {
        $combined = Db_Sql::cat(array());
        $this->assertSame("", $combined->sql);
        $this->assertSame(array(), $combined->params);
        
        $combined = Db_Sql::cat(array(new Db_Sql()));
        $this->assertSame("", $combined->sql);
        $this->assertSame(array(), $combined->params);
        
        $combined = Db_Sql::cat(array(new Db_Sql(), new Db_Sql()));
        $this->assertSame("", $combined->sql);
        $this->assertSame(array(), $combined->params);
        
        $combined = Db_Sql::cat(array(new Db_Sql("sql1", array("abc")), new Db_Sql("sql2", array("def"))));
        $this->assertSame("sql1sql2", $combined->sql);
        $this->assertSame(array("abc", "def"), $combined->params);
    }
    
    /**
     * @covers Noce\Db_Sql::checkSqlInjection
     */
    public function testCheckSqlInjection()
    {
        $sql = new Db_Sql();
        $r = $sql->checkSqlInjection();
        $this->assertSame(null, $r);
        
        foreach (array(";", "\\", "#", "--", "/*") as $injection) {
            foreach (array($injection, "test$injection", "{$injection}test", "test{$injection}test") as $s) {
                $sql = new Db_Sql($s);
                try {
                    $sql->checkSqlInjection();
                    $this->fail("Could not catch '$injection' in '$s'");
                }
                catch (\RuntimeException $e) {
                    $this->assertSame($e->getMessage(), "err_db_sql_injection");
                }
            }
        }
    }
}
