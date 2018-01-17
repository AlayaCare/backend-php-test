<?php
/**
 * controllersBootstrapper sets shared vars and mounts the controllers
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

/**
 * Mount the root '/' controllers, contains all the routes and actions.
 */
$app->mount('/', new Controllers\RootController());

/**
 * Mount the Todo '/todo' controllers, contains all the routes and actions.
 */
$app->mount('/todo', new Controllers\TodoController());
