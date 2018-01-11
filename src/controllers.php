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


// Task 3
/*
Test cases:
/todos --> displays all todos
/todos/ --> displays all todos
/todos/id (id exists) --> displays task with given id
/todos/id (id NOT exists) --> displays 404
/todos/id/json (id exists) --> displays json of task with given id
/todos/id/json (id NOT exists) --> displays 404
/todos/id/gibberish (whether id exists or NOT exists) --> displays all todos
/todos/gibberish --> displays 404
*/
$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id && empty($format)) {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($todo) {
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        } else {
            $app->abort(404, "Post $id does not exist.");
        }

    } elseif ($id && $format=="json") {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($todo) {
            return $app->json($todo);
        } else {
            $app->abort(404, "Post: $id does not exist.");
        }

    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
    ->value('id', null)->value('format', null);


$app->get('/todo/', function () use ($app) {
    return $app->redirect('/todo');
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // Task 1
    if (trim($description) == "") {
        $app['session']->getFlashBag()->add('error', 'Todo not added. Description cannot be empty.');
        return $app->redirect('/todo');
    }

    // Extra task
    //$sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    //$app['db']->executeUpdate($sql);
    $app['db']->insert('todos', array(
        'user_id' => $user_id,
        'description' => $description
    ));

    // Task 4
    $app['session']->getFlashBag()->add('success', 'Todo added!');
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    // Extra task
    /*$sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);*/
    $app['db']->delete('todos', array(
        'id' => $id
    ));

    // Task 4
    $app['session']->getFlashBag()->add('success', 'Todo deleted!');
    return $app->redirect('/todo');
});