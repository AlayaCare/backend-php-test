<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Repository\TodoRepository;

use Symfony\Component\Validator\Constraints as Assert;

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
     * Returns the Todo id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the Todo description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the Todo description.
     *
     * @param string
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the Todo user id.
     *
     * @return int
     */
    public function getuser_id()
    {
        return $this->user_id;
    }

    /**
     * Sets the Todo user id.
     *
     * @param int
     */
    public function setuser_id($user_id)
    {
        $this->user_id = $user_id;
    }

}
