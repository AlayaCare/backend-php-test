<?php

namespace AC\Repository;


use Silex\Application;

/**
 * Class Repository
 * @package AC\Repository
 */
abstract class Repository{
    protected $app;

    public function __construct(Application $app){
        $this->app = $app;
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