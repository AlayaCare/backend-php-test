<?php

namespace Dal;


class UserRepo
{
    private $database;

    function __construct($database) {
        if(!$database){
            throw new \Exception("Argument database cannot be null");
        }
        $this->database = $database;
    }

    public function login($username, $password){
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        return $this->database ->fetchAssoc($sql);
    }
}