<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Todos
 *
 * @Entity 
 * @Table(name="users")
 */
class User
{
    /**
     * @var integer
     * 
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

     /**
     * @var string
     * 
     * @Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     * 
     * @Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * Get id
     * 
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     * 
     * @return string
     */
    public function getUsername()
    {
    	return $this->username;
    }

    /**
     * Get password
     * 
     * @return string
     */
    public function getPassword()
    {
    	return $this->password;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }
}
