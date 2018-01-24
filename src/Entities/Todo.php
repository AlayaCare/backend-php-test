<?php
namespace Entities;

/**
 * Class Todo
 * @Entity
 * @Table(name="todos")
 */
class Todo
{
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
  private $description;

  /**
   * @Column(type="boolean")
   * @var boolean
   */
  private $completed = 0;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="todos")
   * @JoinColumn(name="user_id", referencedColumnName="id")
   */
  private $user;

  /**
   * Get Todo Id
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get the Todo Description
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Get the User object
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Get the Todo Complete Status
   * @return boolean
   */
  public function getCompleted()
  {
    return $this->completed;
  }

  /**
   * Set the Todo Description
   * @param string $description
   * @return Todo for method chain
   */
  public function setDescription(string $description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * Set the User of the Todo
   * @param User $user A User object
   */
  public function setUser(User $user)
  {
    $this->user = $user;
    return $this;
  }

  /**
   * Set the Todo as Completed
   * @return Todo for method chain
   */
  public function setAsCompleted()
  {
    $this->completed = true;
    return $this;
  }

}
