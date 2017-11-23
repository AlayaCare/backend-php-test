<?php

namespace Module\Generalmodel;

class Usermodel 
{
	protected static $table = "users";
	public $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	public function select_user($username, $password)
	{
		$sql = "SELECT * FROM ". static::$table ." WHERE username = '$username' and password = '$password'";
        $user = $this->app['db']->fetchAssoc($sql);
		return $user;
	}
}