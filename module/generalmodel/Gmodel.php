<?php

namespace Module\Generalmodel;

class Gmodel 
{
	protected static $table = "todos";
	public $app;
	private $user_id;
	
	public function __construct($app, $user_id)
	{
		$this->app = $app;
		$this->user_id = $user_id;
	}

	function get_todos_byid($id)
	{
	    $sql = "SELECT * FROM ". static::$table ." WHERE id = '$id'";
        return $this->app['db']->fetchAssoc($sql);
	}
	
	function get_todos_by_userid($id) 
	{
	    $sql = "SELECT * FROM ". static::$table ." WHERE id = '$id' and user_id = '".$this->user_id."'";
        return $todos = $this->app['db']->fetchAssoc($sql);
	}
	
	function get_todos_by_userid_withlimit($offset, $rowsperpage)
	{
       $sql = "SELECT * FROM ". static::$table ." WHERE user_id = '".$this->user_id."' LIMIT $offset, $rowsperpage";
       return $todos = $this->app['db']->fetchAll($sql);
	}
	
	function delete_todo_by_id($id)
	{
		$sql = "DELETE FROM ". static::$table ." WHERE id = '$id' and user_id = '".$this->user_id."'";
		$returnFlag = $this->app['db']->executeUpdate($sql);
		return $returnFlag;
	}
	
	public function insert_todo($description)
	{
		$sql = "INSERT INTO ". static::$table ." (user_id, description) VALUES ('".$this->user_id."', '$description')";
		$returnFlag = $this->app['db']->executeUpdate($sql);
		return $returnFlag;
	}
	
	public function update_todo_status($id,$status)
	{
		echo $sql = "UPDATE `". static::$table ."` SET `status` = '".$status."' WHERE `id` = ".$id." and user_id = '".$this->user_id."'";
		$returnFlag = $this->app['db']->executeUpdate($sql);
		return $returnFlag;
	}
}