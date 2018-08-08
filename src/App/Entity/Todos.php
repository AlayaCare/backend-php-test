<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM; 
use \DateTime;
use App\Entity\Users;

/**
 * Todos
 *
 * @ORM\Table(name="todos")
 * @ORM\Entity(repositoryClass="TodoRepository")
 */
 
class Todos
{
	/**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="todo_status", type="string", nullable=true)
     */
    protected $todoStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed_date", type="datetime", nullable=true)
     */
    protected $completedDate;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Users")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

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
     * Set description
     *
     * @param string $description
     * @return Todos
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
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
     * Set todoStatus
     *
     * @param string $todoStatus
     * @return Todos
     */
    public function setTodoStatus($todoStatus)
    {	
		$this->todoStatus = $todoStatus;
		
		return $this;
    }

    /**
     * Get todoStatus
     *
     * @return string 
     */
    public function getTodoStatus()
    {
        return $this->todoStatus;
    }

    /**
     * Set completedDate
     *
     * @param \DateTime $completedDate
     * @return Todos
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;
    
        return $this;
    }

    /**
     * Get completedDate
     *
     * @return \DateTime 
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }    
    
	/**
     * Set user
     *
     * @param \Users $user
     * @return Todos
     */
    public function setUser(\App\Entity\Users $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Users 
     */
    public function getUser()
    {
        return $this->user;
    }
}
