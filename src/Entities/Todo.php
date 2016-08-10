<?php 

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/** 
 * @Entity 
 * @Table(name="todos")
 * */
class Todo{
	
	/** 
	 * @Id @Column(type="integer") 
	 * @GeneratedValue
	 */
	public $id;
	/** @Column(type="integer") */
	public $user_id;
	/** @Column(length=255, nullable=TRUE) */
	public $description;
	/** @Column(type="boolean") */
	public $is_completed = 0;
	
	static public function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('description', new Assert\NotBlank());
	}
}
?>