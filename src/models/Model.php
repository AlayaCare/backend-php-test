<?php
namespace models;
class Model{
	protected static $db;
	public static function setDBConnection($dbConnection){
		self::$db = $dbConnection;
	}
}
