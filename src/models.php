<?php

use Silex\Application;

class Models{
    
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }


    public function addTodo($user_id, $description){
        try{
            $data = array('user_id' => $user_id, 'description' => $description);
            $this->app['db']->insert('todos', $data);
            return 'Todo added successfully';
        }
        catch(Exception $e){
            return null;
        }
    }

    public function deleteTodo($id){
        try{
            $this->app['db']->delete('todos', array('id' =>$id));
            return 'Todo deleted successfully';
        }
        catch(Exception $e){
            return null;
        }
    }

    public function markTodo($id){
        try{
            $now = date_create('now')->format('Y-m-d H:i:s');
            $this->app['db']->update('todos', array('completed' => 1, 'date_completed' => $now), array('id' => $id));
            return 'Todo completed';
        }
        catch(Exception $e){
            return null;
        }
    }

    public function getTodo($id){
        try{
            $sql = "SELECT * FROM todos WHERE id = ?";
            $todo = $this->app['db']->fetchAssoc($sql, array((int) $id));
            return $todo;
        }
        catch(Exception $e){
            return null;
        }
    }

    public function toJson($id){
        try{
            $sql = "SELECT id, user_id, description FROM todos WHERE id = ?";
            $todo = $this->app['db']->fetchAssoc($sql, array((int) $id));
            return $todo;
        }
        catch(Exception $e){
            return null;
        }
    }

    public function checkUserPwd($username, $password){
        try{      
            $sql = "SELECT * FROM users WHERE username = ? and password = ?";
            $user = $this->app['db']->fetchAssoc($sql, array($username, $password));
            return $user;
        }
        catch(Exception $e){
            return $e;
        }
    }

    public function countTodos(){
        try{
            $user = $this->app['session']->get('user');
            $sql = "SELECT * FROM todos WHERE user_id = ? AND completed!=1"; 
            $total = count($this->app['db']->fetchAll($sql, array((int) $user['id']) ));
            return $total;
        }
        catch(Exception $e){
            return null;
        }
    }

    public function getTodos($row_start, $limit){
        try{
            $user = $this->app['session']->get('user');
            $sql = "SELECT * FROM todos WHERE user_id = ? AND completed!=1 LIMIT {$row_start}, $limit"; 
            $todos = $this->app['db']->fetchAll($sql, array($user['id']));
            return $todos;
        }
        catch(Exception $e){
            return null;
        }
    }


}
