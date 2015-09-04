<?php
use Noce\Db_Param;

class Db_ParamSqlTest extends PHPUnit_Framework_TestCase
{
    public function testConstruction()
    {
        $p = new Db_Param(0);
        $this->assertSame(0, $p->value);
        $this->assertSame(\PDO::PARAM_STR, $p->type);
        
        $p = new Db_Param("test", \PDO::PARAM_INT);
        $this->assertSame("test", $p->value);
        $this->assertSame(\PDO::PARAM_INT, $p->type);
    }
}
