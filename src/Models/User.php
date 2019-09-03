<?php

namespace Models;

class User
{
	public function __construct()
    {}
        
	static public function findByUsername($username, $app)
	{
		$sql = "SELECT * FROM users WHERE username = '$username'";
        $user = $app['db']->fetchAssoc($sql);

        return $user;
    }

    static public function create()
	{
       // TODO
    }

    static public function find()
	{
		// TODO
    }
    
    static public function list()
	{
		// TODO
    }
    
    static public function update()
	{
        // TODO
    }
    
    static public function delete()
	{
        // TODO
	}
}