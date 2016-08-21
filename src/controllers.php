<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\Model;
use models\Todo;
use models\User;
Model::setDBConnection($app['db']);

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
    	$user = User::login($username, $password);
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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    if ($id){
    	$todo = new Todo($id);
    	if (!$todo->getId() || $todo->getUserId() != $user["id"]){
    		return $app->redirect('/todo');
    	}
        
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
    	$pageNb = (int)$request->get('page');
    	$pageNb = $pageNb > 0 ? $pageNb : 1;
    	$nbOfRecordsPerPage = $app['config']['paginator']['nb_of_records_per_page'];
    	/*
    	 * This is a simple paginator.
    	 * It does not account for other GET or POST variables (for example, when performing a search on the TODO tasks, we want to maintain the search parameters when paginating)
    	 * It does not account for other features (e.g. limit the number of pages displayed to a specific number)
    	 * In an actual project, I would probably use a third party pagiantor class, or build a new one with all the features I need
    	 * In this example, it was built as a simple paginator because there is no need for a more complex one
    	 */
    	$nbOfRecords = Todo::getNumberOfUserTasks($user['id']);
    	$nbOfPages = ceil($nbOfRecords / $nbOfRecordsPerPage);
    	$nbOfPages = $nbOfPages > 0 ? $nbOfPages : 1;
    	if ($pageNb > $nbOfPages){
    		return $app->redirect("/todo?page={$nbOfPages}");
    	}
    	
    	$paginatorLinks = array();
    	for ($i = 1; $i <= $nbOfPages; $i++){
    		$paginatorLinks[$i] = "/todo?page={$i}";
    	}
    	
    	$startFromRecord = ($pageNb - 1 )* $nbOfRecordsPerPage;
        $todos = Todo::getUserTasks($user['id'], $startFromRecord, $nbOfRecordsPerPage);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        	'paginatorLinks' => $paginatorLinks,
        	'pageNb' => $pageNb
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
    $description = strip_tags($description);//Do not allow the user to use any tags. This is a text-only field

    if ($description) {//If there is no description, redirect the user to the main todo page
    	$todo = new Todo();
    	$todo->setDescription($description);
    	$todo->setUserId($user_id);
    	$todo->setIsComplete(0);
    	$todo->save();
        $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been added successfully');
    }
    else{
        $app['session']->getFlashBag()->add('todoErrorMessages', 'Please enter the description');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	$todo = new Todo($id);
    if (!$todo->getId() || $todo->getUserId() != $user["id"]){
    	return $app->redirect('/todo');
    }
    $todo->delete();
    $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been deleted successfully');
    return $app->redirect('/todo');
});

$app->post('/todo/complete/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	$todo = new Todo($id);
	if (!$todo->getId() || $todo->getUserId() != $user["id"]){
		return $app->redirect('/todo');
	}
	$todo->setIsComplete(1);
	$todo->save();
    $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been flaged as complete');
    return $app->redirect('/todo');
});

$app->post('/todo/activate/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	$todo = new Todo($id);
	if (!$todo->getId() || $todo->getUserId() != $user["id"]){
		return $app->redirect('/todo');
	}
	$todo->setIsComplete(0);
	$todo->save();
    $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been activated');
    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	$todo = new Todo($id);
	if (!$todo->getId() || $todo->getUserId() != $user["id"]){
		return $app->redirect('/todo');
	}
	return json_encode($todo->getArrayFromObject());
});
