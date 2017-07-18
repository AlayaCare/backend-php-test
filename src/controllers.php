<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

define('PER_PAGE', 5);

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


$app->get('/todo/{id}', $ref = function ($id, $page) use ($app) 
{
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
		
		$sql = "SELECT COUNT(*) FROM todos WHERE user_id = '${user['id']}'";
		$todos = $app['db']->fetchAssoc($sql);
		$total_items = $todos['COUNT(*)'];
		$last_page = round($total_items / PER_PAGE);
		
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT " . PER_PAGE . " OFFSET " . (($page-1)*PER_PAGE);
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
			'page' => $page,
			'last_page' => $last_page
        ]);
    }
})
->value('id', null)->value('page',1);

$app->get('/todo/page/{page}', $ref)
->value('page', 1)->value('id',null);

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    
    if (strlen($description) > 0){
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
		$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo added');
    } else {
		$request->getSession()
			->getFlashBag()
			->add('msg', 'A description is required');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id, Request $request) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

	$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo deleted');
			
    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id, Request $request) use ($app) {
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	
    $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

	$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo completed');
			
    return $app->redirect('/todo');
});

$app->match('/todo/uncomplete/{id}', function ($id, Request $request) use ($app) {
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed = 0 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

	$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo uncompleted');
			
    return $app->redirect('/todo');
});

$app->get('/todo/json/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

	$arr = array('id' => $todo['id'], 'user_id' => $todo['user_id'], 'description' => $todo['description'], 'completed' => $todo['completed']);
	return json_encode($arr);
    
});