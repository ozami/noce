<?php
namespace Noce;

class Db_Param
{
    public function __construct($value, $type = \PDO::PARAM_STR)
    {
        $this->value = $value;
        $this->type = $type;
    }
}
