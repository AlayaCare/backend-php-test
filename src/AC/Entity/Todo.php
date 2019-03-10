<?php


namespace AC\Entity;

/**
 * Class Todo
 * @package AC\Entity
 */
class Todo implements IEntity
{

    private $id;
    private $user_id;
    private $description;


    public function __construct()
    {
    }


    public function fill(array $data)
    {
        if(isset($data['id'])){
            $this->id=(int)$data['id'];
        }
        if(isset($data['user_id'])){
            $this->user_id=(int)$data['user_id'];
        }
        if(isset($data['description'])){
            $this->description=$data['description'];
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }



    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    public function toArray()
    {
        return ['description' => $this->description, 'id' => $this->id, 'user_id' => $this->user_id];
    }
}