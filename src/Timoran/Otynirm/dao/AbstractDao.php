<?php

namespace Timoran\Otynirm\dao;
use Timoran\Otynirm\Otynirm;
use Timoran\Otynirm\entity\AbstractEntitie;
abstract class AbstractDao
{
    protected $orm;
    protected $nameEntity;
    public function __construct($nameEntity)
    {
        $this->orm = Otynirm::getInstance();
        $this->nameEntity = $nameEntity;
    }
    public function getOrm()
    {
        return $this->orm;
    }
    public function setOrm($orm)
    {
        $this->orm = $orm;

        return $this;
    }
    public function getNameEntity()
    {
        return $this->nameEntity;
    }
    public function setNameEntity($nameEntity)
    {
        $this->nameEntity = $nameEntity;

        return $this;
    }
    public function findAll()
    {
        return $this->orm->findAll($this->nameEntity);
    }
    public function findByField(array $fields, $predicate=null, $logic=null)
    {
        return $this->orm->findByField($this->nameEntity, $fields, $predicate, $logic);
    }
    public function findById($id)
    {
        return $this->orm->findById($this->nameEntity, $id);
    }
    public function remove(AbstractEntitie $entitie)
    {
        $this->getOrm()->remove($entitie);
    }
    public function update(AbstractEntitie $entitie)
    {
        $this->getOrm()->update($entitie);
    }
    public function create(&$entitie)
    {
        $this->getOrm()->create($entitie);
    }
}
