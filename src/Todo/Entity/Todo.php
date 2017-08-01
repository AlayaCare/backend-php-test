<?php
namespace Todo\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Todo
{
    /**
     * @Assert\NotBlank()
     */
    public $id;
    public $user_id;
    public $description;
}