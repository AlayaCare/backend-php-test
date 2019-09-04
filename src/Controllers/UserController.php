<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Models\User;

$user = $app['controllers_factory'];

$user->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = hash("sha256", $request->get('password'));
    
    if ($username) {
        $user = User::findByUsername($username, $app);

        if ($user && ($user['password'] === $password)) {
            $app['session']->set('user', $user);
            return $app->redirect('/todo/list');
        } else {
            $app['session']->getFlashBag()->add('error', 'Error! Invalid Username and/or Password!');
        }
    }

    return $app['twig']->render('login.html', array());
});

$user->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

return $user;