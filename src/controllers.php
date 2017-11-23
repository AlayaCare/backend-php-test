<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Module\Generalmodel\Gmodel;

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
    $user = login_check($app);

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
	$user = login_check($app);
	
	try {
		
		$user_id = $user['id'];
		$description = $request->get('description');
		
		if(empty($description)) {
			$error = "Description field is required.";
			throw new Exception($error);
		} 
		else {
			$sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
			$app['db']->executeUpdate($sql);
			$app['session']->getFlashBag()->add('success_message', "Todo is added successfully");
		}
	} catch (Exception $e) {
		$app['session']->getFlashBag()->add('description_blank', $e->getMessage());
	}
	
	return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
	$user = login_check($app);
	$user_id = "${user['id']}";
	
    $sql = "DELETE FROM todos WHERE id = '$id' and user_id = '$user_id'";
    $returnFlag = $app['db']->executeUpdate($sql);
	if(!empty($returnFlag)) {
		$app['session']->getFlashBag()->add('success_message', "Todo is deleted successfully");
	} else {
		die('Something went wrong!');
	}
    return $app->redirect('/todo');
});

function login_check($app)
{
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    } else {
		return $user;
	}	
}

$app->match('/editstatus/{id}/{status}', function ($id, $status) use ($app) {
	$user = login_check($app);

	if ($id and is_numeric($id)){
		//update flage
		$user_id = "${user['id']}";
		$objGolb = new Gmodel($app, $user_id);
		if($status == "Incomplete") {
			$edit_status = "Complete";
		} 
		if( $status === "Complete") {
			$edit_status = "Incomplete";
		}
		
		$returnFlag = $objGolb->update_todo_status($id,$edit_status);
		if(!empty($returnFlag)) {
			$app['session']->getFlashBag()->add('success_message', "Status update successfully");
		} else {
			die('Something went wrong!');
		}
	}
    return $app->redirect('/todo');
})
->value('id', null);


$app->match('/todo/{id}/json', function ($id) use ($app) {

	$user = login_check($app);
	$user_id = "${user['id']}";
	$objGolb = new Gmodel($app, $user_id);
	$todo = $objGolb->get_todos_by_userid($id);
	if(empty($todo))
	{
		die("You are not permitted to view others todo!");
	} else {
		return $app->json($todo);
	}
});
