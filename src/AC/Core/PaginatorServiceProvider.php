<?php

namespace AC\Core;


use Silex\Application;
use Silex\ServiceProviderInterface;
use Kosinix\Paginator;

class PaginatorServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {
        $app['paginator.per_page'] = isset($app['paginator.per_page']) ? (int)$app['paginator.per_page'] : 10;
        $app['paginator'] = $app->protect(
            function ($total, $page, $per_page=null) use ($app) {
                if(null === $per_page){
                    $per_page = $app['paginator.per_page'];
                }
                return new Paginator($total, $page, $per_page);
            }
        );
    }

    public function boot(Application $app) {

    }
}