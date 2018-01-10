<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Repository\TodoRepository;

/**
 * @ORM\Entity(repositoryClass="Repository\TodoRepository")
 * @ORM\Table(name="todos")
 */
class Todo
{
    /**
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     * 
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $description;
    /**
     * @ORM\Column(type="integer")
     * 
     * Many Todos per One User
     * @ORM\ManyToOne(targetEntity="Entity\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * 
     */
    private $user_id;

    public function __construct()
    {
        $this->user_id = new ArrayCollection();
    }

    /**
     * Returns the todo id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the todo description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the todo description.
     *
     * @param string
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the todo user id.
     *
     * @return int
     */
    public function getuser_id()
    {
        return $this->user_id;
    }

    /**
     * Sets the todo user id.
     *
     * @param int
     */
    public function setuser_id($user_id)
    {
        $this->user_id = $user_id;
    }

}
