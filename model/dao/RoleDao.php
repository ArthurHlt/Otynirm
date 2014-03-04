<?php
include_once ROOT .'/model/dao/AbstractDao.php';
class RoleDao extends AbstractDao
{
    /* (non-PHPdoc)
     * @see AbstractDao::__construct()
     */
    public function __construct()
    {
        parent::__construct('Role');

    }

    public function getRoleByName($name)
    {
        return $this->findByField(array('name'=>$name));
    }
}
