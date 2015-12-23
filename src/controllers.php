<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
include_once "Todo.php";
include_once "User.php";

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
        $user = $userModel->login($username, $password);
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


$app->get('/todos/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    if (! $id){
      $id = 1;
    }
    
    $todoModel = new Todo($app);
    $all = $todoModel->countTodosByUserId($user['id']);
    
    
    $todoperPage = 5;
    $currentPage = $id;
    $startTodo = $todoperPage * ($currentPage-1);
    $pagesNumber = (intval($all)/$todoperPage);
    
    $todos = $todoModel->findFromTo($user['id'], $startTodo, $todoperPage);
    

    return $app['twig']->render('todos.html', [
    'todos' => $todos,
    'pagesNumber' => $pagesNumber
    ]);
})
->value('id', null);

$app->get('/todo/{id}', function ($id) use ($app) {
    
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    $todoModel = new Todo($app);
    $todo = $todoModel->find($id);

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todoModel = new Todo($app);
    $todo = $todoModel->find($id);
    
    return $app->json($todo, 200);
})
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todoModel = new Todo($app);
    $todoModel->user_id = $user['id'];
    $todoModel->description = $request->get('description');
   
    if ( !empty($todoModel->description) ){
      $todoModel->Save();
      $app['session']->getFlashBag()->add('message',array('type'=>"success",'content'=>"your todo has been added"));
    }else{
      $app['session']->getFlashBag()->add('message',array('type'=>"danger",'content'=>"your todo hasn't been added"));
    }
    
    return $app->redirect('/todos');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $todoModel = new Todo($app);
    $todoModel->id = $id;
    if ($todoModel->delete()){
      $app['session']->getFlashBag()->add('message',array('type'=>"sucess",'content'=>"your todo has been deleted"));
    }else{
      $app['session']->getFlashBag()->add('message',array('type'=>"danger",'content'=>"your todo hasn't been deleted"));

    }

    return $app->redirect('/todos');
});

$app->match('/todo/edit/{id}', function ($id) use ($app) {
    
    $todoModel = new Todo($app, $id);
    $todoModel->completed = true;
    $todoModel->save();
    return $app->redirect('/todos');
});