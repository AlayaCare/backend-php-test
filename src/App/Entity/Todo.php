<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 * @ORM\Table(name="todos")
 */
class Todo
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userid;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string",length=255, nullable=true)
     */
    private $description;
     /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param int $id
     *
     * @return todo
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userid;
    }
    /**
     * @param int $userid
     *
     * @return todo
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;
        return $this;
    }

     /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * @param int $status
     *
     * @return todo
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

     /**
     * @return int
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * @param int $description
     *
     * @return todo
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
}