<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User
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
     * @ORM\Column(name="username", type="string",length=255, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string",length=255, nullable=false)
     */
    private $password;
    
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
     * @return String
     */
    public function getUserName()
    {
        return $this->username;
    }
    /**
     * @param int $username
     *
     * @return user
     */
    public function setUserName($username)
    {
        $this->username = $username;
        return $this;
    }

    

     /**
     * @return int
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * @param int $description
     *
     * @return user
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
}