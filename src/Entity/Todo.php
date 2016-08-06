<?php 

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Todo{
	
	public $id;
	public $user_id;
	public $description;
	
	static public function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('description', new Assert\NotBlank());
	}
}
?>