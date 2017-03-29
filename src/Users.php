<?php
/**
 * @Entity @Table(name="users")
 **/
class Users
{
    /**
     * @Id 
     * @Column(type="integer")
     * @var int
     */
    protected $id;
    /**
     * @Column(type="string")
     * @var string
     */
    protected $username;
    /**
     * @Column(type="string")
     * @var string
     */
    protected $password;

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($name)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }
}