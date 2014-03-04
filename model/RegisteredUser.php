<?php
use Timoran\Otynirm\entity\AbstractEntity;
class RegisteredUser
{
    private $firstname;
    private $lastname;
    private $age;
    private $email;
    private $password;
    private $roles;
    public function __construct($firstname=null, $lastname=null, $email=null, $age=null, $password=null)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->age = $age;
        $this->password = $password;
    }
    public function getFirstname()
    {
        return $this->firstname;
    }
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }
    public function getLastname()
    {
        return $this->lastname;
    }
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }
    public function getAge()
    {
        return $this->age;
    }
    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    public function getRoles()
    {
        return $this->roles;
    }
    public function setRoles($role)
    {
        $this->roles = $role;
        return $this;
    }


}
