<?php

namespace Repository;


use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class RepositoryServiceProvider
 *
 * Service for managing the Repositories
 *
 * @package Repository
 *
 * @author Jerome Catric
 */
class RepositoryServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $app['repository.user'] = $app->share(function ($app) {
            return new UserRepository($app['db']);
        });

        $app['repository.todo'] = $app->share(function ($app) {
            return new TodoRepository($app['db']);
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {

    }

}