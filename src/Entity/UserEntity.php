<?php

namespace Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class UserEntity implements UserInterface
{


    protected $db;


    /**
     * Class constructor
     * 
     * 
     */
	function __construct( $db )
	{
		$this->db = $db;
	}

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * 
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * 
     */
    private $username;
    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $password;


    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->username;
    }
    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getSalt()
    {

    }

    public function eraseCredentials()
    {

    }

}
