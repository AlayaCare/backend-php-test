<?php

namespace AC\Repository;


use Silex\Application;

/**
 * Class Repository
 * @package AC\Repository
 */
abstract class Repository{
    protected $app;
    protected $builder;
    protected $table;

    public function __construct(Application $app){
        $this->app = $app;
        $this->builder = $app['db']->createQueryBuilder();
    }

    protected function db()
    {
        return $this->app['db'];
    }

    protected function fetchAll($sql, $args = [])
    {
        return $this->db()->fetchAll($sql, $args);
    }

    protected function fetchAssoc($sql, $args = [])
    {
        return $this->db()->fetchAssoc($sql, $args);
    }
}