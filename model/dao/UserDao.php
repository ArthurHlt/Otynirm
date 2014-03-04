<?php

use Timoran\Otynirm\dao\AbstractDao;
class UserDao extends AbstractDao
{
    /* (non-PHPdoc)
     * @see AbstractDao::__construct()
    */
    public function __construct()
    {
        parent::__construct('RegisteredUser');

    }

    public function getByEmail($email)
    {
        return $this->findByField(array('email'=>$email));
    }
}
