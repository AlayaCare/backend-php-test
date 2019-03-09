<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Models\Todo;
use Models\User;

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
        $userModel = new User($app);
        $user = $userModel->get($username, $password);

        if ($user){
            $app['session']->set('user', $user[0]);
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

    $todoModel = new Todo($app);

    if ($id){
        $todo = $todoModel->get([
            ['id', 'eq', $id]
        ]);

        return $app['twig']->render('todo.html', [
            'todo' => $todo[0],
        ]);
    } else {
        $todos = $todoModel->get([
            ['user_id', 'eq', $user['id']]
        ]);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (is_null($app['session']->get('user'))) {
        return $app->redirect('/login');
    }

    $todoModel = new Todo($app);
    
    $todo = $todoModel->get([
        ['id', 'eq', $id]
    ]);

    if (empty($todo)) {
        return json_encode([
            'success' => 0,
            'message' => 'Invalid TODO id.'
        ]);
    }

    return json_encode(array_pop($todo));
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = trim($request->get('description'));

    if (empty($description)) {
        $app['session']->getFlashBag()->set('danger', 'Error: A TODO must contain a description!');
    } else {
        $todoModel = new Todo($app);
        $update = $todoModel->add([
            'user_id' => $user_id,
            'description' => '"' . $description . '"'
        ]);
        $app['session']->getFlashBag()->set('success', 'TODO added successfully!');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $todoModel = new Todo($app);
    $update = $todoModel->delete($id);

    return $app->redirect('/todo');
});

$app->match('/todo/update/{id}', function ($id) use ($app) {
    $todoModel = new Todo($app);
    $update = $todoModel->complete($id);

    return $app->redirect('/todo');
});