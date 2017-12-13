<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dal\TodoRepo;
use Dal\UserRepo;

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
        $dal = new UserRepo($app['db']);
        $user = $dal->login($username, $password);

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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $dal = new TodoRepo($app['db']);
    $user_id = $user['id'];
    if ($id){
        $todo = $dal->findById($user_id, $id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $nbElementByPage = 5;
        $elementTotal = $dal->countAll($user_id);

        $nbPageTotal = floor($elementTotal/$nbElementByPage);
        $currentPage = is_numeric($request->query->get('page')) ? intval($request->query->get('page')) : 0;
        $offset = $currentPage*$nbElementByPage;
        $todos = $dal->findLimited($user_id, $nbElementByPage, $offset);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'nbPageTotal' => $nbPageTotal,
            'currentPage' => $currentPage
        ]);
    }
})
->value('id', null);

$app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        $error = ["code" => 403, "message" => "cannot access"];
        return $app->json($error);
    }

    $dal = new TodoRepo($app['db']);
    $user_id = $user['id'];
    if ($id) {
        $todo = $dal->findById($user_id, $id);

        if ($todo) {
            return $app->json($todo);
        }
    }

    $error = ["code" => 404, "message" => "not found"];
    return $app->json($error);
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dal = new TodoRepo($app['db']);
    $user_id = $user['id'];
    $description = $request->get('description');
    if(!empty($description)) {
        $dal->add($user_id, $description);
        $app['session']->getFlashBag()->add('message', 'Your new todo as been correctly added');
    }else{
        $app['session']->getFlashBag()->add('error-message', 'Your new todo cannot be added. Please add a description');
    }
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dal = new TodoRepo($app['db']);
    $user_id = $user['id'];
    $dal->delete($user_id, $id);
    $app['session']->getFlashBag()->add('message', 'Your todo as been correctly removed');
    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dal = new TodoRepo($app['db']);
    $user_id = $user['id'];
    $dal->updateStatus($user_id, $id, 1);

    return $app->redirect('/todo');
});