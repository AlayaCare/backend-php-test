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

    if ($id) {
        $sql = "SELECT * FROM todos WHERE id = ?";
        $todo = $app['db']->fetchAssoc($sql, [(int) $id]);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $sql = "SELECT * FROM todos WHERE id = ?";
        $todo = $app['db']->fetchAssoc($sql, [(int) $id]);
        return $todo ? $app->json($todo) : $app->json(['error' => 'To-do not found.'], 404);
    } else {
        return $app->redirect('/todo');
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    if (!empty($description)) {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $response = $app['db']->executeUpdate($sql, [(int) $user_id, $description]);

        if ($response) {
            $app['session']->getFlashBag()->add('success', 'Nice! Item added!');
        } else {
            $app['session']->getFlashBag()->add('danger', 'Ops! Item not added!');
        }

    } else {
        $app['session']->getFlashBag()->add('danger', 'Ops! Description is required.');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = ?";
    $response = $app['db']->executeUpdate($sql, [(int) $id]);
    
    if ($response) {
        $app['session']->getFlashBag()->add('success', 'Nice! Item deleted!');
    } else {
        $app['session']->getFlashBag()->add('danger', 'Ops! Item not deleted!');
    }

    return $app->redirect('/todo');
});

$app->post('/todo/complete/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $complete = $request->request->get('complete');
        $sql = "UPDATE todos SET complete = ? WHERE id = ?";
        $app['db']->executeUpdate($sql, [(int) $complete, (int) $id]);
        $app['session']->getFlashBag()->add('success', 'Nice! Item completed!');
    }

    return $app->redirect('/todo');
})
->value('id', null);