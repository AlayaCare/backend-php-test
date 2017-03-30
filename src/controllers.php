<?php
include ('../Entities/todo.php');
include ('../Entities/user.php');
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$before =  function (Request $request) use ($app) {
      if (null ===  $app['session']->get('user')) {
         return $app->redirect($app['url_generator']->generate('login'));
    }

};


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents(__DIR__.'/../README.md'),
    ]);
})->bind('home');


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = md5($request->get('password'));
   
    if ($username) {  
        $user=User::login($app,$username,$password);
        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect($app['url_generator']->generate('todo'));
        }        
          $desc="Login Fail, try again!";
          $type="danger";
          $app['session']->getFlashBag()->add('message', array('desc' =>$desc,'type'=>$type));
    }
  
    return $app['twig']->render('login.html', array());
})->bind('login');


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect($app['url_generator']->generate('home'));
});


$app->get('/todo/{id}', function ($id) use ($app) {
  
    $user = $app['session']->get('user');
    if ($id){
      
        $todo =Todo::getTodo($app,$id,$user['id']);
        if($todo)   
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
        return "Can not access";
    
    } else {
   
            $todos=Todo::getAllTodo($app,$user['id']);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})->value('id', null)->bind("todo")->before($before);
// json 
$app->get('/todo/{id}/json', function ($id) use ($app) {
     $user = $app['session']->get('user');

    if ($id){
      
           $todo =Todo::getTodo($app,$id,$user['id']);
         if (!$todo) {
                 $error = array('message' => 'The todo Id was not found.');
              return $app->json($error, 404);
             }
        return $app->json($todo);
    } 
})
->assert('id', '\d+')->before($before);

//mark on todo list
$app->match('/todo/mark/{id}', function ($id) use ($app) {

    $user = $app['session']->get('user');
 
    Todo::markTodo($app,$id,$user['id']);
    $desc="Your todo has already updated ";
    $type="success";
    $app['session']->getFlashBag()->add('message', array('desc' =>$desc,'type'=>$type));
    return $app->redirect($app['url_generator']->generate('todo'));
})->assert('id', '\d+')->before($before);


$app->post('/todo/add', function (Request $request) use ($app) {
    $user = $app['session']->get('user');

    $user_id = $user['id'];
    $description = trim($request->get('description'));
    if (""=== $description) {
        $desc="You must enter description ";
        $type="danger";
    }
    else
    {
        
        Todo::insertTodo ($app,$user_id,$description);
         $desc="Your todo has already inserted ";
         $type="success";
    }
   
         $app['session']->getFlashBag()->add('message', array('desc' =>$desc,'type'=>$type));
         
      return $app->redirect($app['url_generator']->generate('todo'));
    })->before($before);

$app->get('/todo/delete/{id}', function ($id) use ($app) {
    return "You can not delete";
});

$app->post('/todo/delete/{id}', function ($id) use ($app) {
   $user = $app['session']->get('user');
  
    Todo::deleteTodo ($app,$id,$user['id']);
     $desc="Your todo has already deleted ";
          $type="success";
    $app['session']->getFlashBag()->add('message', array('desc' =>$desc,'type'=>$type));
    return $app->redirect($app['url_generator']->generate('todo'));
})->before($before);