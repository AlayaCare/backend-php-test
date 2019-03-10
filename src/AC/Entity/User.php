<?php

namespace AC\Entity;

/**
 * Class User
 * @package AC\Entity
 */
class User implements IEntity
{
    private $id;
    private $username;
    private $password;


    public function __construct()
    {
    }

    private function encryptPassword($password){
        return md5($password);
    }

    public function __toString()
    {
        return self::class.'(id)'.$this->id.' (username)'.$this->username;
    }


    public function fill(array $data)
    {
        if(isset($data['id'])){
            $this->id=(int)$data['id'];
        }
        if(isset($data['username'])){
            $this->username=$data['username'];
        }
        if(isset($data['password'])){
            $this->password=$data['password'];
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $this->encryptPassword($password);
    }


    public function toArray()
    {
        return ['username' => $this->username, 'password' => $this->password, 'id' => $this->id];
    }
}