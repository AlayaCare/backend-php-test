<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Repository\TodoRepository")
 * @ORM\Table(name="todos")
 */
class Todo extends Entity
{
    /**
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     *
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=60)
     *
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP")
     * @var DateTime
     */
    protected $updated;

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
        $this->created = new \DateTime();
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
     * Returns the Todo title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the Todo title.
     *
     * @param string
     */
    public function setTitle($title)
    {
        $this->title = $title;
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

    /**
     * Returns the Todo status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the Todo status.
     *
     * @param string
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

}
