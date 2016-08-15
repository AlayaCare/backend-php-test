<?php

use Doctrine\DBAL\Connection;

/****************************************************************************/
/*   			  					ORM 									*/
/* 	main class wrapping requests for users, descriptions from controller 	*/
/*  into "raw" SQL commands (as an abstraction).							*/
/****************************************************************************/

class UserDao
{
	private $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	protected function getDb()
	{
		return $this->db;
	}

	public function findUser($username, $password)
	{
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        try {
        	$user = $this->getDb()->fetchAssoc($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
        return $user;
	}

	public function findDescriptionById($id)
	{
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        try {
        	$todo = $this->getDb()->fetchAssoc($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
        return $todo;
	}

	public function findDescriptionByUser($user)
	{
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        try {
        	$todos = $this->getDb()->fetchAll($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
        return $todos;
	}

	public function getDescription($id)
	{
	    $sql = "SELECT * FROM todos WHERE id = '$id'";
        try {
        	$todo = $this->getDb()->fetchAssoc($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
	    return $todo["description"];
	}

	public function deleteDescription($id)
	{
	    $sql = "DELETE FROM todos WHERE id = '$id'";
        try {
        	$this->getDb()->executeUpdate($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
	}

	public function markDescriptionAsCompleted($id)
	{
	    $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
        try {
        	$this->getDb()->executeUpdate($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
	}

	public function addDescription($user_id, $description, $completed)
	{
        // #TODO: avoid entering same description twice (or more)
	    $sql = "INSERT INTO todos (user_id, description, completed) VALUES ('$user_id', '$description', '$completed')";
        try {
        	$this->getDb()->executeUpdate($sql);
        } catch (Doctrine\DBAL\DBALException $e) {
        	$error_log( $sql->errorInfo() );
        	$error_log( $sql->errorCode() );
        }
	}

}