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
    $data = new DataManager();

    if ($username) {
        $user = $data->getUser($app, $username, $password);

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

    $data = new DataManager();
    if ($id){
        $todo = $data->getItem($app, $user['id'], $id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {

        $todos = $data->getAllItems($app, $user['id']);
	
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $data = new DataManager();
    if ($id){
        $todo = $data->getItem($app, $user['id'], $id);

        return $app['twig']->render('json.html', [
            'json' => json_encode($todo),
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
    $data = new DataManager();

    if( strlen($description) < 1 ){
        $app['session']->getFlashBag()->add('messageType', 'danger');
        $app['session']->getFlashBag()->add('message', 'Description is required');
    }else{
        $data->insertItem($app, $user_id, $description);
        $app['session']->getFlashBag()->add('messageType', 'success');
        $app['session']->getFlashBag()->add('message', 'TODO added successfully');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $user = $app['session']->get('user');
    $user_id = $user['id'];
    $data = new DataManager();
    $data->deleteItem($app, $user_id, $id);

    $app['session']->getFlashBag()->add('messageType', 'warning');
    $app['session']->getFlashBag()->add('message', "TODO #{$id} removed successfully");

    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $user = $app['session']->get('user');
    $user_id = $user['id'];
    $data = new DataManager();
    $status = 1;
    $data->updateStatusItem($app, $user_id, $id, $status);

    $app['session']->getFlashBag()->add('messageType', 'success');
    $app['session']->getFlashBag()->add('message', "TODO #{$id} completed");

    return $app->redirect('/todo');
});

$app->match('/todo/notcomplete/{id}', function ($id) use ($app) {

    $user = $app['session']->get('user');
    $user_id = $user['id'];
    $data = new DataManager();
    $status = 0;
    $data->updateStatusItem($app, $user_id, $id, $status);    

    $app['session']->getFlashBag()->add('messageType', 'success');
    $app['session']->getFlashBag()->add('message', "TODO #{$id} changed to not completed");

    return $app->redirect('/todo');
});
