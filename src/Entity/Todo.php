<?php

namespace Entity;

/**
 * Class Todo
 *
 * @package Entity
 *
 * @author Jerome Catric
 */
class Todo
{
    /**
     * id
     *
     * @var int
     */
    private $id;

    /**
     * User Id
     *
     * @var int
     */
    private $userId;

    /**
     * Description
     *
     * @var string
     */
    private $description;

    /**
     * Indicate if it is completed or open
     *
     * @var boolean
     */
    private $completed;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->completed = false;
    }

    /**
     * Get todo id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set todo id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set user id
     *
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

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
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Indicate if it's completed or open
     *
     * @return boolean
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * Set the todo to completed (true) or open (false)
     *
     * @param boolean $completed
     *
     * @return $this
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

}