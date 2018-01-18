<?php

namespace Entity;


abstract class Entity
{

    private $id;

    protected $created;

    protected $updated;



    public function __construct()
    {
        $this->created = new \DateTime();
    }

    /**
     * Returns the Entity id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the Entity creation DateTime.
     *
     * @param DateTime
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * Gets the Entity creation DateTime.
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Gets the Entity updated at DateTime.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Gets the Entity created at formatted DateTime timestamp.
     *
     * @param string DateTime format
     * @return DateTime timestamp
     */
    public function getCreatedDateTime($format = 'Y-m-d H:i')
    {
        return $this->getCreated()->format($format);
    }

    /**
     * Gets the Entity updated at formatted DateTime timestamp.
     *
     * @param string DateTime format
     * @return DateTime timestamp
     */
    public function getUpdatedDateTime($format = 'Y-m-d H:i')
    {

        if($this->getUpdated()) {

            return $this->getUpdated()->format($format);
        }

        return $this->getCreatedDateTime();
    }

}
