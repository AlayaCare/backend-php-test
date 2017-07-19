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
        $model = new model;
		$user = $model->Validate_user($username, $password, $app);

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
		
		$model = new model;
		$todos = $model->Get_Todos($user['id'],PER_PAGE, $page, $app);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
			'page' => $page,
			'last_page' => $model->Get_LastPage()
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
        $model = new model;
		$model->Add_Todo($user_id, $description, $app);
		
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

	$model = new model;
	$model->Delete_Todo($id, $app);
 
	$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo deleted');
			
    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id, Request $request) use ($app) {
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	
	$model = new model;
    $model->Complete_Todo($id, $app);

	$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo completed');
			
    return $app->redirect('/todo');
});

$app->match('/todo/uncomplete/{id}', function ($id, Request $request) use ($app) {
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	
    $model = new model;
    $model->Uncomplete_Todo($id, $app);

	$request->getSession()
			->getFlashBag()
			->add('msg', 'Todo uncompleted');
			
    return $app->redirect('/todo');
});

$app->get('/todo/json/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

	$model = new model;
    $todo = $model->Get_Todo($id, $app);

	$arr = array('id' => $todo['id'], 'user_id' => $todo['user_id'], 'description' => $todo['description'], 'completed' => $todo['completed']);
	return json_encode($arr);
    
});