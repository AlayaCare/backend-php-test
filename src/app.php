<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new TranslationServiceProvider(), [
    'translator.domains' => [],
]);
$app->register(new FormServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new YamlConfigServiceProvider(__DIR__.'/../config/config.yml'));
$app->register(new DoctrineServiceProvider, [
    'db.options' => [
        'driver'    => 'pdo_mysql',
        'host'      => $app['config']['database']['host'],
        'dbname'    => $app['config']['database']['dbname'],
        'user'      => $app['config']['database']['user'],
        'password'  => $app['config']['database']['password'],
        'charset'   => 'utf8',
    ],
]);
$app->register(new DoctrineOrmServiceProvider());
$app['orm.proxies_dir'] = __DIR__.'/../cache/doctrine/proxies';
$app['orm.default_cache'] = [
    'driver' => 'filesystem',
    'path' => __DIR__.'/../cache/doctrine',
];
$app['orm.em.options'] = [
    'mappings' => [
        [
            'type' => 'annotation',
            'path' => __DIR__.'/../src/Entities',
            'namespace' => 'App\Entities',
        ],
    ],
];

return $app;
