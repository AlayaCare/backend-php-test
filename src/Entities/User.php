<?php 

/** 
 * @Entity 
 * @Table(name="users")
 * */
class User{
	
	/** 
	 * @Id @Column(type="integer") 
	 * @GeneratedValue
	 */
	public $id;
	/** @Column(length=255) */
	public $username;
	/** @Column(length=255) */
	public $password;
}
?>