<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Todos
 *
 * @Entity
 * @Table(name="todos")
 */
class Todo
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
     * @var integer
     *
     * @Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @var string
     *
     * @Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var boolean
     *
     * @Column(name="is_completed", type="integer")
     */
    private $is_completed;

    public function __construct(){
    }

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
     * Get user_id
     *
     * @return integer
     */

    public function getUser_Id(){
        return $this->user_id;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return Todo
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Menu
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get is_completed
     *
     * @return string
     */
    public function getIs_Completed()
    {
        return $this->is_completed;
    }

     /**
     * Set is_completed
     *
     * @param string $is_completed
     * @return Menu
     */
    public function setIs_Completed($is_completed)
    {
        $this->is_completed = $is_completed;

        return $this;
    }

    /**
     * Get json formated Todo
     *
     * @return string
     */
    public function getJson()
    {
        $todo_array = array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'is_completed' => $this->is_completed
        );

        return json_encode($todo_array);
    }

}