<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Lokhman\Silex\Provider\ConfigServiceProvider;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new ConfigServiceProvider(),array('config.dir' => __DIR__ . '/../config/'));
$app->register(new DoctrineServiceProvider, array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => $app['config']['database']['host'],
        'dbname'    => $app['config']['database']['dbname'],
        'user'      => $app['config']['database']['user'],
        'password'  => $app['config']['database']['password'],
        'charset'   => 'utf8',
    ),
));

return $app;
