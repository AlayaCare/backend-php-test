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
     * @ManyToOne(targetEntity="User", inversedBy="todos")
     */
    private $user;

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
    
    public function __construct($user, $description){
        $this->user = $user;
        $this->user_id = $user->getId();
    	$this->is_complete = 0;
    	$this->description =  $description;

    }

    public function getUser(){
        return $this->user;
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

    public function setIs_complete($is_complete)
    {
		$this->is_complete = $is_complete;
		return $this->is_complete;
    }

    public function getJson(){
		return [
		'id' => $this->id,
		'user_id' => $this->user_id,
		'description' => $this->description,
		'is_complete' => $this->is_complete
		];
    }

    public function canDelete($user_id){
        if($user_id == $this->user_id)
    		return true;
        else
    		return false;
    }

}