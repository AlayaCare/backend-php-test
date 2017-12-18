<?php

interface DatabaseInterface
{
	function select($fields);

	function insert($data);

	function update($data);

	function delete();

	function where($data);

	function paginate($offset, $max);

	function findById($id);

	function find();

	function findAll();
}

class DB implements DatabaseInterface
{
	private $table;
	private $app;
	private $pk = 'id';
	private $query = '';

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function table($table)
	{
		$this->table = $table;
		return $this;
	}

	public function select($fields = '*')
	{
		$this->query = "SELECT {$fields} FROM " . $this->table;
		return $this;
	}

	public function insert($data)
	{
		$fields = implode(', ', array_keys($data));
		$values = implode(', ', array_values($data));
		$this->query = "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
		return $this;
	}

	public function update($data)
	{
		if (strpos($this->query, 'UPDATE') === FALSE) {
			$this->query .= "UPDATE {$this->table} SET ";
		} else {
			$this->query .= ", ";
		}

		$this->query .= "{$data[0]} = {$data[1]}";
		return $this;
	}

	public function delete()
	{
		$this->query = "DELETE FROM " . $this->table;
		return $this;
	}

	public function where($data) 
	{
		if (strpos($this->query, 'WHERE') === FALSE) {
			$this->query .= " WHERE ";
		} else {
			$this->query .= " AND ";
		}

		$this->query .= "{$data[0]} {$data[1]} {$data[2]}";
		return $this;
	}

	public function orWhere($data)
	{
		$this->query .= " OR {$data[0]} {$data[1]} {$data[2]}";
		return $this;
	}

	public function paginate($offset, $max)
	{
		$this->query .= " LIMIT {$offset}, {$max} ";
		return $this;
	}

	public function findById($id)
	{
		$result = $this->select()->where([$this->pk, '=', $id])->find();

		if ($result) {
			return $result;
		} else throw new Exception("Database Error");
	}

	public function find()
	{
		$result = $this->app['db']->fetchAssoc($this->query);

		if ($result) {
			return $result;
		} else throw new Exception("Database Error");
	}

	public function findAll()
	{
		$result = $this->app['db']->fetchAll($this->query);

		if ($result) {
			return $result;
		} else throw new Exception("Database Error");
	}

	public function execute()
	{
		if (!$this->app['db']->executeUpdate($this->query)) {
			throw new Exception("Database Error");
		} 
	}
}