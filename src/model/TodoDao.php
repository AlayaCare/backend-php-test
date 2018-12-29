<?php

namespace model;

class TodoDao {
    
     /**
    * Get list of todo
    * @param $app
    * @param int $user_id
    * @param int $startOf
    * @param int $pagesize
    * @return list de todos
    */
    public function listTodo($app, int $user_id, int $startOf, int $pagesize){
        $sql = "select * from todos where user_id='$user_id' LIMIT $startOf, $pagesize";
        $todos = $app['db']->fetchAll($sql);
        return $todos;  
    }
    
    /**
    * Get a todo by id
    * @param int $id
    * @param $app
    * @return a representation of a Todo
    */
    public function getTodoById(int $id, $app){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);        
        return $todo;
    }
    
    /**
    * Delete a todo
    * @param int $id
    * @param $app      
    */    
    public function delete(int $id, $app){
        $sql = "DELETE FROM todos WHERE id = '$id'";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add("SUCCESS", "Todo Was Deleted");
    }
    
    /**
    * Add a todo
    * @param int $user_id
    * @param string $description
    * @param $app 
    */
    public function add(int $user_id, string $description, $app){
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add("SUCCESS", "Success, TODO was added!"); 
    }
    
    /**
    * Change status of the todo as completed
    * @param int $id todo
    * @param bool $completed
    * @param $app    
    */
    public function changeCompleted(int $id, bool $completed ,$app){
        ($completed==0) ? $completed = 1: $completed = 0;       
        $sql = "UPDATE todos SET completed = '$completed' WHERE id = '$id'";
        $app['db']->executeUpdate($sql);
        if($completed==0){
            $app['session']->getFlashBag()->add("INFO", "Todo is unsolved!"); 
        }else{
            $app['session']->getFlashBag()->add("SUCCESS", "Todo is solved!");
        }
    }   
    
    /**
    * Get a total of todo
    * @param $app 
    * @param int $id    
    * @return Total of todo
    */
    public function total($app, int $user_id){       
        $sql = "SELECT COUNT(*) as total FROM  todos WHERE user_id = '$user_id'";
        $total = $app['db']->fetchAssoc($sql);
        return $total['total']; 
    }
    
}
