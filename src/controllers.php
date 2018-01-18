<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Models\User;
use Models\Todo;

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
    //Filtering user entries
    $username = filter_var($request->get('username'), FILTER_SANITIZE_STRIPPED);
    $password = filter_var($request->get('password'), FILTER_SANITIZE_STRIPPED);

    if ($username) {
        //md5 for increase security
        $user = User::login($username, md5($password));

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

    $getTodo = Todo::getTodo($id);

    //Filtering user entries
    if ($id and is_numeric($id)){
        if($getTodo) {
            return $app['twig']->render('todo.html', [
                'todo' => $getTodo,
            ]);
        } else {
            return $app->redirect('/todo');
        }
    } else {
        return $app['twig']->render('todos.html', [
            'todos' => $getTodo,
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if(is_numeric($id)) {
        $todo = Todo::getTodo($id);
    }

    if($todo) {
        return json_encode($todo);
    } else {
        return $app->redirect('/todo');
    }
});

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = $request->get('description');

    if($description != "") {
        //Filtering user entries
        Todo::add(addslashes($description));
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    //Filtering user entries
    if(is_numeric($id)) {
        Todo::delete($id);
    }

    return $app->redirect('/todo');
});