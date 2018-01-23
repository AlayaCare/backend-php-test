<?php
namespace Entities;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class User
 * @Entity
 * @Table(name="users")
 */
class User
{
  /**
   * Set the todos var as an ArrayCollection of Todos
   */
  public function __construct()
  {
    $this->todos = new ArrayCollection();
  }

  /**
   * @Column(type="integer")
   * @Id
   * @GeneratedValue
   * @var int
   */
  private $id;

  /**
   * @Column(type="string")
   * @var string
   */
  private $username;

  /**
   * @Column(type="string")
   * @var string
   */
  private $password;

  /**
   * @OneToMany(targetEntity="Todo", mappedBy="user")
   */
  private $todos;

  /**
   * Get the User id
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get the Username
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Get the User's Todos
   * @return Todo
   */
  public function getTodos()
  {
    return $this->todos;
  }
}
