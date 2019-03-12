<?php


namespace AC\Entity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use AC\Core\StatusEnum;

/**
 * Class Todo
 * @package AC\Entity
 */
class Todo implements IEntity
{

    private $id;
    private $user_id;
    private $description;
    private $status;


    public function __construct()
    {
        $this->status=StatusEnum::TODO;
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
        if(isset($data['status'])){
            $this->status=$data['status'];
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

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function isNotComplete(){
        return $this->getStatus()==StatusEnum::TODO;
    }


    public function toArray()
    {
        return ['description' => $this->description, 'id' => $this->id, 'user_id' => $this->user_id, 'status' => $this->status];
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('description', new Assert\NotBlank());
    }
}