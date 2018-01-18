<?php
// configure your app for the production environment

// Localisation
$app['locale'] = 'en';
/*
$app['translator.messages'] = array(
	'en' => SOME_PATH . '/en.yml',
);

// Database
$app['db.options'] = array(
	'driver' => 'pdo_mysql',
	'host' => 'localhost',
	'dbname' => 'my_db',
	'user' => 'my_user',
	'password' => 'my_secret',
	'charset' => 'utf8'
);*/

// Security
$app['security.firewalls'] = array(
	'login' => array(
		'pattern' => '^/login$',
		'anonymous' => true,
	) ,
	'home' => array(
		'pattern' => '^/$',
		'anonymous' => true,
	) ,
	'site' => array(
		'pattern' => '^/.*$',
		'form' => array(
			'login_path' => '/login',
			'check_path' => '/check_login'
		) ,
		'anonymous' => false,
		'logout' => array(
			'logout_path' => '/logout'
		) ,
		'users' => $app['user.provider'],
	) ,
);

// Views
$app['twig.path'] = array(
	__DIR__ . '/../templates'
);
$app['assets.version'] = 'v2';
$app['assets.version_format'] = '%s?version=%s';
$app['assets.named_packages'] = array(
	'css' => array(
		'version' => '2.0',
		'base_path' => '/css'
	) ,
	'js' => array(
		'version' => '1.1',
		'base_path' => '/js'
	) ,
	// 'images' => array('base_urls' => array('https://img.example.com')),
);

// Caching
// $app['cache.path'] = PATH_CACHE;
// $app['http_cache.cache_dir'] = $app['cache.path'] . '/http';
// $app['twig.options.cache'] = $app['cache.path'] . '/twig';

