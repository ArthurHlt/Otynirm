<?php
namespace Timoran\Otynirm\entity;
use Stringy\StaticStringy as S;
class SchemaEntity
{
    const MANY = "many";
    const ONE = "one";
    const MANYTOMANY = "manytomany";
    private $tableName;
    private $isLinker = false;
    private $className;
    private $values = array();
    private $map = array();
    public function __construct($className, array $values=array(), array $map=array(), $tableName=null)
    {
        $this->className = S::upperCamelize($className);
        if (empty($tableName)) {
            $this->tableName = S::underscored($className);
        }
        $this->values = $values;
        $this->map = $map;
    }
    public function getTableName()
    {
        return $this->tableName;
    }
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }
    public function getClassName()
    {
        return $this->className;
    }
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }
    public function addMap($key, $value)
    {
        if (isset($this->map[$key])) {
            return;
        }
        $this->map[$key] = $value;
    }
    public function removeMap($key=null, $value=null)
    {
        if (!is_null($key)) {
            unset($this->map[$key]);

            return;
        }
        if (is_null($value)) {
            return;
        }
        if (!in_array($value, $this->map)) {
            return;
        }
        $toRemove = null;
        foreach ($this->map as $key=>$valueItem) {
            if ($valueItem==$value) {
                $toRemove = $key;
            }
        }
        unset($this->map[$toRemove]);
    }
    public function addValue($value)
    {
        if (in_array($value, $this->values)) {
            return;
        }
        $this->values[] = $value;
    }
    public function removeValue($key=null, $value=null)
    {
        if (!is_null($key)) {
            unset($this->values[$key]);

            return;
        }
        if (is_null($value)) {
            return;
        }
        if (!in_array($value, $this->values)) {
            return;
        }
        $toRemove = null;
        foreach ($this->values as $key=>$valueItem) {
            if ($valueItem==$value) {
                $toRemove = $key;
            }
        }
        unset($this->values[$toRemove]);
    }
    public function getValues()
    {
        return $this->values;
    }
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }
    public function getMap()
    {
        return $this->map;
    }
    public function setMap($map)
    {
        $this->map = $map;

        return $this;
    }
    public function isLinker()
    {
        return $this->isLinker;
    }
    public function setIsLinker($isLinker)
    {
        $this->isLinker = $isLinker;

        return $this;
    }

}
