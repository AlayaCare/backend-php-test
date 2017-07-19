<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class model {
	
	private $last_page;
	private $user_id;
	
	public function __construct($userid) {
        $this->user_id = $userid;
    }
	
	public function Complete_Todo($id, $app) {
	 $sql = "UPDATE todos SET completed = 1 WHERE id = '$id' AND user_id = '$this->user_id'";
	 $app['db']->executeUpdate($sql);
	}
	
	public function Uncomplete_Todo($id, $app) {
	 $sql = "UPDATE todos SET completed = 0 WHERE id = '$id' AND user_id = '$this->user_id'";
	 $app['db']->executeUpdate($sql);
	}
	
	public function Add_Todo($description, $app) {
	 $sql = "INSERT INTO todos (user_id, description) VALUES ('$this->user_id', '$description')";
     $app['db']->executeUpdate($sql);
	}
	
	public function Delete_Todo($id, $app) {
	 $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$this->user_id'";
	 $app['db']->executeUpdate($sql);
	}
	
	public function Get_Todo($id, $app) {
		$sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$this->user_id'";
		return $app['db']->fetchAssoc($sql);
	}
	
	public function Get_Todos($per_page, $page, $app) {
		$sql = "SELECT COUNT(*) FROM todos WHERE user_id = '$this->user_id'";
		$todos = $app['db']->fetchAssoc($sql);
		$total_items = $todos['COUNT(*)'];
		$this->last_page = round($total_items / $per_page);
		
        $sql = "SELECT * FROM todos WHERE user_id = '$this->user_id' LIMIT " . PER_PAGE . " OFFSET " . (($page-1)*PER_PAGE);
        return $app['db']->fetchAll($sql);
	}
	
	public function Validate_user($username, $password, $app) {
		$sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        return $app['db']->fetchAssoc($sql);
	}
	
	public function Get_LastPage() {
		return $this->last_page;
	}
	
}