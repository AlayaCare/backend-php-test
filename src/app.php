<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Lokhman\Silex\Provider\ConfigServiceProvider;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new ConfigServiceProvider(),array('config.dir' => __DIR__ . '/../config/'));
$app->register(new DoctrineServiceProvider, array(
    'db.options' => $app['config']['database'],
));
$app->register(new AssetServiceProvider(), array(
    'assets.version' => $app['config']['assets']['version'],
    'assets.version_format' => $app['config']['assets']['version_format'],
    'assets.named_packages' => $app['config']['assets']['named_packages'],
));
return $app;
