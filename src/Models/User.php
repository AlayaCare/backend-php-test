<?php

namespace Models;

class User
{
    public function login( $username, $password ) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '".$password."'";
        $user = $GLOBALS['app']['db']->fetchAssoc($sql);

        return $user;
    }
}