<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config/dev.php';

use Doctrine\ORM\Configuration as Configuration;
use Doctrine\Common\Cache\ApcCache as Cache;
use Doctrine\ORM\EntityManager as EntityManager;
use Symfony\Component\Console\Helper\HelperSet as HelperSet;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper as ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper as EntityManagerHelper;

$newDefaultAnnotationDriver = array(
    __DIR__.'/src/App/Entity',
);

$config = new Configuration();
$config->setMetadataCacheImpl(new Cache());

$driver = $config->newDefaultAnnotationDriver($newDefaultAnnotationDriver, false);
$config->setMetadataDriverImpl($driver);

$config->setProxyDir($app['orm.proxies_dir']);
$config->setProxyNamespace('Proxies');

$em = EntityManager::create($app['db.options'], $config);

$helpers = new HelperSet(array(
    'db' => new ConnectionHelper($em->getConnection()),
    'em' => new EntityManagerHelper($em),
));
