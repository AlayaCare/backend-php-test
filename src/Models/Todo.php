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
        if($GLOBALS['app']['db']->executeUpdate($sql)) {
            $GLOBALS['app']['session']->getFlashBag()->add('success', 'Todo added.');
        } else {
            $GLOBALS['app']['session']->getFlashBag()->add('error', 'Fail to add.');
        }

    }

    public function delete($id) {
        //Added owner verification
        $sql = "DELETE FROM todos WHERE user_id = '{$GLOBALS['app']['session']->get('user')['id']}' AND id = '$id'";
        if($GLOBALS['app']['db']->executeUpdate($sql)) {
            $GLOBALS['app']['session']->getFlashBag()->add('success', 'Todo deleted.');
        } else {
            $GLOBALS['app']['session']->getFlashBag()->add('error', 'Fail to del.');
        }
    }

    public function complete($id) {
        $sql = "UPDATE todos SET completed = !completed WHERE id = '$id' AND user_id = '{$GLOBALS['app']['session']->get('user')['id']}'";
        $GLOBALS['app']['db']->executeUpdate($sql);
    }
}