<?php

namespace AC\Repository;

use AC\Entity\IEntity;
use AC\Entity\Todo;
use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

/***
 * Class TodoRepository
 * @package AC\Repository
 */
class TodoRepository extends Repository implements IRepository
{
    public function __construct(Application $app){
        parent::__construct($app);
        $this->table='todos';
    }

    public function findById($id)
    {
        $sql=$this->builder->select('t')
            ->from($this->table)
            ->where('t.id =:id')
            ->setParameter(':id',(int) $id);
        $queryResult=$this->fetchAssoc($sql);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findByIdAndUserId($id,$user_id)
    {
        $sql=$this->builder->select('*')
            ->from($this->table)
            ->where($this->builder->expr()->eq('id', (int)$id))
        ->andWhere($this->builder->expr()->eq('user_id', (int)$user_id));
        $queryResult = $this->fetchAssoc($sql);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findAll()
    {
        $sql=$this->builder->select('*')
            ->from($this->table);
        $queryResult=$this->fetchAll($sql);
        $todoList=[];
        foreach ($queryResult as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }
    public function findAllByUser($user_id)
    {
        $sql=$this->builder->select('*')
            ->from($this->table)
            ->where($this->builder->expr()->eq('user_id', $user_id));
        $queryResult=$this->fetchAll($sql);
        $todoList=[];
        foreach ($queryResult as $todoData){
            array_push($todoList,$this->toObject($todoData));
        }
        return $todoList;
    }

    public function countByUser($user_id){
        $sql=$this->builder->select('COUNT(*) AS total')
            ->from($this->table)
            ->where($this->builder->expr()->eq('user_id', $user_id));
        $count = $this->fetchAssoc($sql);
        return (int)$count['total'];
    }

    public function findAllPaginator($paginator,$user_id,$sort_by,$sorting){
        $sql=$this->builder->select('*')
            ->from($this->table)
            ->where($this->builder->expr()->eq('user_id', $user_id))
            ->addOrderBy($sort_by,$sorting)
        ->setMaxResults($paginator->getPerPage())
        ->setFirstResult($paginator->getStartIndex());
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
        $sql=$this->builder->update($this->table)
                 ->set('description', "'".$entity->getDescription()."'")
                 ->set('status', "'".$entity->getStatus()."'")
             ->where($this->builder->expr()->eq('id', $entity->getId()));
        return $this->db()->executeUpdate($sql);
    }

    public function remove($id)
    {
        $sql=$this->builder->delete($this->table)->where($this->builder->expr()->eq('id', $id));
        return $this->db()->executeUpdate($sql);
    }

    public function toObject(array $data)
    {
        $todo= new Todo();
        $todo->fill($data);
        return $todo;
    }
}