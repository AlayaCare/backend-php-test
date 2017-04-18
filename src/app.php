<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use DerAlex\Silex\YamlConfigServiceProvider;

$paths = array("/src/Entity");
$isDevMode = false;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new YamlConfigServiceProvider(__DIR__.'/../config/config.yml'));

$dbParams = array(
    'driver'    => 'pdo_mysql',
    'host'      => $app['config']['database']['host'],
    'dbname'    => $app['config']['database']['dbname'],
    'user'      => $app['config']['database']['user'],
    'password'  => $app['config']['database']['password'],
    'charset'   => 'utf8',
);

$app->register(new DoctrineServiceProvider, array(
    'db.options' => $dbParams
));

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);
$app->em = $entityManager;

return $app;
