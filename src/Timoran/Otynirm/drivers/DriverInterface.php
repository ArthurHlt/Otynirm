<?php
namespace Timoran\Otynirm\drivers;
interface DriverInterface
{
    public function prepare($sql);
    public function fetch();
    public function closeCursor();
    public function execute();
    public function exec($sql);
    public function query($sql);
    public function bindParam($parameter , &$variable);
    public function errorInfo();
    public function lastInsertId();
}
