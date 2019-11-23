<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

define("PAGE_SIZE",10);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$requireUser = function(Request $request) use($app){
    $user = $app['session']->get('user');
    if (null === $user) {
        return $app->redirect('/login');
    }
    return null;
};

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
})
->bind("login");


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
})
->bind("logout");

$app->get('/todo/{id}', function (Request $request,$id) use ($app) {
    $user = $app['session']->get('user');
    $user_id = $user['id'];
    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo
        ]);
    } 
})
->assert('id','\d+')
->before($requireUser)
->bind("todo_item");

$app->get('/todo/{id}/json', function ($id) use ($app) {
    
    $user = $app['session']->get('user');
    $user_id = $user['id'];

    $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $todo = $app['db']->fetchAssoc($sql);  
    if($todo){
        return new JsonResponse($todo);
    }
    return new Response('The todo does not exist',404); 
})
->assert('id','\d+')
->before($requireUser)
->bind("todo_item_json");

$app->get('/todo/{id}', function ($id) use ($app) {
    $user = $app['session']->get('user');
    $user_id = $user['id'];

    $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $todo = $app['db']->fetchAssoc($sql);
    if($todo){
        return $app['twig']->render('todo.html', [
            'todo' => $todo
        ]);
    }
    return new Response('The todo does not exist',404); 
})
->before($requireUser)
->assert('id','\d+')
->bind("todo_item");

$app->get('/todos/{page}', function (Request $request,$page) use ($app) {

    $user = $app['session']->get('user');
    $user_id = $user['id'];

    $countsql = "SELECT * FROM todos WHERE user_id = '$user_id'";
    $totalCount = $app['db']->executeQuery($countsql)->rowCount();

    $pageSize = PAGE_SIZE;
    $offset = ($page-1)*PAGE_SIZE;
    $totalPages = ceil($totalCount/PAGE_SIZE);

    $sql = "SELECT * FROM todos WHERE user_id = '$user_id' ORDER BY id DESC LIMIT ${pageSize} OFFSET ${offset} ";
    $todos = $app['db']->fetchAll($sql);

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'totalPages'=>$totalPages,
        'pageNumber'=>$page
    ]);
    
})
->assert('page','\d+')
->value('page', 1)
->before($requireUser)
->bind("todo_list");


$app->post('/todo/add', function (Request $request) use ($app) {
    $user = $app['session']->get('user');
    $user_id = $user['id'];

    $description = $request->get('description');

    if(empty($description)){
        $app['session']->getFlashBag()->set('message',"The description can not be empty");
    }else{
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->set('message',"The todo:".$description.' has been added');
    }

    return $app->redirect('/todos');
})
->before($requireUser)
->bind("todo_add");


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $user = $app['session']->get('user');
    $user_id = $user['id'];

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->set('message',"The todo:".$id.' has been removed');

    return $app->redirect('/todos');
})
->assert('id','\d+')
->before($requireUser)
->bind("todo_delete");