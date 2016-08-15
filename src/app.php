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
use FranMoreno\Silex\Provider\PagerfantaServiceProvider;

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

/* user (and description) abstraction layer to hide "native SQL" requests */
$app['dao.user'] = $app->share(function ($app) {
    return new UserDao($app['db']);
});

/* flashbag abstraction layer to highlight description insertion/deletion */
$app['flashbag.manager'] = $app->share(function ($app) {
    return new FlashBagManager($app);
});

$app->register(new PagerfantaServiceProvider());
$app['pagerfanta.view.options'] = array(
    'next_message'  => ' next &raquo;',
    'previous_message'  => '&laquo; previous ',
);


return $app;
