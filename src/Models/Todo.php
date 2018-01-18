<?php

namespace Models;


class Todo
{
    public function getTodo( $id = NULL ) {
        if(is_numeric($id)) {
            //Added owner verification
            $sql = "SELECT * FROM todos WHERE user_id = '{$GLOBALS['app']['session']->get('user')['id']}' AND id = '{$id}'";
            return $GLOBALS['app']['db']->fetchAssoc($sql);
        } else {
            $sql = "SELECT * FROM todos WHERE user_id = '{$GLOBALS['app']['session']->get('user')['id']}'";
            return $GLOBALS['app']['db']->fetchAll($sql);
        }
    }

    public function add($description) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('{$GLOBALS['app']['session']->get('user')['id']}', '$description')";
        $GLOBALS['app']['db']->executeUpdate($sql);
    }

    public function delete($id) {
        //Added owner verification
        $sql = "DELETE FROM todos WHERE user_id = '{$GLOBALS['app']['session']->get('user')['id']}' AND id = '$id'";
        $GLOBALS['app']['db']->executeUpdate($sql);
    }

    public function complete($id) {
        $sql = "UPDATE todos SET completed = !completed WHERE id = '$id' AND user_id = '{$GLOBALS['app']['session']->get('user')['id']}'";
        $GLOBALS['app']['db']->executeUpdate($sql);
    }
}