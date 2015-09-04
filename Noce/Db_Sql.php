<?php
namespace Noce;

class Db_Sql
{
    public function __construct($sql = "", array $params = array())
    {
        $this->sql = (string) $sql;
        $this->params = array_values($params);
    }
    
    public function isEmpty()
    {
        return trim($this->sql) == "";
    }
    
    public function append(Db_Sql $sql)
    {
        $this->sql .= $sql->sql;
        $this->params = array_merge($this->params, $sql->params);
        return $this;
    }
    
    public function appendString($sql)
    {
        $this->append(new Db_Sql($sql));
        return $this;
    }
    
    public function paren()
    {
        if (!$this->isEmpty()) {
            $this->sql = "($this->sql)";
        }
        return $this;
    }
    
    public function appendAnd(Db_Sql $sql)
    {
        $this->appendHelper("and", $sql);
        return $this;
    }
    
    public function appendOr(Db_Sql $sql)
    {
        $this->appendHelper("or", $sql);
        return $this;
    }
    
    public function appendHelper($connect, Db_Sql $sql)
    {
        if ($this->isEmpty()) {
            if (!$sql->isEmpty()) {
                $this->sql = $sql->sql;
            }
        }
        else if (!$sql->isEmpty()) {
            $this->sql = "$this->sql $connect $sql->sql";
        }
        $this->params = array_merge($this->params, $sql->params);
    }
    
    public function combine()
    {
        $splits = explode("?", $this->sql, count($this->params) + 1);
        $query = array_shift($splits);
        foreach ($splits as $i => $s) {
            $p = $this->params[$i];
            if (!($p instanceof Db_Param)) {
                $p = new Db_Param($p);
            }
            $query .= Db::pdo()->quote($p->value, $p->type) . $s;
        }
        return $query;
    }
    
    public static function cat(array $sqls)
    {
        $cat = new Db_Sql();
        foreach ($sqls as $sql) {
            $cat->append($sql);
        }
        return $cat;
    }
    
    public static function join($glue, array $pieces)
    {
        $joined = array_shift($pieces);
        foreach ($pieces as $piece) {
            $joined->appendString($glue);
            $joined->append($piece);
        }
        return $joined;
    }

    public function checkSqlInjection()
    {
        if (strcspn($this->sql, "';\\#") != strlen($this->sql)) {
            throw new \RuntimeException("err_db_sql_injection");
        }
        if (strpos($this->sql, "--") !== false) {
            throw new \RuntimeException("err_db_sql_injection");
        }
        if (strpos($this->sql, "/*") !== false) {
            throw new \RuntimeException("err_db_sql_injection");
        }
    }
}
