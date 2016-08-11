<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Tools\Pagination\Paginator;
require "Entities\Todo.php";
require "Entities\User.php";

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
        
    	$entity_manager = $app["orm.em"];
    	
    	$user = $entity_manager->getRepository('User')->findOneBy(['username'=>$username, 'password'=>$password]);
    	
        if ($user !== null){
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

$app->get('/todo', function (Request $request) use ($app) {
    
	if(!$app['security_service']->is_user_logged_in()){
	    return $app->redirect('/login');
	}
    
    $user = $app['session']->get('user');
    
    $entity_manager = $app["orm.em"];

	$page = intval($request->get('page'));
	
	if($page < 1){
	    $page = 1;
	}
	
	$todos_per_page = 5;
	$first_result = ($page-1) * $todos_per_page;
	
	$query = $entity_manager
	   ->createQuery("SELECT t FROM Todo t WHERE t.user_id = :user_id")
	   ->setParameter("user_id", $user->id)
	   ->setFirstResult($first_result)
	   ->setMaxResults($todos_per_page);
	
	$todos = new Paginator($query);
	
	$total_todos = count($todos);
	
	$nb_pages = ceil( $total_todos / $todos_per_page );
	
    return $app['twig']->render('todos.html', [
        'todos' => $todos,
    	'nb_pages' => $nb_pages,
    	'current_page' => $page
    ]);
})
->value('page', 1);

$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    
	if(!$app['security_service']->is_user_logged_in()){
	    return $app->redirect('/login');
	}
    
    $user = $app['session']->get('user');
    
    $entity_manager = $app["orm.em"];

    $todo = $entity_manager->getRepository('Todo')->findOneBy(['id'=>$id, 'user_id'=>$user->id]);
	
    if($todo === null){
        return new Response("Not Found", 404);
    }

    if($request->get('format') === "json"){
    	$json = json_encode($todo);
    	return new Response($json, 200, ["Content-Type"=>"application/json"]);
    }
    
    return $app['twig']->render('todo.html', [
        'todo' => $todo
    ]);
})
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    
	if(!$app['security_service']->is_user_logged_in()){
	    return $app->redirect('/login');
	}
	
	$user = $app['session']->get('user');

    $user_id = $user->id;
    $description = $request->get('description');
    
    $todo = new Todo();
    $todo->user_id = $user_id;
    $todo->description = $description;
    
    $errors = $app['validator']->validate($todo);
    
    if(count($errors)){
    	$app['session']->getFlashBag()->add('todo_add_errors', $errors[0]->getMessage());
    }
    else{
    	
    	$entity_manager = $app["orm.em"];
    	$entity_manager->persist($todo);
    	$entity_manager->flush();
    	
    	$app['session']->getFlashBag()->add('todo_confirmation', "Todo successfully added.");
    }

    return $app->redirect('/todo');
});

$app->post('/todo/is_completed/{id}', function (Request $request) use ($app) {
	
	if(!$app['security_service']->is_user_logged_in()){
	    return $app->redirect('/login');
	}
	
	$user = $app['session']->get('user');
	
	$entity_manager = $app['orm.em'];
	
	$todo = $entity_manager->find('Todo', $request->get('id'));
	
	if($todo !== null){
		
		if($todo->user_id != $user->id){
			return $app->redirect('/todo');
		}
		
		$todo->is_completed = 1;
		$entity_manager->persist($todo);
		$entity_manager->flush();
		
	}
	
	return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if(!$app['security_service']->is_user_logged_in()){
	    return $app->redirect('/login');
	}
	
	$user = $app['session']->get('user');
	
    $entity_manager = $app['orm.em'];
	
    $todo = $entity_manager->getRepository('Todo')->findOneBy(['id'=>$id, 'user_id'=>$user->id]);
	
    if($todo !== null){
        $entity_manager->remove($todo);
        $entity_manager->flush();
    }
	
    $app['session']->getFlashBag()->add('todo_confirmation', "Todo successfully deleted.");

    return $app->redirect('/todo');
});