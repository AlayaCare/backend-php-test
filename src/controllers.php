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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
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
    	$sql = "SELECT count(*) as nb FROM todos WHERE user_id = '${user['id']}'";
    	$nbOfRecords = $app['db']->fetchAll($sql);
    	$nbOfRecords = $nbOfRecords[0]["nb"];
    	$nbOfPages = ceil($nbOfRecords / $nbOfRecordsPerPage);
    	if ($pageNb > $nbOfPages){
    		return $app->redirect("/todo?page={$nbOfPages}");
    	}
    	
    	$paginatorLinks = array();
    	for ($i = 1; $i <= $nbOfPages; $i++){
    		$paginatorLinks[$i] = "/todo?page={$i}";
    	}
    	
    	$startFromRecord = ($pageNb - 1 )* $nbOfRecordsPerPage;
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' order by is_complete, id desc limit {$startFromRecord}, {$nbOfRecordsPerPage}";
        $todos = $app['db']->fetchAll($sql);

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

    if ($description) {//If there is no description, redirect the user to the main todo page
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been added successfully');
    }
    else{
        $app['session']->getFlashBag()->add('todoErrorMessages', 'Please enter the description');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been deleted successfully');
    return $app->redirect('/todo');
});

$app->post('/todo/complete/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	
    $sql = "update todos set is_complete = 1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been flaged as complete');
    return $app->redirect('/todo');
});

$app->post('/todo/activate/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}
	
    $sql = "update todos set is_complete = 0 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('todoSuccessMessages', 'Your task has been activated');
    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
		return $app->redirect('/login');
	}

	$sql = "SELECT * FROM todos WHERE id = '$id'";
	$todo = $app['db']->fetchAssoc($sql);
	return json_encode($todo);
});
