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
     * @ORM\ManyToOne(targetEntity="Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * 
     */
    private $user_id;

    public function __construct()
    {
        $this->user_id = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getuser_id()
    {
        return $this->user_id;
    }

    public function setuser_id($user_id)
    {
        $this->user_id = $user_id;
    }

}
