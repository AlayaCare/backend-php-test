<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

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


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = ?";
        $todo = $app['db']->fetchAssoc($sql, [$id]);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = ?";
        $todos = $app['db']->fetchAll($sql, [
            $user['id']
        ]);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    if ($description !== '') {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $app['db']->executeUpdate($sql, [
            $user_id, $description
        ]);
    } else {
        $app['session']->getFlashBag()->add('error', 'A description cannot be empty.');
    }

    return $app->redirect('/todo');
});

$app->post('/todo/done/{id}', function ($id) use ($app) {
    $sql = "UPDATE todos SET status = ? WHERE id = ?";
    $app['db']->executeUpdate($sql, [
        'done', $id
    ]);

    return $app->redirect('/todo#' . $id);
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = ?";
    $app['db']->executeUpdate($sql, [$id]);

    return $app->redirect('/todo');
});
