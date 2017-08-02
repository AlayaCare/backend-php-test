<?php
namespace Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * Todos
 *
 * @Entity 
 * @Table(name="todos")
 * documentation https://symfony.com/doc/current/doctrine.html
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
     * @var tinyint
     * 
     * @Column(name="is_complete", type="boolean")
     */
    private $is_complete;
    
    public function __construct($user_id, $description){
    	$this->user_id = $user_id;
    	$this->is_complete = 0;
    	$this->description =  $description;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getIs_complete()
    {
        return $this->is_complete;
    }

}