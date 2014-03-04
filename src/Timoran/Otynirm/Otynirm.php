<?php
namespace Timoran\Otynirm;
use Arhframe\Util\Folder;
use \ReflectionClass;
use \Exception;
use Stringy\StaticStringy as S;
use Timoran\Otynirm\entity\SchemaEntity;
use Timoran\Otynirm\entity\ProxyEntity;
use Timoran\Otynirm\entity\Linker;
use Timoran\Otynirm\drivers\DriverInterface;
/**
 * @author ArthurH
 * Tiny orm for this website
 * you should repect this in your entity:
 * class name is the same name of your table name
 * class properties must be the same of your table fields
 *
 */
class Otynirm
{
    private $driver;
    private $cachedEntity = array();
    private $entities = array();
    private static $_instance;
    private function __construct($args)
    {
        $driverType = $args[0];
        $driverType = strtolower($driverType);
        unset($args[0]);
        $rc = null;
        if (is_object($driverType)) {
            $this->setDriver($driverType);

            return;
        }
        switch ($driverType) {
            case 'pdo':
                $rc = new ReflectionClass('Timoran\\Otynirm\\drivers\\PDODriver');
                break;
            case 'mysqli':
                $rc = new ReflectionClass('Timoran\\Otynirm\\drivers\\MysqliDriver');
                break;
        }
        if (empty($rc)) {
            throw new Exception("Driver '$driverType' doesn't exist");
        }

        $this->setDriver($rc->newInstanceArgs($args));
    }
    private function generateLinker(SchemaEntity $entity1, SchemaEntity $entity2)
    {
        $tableNamePossible = 'link_'. $entity1->getTableName() .'_'. $entity2->getTableName();
        $tableNamePossible2 = 'link_'. $entity2->getTableName() .'_'. $entity1->getTableName();
        if(!empty($this->entities[$tableNamePossible])
        || !empty($this->entities[$tableNamePossible2])){
            return;
        }
        $className = S::upperCamelize($tableNamePossible);
        $linkedSchemaEntity = new SchemaEntity('Timoran\\Otynirm\\entity\\Linker');
        $linkedSchemaEntity->setTableName($tableNamePossible);
        $linkedSchemaEntity->addMap($entity1->getClassName(), SchemaEntity::ONE);
        $linkedSchemaEntity->addMap($entity2->getClassName(), SchemaEntity::ONE);
        $linkedSchemaEntity->setIsLinker(true);
        $this->entities[$tableNamePossible] = $linkedSchemaEntity;
    }
    private function getLinkerTable(SchemaEntity $entity1, $entitieMappedName)
    {
        $entitieMappedName = S::underscored($entitieMappedName);
        $tableNamePossible = 'link_'. $entity1->getTableName() .'_'. $entitieMappedName;
        $tableNamePossible2 = 'link_'. $entitieMappedName .'_'. $entity1->getTableName();
        if (!empty($this->entities[$tableNamePossible])) {
            return $this->entities[$tableNamePossible];
        }
        if (!empty($this->entities[$tableNamePossible2])) {
            return $this->entities[$tableNamePossible2];
        }

        return null;
    }
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
    public static function getInstance($driverType=null)
    {
        if (is_null(self::$_instance)) {
            if (empty($driverType)) {
                throw new Exception("You must set a driver");
            }
            self::$_instance = new Otynirm(func_get_args());
        }

        return self::$_instance;
    }
    public function addEntitie($entities)
    {
        if (!is_object($entities)) {
            throw new Exception("This is not an object");
        }
        $class = new ReflectionClass($entities);
        $properties = $class->getProperties();
        $infoEntities = array();
        foreach ($properties as $propertie) {

            $infoEntities[] = S::underscored($propertie->getName());
        }
        $this->entities[$class->getName()] = new SchemaEntity($class->getName(), $infoEntities);
    }
    public function load()
    {
        foreach ($this->entities as $tableName=>$schemaEntitie) {
            $tableName = $tableName;
            foreach ($schemaEntitie->getValues() as $key => $value) {
                if (!$this->isEntitie(S::upperCamelize($value))) {
                    continue;
                }
                $typeRelation = $this->getTypeRelation($value);
                $mapLinked = $this->entities[$this->getRealEntitie($value)]->getMap();
                if ($typeRelation == SchemaEntity::MANY && $mapLinked[$tableName] == SchemaEntity::MANY) {
                    $typeRelation = SchemaEntity::MANYTOMANY;
                    $mapLinked[$tableName] = SchemaEntity::MANYTOMANY;
                    $this->entities[$this->getRealEntitie($value)]->setMap($mapLinked);
                    $this->generateLinker($this->entities[$tableName], $this->entities[$this->getRealEntitie($value)]);
                }
                $this->entities[$tableName]->addMap($this->getRealEntitie($value), $typeRelation);
                $this->entities[$tableName]->removeValue($key);
            }
        }
        d($this->entities);
    }
    private function isEntitie($attr)
    {
        $attr = S::upperCamelize($attr);
        if (!empty($this->entities[$attr])) {
            return true;
        }
        $attrFinal = $attr[strlen($attr)-1];
        if ($attrFinal=='s') {
            return $this->isEntitie(substr($attr, 0, strlen($attr)-1));
        }

        return false;
    }
    private function getRealEntitie($attr)
    {
        $attr = S::upperCamelize($attr);
        if ($this->getTypeRelation($attr) == SchemaEntity::ONE) {
            return $attr;
        }

        return substr($attr, 0, strlen($attr)-1);
    }
    private function getTypeRelation($attr)
    {
        $attrFinal = $attr[strlen($attr)-1];
        if ($attrFinal=='s') {
            return SchemaEntity::MANY;
        }

        return SchemaEntity::ONE;
    }
    public function getEntityName(ProxyEntity $entitie)
    {
        $entitie = new \ReflectionClass($entitie->getEntity());

        return $entitie->getName();
    }
    public function getEntitie(ProxyEntity $entities)
    {
        if ($entities==null) {
            return null;
        }
        if (!is_object($entities)) {
            throw new \Exception("This is not an object");
        }
        $entities = $this->getEntityName($entities);
        if (empty($this->entities[$entities])) {
            throw new Exception("This is not an entitie");
        }

        return $this->entities[$entities];
    }
    public function loadEntitiesByFolder($folderPath, $recursive=false, $namespace=null)
    {
        $folder = new Folder($folderPath, $recursive);
        $files = $folder->getFiles('/\.php$/');
        foreach ($files as $file) {
            include_once $file->absolute();
            $entitieName = $file->getBase();
            $entitieName = $namespace.'\\'.$entitieName;
            $this->addEntitie(new $entitieName());
        }
        $this->load();
    }
    public function getEntitieByName($entitieName)
    {
        if (!empty($this->entities[$entitieName])) {
            return $this->entities[$entitieName];
        }
        if (!empty($this->entities[S::underscored($entitieName)])) {
            return $this->entities[S::underscored($entitieName)];
        }
        throw new Exception("This is not an entitie");
    }
    public function create(&$entitie)
    {
    	$entitie = new ProxyEntity($entitie);
        $entitieInfo = $this->getEntitie($entitie);
        $entitieName = $this->getEntityName($entitie);
        $table = $entitieInfo->getTableName();
        $values = $entitieInfo->getValues();
		$className = S::camelize($entitieInfo->getClassName());
        $map = $entitieInfo->getMap();
        $reversedValues = array_flip($values);
        unset($reversedValues['id']);
        $values = array_flip($reversedValues);
        $prepareSql = "INSERT INTO ". $table ." (";
        $prepareSql .= implode(', ', $values);

        foreach ($map as $entitieMappedName=>$relationType) {
            if ($relationType != SchemaEntity::ONE) {
                continue;
            }
            $prepareSql .= ", id_". strtolower($entitieMappedName);
        }
        $prepareSql .= ') VALUES(';
        $prepareSql .= implode(', ', array_fill(0, count($values), "?"));
        foreach ($map as $entitieMappedName=>$relationType) {
        	$get = S::camelize($entitieMappedName);
        	$linkedEntity = $entitie->$get;
            if ($relationType == SchemaEntity::ONE) {

	            if (!($linkedEntity instanceof ProxyEntity)) {
	                try {
	                    $this->create($linkedEntity);
	                } catch (Exception $e) {
	                    throw new Exception("Error when update entitie '$entitieName' : ". $e->getMessage());
	                }
	            }
	            if ($linkedEntity === null) {
	                $prepareSql .= ", DEFAULT";
	            } else {
	                $prepareSql .= ", ". $linkedEntity->getId();
	            }
	            continue;
            }
            if(!($linkedEntity instanceof ProxyEntity)){
            	try {
            		$this->create($linkedEntity);
            	} catch (Exception $e) {
            		throw new Exception("Error when update entitie '$entitieName' : ". $e->getMessage());
            	}
            }else if(($linkedEntity instanceof ProxyEntity) && $linkedEntity->isDirty()){
            	try {
            		$this->update($linkedEntity);
            	} catch (Exception $e) {
            		throw new Exception("Error when update entitie '$entitieName' : ". $e->getMessage());
            	}
            }
            if($relationType == SchemaEntity::MANY){
            	$entitie = $linkedEntity->$className;
            }else{
            	$className .= 's';
            	$entities = $linkedEntity->$className;
            	$entitie = $entities[count($entities-1)];
            }
            return;
        }
        $prepareSql .= ')';
        $stmt = $this->driver->prepare($prepareSql);
        $i=1;
        foreach ($values as $value) {
            $get = S::camelize($value);
            $toSend = "toSend$i";
            $$toSend = $entitie->$get;
            $stmt->bindParam($i, $$toSend);
            $i++;
        }
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new Exception("Error when creating entitie '$entitieName' : ". $error[2]);
        }
        try {
            $entitie->setId($this->driver->lastInsertId());
            $stmt->closeCursor();
            $this->findById($entitieName, $entitie->getId());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    private function getCachedEntity($tableName=null, $id=null)
    {
    	if(empty($id) && empty($tableName)){
    		return $this->cachedEntity;
    	}
        if (empty($this->cachedEntity[$tableName][$id])) {
            return null;
        }

        return $this->cachedEntity[$tableName][$id];
    }
    private function addEntityToCache($tableName, ProxyEntity $entitie)
    {
        $this->cachedEntity[$tableName][$entitie->getId()] = $entitie;
    }
    private function selectRequest($entitieName, $sql, $linkedEntity = null, $filledBy=array())
    {
        $entitieInfo = $this->getEntitieByName($entitieName);
        $table = $entitieInfo->getTableName();
        $values = $entitieInfo->getValues();
        $className = $entitieInfo->getClassName();
        $map = $entitieInfo->getMap();
        $query = $this->driver->query($sql);

        $returnEntities = array();
        while ($data = $query->fetch()) {
            $cachedEntity = $this->getCachedEntity($table, $data['id']);
            if (!empty($cachedEntity)) {
                $entitie = $cachedEntity;
            } else {
                $entitie = new ProxyEntity(new $className());
                $entitie->setId($data['id']);
                $this->addEntityToCache($table, $entitie);
            }

            foreach ($values as $value) {
                if ($value=='id') {
                    continue;
                }
                $set = S::camelize($value);
                $entitie->$set = $data[strtolower($value)];
            }

            foreach ($map as $entitieMappedName=>$relationType) {
                if (in_array($entitieMappedName, $filledBy)) {
                    continue;
                }
                if($relationType == SchemaEntity::MANY
                || $relationType == SchemaEntity::MANYTOMANY
                || $entitieInfo->isLinker()){
                    $attr = $entitieMappedName .'s';
                } else {
                    $attr = $entitieMappedName;
                }
                $set = S::camelize($attr);
                $get = S::camelize($attr);
                $setLinkedEntity = S::camelize($entitieName);
                if ($relationType == SchemaEntity::MANY) {
                    if (!empty($linkedEntity)) {
                        $get = $entitie->$get;
                        if (!is_array($get)) {
                            $get = array($get);
                        }
                        $get[] = $linkedEntity;
                        $entitie->$set = $get;
                        $linkedEntity = null;
                    }
                    $filledBy[]=$entitieName;
                    $linkedEntities = $this->findByFieldPrivate($entitieMappedName, array('id_'. strtolower($table) => $entitie->getId()), null, null, null, $filledBy);
                    if (empty($linkedEntities)) {
                        $linkedEntities = array();
                    }
                    $entitie->$set = $linkedEntities;

                    foreach ($linkedEntities as $linkedEntitie) {
                        $linkedEntitie->$setLinkedEntity= $entitie;
                    }
                } elseif ($relationType == SchemaEntity::ONE) {
                    $linkedEntitie = $this->findByFieldPrivate($entitieMappedName, array('id'=>$data['id_'. strtolower($entitieMappedName)]), null, null, $entitie, $filledBy);

                    if ($entitieInfo->isLinker()) {
                        $entitie = $linkedEntitie;
                    } else {
                        $entitie->$set = $linkedEntitie;
                    }

                } else {
                    $entitieMappedName = $this->getLinkerTable($entitieInfo, $entitieMappedName);
                    $filledBy[]=$entitieName;

                    $linkedEntitie = $this->findByFieldPrivate($entitieMappedName->getTableName(),
                            array('id_'. strtolower($table)=>$data['id']), null, null, $entitie, $filledBy);
                    $entitie->$set = $linkedEntitie;
                    if (!empty($linkedEntitie)) {
                        $attr = S::camelize($attr);

                        $get = $entitie->$attr;

                        if (!is_array($get)) {
                            $get = array($get);
                        }
                        $valueToUse = S::camelize($className) .'s';

                        foreach ($get as $getEntity) {
                            $valuesEntity = $getEntity->$valueToUse;
                            if (!is_array($valuesEntity)) {
                                $valuesEntity = array($valuesEntity);
                            }
                            if (empty($valuesEntity)) {
                                continue;
                            }
                            unset($valuesEntity[0]);
                            $valuesEntity[] = $entitie;
                            if ($entitie instanceof \Role) {
                                var_dump($valuesEntity);
                            }
                            $getEntity->$valueToUse = $valuesEntity;
                        }
                        $get[] = $entitie;
                    }
                }
            }
            $entitie->setDirty(false);
            $returnEntities[] = $entitie;
        }
        $query->closeCursor();
        if (count($returnEntities) == 0) {
            return null;
        }
        if (count($returnEntities) == 1) {
            return $returnEntities[0];
        }

        return $returnEntities;
    }
    public function findAll($entitieName)
    {
        $entitieInfo = $this->getEntitieByName($entitieName);
        $table = $entitieInfo->getTableName();

        return $this->selectRequest($entitieName, "SELECT * FROM $table");
    }
    private function findByFieldPrivate($entitieName, array $fields, $predicate=null, $logic=null, $linkedEntity = null, $filledBy=array())
    {
        $request = "";
        $i=0;
        if (empty($predicate)) {
            $predicate = "=";
        }
        if (empty($logic)) {
            $logic = "AND";
        }
        foreach ($fields as $field=>$value) {
            if ($i>0) {
                $request .= " $logic ";
            }
            $value = addslashes($value);
            $request .= "$field $predicate '$value'";
            $i++;
        }
        $entitieInfo = $this->getEntitieByName($entitieName);
        $table = $entitieInfo->getTableName();

        return $this->selectRequest($entitieName, "SELECT * FROM $table WHERE ". $request, $linkedEntity, $filledBy);
    }
    /**
     * @param  unknown           $entitieName
     * @param  array             $fields
     * @return multitype:unknown
     *
     * &fields is array with field=>value
     */
    public function findByField($entitieName, array $fields, $predicate=null, $logic=null)
    {
        return $this->findByFieldPrivate($entitieName, $fields, $predicate, $logic);
    }

    public function findById($entitieName, $id, $linkedEntity = null)
    {
        $id = (int) $id;

        return $this->findByField($entitieName, array('id'=>$id), $linkedEntity);
    }
    private function removeFromEntitie(ProxyEntity $entitie, ProxyEntity $toRemove)
    {
        $entitieInfo = $this->getEntitie($entitie);
        $map = $entitieInfo->getMap();
        $attr = $this->getEntityName($toRemove);
        $set = S::camelize($attr);
        $get = S::camelize($attr);
        if (!$map[$attr]) {
            $entitie->$set = null;

            return;
        }
        $set .= "s";
        $get .= "s";
        $entities = $entitie->$get;
        $idToRemove = null;
        if (empty($entities)) {
            return;
        }
        foreach ($entities as $key=>$entitieItem) {
            if ($entitieItem->getId()==$toRemove->getId()) {
                $idToRemove = $key;
            }
        }
        if ($idToRemove === null) {
            return;
        }
        unset($entities[$idToRemove]);
        $entitie->$set = $entities;
    }
    public function update(ProxyEntity $entitie)
    {
        $entitieInfo = $this->getEntitie($entitie);
        $entitieName = $this->getEntityName($entitie);
        $table = $entitieInfo->getTableName();
        $values = $entitieInfo->getValues();

        $map = $entitieInfo->getMap();
        $reversedValues = array_reverse($values);
        unset($reversedValues['id']);
        $values = array_reverse($reversedValues);

        $prepareSql = "UPDATE ". $table ." SET ";
        $i=0;
        foreach ($values as $value) {
            if ($i>0) {
                $prepareSql .= ", ";
            }
            $prepareSql .= "$value=?";
            $i++;
        }
        foreach ($map as $entitieMappedName=>$relationType) {
        	$entitieMappedTableName = S::underscored($entitieMappedName);
        	if (empty($this->cachedEntity[$entitieMappedTableName])) {
        		$this->cachedEntity[$entitieMappedTableName] = array();
        	}
        	foreach ($this->cachedEntity[$entitieMappedTableName] as $entitieItem) {
        		$this->removeFromEntitie($entitieItem, $entitie);
        	}
        	$get = S::camelize($entitieMappedName);
        	$linkedEntity = $entitie->$get;
            if ($relationType == SchemaEntity::ONE) {
	            if ($linkedEntity===null) {
	                continue;
	            }
	            if (!($linkedEntity instanceof ProxyEntity)) {
	                try {
	                    $this->create($linkedEntity);
	                } catch (Exception $e) {
	                    throw new Exception("Error when update entitie '$entitieName' : ". $e->getMessage());
	                }
	            }
	            $prepareSql .= ", id_". strtolower($entitieMappedName) ."=". $linkedEntity->getId();
            }elseif($relationType == SchemaEntity::MANY){
            	$linkedEntities = $entitie->$entitieMappedName;
            	foreach ($entitie->$entitieMappedName as $linkedEntity){
            		if(!($linkedEntity instanceof ProxyEntity)){
	            		try {
		                    $this->create($linkedEntity);
		                } catch (Exception $e) {
		                    throw new Exception("Error when update entitie '$entitieName' : ". $e->getMessage());
		                }
            		}else if(($linkedEntity instanceof ProxyEntity) && $linkedEntity->isDirty()){
            			try {
		                    $this->update($linkedEntity);
		                } catch (Exception $e) {
		                    throw new Exception("Error when update entitie '$entitieName' : ". $e->getMessage());
		                }
            		}
            	}
            }else{

            }

        }
        $prepareSql .= " WHERE id=". $entitie->getId();
        $stmt = $this->driver->prepare($prepareSql);
        $i=1;
        foreach ($values as $value) {
            $get = S::camelize($value);
            $toSend = "toSend$i";
            $$toSend = $entitie->$get;
            $stmt->bindParam($i, $$toSend);
            $i++;
        }
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new Exception("Error when update entitie '$entitieName' : ". $error[2]);
        }
        try {

            $stmt->closeCursor();
            $this->findById($entitieName, $entitie->getId());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function remove(ProxyEntity $entitie)
    {
        $entitieInfo = $this->getEntitie($entitie);
        $entitieName = $this->getEntityName($entitie);
        $table = $entitieInfo->getTableName();

        try {
            $this->driver->exec("DELETE FROM $table WHERE id=". $entitie->getId());
        } catch (Exception $e) {
            throw new Exception("Error when delete entitie '$entitieName' : ". $e->getMessage());
        }

    }
    public function getDriver()
    {
        return $this->driver;
    }

}
