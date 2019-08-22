<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new YamlConfigServiceProvider(__DIR__.'/../config/config.yml'));
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

$app->register(new DoctrineOrmServiceProvider(), [
    'orm.proxies_dir'             => __DIR__ . '/App/Entity/Proxy',
    'orm.auto_generate_proxies'   => true,
    'orm.em.options'              => [
        'mappings' => [
            [
                'type'                         => 'annotation',
                'namespace'                    => 'App\\Entity\\',
                'path'                         => __DIR__ . '/App/Entity',
                'use_simple_annotation_reader' => false,
            ],
        ],
    ]
]);

return $app;
