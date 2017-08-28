<?php

use App\Controllers\TodoController;
use App\Repositories\TodoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));
    $twig->addGlobal('app', $app);
    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

$app['todo.controller'] = $app->share(function() use ($app) {
    return new TodoController($app, new TodoRepository($app));
});
$loginBefore = function () use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
};
$app->get('/todo', 'todo.controller:index')->before($loginBefore);
$app->get('/todo/{id}', 'todo.controller:show')->value('id', null)->before($loginBefore);
$app->post('/todo/add', 'todo.controller:add')->before($loginBefore);
$app->match('/todo/delete/{id}', 'todo.controller:delete')->value('id', null)->before($loginBefore);
$app->match('/todo/completed/{id}', 'todo.controller:setCompleted')->value('id', null)->before($loginBefore);
$app->match('/todo/uncompleted/{id}', 'todo.controller:setUncompleted')->value('id', null)->before($loginBefore);