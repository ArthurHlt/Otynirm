<?php
namespace Timoran\Otynirm\entity;
/**
 * @author XPBP8114
 * abstract entitie for model class and orm
 */
abstract class AbstractEntity
{
    protected $id;

    public function __construct()
    {
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
    public function __get($name)
    {
        $get = "get". ucfirst($name);

        return $this->$get();
    }
    public function __set($name, $value)
    {
        $set = "set". ucfirst($name);
        $this->$set($value);
    }
}
