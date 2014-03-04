<?php
namespace Timoran\Otynirm\drivers;
class PDODriver implements DriverInterface
{
    private $pdo;
    public function __construct()
    {
        $rc = new \ReflectionClass('\\PDO');
        $this->pdo = $rc->newInstanceArgs(func_get_args());
    }
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
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
        return ;
    }
    public function errorInfo()
    {
        return;
    }
    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }
    public function query($sql)
    {
        return $this->pdo->query($sql);
    }
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
