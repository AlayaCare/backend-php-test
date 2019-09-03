<?php

namespace Models;

class Todo
{
	public function __construct()
    {}

    static public function create($user, $description, $app)
    {
        $user_id = $user['id'];
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $result = $app['db']->executeUpdate($sql);

        return $result;
    }

    static public function list($user, $sort_by, $sorting, $paginator, $app)
	{
		$sql = sprintf('SELECT * FROM todos WHERE user_id='. $user['id']. '
                    ORDER BY %s %s
                    LIMIT %d,%d',
            $sort_by, 
            strtoupper($sorting), 
            $paginator->getStartIndex(), 
            $paginator->getPerPage());

        $todos = $app['db']->fetchAll($sql);

        return $todos;
    }
        
	static public function findById($id, $app)
	{
		$sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $todo;
    }
    
    static public function countById($user, $app)
	{
		$sql = 'SELECT COUNT(*) AS `total` FROM todos WHERE user_id='.$user['id'] ;
        $count = $app['db']->fetchAssoc($sql);
        $count = (int) $count['total'];

        return $count;
    }
    
    static public function updateDone($id, $app)
	{
        $sql = "UPDATE todos SET status = 1 WHERE id = '$id'";
        $result = $app['db']->executeUpdate($sql);

        return $result;
    }
    
    static public function updateUndone($id, $app)
	{
        $sql = "UPDATE todos SET status = 0 WHERE id = '$id'";
        $result = $app['db']->executeUpdate($sql);

        return $result;
    }
    
    static public function delete($id, $app)
	{
        $sql = "DELETE FROM todos WHERE id = '$id'";
        $result = $app['db']->executeUpdate($sql);

        return $result;
	}
}