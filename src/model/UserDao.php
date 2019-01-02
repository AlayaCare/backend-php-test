<?php

namespace model;

class UserDao {
    public function login(string $username, string $password, $app){      
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);
        return $user;
    }
}