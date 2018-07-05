<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
require ('models.php');

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
        $user = new Models($app);
        $user = $user->checkUserPwd($username, $password);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
        else{
            $app['session']->getFlashBag()->add('error', 'Wrong credentials. Try again.');
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

    if ($id){
        $todo = new Models($app);
        $todo = $todo->getTodo($id);

        if($todo == null){
            $app['session']->getFlashBag()->add('error', 'Data does not exists.');
        }

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } 
    else {    
        //these variables are passed via URL
        $limit = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 5; 
        $page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1; //starting page
        $links = 5;
        $paginator = new Paginator($app);
        $results = $paginator->getData( $limit, $page );
        $pagination_links = $paginator->createLinks($links, 'pagination pagination-sm');


        return $app['twig']->render('todos.html', [
            'results' => $results, 
            'pagination_links' => $pagination_links
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

    if(($user_id !=null) and ($description !=null)){
        $todo = new Models($app);
        $todo = $todo->addTodo($user_id, $description);

        if($todo != null){
            $app['session']->getFlashBag()->add('success', $todo);
        }
        else{
            $app['session']->getFlashBag()->add('error', 'There has been an error. Try again.');
        }
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $todo = new Models($app);
    $todo = $todo->deleteTodo($id);
    if($todo != null){
        $app['session']->getFlashBag()->add('success', $todo);
    }
    else{
        $app['session']->getFlashBag()->add('error', 'There has been an error. Try again.');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/mark/{id}', function ($id) use ($app) {
    $todo = new Models($app);
    $todo = $todo->markTodo($id);
    if($todo != null){
        $app['session']->getFlashBag()->add('success', $todo);
    }
    else{
        $app['session']->getFlashBag()->add('error', 'There has been an error. Try again.');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {

    $todo = new Models($app);
    $todo = $todo->toJson($id);
    return json_encode($todo);
});
