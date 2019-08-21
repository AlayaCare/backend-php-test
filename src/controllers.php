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
    $user = Model\User::auth($request, $app);
    if ($user){
        $app['session']->set('user', $user);
        return $app->redirect('/todo');
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
        $todo = Model\Todo::find($id, $app);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        list($todos, $pagination) = Model\Todo::listByPage($user['id'], $app);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'total' => $pagination['total'],
            'currentPage' => $pagination['currentPage'],
            'pages' => $pagination['pages'],
            'start' => $pagination['start'],
            'end' => $pagination['end']
        ]);
    }

})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $todo = Model\Todo::find($id, $app);
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

    Model\Todo::add($request, $app);
    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    Model\Todo::delete($id, $app);
    return $app->redirect('/todo');
});

$app->post('/todo/complete/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $complete = $request->request->get('complete');
        Model\Todo::complete($id, $complete, $app);
    }

    return $app->redirect('/todo');
})
->value('id', null);