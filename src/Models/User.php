<?php

namespace Models;

class User {

    public $table_name = 'users';
    public $queryBuilder;
    public $app;

    public function __construct($app) {
        $this->app = $app;
        $this->queryBuilder = $app['db']->createQueryBuilder();
    }

    public function get($username, $password) {
        $query = $this->queryBuilder->select('*')->from($this->table_name)
        ->where(
            $this->queryBuilder->expr()->eq('username', '"' . $username . '"')
        )
        ->andWhere(
            $this->queryBuilder->expr()->eq('password', '"'. md5($password) .'"')
        );

        return ($this->queryBuilder->execute()->fetchAll());
    }
}