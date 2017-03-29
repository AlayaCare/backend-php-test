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
     * @Column(name="completed", type="boolean", length=255, options={"default": false})
     */
    private $completed;

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
     * Set description
     * 
     * @param string $description
     * @return Menu
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $description;
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
     * Set completed
     * 
     * @param string $completed
     * @return Menu
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    
        return $completed;
    }

    /**
     * Get completed
     * 
     * @return string
     */
    public function getCompleted()
    {
    	return $this->completed;
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

    public function json(){
        return array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'completed' => $this->completed
        );
    }
}
