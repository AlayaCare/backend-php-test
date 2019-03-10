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
        $queryResult=$this->app["db"]->fetchAssoc("SELECT * FROM todos where id=? limit 1", [(int) $id]);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findByIdAndUserId($id,$user_id)
    {
        $queryResult = $this->app["db"]->fetchAssoc("SELECT * FROM todos where id=? and user_id=?limit 1", [(int)$id, (int)$user_id]);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findAll()
    {
        $queryResult=$this->app["db"]->fetchAll("SELECT * FROM todos");
        $todoList=[];
        foreach ($queryResult as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }
    public function findAllByUser($user_id)
    {
        $queryResult=$this->app["db"]->fetchAll("SELECT * FROM todos WHERE user_id=?",[$user_id]);
        $todoList=[];
        foreach ($queryResult as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }

    public function insert(IEntity $entity)
    {
        return $this->app["db"]->insert("todos", ["description" => $entity->getDescription(),'user_id'=>$entity->getUserId()]);
    }

    public function update(IEntity $entity)
    {
        return $this->app["db"]->update("todos", ["description" => $entity->getDescription()], ["id" => $entity->getId()]);
    }

    public function remove($id)
    {
        return $this->app["db"]->delete("todos", ["id" => $id]);
    }

    public function toObject(array $data)
    {
        $todo= new Todo();
        $todo->fill($data);
        return $todo;
    }
}