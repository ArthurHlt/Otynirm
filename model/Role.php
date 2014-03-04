<?php
use Timoran\Otynirm\entity\AbstractEntity;
class Role
{
    private $name;
    private $registeredUsers;
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    public function getRegisteredUsers()
    {
        return $this->registeredUsers;
    }
    public function setRegisteredUsers($registered_Users)
    {
        $this->registeredUsers = $registered_Users;
        return $this;
    }

}
