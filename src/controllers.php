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

    $dba = new DatabaseAccessModel($app);

    if ($username) {
        $user = $dba->getUserLogin($username, $password);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todos');
        }else{
            $app['session']->getFlashBag()->add('error', 'Wrong login credentials.');
            return $app->redirect('/login');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dba = new DatabaseAccessModel($app);

    if ($id){
        $todo = $dba->getTodo($id);

        if($user['id'] != $todo['user_id']){
            $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
            return $app->redirect('/todos');
        }

        if($format == 'json'){
            return $app['twig']->render('todo_json.html', [
                'todo' => $todo,
                'encoded_todo' => json_encode($todo),
            ]);
        }else{
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        }

    } else {
        $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
        return $app->redirect('/todos');
    }
})
->value('id', null)
->value('format', null);

$app->get('/todos/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dba = new DatabaseAccessModel($app);

    //pagination management
    $todos_count = $dba->getTodoCountByUser($user['id']);
    $page_count = ceil($todos_count/5);
    $start = ($page-1)*5;
    $limit = 5;

    $todos = $dba->getTodosByUser($user['id'], $start, $limit);

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'page' => $page,
        'page_count' => $page_count
    ]);

})
->value('page', 1);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dba = new DatabaseAccessModel($app);

    $user_id = $user['id'];
    $description = $request->get('description');

    if(empty($description)){
        $app['session']->getFlashBag()->add('error', 'The todo description can\'t be empty.');
        return $app->redirect('/todo');
    }

    $insert_todo = $dba->insertTodo($user_id, $description);

    $app['session']->getFlashBag()->add('success', 'You successfulyl added a new todo.');

    return $app->redirect('/todos');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dba = new DatabaseAccessModel($app);

    $todo = $dba->getTodo($id);

    if($user['id'] != $todo['user_id']){
        $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
        return $app->redirect('/todos');
    }

    $delete_todo = $dba->deleteTodo($id);

    $app['session']->getFlashBag()->add('success', 'The todo #'.$id.' has been successfully deleted.');

    return $app->redirect('/todos');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $dba = new DatabaseAccessModel($app);

    $todo = $dba->getTodo($id);

    if($user['id'] != $todo['user_id']){
        $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
        return $app->redirect('/todos');
    }

    $dba = new DatabaseAccessModel($app);
    $complete_todo = $dba->completeTodo($id);

    $app['session']->getFlashBag()->add('success', 'The todo #'.$id.' has been successfully completed.');

    return $app->redirect('/todos');
});