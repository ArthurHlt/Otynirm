<?php
namespace Timoran\Otynirm\entity;

class Linker extends AbstractEntity
{
    private $values = array();
    public function __get($name)
    {
        return $this->values[$name];
    }
    public function __set($name, $value)
    {
        $this->values[$name] = $value;
    }
}
