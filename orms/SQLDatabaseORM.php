<?php

namespace ORMs;



class SQLDatabaseORM
{
	/**
	 * @var
	 */
	private $sql;
	/**
	 * @var
	 */
	private $tableName;
	
	private function arrayToQuery($array, $quotes = false)
	{
		$query = '';
		
		foreach ($array as $element) {
			if($quotes)
				$query = $query . " '" . $element . "',";
			else
				$query = $query . " " . $element . ",";
		}
		
		$query = substr($query, 0, -1);
		
		return $query;
	}
	
	public function table($tableName)
	{
		$this->tableName = $tableName;
		
		return $this;
	}
	
	public function select ($fields = '*')
	{
		$this->sql = 'SELECT `' . $fields . '` FROM `' . $this->tableName . '`';
		
		return $this;
	}
	
	public function insert ($fields, $values)
	{
		
		$fieldsQuery = self::arrayToQuery($fields);
		$valuesQuery = self::arrayToQuery($values, true);
		
		$this->sql = 'INSERT INTO `' . $this->tableName . '` (' . $fieldsQuery . ') VALUES (' . $valuesQuery . ')';
		
		return $this;
	}
	
	public function where ($field, $operator, $value)
	{
		$this->sql = $this->sql . ' WHERE `' . $field . '` ' . $operator . ' "' . $value . '"';
		
		return $this;
	}
	
	public function update ($fields, $values)
	{
		$setQuery = '';
		
		$length = count($fields);
		
		for($i = 0; $i < $length; $i++)
			$setQuery = $fields[$i] . " = '" . $values[$i] . "'";
		
		$this->sql = 'UPDATE ' . $this->tableName . ' SET ' . $setQuery;
		
		return $this;
	}
	
	public function delete()
	{
		$this->sql = 'DELETE FROM `' . $this->tableName . '`';
		
		return $this;
	}
	
	public function getQuery()
	{
		return $this->sql;
	}
}