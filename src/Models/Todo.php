<?php

namespace Models;

class Todo {

    public $table_name = 'todos';
    public $queryBuilder;
    public $app;

    public function __construct($app) {
        $this->app = $app;
        $this->queryBuilder = $app['db']->createQueryBuilder();
    }

    public function get($params) {
        $query = $this->queryBuilder->select('*')->from($this->table_name);

        foreach ($params as $param) {        
            $query->where($this->queryBuilder->expr()->{$param[1]}($param[0], $param[2]));
        }

        return ($this->queryBuilder->execute()->fetchAll());
    }

    public function add($params) {
        $update = $this->queryBuilder->insert($this->table_name)->values($params);
        $this->app['db']->executeUpdate($update);
    }

    public function delete($id) {
        $query = $this->queryBuilder->delete($this->table_name)->where(
            $this->queryBuilder->expr()->eq('id', $id)
        );
        //->andWhere('user_id', $user['id']);
        $this->app['db']->executeUpdate($query);

        return $query;
    }
}