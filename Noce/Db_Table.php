<?php
namespace Noce;

class Db_Table
{
    const UPDATE_BY_PK = "*by_pk";
    const UPDATE_ALL = null;

    public $_pk;
    public $_name;
    public $_lastStatement = null;

    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function get($id, array $cols = array("*"))
    {
        $found = $this->select(array(
            "where" => new Db_Sql($this->getPk() . " = ?", array($id)),
            "cols" => $cols, 
            "limit" => 1,
            "select" => Db::SELECT_ROW));
        if (!$found) {
            throw new \Exception("err_db_record_not_found");
        }
        return $found;
    }

    public function select($options = array())
    {
        $cols = @$options["cols"];
        if (!$cols) {
            $cols = array("*");
        }
        foreach ($cols as $i => $col) {
            if ($col instanceof Db_Sql) {
                // do nothing
            }
            else if ($col == "*" || $col == "count(*)") {
                $col = new Db_Sql($col);
            }
            else if (preg_match("/^(count|sum|avg|min|max)\\((.+)\\)$/u", $col, $match)) {
                $col = new Db_Sql($match[1] . "(" . Db::quoteName($match[2]) . ")");
            }
            else {
                $col = new Db_Sql(Db::quoteName($col));
            }
            $cols[$i] = $col;
        }
        $sql = new Db_Sql("select ");
        $sql->append(Db_Sql::join(", ", $cols));
        $sql->appendString(" from " . Db::quoteName($this->_name));
        // JOIN
        if (isset($options["join"])) {
            $sql->appendString(" ")->append($options["join"]);
        }
        // WHERE
        if (@$options["where"]) {
            $sql->appendString(" where ")->append(Db::where($options["where"]));
        }
        // GROUP BY
        if (isset($options["group"])) {
            foreach ($options["group"] as $i => $g) {
                $options["group"][$i] = Db::quoteName($g);
            }
            $sql->appendString(" group by " . join(", ", $options["group"]));
        }
        // ORDER BY
        if (isset($options["order"])) {
            static $orderDirs = array("+" => "asc", "-" => "desc");
            $order = (array) $options["order"];
            foreach ($order as $i => $o) {
                if ($o == "") {
                    throw new \Exception("err_db_invalid_order_specification");
                }
                if ($o[0] != "+" && $o[0] != "-") {
                    $o = "+" . $o;
                }
                $dir = $orderDirs[$o[0]];
                $o = Db::quoteName(substr($o, 1));
                $order[$i] = "$o $dir";
            }
            $sql->appendString(" order by " . join(", ", $order));
        }
        // LIMIT
        if (isset($options["limit"]) && $options["limit"] == (int) $options["limit"]) {
            $sql->append(new Db_Sql(" limit ?", array(new Db_Param($options["limit"], \PDO::PARAM_INT))));
        }
        // OFFSET
        if (isset($options["offset"]) && $options["offset"] == (int) $options["offset"]) {
            $sql->append(new Db_Sql(" offset ?", array(new Db_Param($options["offset"], \PDO::PARAM_INT))));
        }
        // 
        if (!isset($options["select"])) {
            $options["select"] = Db::SELECT_ALL;
        }
        // Execute
        return Db::select($sql, $options["select"]);
    }

    public function selectOne($options = array())
    {
        $options["select"] = Db::SELECT_ONE;
        $options["limit"] = 1;
        return $this->select($options);
    }

    public function selectRow($options = array())
    {
        $options["select"] = Db::SELECT_ROW;
        $options["limit"] = 1;
        return $this->select($options);
    }
    
    public function selectCol($options = array())
    {
        $options["select"] = Db::SELECT_COL;
        return $this->select($options);
    }
    
    public function save($data)
    {
        // Try update
        if (@$data[$this->getPk()] != "") {
            try {
                $this->update($data);
                return $data[$this->getPk()];
            }
            catch (\Exception $e) {
                if ($e->getMessage() != "err_db_record_not_found") {
                    throw $e;
                }
                // Continue to insert
            }
        }
        // Insert
        $this->insert($data);
        return $this->lastInsertId();
    }

    public function insert($data)
    {
        $this->_lastStatement = Db::execute($this->makeInsertQuery($data));
    }

    public function makeInsertQuery($data)
    {
        $data = $this->prepareInsertOrUpdate($data);
        $data = $this->prepareInsert($data);
        $data = ArrayX::pick($data, $this->getCols());
        // 空の主キーを削除
        if ($this->isEmptyPk(@$data[$this->getPk()])) {
            unset($data[$this->getPk()]);
        }
        $cols = array_keys($data);
        foreach ($cols as $i => $col) {
            $cols[$i] = Db::quoteName($col);
        }
        $cols = join(", ", $cols);
        $data = array_values($data);
        $marks = join(", ", array_fill(0, count($data), "?"));
        return new Db_Sql("insert into " . Db::quoteName($this->_name) . " ($cols) values ($marks)", $data);
    }
    
    public function prepareInsertOrUpdate($data)
    {
        return $data;
    }
    
    public function prepareInsert($data)
    {
        return $data;
    }
    
    public function insertIgnore($data)
    {
        $driver = Db::getDriver();
        if ($driver == "pgsql") {
            $this->insertIgnorePgsql($data);
            return;
        }
        if ($driver == "mysql") {
            $this->insertIgnoreMysql($data);
            return;
        }
        // TODO: Support SQLite
        throw new \Exception("err_db_not_implemented");
    }

    public function insertIgnorePgsql($data)
    {
        try {
            $tran = new Db_Transaction();
            $sp = new Db_Savepoint();
            $id = $this->insert($data);
            $sp->commit();
            $tran->commit();
        }
        catch (\PDOException $e) {
            if ($e->getCode() != 23505) { // 23505 = unique_violation
                throw $e;
            }
            $sp->rollback();
        }
    }

    public function insertIgnoreMysql($data)
    {
        $q = $this->makeInsertQuery($data);
        $q->sql = "insert ignore" . substr($q->sql, strlen("insert"));
        $this->_lastStatement = Db::execute($q);
    }

    public function update($data, $where = self::UPDATE_BY_PK)
    {
        $this->_lastStatement = Db::execute($this->makeUpdateQuery($data, $where));
    }

    public function makeUpdateQuery($data, $where = self::UPDATE_BY_PK)
    {
        $data = $this->prepareInsertOrUpdate($data);
        $data = $this->prepareUpdate($data);
        $data = ArrayX::pick($data, $this->getCols());
        $id = @$data[$this->getPk()];
        unset($data[$this->getPk()]);

        $sql = new Db_Sql("update " . Db::quoteName($this->_name));
        // SET
        $set = array();
        $params = array();
        foreach ($data as $col => $value) {
            if ($value instanceof Db_Sql) {
                $set[] = Db::quoteName($col) . " = $value->sql";
                $params = array_merge($params, $value->params);
            }
            else {
                $set[] = Db::quoteName($col) . " = ?";
                $params[] = $value;
            }
        }
        $sql->append(new Db_Sql(" set " . join(", ", $set), $params));
        // WHERE
        if ($where == self::UPDATE_BY_PK) {
            if ($this->isEmptyPk($id)) {
                throw new \Exception("err_db_no_pkey");
            }
            $sql->append(new Db_Sql(" where " . Db::quoteName($this->getPk()) . " = ?", array($id)));
        }
        else if ($where) {
            $sql->appendString(" where ")->append(Db::where($where));
        }
        return $sql;
    }
    
    function prepareUpdate($data)
    {
        return $data;
    }
    
    public function insertOrUpdate($insert_data, $update_data)
    {
        $driver = Db::getDriver();
        if ($driver == "mysql") {
            $update_q = $this->makeUpdateQuery($update_data, null);
            $update_q->sql = preg_replace("/^update " . Db::quoteName($this->_name) . " set/", "update", $update_q->sql);
            $q = $this->makeInsertQuery($insert_data);
            $q->appendString(" on duplicate key ");
            $q->append($update_q);
            $this->_lastStatement = Db::execute($q);
            return;
        }
        throw new \Exception("err_db_not_implemented");
    }
    
    /**
     * 行を削除
     *
     * @params $where array|Db_Sql
     */
    public function delete($where)
    {
        if (!($where instanceof Db_Sql)) {
            $where = Db::where($where);
        }
        if ($where->isEmpty()) {
            throw new \RuntimeException("err_db_delete_all");
        }
        $sql = new Db_Sql("delete from " . Db::quoteName($this->_name));
        $sql->appendString(" where ")->append($where);
        $this->_lastStatement = Db::execute($sql);
    }
    
    public function truncate()
    {
        if (Db::getDriver() == "sqlite") {
            $sql = new Db_Sql("delete from " . Db::quoteName($this->_name));
        }
        else {
            $sql = new Db_Sql("truncate table " . Db::quoteName($this->_name));
        }
        $this->_lastStatement = Db::execute($sql);
    }

    public function getName()
    {
        return $this->_name;
    }

    public function lastInsertId()
    {
        $name = null;
        if (Db::getDriver() == "pgsql") {
            $name = $this->_name . "_" . $this->getPk() . "_seq";
        }
        return Db::lastInsertId($name);
    }

    public function affectedCount()
    {
        return $this->_lastStatement->rowCount();
    }

    public function isEmptyPk($pk)
    {
        return $pk == "";
    }

    public function getCols()
    {
        return array_keys(Db::getTableInfo($this->_name));
    }

    public function getPk()
    {
        if ($this->_pk) {
            return $this->_pk;
        }
        foreach (Db::getTableInfo($this->_name) as $col => $info) {
            if ($info["pk"]) {
                $this->_pk = $col;
                return $this->_pk;
            }
        }
        throw new \Exception("err_db_no_pk");
    }
    
    public function setPk($pk)
    {
        $this->_pk = $pk;
    }
}
