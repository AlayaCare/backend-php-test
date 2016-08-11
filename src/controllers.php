<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    $entity_manager = $app["orm.em"];

    if ($id){
        
        $todo = $entity_manager->getRepository('Todo')->findOneBy(['id'=>$id, 'user_id'=>$user->id]);
    	
        if($todo === null){
            return new Response("Not Found", 404);
        }

        if($request->get('format') === "json"){
        	$json = json_encode($todo);
        	return new Response($json, 200, ["Content-Type"=>"application/json"]);
        }
        
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
    	
        $todos = $entity_manager->getRepository('Todo')->findBy(['user_id'=>$user->id]);
    	
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

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
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	
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
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	
    $entity_manager = $app['orm.em'];
	
    $todo = $entity_manager->getRepository('Todo')->findOneBy(['id'=>$id, 'user_id'=>$user->id]);
	
    if($todo !== null){
        $entity_manager->remove($todo);
        $entity_manager->flush();
    }
	
    $app['session']->getFlashBag()->add('todo_confirmation', "Todo successfully deleted.");

    return $app->redirect('/todo');
});