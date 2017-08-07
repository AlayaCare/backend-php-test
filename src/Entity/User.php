<?php
namespace Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @Entity 
 * @Table(name="users")
 * documentation https://symfony.com/doc/current/doctrine.html
 */
class User
{
    /**
     * @OneToMany(targetEntity="Todo", mappedBy="user")
     */
	private $todos;

    /**
     * @var integer
     * @GeneratedValue(strategy="AUTO")
     * @Column(name="id", type="integer")
     * @Id
     */

    private $id;

     /**
     * @var string
     * 
     * @Column(name="username", type="string", length=255)
     * @Assert\Regex(
     *     pattern     = "/^[a-z\-0-9]+$/i",
     *     htmlPattern = "^[a-zA-Z\-0-9]+$"
     * )
     */

    private $username;

    /**
     * @var string
     * 
     * @Column(name="password", type="string", length=255)
     */

    private $password;

    public function getId(){
    	return $this->id;
    }

    public function getUsername(){
    	return $this->username;
    }
    
    public function getTodos(){
    	return $this->todos;
    }

}