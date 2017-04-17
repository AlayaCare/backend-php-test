<?php

class DatabaseAccessModel
{

	public function __construct(\Silex\Application $app) {
        $this->app = $app;
    }

	function getTodoCountByUser($user_id){
		$sql = "SELECT count(id) as count FROM todos WHERE user_id = :user_id GROUP BY user_id";
		$stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("user_id", intval($user_id));
		$stmt->execute();
    	$result = $stmt->fetch();

    	return $result['count'];
	}

	function getTodosByUser($user_id, $start, $limit){
    	$sql = "SELECT * FROM todos WHERE user_id = :user_id ORDER BY id ASC LIMIT :start,:limit";
		$stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("user_id", intval($user_id));
		$stmt->bindValue("start", intval($start), \PDO::PARAM_INT);
		$stmt->bindValue("limit", intval($limit), \PDO::PARAM_INT);
		$stmt->execute();
		$todos = $stmt->fetchAll();

    	return $todos;
	}

	function getTodo($id){
		$sql = "SELECT * FROM todos WHERE id = :id";
        $stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("id", intval($id));
		$stmt->execute();
    	$todo = $stmt->fetch();

    	return $todo;
	}

	function getUserLogin($username, $password){
		$salt = "vrcYS95jp1";
		$salted_password = md5($password.$salt);
		$sql = "SELECT * FROM users WHERE username = :username and password = :password";
        $stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("username", $username);
		$stmt->bindValue("password", $salted_password);
		$stmt->execute();
    	$user = $stmt->fetch();

    	return $user;
	}

	function insertTodo($user_id, $description){
		$sql = "INSERT INTO todos (user_id, description) VALUES (:user_id, :description)";
		$stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("user_id", $user_id);
		$stmt->bindValue("description", $description);
		$stmt->execute();

    	return 1;
	}

	function deleteTodo($id){
    	$sql = "DELETE FROM todos WHERE id = :id";
    	$stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("id", $id);
		$stmt->execute();

    	return 1;
	}

	function completeTodo($id){
    	$sql = "UPDATE todos SET status = 'COMPLETED' WHERE id = :id";
    	$stmt = $this->app['db']->prepare($sql);
		$stmt->bindValue("id", $id);
		$stmt->execute();

    	return 1;
	}
 
}