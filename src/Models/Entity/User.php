<?php

namespace App\Models\Entity;
use App\Auth\Bcrypt;

/**
 * @Entity @Table(name="users")
 **/
class User
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    private $id;

    /** @Column(type="string", length=100,unique=true, nullable=false) **/
    private $username;

    /** @Column(type="string", length=100) **/
    private $password;

  

    public function setUsername($username){
        if (!$username) {
            throw new \InvalidArgumentException("Username is required", 400);
        }
        $this->username = $username;
    }

    public function setPassword($password){
        if (!$password ) {
            throw new \InvalidArgumentException("Password is required", 400);
        }
        $bcrypt = new Bcrypt();
        $this->password = $bcrypt->setHash($password);
    }

    public function getId()
    {
       return $this->id;
    }

    public function getUsername()
    {
       return $this->username;
    }

    public function getPassword()
    {
       return $this->password;
    }
    
}