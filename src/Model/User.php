<?php

namespace Model;

class User {

    public static function find($id, $app) {
        $sql = "SELECT * FROM user WHERE id = ?";
        $todo = $app['db']->fetchAssoc($sql, [(int) $id]);
        return $todo;
    }

    public static function findByUsername($username, $app) {
        $sql = "SELECT * FROM user WHERE username = ?";
        $todos = $app['db']->fetchAll($sql, [(string) $username]);
        return $todos;
    }

    public static function auth($request, $app) {
        $username = $request->get('username');
        $password = $request->get('password');
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);
        return $user ? $user : false;
    }

}

?>