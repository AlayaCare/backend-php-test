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
            return $app->redirect('/todos');
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

    if ($id){

        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

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

    //pagination management
    $sql = "SELECT count(*) as count FROM todos WHERE user_id = '${user['id']}' GROUP BY user_id";
    $todos_count = $app['db']->fetchAssoc($sql);

    $page_count = ceil($todos_count['count']/5);
    $start = ($page-1)*5;
    $limit = 5;

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' ORDER BY id ASC LIMIT ".$start.",".$limit;
    $todos = $app['db']->fetchAll($sql);

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

    $user_id = $user['id'];
    $description = $request->get('description');

    if(empty($description)){
        $app['session']->getFlashBag()->add('error', 'The todo description can\'t be empty.');
        return $app->redirect('/todo');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'You successfulyl added a new todo.');

    return $app->redirect('/todos');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'You successfulyl deleted todo #'.$id.'.');

    return $app->redirect('/todos');
});


$app->match('/todo/complete/{id}', function ($id) use ($app) {
    $sql = "UPDATE todos SET status = 'COMPLETED' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'You successfulyl completed todo #'.$id.'.');

    return $app->redirect('/todos');
});