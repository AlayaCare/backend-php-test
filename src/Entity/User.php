<?php

namespace Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{

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

    /**
     * Returns the user id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the user name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->username;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return (Role|string)[]
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * The default configuration of this here extension will return null.
     *
     * @return string|null
     */
    public function getSalt()
    {

    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like the plain-text password is stored on this object.
     *
     * The default configuration of this here extension enforces encoded passwords using the sha512 algorithm.
     */
    public function eraseCredentials()
    {

    }

}
