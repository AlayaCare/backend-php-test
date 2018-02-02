<?php

use Symfony\Component\HttpFoundation\Request;
use ControllerProviders\TodoControllerProvider;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

/**
 * Home Route
 */
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});

/**
 * Login Route
 */
$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT id, username FROM users WHERE username = ? AND password = ?";
        $user = $app['db']->fetchAssoc($sql, [
            $username,
            $password
        ]);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        } else {
            $app['session']->getFlashBag()->add('errors', 'User not found');
        }
    }

    return $app['twig']->render('login.html', array());
});

/**
 * Logout Route
 */
$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

/**
 * Controller: Todo
 */
$app->mount('/todo', new TodoControllerProvider());

/**
 * Error Handle
 */
$app->error(function (\Exception $e, $code) use ($app) {
    switch ($code) {
        case 404:
            $app['session']->getFlashBag()->add('errors', 'Sorry, Page not found');
            return $app->redirect('/todo');
            break;
        case 500:
            $app['session']->getFlashBag()->add('errors', 'Ops... Something wrong happened');
            return $app->redirect('/todo');
            break;
        default:
            $app['session']->getFlashBag()->add('errors', 'Ops... Something wrong happened');
            return $app->redirect('/todo');
            break;
    }
});

