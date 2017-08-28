<?php

// Timezone.
date_default_timezone_set('America/Toronto');

// Cache
$app['cache.path'] = __DIR__ . '/../cache';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';

// Doctrine DB
$app['db.options'] = array(
    'driver' => 'pdo_mysql',
    'charset' => 'utf8',
    'host' => 'localhost',
    'dbname' => 'ac_todos',
    'user' => 'homestead',
    'password' => 'secret',
);

// Doctrine ORM
$app['orm.proxies_dir'] = $app['cache.path'].'/doctrine/Proxy';
$app['orm.default_cache'] = array(
    'driver' => 'filesystem',
    'path' => $app['cache.path'].'/doctrine/cache'
);

$app['orm.em.options'] = array(
    'mappings' => array(
        array(
            'type' => 'annotation',
            'path' => __DIR__.'/../src/App/Entity',
            'namespace' => 'App\Entity',
            'use_simple_annotation_reader' => false,
        ),
    ),
);
