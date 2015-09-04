<?php
use Noce\Db;

class Pdo_Mock
{
    public $queries = array();
    
    public function exec($query)
    {
        $this->queries[] = $query;
    }

    public function prepare($query)
    {
        return new Pdo_Statement_Mock($query);
    }
}

class Pdo_Statement_Mock
{
    public $sql = "";
    public $params = array();
    
    public function __construct($sql)
    {
        $this->sql = $sql;
    }
    
    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR)
    {
        
    }
    
    public function execute()
    {
        
    }
}

class DbTest extends PHPUnit_Framework_TestCase
{
    public function installMock()
    {
        $this->pdo = new Pdo_Mock();
        Db::$pdo = $this->pdo;
    }

    /**
     * @covers Noce\Db::begin
     */
    public function testBegin()
    {
        Db::init("pgsql:test");
        $this->installMock();
        Db::begin();
        $this->assertEquals(1, Db::$transactionStack);
        $this->assertEquals(array("begin"), $this->pdo->queries);
        Db::begin();
        $this->assertEquals(2, Db::$transactionStack);
        $this->assertEquals(array("begin"), $this->pdo->queries);
    }
    
    /**
     * @covers Noce\Db::commit
     */
    public function testCommit()
    {
        Db::init("pgsql:test");
        $this->installMock();
        Db::begin();
        Db::commit();
        $this->assertEquals(0, Db::$transactionStack);
        $this->assertEquals(array("begin", "commit"), $this->pdo->queries);
    }
    
    /**
     * @covers Noce\Db::rollback
     */
    public function testRollback()
    {
        Db::init("pgsql:test");
        $this->installMock();
        Db::begin();
        Db::begin();
        Db::rollback();
        $this->assertEquals(1, Db::$transactionStack);
        $this->assertEquals(true, Db::$rollbacked);
        Db::rollback();
        $this->assertEquals(0, Db::$transactionStack);
        $this->assertEquals(array("begin", "rollback"), $this->pdo->queries);
    }
    
    /**
     * @covers Noce\Db::popTransaction
     */
    public function testPopTransaction()
    {
        Db::init("pgsql:test");
        $this->installMock();
        try {
            Db::popTransaction();
            $this->fail();
        }
        catch (Exception $e) {
            $this->assertEquals("err_db_transaction_mismatch", $e->getMessage());
            $this->assertEquals(0, Db::$transactionStack);
        }

        Db::init("pgsql:test");
        $this->installMock();
        Db::begin();
        Db::begin();
        Db::popTransaction();
        $this->assertEquals(1, Db::$transactionStack);
        $this->assertEquals(array("begin"), $this->pdo->queries);
        Db::popTransaction();
        $this->assertEquals(0, Db::$transactionStack);
        $this->assertEquals(array("begin", "commit"), $this->pdo->queries);

        Db::init("pgsql:test");
        $this->installMock();
        Db::begin();
        Db::begin();
        Db::commit();
        Db::rollback();
        $this->assertEquals(0, Db::$transactionStack);
        $this->assertEquals(array("begin", "rollback"), $this->pdo->queries);

        Db::init("pgsql:test");
        $this->installMock();
        Db::begin();
        Db::begin();
        Db::rollback();
        Db::commit();
        $this->assertEquals(0, Db::$transactionStack);
        $this->assertEquals(array("begin", "rollback"), $this->pdo->queries);
    }

    /**
     * @covers Noce\Db::quoteName
     */
    public function testQuoteNameForInvalidName()
    {
        $valids = array_merge(
            range(0x2e, 0x2e), // period
            range(0x30, 0x39), // digits
            range(0x41, 0x5a), // upper alphabets
            range(0x5f, 0x5f), // underbar
            range(0x61, 0x7a));// lower alphabets
        for ($i = 0; $i <= 0xff; ++$i) {
            try {
                Db::quoteName(chr($i));
                if (!in_array($i, $valids)) {
                    $this->fail();
                }
            }
            catch (\Exception $e) {
                if (in_array($i, $valids)) {
                    $this->fail();
                }
                $this->assertEquals($e->getMessage(), "err_db_invalid_name");
            }
        }
    }

    /**
     * @covers Noce\Db::quoteName
     */
    public function testQuoteNameForNonMySql()
    {
        Db::init("pgsql:test");
        $this->assertEquals('"test"', Db::quoteName("test"));
    }
    
    /**
     * @covers Noce\Db::quoteName
     */
    public function testQuoteNameForMySql()
    {
        Db::init("mysql:test");
        $this->assertEquals('`test`', Db::quoteName("test"));
    }
    
    /**
     * @covers Noce\Db::quoteName
     */
    public function testQuoteNameWithPeriod()
    {
        Db::init("pgsql:test");
        $this->assertEquals('"public"."test"', Db::quoteName("public.test"));
    }
}
