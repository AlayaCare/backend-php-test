<?php
/**
 * Created by PhpStorm.
 * User: Karim Wallani
 * Date: 12-Jan-2018
 * Time: 2:50 PM
 */

class User
{
    static function getOne($username, $password)
    {
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $result = $GLOBALS['app']['db']->fetchAssoc($sql, array((string) $username, (string) $password));
        return $result;
    }
}