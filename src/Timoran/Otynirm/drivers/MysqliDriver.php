<?php
namespace Timoran\Otynirm\drivers;
class MysqliDriver implements DriverInterface
{
    private $mysqli;
    public function __construct()
    {
        $rc = new \ReflectionClass('\\mysqli');
        $this->mysqli = $rc->newInstanceArgs(func_get_args());
    }
    public function prepare($sql)
    {
        return new MysqliStatement($this->mysqli->prepare($sql));
    }
    public function bindParam($parameter , &$variable)
    {
        return;
    }
    public function fetch()
    {
        return;
    }
    public function closeCursor()
    {
        return;
    }
    public function execute()
    {
        return;
    }
    public function exec($sql)
    {
        return $this->query($sql);
    }
    public function query($sql)
    {
        return new MysqliStatement($this->mysqli->query($sql));
    }
    public function errorInfo()
    {
        return;
    }
    public function lastInsertId()
    {
        return $this->mysqli->insert_id;
    }
}
class MysqliStatement
{
    private $stmt;
    private $params=array();
    public function __construct($stmt)
    {
        $this->params[] = '';
        $this->stmt = $stmt;
    }
    public function fetch()
    {
        return $this->stmt->fetch_array();
    }
    public function closeCursor()
    {
        return;
    }
    public function errorInfo()
    {
        return array(2=>$this->stmt->error);
    }
    public function execute()
    {
        $this->params[0] = implode('', array_fill(0, count($this->params)-1, 's'));
        call_user_func_array(array($this->stmt, "bind_param"),$this->params);

        return $this->stmt->execute();
    }
    public function bindParam($parameter , &$variable)
    {
        $this->params[] = &$variable;
    }
}
