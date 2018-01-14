<?php
/**
 * Created by PhpStorm.
 * User: Karim Wallani
 * Date: 12-Jan-2018
 * Time: 12:58 PM
 */

class Todo
{
    private $user_id;
    private $description;

    function __construct($user_id, $description)
    {
        $this->user_id = (int) $user_id;
        $this->description = (string) $description;
    }

    function save()
    {
        $sql = "INSERT INTO todos (user_id, description) VALUES ($this->user_id, ?)";
        $result = $GLOBALS['app']['db']->executeUpdate($sql, array((string) $this->description));
        return $result;
    }

    static function getAll($user_id)
    {
        $sql = "SELECT * FROM todos WHERE user_id = ?";
        $result = $GLOBALS['app']['db']->fetchAll($sql, array((int) $user_id));
        return $result;
    }

    static function getOne($id, $user_id) {
        $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
        $result = $GLOBALS['app']['db']->fetchAssoc($sql, array((int) $id, (int) $user_id));
        return $result;
    }

    static function update($id, $user_id, $new_status)
    {
        $sql = "UPDATE todos SET status = ? WHERE id = ? AND user_id = ?";
        $result = $GLOBALS['app']['db']->executeUpdate($sql, array((bool) $new_status, (int) $id, (int) $user_id));
        return $result;
    }

    static function delete($id, $user_id)
    {
        $sql = "DELETE FROM todos WHERE id = ? AND user_id = ?";
        $result = $GLOBALS['app']['db']->executeUpdate($sql, array((int) $id, (int) $user_id));
        return $result;
    }
}