<?php

namespace Model;

class Todo {

    public static function find($id, $app) {
        $sql = "SELECT * FROM todos WHERE id = ?";
        $todo = $app['db']->fetchAssoc($sql, [(int) $id]);
        return $todo;
    }

    public static function findByUserId($userId, $app) {
        $sql = "SELECT * FROM todos WHERE user_id = ?";
        $todos = $app['db']->fetchAll($sql, [(int) $userId]);
        return $todos;
    }

    public static function add($request, $app) {
        $description = $request->get('description');

        if (!empty($description)) {
            $user = $app['session']->get('user');
            $user_id = $user['id'];
            $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
            $response = $app['db']->executeUpdate($sql, [(int) $user_id, $description]);
    
            if ($response) {
                $app['session']->getFlashBag()->add('todos.success', 'Nice! Item added!');
            } else {
                $app['session']->getFlashBag()->add('todos.danger', 'Ops! Item not added!');
            }
    
        } else {
            $app['session']->getFlashBag()->add('todos.danger', 'Ops! Description is required.');
        }
    }

    public static function delete($id, $app) {
        $sql = "DELETE FROM todos WHERE id = ?";
        $response = $app['db']->executeUpdate($sql, [(int) $id]);
        
        if ($response) {
            $app['session']->getFlashBag()->add('todos.success', 'Nice! Item deleted!');
        } else {
            $app['session']->getFlashBag()->add('todos.danger', 'Ops! Item not deleted!');
        }
    }

    public static function complete($id, $complete, $app) {
        $sql = "UPDATE todos SET complete = ? WHERE id = ?";
        $app['db']->executeUpdate($sql, [(int) $complete, (int) $id]);
        $app['session']->getFlashBag()->add('todos.success', 'Nice! Item completed!');
    }

}

?>