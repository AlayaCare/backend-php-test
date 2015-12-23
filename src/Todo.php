<?php
class Todo{
  public $id = null;
  public $user_id = -1;
  public $description = "description";
  public $completed = false;
  private $app = null;
  function __construct($app, $id = null) {
    $this->app = $app;
    if ($id != null){
      $sql = "SELECT * FROM todos WHERE id = '$id'";
      $todo = $app['db']->fetchAssoc($sql);
      $this->id = intval($todo["id"]);
      $this->user_id = intval($todo["user_id"]);
      $this->description = $todo["description"];
      $this->completed = $todo["completed"];
    }
  }
  
  function find($id){
    $sql = "SELECT * FROM todos WHERE id = '$id'";
    return $this->app['db']->fetchAssoc($sql);
  }
  
  function save(){
    if($this->id == null){
      $sql = "INSERT INTO todos (user_id, description) VALUES ('$this->user_id', '$this->description')";
      $this->app['db']->executeUpdate($sql);
      return true;
    }else{
      $sql = "UPDATE todos SET completed= '$this->completed' WHERE id = '$this->id'";
      $this->app['db']->executeUpdate($sql);
    }
  }
  
  function delete(){
    if($this->id != null){
      $sql = "DELETE FROM todos WHERE id = '$this->id'";
      $this->app['db']->executeUpdate($sql);
      return true;
    }
    return false;
  }
  
  function countTodosByUserId($userId){
    $sql = "SELECT count(*) as pages FROM todos WHERE user_id = '$userId'";
    $all = $this->app['db']->fetchAll($sql);
    return $all[0]["pages"];
  }
  
  function findFromTo($userId, $from, $to){
    $sql = "SELECT * FROM todos WHERE user_id = '$userId' LIMIT $from, $to";
    return $this->app['db']->fetchAll($sql);
  }
  
}