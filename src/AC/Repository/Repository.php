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
}