<?php
namespace Noce;

class Db
{
    const SELECT_ALL = "all";
    const SELECT_ROW = "row";
    const SELECT_COL = "col";
    const SELECT_ONE = "one";
    
    public static $pdo;
    public static $dsn;
    public static $driver;
    public static $user;
    public static $password;
    public static $options;
    public static $transactionStack = 0;
    public static $rollbacked = false;
    public static $statementCache = array();
    public static $dumpQuery = false;
    public static $tableInfos = array();
    
    public static function init($dsn, $user = null, $password = null, $options = array())
    {
        self::$dsn = $dsn;
        self::$user = $user;
        self::$password = $password;
        self::$options = $options;
        list (self::$driver, ) = explode(":", self::$dsn);
        self::$pdo = null;
        self::$transactionStack = 0;
        self::$rollbacked = false;
        self::$statementCache = array();
        self::$tableInfos = array();
    }
    
    public static function getDsn()
    {
        return self::$dsn;
    }
    
    public static function getDriver()
    {
        return self::$driver;
    }
    
    public static function pdo()
    {
        if (!self::$pdo) {
            self::$pdo = new \PDO(self::$dsn, self::$user, self::$password, self::$options);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION);
            if (self::$driver === "mysql") {
                self::$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            }
            if (self::$driver === "sqlite") {
                self::$pdo->exec("pragma foreign_keys = ON");
            }
        }
        return self::$pdo;
    }
    
    public static function begin()
    {
        if (self::$transactionStack == 0) {
            self::pdo_exec("begin");
        }
        ++self::$transactionStack;
    }
    
    public static function commit()
    {
        self::popTransaction();
    }
    
    public static function rollback()
    {
        self::$rollbacked = true;
        self::popTransaction();
    }
    
    public static function popTransaction()
    {
        --self::$transactionStack;
        if (self::$transactionStack < 0) {
            self::$transactionStack = 0;
            throw new \Exception("err_db_transaction_mismatch");
        }
        if (self::$transactionStack > 0) {
            return;
        }
        if (self::$rollbacked) {
            self::pdo_exec("rollback");
            self::$rollbacked = false;
            return;
        }
        self::pdo_exec("commit");
    }
    
    public static function savepoint($name)
    {
        $name = self::quoteName($name);
        self::pdo_exec("savepoint $name");
    }
    
    public static function releaseSavepoint($name)
    {
        $name = self::quoteName($name);
        self::pdo_exec("release savepoint $name");
    }
    
    public static function rollbackTo($name)
    {
        $name = self::quoteName($name);
        self::pdo_exec("rollback to savepoint $name");
    }
    
    public static function execute(Db_Sql $sql)
    {
        if (!isset(self::$statementCache[$sql->sql])) {
            $sql->checkSqlInjection();
            self::$statementCache[$sql->sql] = self::pdo()->prepare($sql->sql);
        }
        $s = self::$statementCache[$sql->sql];
        // Bind parameters
        $sql->params = array_values($sql->params);
        foreach ($sql->params as $i => $p) {
            if ($p instanceof Db_Param) {
                $s->bindParam($i + 1, $sql->params[$i]->value, $sql->params[$i]->type);
            }
            else {
                $s->bindParam($i + 1, $sql->params[$i], \PDO::PARAM_STR);
            }
        }
        // Execute
        self::pdo_exec($s, $sql);
        return $s;
    }

    public static function select($sql, $select = self::SELECT_ALL)
    {
        $s = self::execute($sql);
        $rows = $s->fetchAll(\PDO::FETCH_ASSOC);
        $s->closeCursor(); // TODO: Needed?
        if ($select == self::SELECT_ALL) {
            return $rows;
        }
        if ($select == self::SELECT_ROW) {
            return @$rows[0];
        }
        if ($select == self::SELECT_COL) {
            $col = array();
            foreach ($rows as $r) {
                $col[] = current($r);
            }
            return $col;
        }
        if ($select == self::SELECT_ONE) {
            if (!$rows) {
                return null;
            }
            return current($rows[0]);
        }
        throw new \Exception();
    }
    
    public static function join($type, $table, $on, $params = array())
    {
        static $types = array("inner", "left outer", "right outer", "full outer", "cross");
        if (!in_array($type, $types)) {
            throw new \Exception();
        }
        $table = self::quoteName($table);
        return new Db_Sql(" $type join $table on $on", $params);
    }
    
    public static function where($where)
    {
        if ($where instanceof Db_Sql) {
            return $where;
        }
        if (!is_array($where)) {
            return new Db_Sql($where);
        }
        $sql = new Db_Sql();
        static $normalOps = array(
            "eq" => "=",
            "not_eq" => "!=",
            "lt" => "<",
            "le" => "<=",
            "gt" => ">",
            "ge" => ">=",
            "like" => "like",
            "ilike" => "ilike",
            "not_like" => "not like",
            "not_ilike" => "not ilike",
            "regexp" => "~",
            "not_regexp" => "!~"
        );
        static $patternOps = array(
            "begin",
            "end",
            "contain",
            "not_begin",
            "not_end",
            "not_contain"
        );
        foreach ($where as $name => $value) {
            if ($value instanceof Db_Sql) {
                $sql->appendAnd($value);
                continue;
            }
            $name = str_replace("::", ".", $name);
            if (!preg_match("/^(([a-zA-Z0-9_]+[.])?[a-zA-Z0-9_]+)(:([a-z_]+))?$/", $name, $matched)) {
                throw new \Exception("err_db_invalid_name");
            }
            @list (, $name, , , $op) = $matched;
            $name = self::quoteName($name);
            if ($op == "") {
                $op = "eq";
            }
            if (isset($normalOps[$op])) {
                $sql->appendAnd(new Db_Sql("$name {$normalOps[$op]} ?", array($value)));
                continue;
            }
            if ($op == "null") {
                $sql->appendAnd(new Db_Sql("$name is " . ($value? "": "not ") . "null"));
                continue;
            }
            if ($op == "in" || $op == "not_in") {
                $holders = join(", ", array_fill(0, count($value), "?"));
                $sql->appendAnd(new Db_Sql("$name " . ($op == "not_in"? "not ": "") . "in ($holders)", $value));
                continue;
            }
            if (in_array($op, $patternOps)) {
                $not = "";
                if (strpos($op, "not_") === 0) {
                    $not = " not";
                    $op = substr($op, 4);
                }
                $pattern = preg_replace("/([#%_])/u", "#$1", $value); // escape specials
                if ($op == "begin" || $op == "contain") {
                    $pattern .= "%";
                }
                if ($op == "end" || $op == "contain") {
                    $pattern = "%" . $pattern;
                }
                $like = "like";
                if (Db::getDriver() == "pgsql") {
                    $like = "ilike";
                }
                $sql->appendAnd(new Db_Sql("$name$not $like ? escape ?", array($pattern, "#")));
                continue;
            }
            throw new \Exception("err_db_invalid_operator");
        }
        return $sql;
    }
    
    public static function lastInsertId($name = null)
    {
        return self::pdo()->lastInsertId($name);
    }

    public static function quoteName($name)
    {
        if (preg_match("/[^A-Za-z0-9_.]/", $name)) {
            throw new \Exception("err_db_invalid_name");
        }
        $quote = '"';
        if (self::getDriver() == "mysql") {
            $quote = "`";
        }
        return $quote . join("$quote.$quote", explode(".", $name)) . $quote;
    }
    
    public static function getTableInfo($table)
    {
        if (!isset(self::$tableInfos[$table])) {
            $driver = Db::getDriver();
            self::$tableInfos[$table] = call_user_func(array(__CLASS__, "getTableInfo$driver"), $table);
        }
        return self::$tableInfos[$table];
    }
    
    public static function getTableInfoSqlite($table)
    {
        $info = array();
        $cols = self::select(new Db_Sql("pragma table_info($table)"));
        foreach ($cols as $c) {
            $info[$c["name"]] = array(
                "pk" => $c["pk"] != 0,
                "not_null" => (bool) $c["notnull"]
            );
        }
        return $info;
    }
    
    public static function getTableInfoPgsql($table)
    {
        $info = array();
        $cols = self::select(new Db_Sql(
            "select a.attname, a.attnotnull, " .
            "(select a.attnum = any (conkey) from pg_constraint where conrelid = c.oid and contype = ?) as pk " .
            "from pg_attribute a " .
            "inner join pg_class c on a.attrelid = c.oid " .
            "where c.relname = ? and a.attnum >= ? and not a.attisdropped",
            array("p", $table, 1))
        );
        foreach ($cols as $c) {
            $info[$c["attname"]] = array(
                "pk" => $c["pk"],
                "not_null" => $c["attnotnull"]
            ); // TODO: check type of attnotnull
        }
        return $info;
    }
    
    public static function getTableInfoMysql($table)
    {
        $info = array();
        $cols = self::select(new Db_Sql("show columns from $table"));
        foreach ($cols as $c) {
            $info[$c["Field"]] = array(
                "pk" => $c["Key"] == "PRI",
                "not_null" => $c["Null"] == "NO"
            );
        }
        return $info;
    }
    
    public static function setDumpQuery($dump = true)
    {
        self::$dumpQuery = $dump;
    }
    
    public static function pdo_exec($query, Db_Sql $sql = null)
    {
        $time = microtime(true);
        try {
            if ($query instanceof \PDOStatement) {
                $query->execute();
            }
            else {
                self::pdo()->exec($query);
            }
        }
        catch (\Exception $e) {
        }
        if (self::$dumpQuery) {
            $time = (microtime(true) - $time) * 1000;
            $code = isset($e)? $e->getCode(): "00000";
            $log = ($query instanceof \PDOStatement)? $sql->combine(): $query;
            $log .= sprintf(" [%s %.2f]", $code, $time);
            Debug::write(Debug::DEBUG, $log, "SQL");
        }
        if (isset($e)) {
            throw $e;
        }
    }
}
