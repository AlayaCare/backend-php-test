<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
require "Entities\Todo.php";

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
            return $app->redirect('/todo');
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
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

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

    $user_id = $user['id'];
    $description = $request->get('description');
    
    $todo = new Todo();
    $todo->user_id = $user_id;
    $todo->description = $description;
    
    $errors = $app['validator']->validate($todo);
    
    if(count($errors)){
    	$app['session']->getFlashBag()->add('todo_add_errors', $errors[0]->getMessage());
    }
    else{
    	$sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    	$app['db']->executeUpdate($sql);
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
		
		if($todo->user_id != $user['id']){
			return $app->redirect('/todo');
		}
		
		$todo->is_completed = 1;
		$entity_manager->persist($todo);
		$entity_manager->flush();
		
	}
	
	return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});