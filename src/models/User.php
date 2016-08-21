<?php
namespace models;
class User extends Model{
	public static function login($username, $password){
		return self::$db->fetchAssoc('SELECT * FROM users WHERE username = ? and password = ?', array($username, $password), array(\PDO::PARAM_STR, \PDO::PARAM_STR));
	}
}
