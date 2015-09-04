<?php
namespace Noce;

class Db_Transaction
{
    const ACTIVE = 0;
    const COMMITTED = 1;
    const ROLLBACKED = 2;
    public $status = self::ACTIVE;

    public function __construct()
    {
        Db::begin();
    }

    public function commit()
    {
        if ($this->status != self::ACTIVE) {
            return;
        }
        $this->status = self::COMMITTED;
        Db::commit();
    }

    public function rollback()
    {
        if ($this->status != self::ACTIVE) {
            return;
        }
        $this->status = self::ROLLBACKED;
        Db::rollback();
    }

    public function __destruct()
    {
        $this->rollback();
    }
}
