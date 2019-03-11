<?php

namespace AC\Repository;

use AC\Entity\IEntity;
use AC\Entity\Todo;
use Silex\Application;

/***
 * Class TodoRepository
 * @package AC\Repository
 */
class TodoRepository extends Repository implements IRepository
{
    public function __construct(Application $app){
        parent::__construct($app);
    }

    public function findById($id)
    {
        $queryResult=$this->fetchAssoc("SELECT * FROM todos where id=? limit 1", [(int) $id]);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findByIdAndUserId($id,$user_id)
    {
        $queryResult = $this->fetchAssoc("SELECT * FROM todos where id=? and user_id=?limit 1", [(int)$id, (int)$user_id]);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findAll()
    {
        $queryResult=$this->fetchAll("SELECT * FROM todos");
        $todoList=[];
        foreach ($queryResult as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }
    public function findAllByUser($user_id)
    {
        $queryResult=$this->fetchAll("SELECT * FROM todos WHERE user_id=?",[$user_id]);
        $todoList=[];
        foreach ($queryResult as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }

    public function countByUser($user_id){
        $sql = 'SELECT COUNT(*) AS `total` FROM todos WHERE user_id=?';
        $count = $this->fetchAssoc($sql, [$user_id]);
        return (int)$count['total'];
    }

    public function findAllPaginator($paginator,$user_id,$sort_by,$sorting){
        $sql = sprintf('SELECT
        *
    FROM
        todos
    WHERE
        user_id='.$user_id.'
        ORDER BY %s %s
    LIMIT %d,%d',
            $sort_by,strtoupper($sorting),$paginator->getStartIndex(), $paginator->getPerPage());
        $todos = $this->fetchAll($sql);
        $todoList=[];
        foreach ($todos as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }

    public function insert(IEntity $entity)
    {
        return $this->db()->insert("todos", ["description" => $entity->getDescription(),'user_id'=>$entity->getUserId()]);
    }

    public function update(IEntity $entity)
    {
        return $this->db()->update("todos", ["description" => $entity->getDescription(),'status'=>$entity->getStatus()], ["id" => $entity->getId()]);
    }

    public function remove($id)
    {
        return $this->db()->delete("todos", ["id" => $id]);
    }

    public function toObject(array $data)
    {
        $todo= new Todo();
        $todo->fill($data);
        return $todo;
    }
}