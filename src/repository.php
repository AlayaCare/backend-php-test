<?php

class DataManager{

public function insertItem($app, $user_id, $description)
{   
    $sql = "INSERT INTO todos (user_id, description) VALUES ('{$user_id}', '{$description}')";
    $app['db']->executeUpdate($sql);

}
public function deleteItem($app, $user_id, $item_id)
{   
    $sql = "DELETE FROM todos WHERE user_id = '{$user_id}' AND id = '{$item_id}'";
    $app['db']->executeUpdate($sql);

}
public function updateStatusItem($app, $user_id, $item_id, $status)
{   
    $sql = "UPDATE todos SET status='{$status}' WHERE user_id = '{$user_id}' AND id = '{$item_id}'";
    $app['db']->executeUpdate($sql);

}
public function getUser($app, $username, $password)
{
    $sql = "SELECT * FROM users WHERE username = '{$username}' and password = '{$password}'";
    $user = $app['db']->fetchAssoc($sql);

    return $user;
}
public function getItem($app, $user_id, $item_id)
{   
    $sql = "SELECT * FROM todos WHERE user_id = '{$user_id}' AND id = '{$item_id}'";
    $result = $app['db']->fetchAssoc($sql);
    
    return $result;
}
public function getAllItems($app, $user_id, $currentPage = 1)
{
    //$sql = "SELECT * FROM todos WHERE user_id = '{$user_id}'";

    $paginator = $this->paginate($app, $user_id, $currentPage);

    return $paginator;
}

public function paginate($app, $user_id, $page = 1, $limit = 7)
{
    $offset = ($page - 1)*$limit;
    $sql = "SELECT *,  CEILING((SELECT count(*) from todos WHERE user_id = '{$user_id}') / {$limit}) as pages, {$page} as actualpage from todos WHERE user_id = '{$user_id}' LIMIT {$limit} OFFSET {$offset}";
   // $sql = "SELECT * FROM todos WHERE user_id = '{$user_id}'";
   // $sql = $sql.' LIMIT '.$limit.' OFFSET '.$offset;
    $paginator = $app['db']->fetchAll($sql);

    return $paginator;
}
}
