<?php
namespace Noce;

class Db_Savepoint
{
    const ACTIVE = 0;
    const COMMITTED = 1;
    const ROLLBACKED = 2;
    public $id;
    public $status = self::ACTIVE;
    static public $lastId = 0;

    public function __construct()
    {
        $this->id = ++self::$lastId;
        Db::savepoint($this->getName());
    }

    public function commit()
    {
        if ($this->status != self::ACTIVE) {
            return;
        }
        $this->status = self::COMMITTED;
        Db::releaseSavepoint($this->getName());
    }

    public function rollback()
    {
        if ($this->status != self::ACTIVE) {
            return;
        }
        $this->status = self::ROLLBACKED;
        Db::rollbackTo($this->getName());
    }

    public function __destruct()
    {
        $this->rollback();
    }

    public function getName()
    {
        return "Db_Savepoint" . $this->id;
    }
}
