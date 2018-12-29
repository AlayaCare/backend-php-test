<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use service\Pagination as Pagination;
use model\TodoDao as TodoDao;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));
    return $twig;
}));

//Go to index
$app->get('/', function () use ($app) {
      return $app['twig']->render('index.html', [
        'readme' => file_get_contents('..\README.MD'),
    ]);
});
//Go to login page
$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');    
    if ($username) {
        $user = TodoDao::login($username, $password, $app);        
        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        } else {
            $app['session']->getFlashBag()->add("ERROR", "Wrong user name or password!");    
        }
    }
    return $app['twig']->render('login.html', array());
});
//Logout user redirecting to home page
$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});
//Go to one single todo or list all todos
$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    //Get asingle todo
    if ($id){
        $todo = TodoDao::getTodoById($id, $app);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
            'todoJson' => json_encode($todo, JSON_PRETTY_PRINT),
        ]);
    } else {  
        //List all todos
        if(isset($_GET['page'])){
            $currenPage = $_GET['page'];
        } else{
            $currenPage = 1;
        }
        $pageSize = 5;
        $startOf = (($currenPage-1) * $pageSize);
        $todos = TodoDao::listTodo($app, $user['id'], $startOf, $pageSize);
        $total = TodoDao::total($app, $user['id']);       
        $pagination = Pagination::pagination($currenPage, $pageSize, $total);
         
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'pages' => $pagination['pages'],
            'next' => $pagination['next'],
            'previous' => $pagination['previous'],
            'total' => floor($pagination['QTDPages']) 
         ]);       
    }  
})
->value('id', null);
//Add a todo
$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];
    $description = $request->get('description');    
    $errors = $app['validator']->validate($description, new Assert\NotBlank());     
    if (count($errors) > 0) {
        $app['session']->getFlashBag()->add("INFO", "Description is required");           
    } else {
        TodoDao::add($user_id, $description, $app);
    } 
    return $app->redirect('/todo');
});
//Delete a todo
$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }else{
        $todo = TodoDao::getTodoById($id, $app);        
        if(!$app['session']->get('user') != $todo['user_id']){
            return $app->redirect('/login');   
        }else{
            TodoDao::delete($id, $app);
            return $app->redirect('/todo');
        }
    }   
});
//Change value (completed) in a todo
$app->post('/todo/completed/{id}/{completed}', function ($id, $completed) use ($app) {    
    if (null === $user = $app['session']->get('user')) {
       return $app->redirect('/login');
    }        
    TodoDao::changeCompleted($id, $completed, $app);
    return $app->redirect('/todo');
});
//Get a one single todo in json's format
$app->get('/todo/{id}/json', function ($id) use ($app) {   
    $todo = TodoDao::getTodoById($id, $app);
    return $app['twig']->render('todoJSON.html', [
       'todoJson' => json_encode($todo, JSON_PRETTY_PRINT),
    ]);
});
