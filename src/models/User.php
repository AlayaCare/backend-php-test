<?php
namespace models;
class User extends Model{
	public static function login($username, $password){
		$sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
		return self::$db->fetchAssoc($sql);
	}
}
