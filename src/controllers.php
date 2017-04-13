<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
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
        $sql = $app['dbOrm']->table('todos')->select()->where('id', '=', $id)->getQuery();;
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = $app['dbOrm']->table('todos')->select()->where('user_id', '=', $user['id'])->getQuery();
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'user' => $user,
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

    $sql = $app['dbOrm']->table('todos')->insert(['user_id', 'description'], [$user_id, $description])->getQuery();
    $app['db']->executeUpdate($sql);
	
	$app['session']->getFlashBag()->add('messages', ['type' => 'success', 'message' => 'Successfully added a new task!']);

    return $app->redirect('/todo');
});

$app->match('/todo/completed/{id}', function ($id) use ($app) {
	
	$sql = $app['dbOrm']->table('todos')->update(['completed'], [1])->where('id', '=', $id)->getQuery();
	$app['db']->executeUpdate($sql);
	
	$app['session']->getFlashBag()->add('messages', ['type'    => 'success',
	                                                 'message' => 'Successfully completed the task!'
	]);
	
	return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {
	
	$sql  = $app['dbOrm']->table('todos')->select()->where('id', '=', $id)->getQuery();;
	$todo = $app['db']->fetchAssoc($sql);
	
	return $app->json($todo);
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = $app['dbOrm']->table('todos')->delete()->where('id', '=', $id)->getQuery();
    $app['db']->executeUpdate($sql);
	
	$app['session']->getFlashBag()->add('messages', ['type'    => 'success',
	                                                 'message' => 'Successfully deleted the task!'
	]);

    return $app->redirect('/todo');
});