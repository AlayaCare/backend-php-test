<?php
/**
 * @Entity @Table(name="todos")
 **/


class Todos
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @Column(type="integer")
     * @var int
     */
    protected $id;
    /**
     * @Column(type="string")
     * @var string
     */
    protected $user_id;
    /**
     * @Column(type="string")
     * @var string
     */
    protected $description;
    /**
     * @Column(type="string")
     * @var string
     */
    protected $completed;


    public function getId()
    {
        return $this->id;
    }

    public function getUser_ID()
    {
        return $this->user_id;
    }

    public function setUser_ID($userid)
    {
        $this->user_id = $userid;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getCompleted()
    {
        return $this->completed;
    }

    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }
}