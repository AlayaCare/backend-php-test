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
       $user = UserORM::get_user($app, $username, $password);
       if($user){
            if (password_verify($password, $user['password'])){
                $app['session']->set('user', $user);
                return $app->redirect('/todo');
            }
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
        $todo = TodoORM::get_todo($app, $id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {

        if(isset($_GET['p']) && is_numeric($_GET['p']) ){
            $page_number = intval( $_GET['p'] ) ;
        } else {
            $page_number = 1;
        }
        $page_size = 2;
        $offset = ($page_number-1) * $page_size;

        $total_todos = TodoORM::get_total_count($app, $user['id']);

        $pagination = pagination($page_number,$page_size, $total_todos);
        
        $todos = TodoORM::get_todos($app, $user['id'], $offset,$page_size);
     
         return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'next' => $pagination['next'],
            'previous' => $pagination['previous'],
            'pages' => $pagination['pages']
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

    if(!isset($description) || trim($description) == ''){
        $app['session']->getFlashBag()->add('empty_description', 'You need a description for the note');
    }else{
        TodoORM::add_todo($app, $description, $user_id);
        $app['session']->getFlashBag()->add('confirmation', 'You created todo with description : ' . $description);
    }
    
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    if(TodoORM::delete_todo($app, $id, $user)){
        $app['session']->getFlashBag()->add('confirmation', 'You deleted todo with id ' . $id);
    }
    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    TodoORM::mark_as_completed($app, $id, $user);
    return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $todo = TodoORM::get_todo($app, $id, $user);
    return json_encode($todo);
});

function pagination($page_number,$page_size,$total_todos){
    $previous = null;
    if(($page_number - 1)>0){
        $previous = '?p=' . ($page_number - 1);
    }
    $next = null;
    if($total_todos > ($page_number * $page_size)){
        $next = '?p=' . ($page_number + 1);
    }
    $number_of_pages = ceil($total_todos / $page_size);
    $pages = array();
    for($i = 1; $i <= $number_of_pages; $i++ ){
        $link_text =$i;
        $class = "";
        if($i == $page_number){
            $class = "active";
        }
        array_push($pages, array('link_text'=> $link_text , 'link' => '?p=' . $i, 'class' => $class));
    }
    return array('pages' => $pages, 'previous' => $previous, 'next' => $next);
}