<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Todos;
use App\Entity\Users;

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
        
		$user = $app['orm.em']->getRepository('App\Entity\Users')->findBy([
			'username' => $username,
			'password' => $password
		]);
		
		if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        } else {
			$app['session']->getFlashBag()->set('error', 'Credentials are invalid.');
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
	
	$todoArr = array();
    if ($id){
		
		$todo = $app['orm.em']->getRepository('App\Entity\Todos')->find($id);
		
		if (! $todo) {
			$app['session']->getFlashBag()->set('error', 'No Todo found for id '.$id);
		} else {
			$todoArr = array('id' => $todo->getId(),
							 'todo_status' => $todo->getTodoStatus(),
							 'description' => $todo->getDescription(),
							 'completed_date' => $todo->getCompletedDate() ? $todo->getCompletedDate()->format('m/d/Y h:i:s') : ''
							);
		}
		
		return $app['twig']->render('todo.html', [
            'todo'     => $todoArr,
			'jsonview' => ''
        ]);
    } else {
		
		$page = $request->get('page', 1);
		
		$todosdb = $app['orm.em']->getRepository('App\Entity\Todos')->findBy([
													'user' => $user[0]->getId(),
												]);
		
		
		//development environemnt was giving a memory exhaust error, this line seems to fix that.
		$app['orm.em']->getConnection()->getWrappedConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);		
		
		//this is preparation for pagination task.
		$todos = array();
		for ($i = 0; $i < count($todosdb); $i++) {
			$completed_date = '-';
			if($todosdb[$i]->getTodoStatus() == 'Completed') {
				$completed_date = $todosdb[$i]->getCompletedDate()->format('m/d/Y h:i:s'); 
			}
			$todos[] = array(				
				'id' => $i+1,
				'description' => "<a href='".$request->getBaseUrl()."/todo/".$todosdb[$i]->getId()."'>".$todosdb[$i]->getDescription()."</a>",
				'status'      => $todosdb[$i]->getTodoStatus(),
				'completed_date' => $completed_date,
				'action'      => "<form method='post' action='".$request->getBaseUrl()."/todo/delete/".$todosdb[$i]->getId()."' id='deleteentity".$todosdb[$i]->getId()."'>
										<button type='submit' title='Delete' class='btn btn-xs btn-danger'><span class='glyphicon glyphicon-remove glyphicon-white'></span></button>
										<input type='hidden' name='page' value='".$page."'>
								  </form>"
			);
		}
		
		$pagination = $app['knp_paginator']->paginate($todos, $page, $app['config']['pagination']['per_page_entity']);
		
		return $app['twig']->render('todos.html', [
			'pagination' => $pagination,
			'page' => $page
        ]);
    }
})
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
	$description = $request->get('description');
	$page = $request->get('page', 1);
	
    //server side validation is necessary, client side is convenience.
	if (empty($description)) {
	
		// add flash message
		$app['session']->getFlashBag()->set('error', 'Description is required.');
		
	} else {    
	
		$userObj = $app['orm.em']->getRepository('App\Entity\Users')->find($user[0]->getId());
		$todo = new Todos();
		$todo->setUser($userObj);  
		$todo->setTodoStatus('Pending');
		$todo->setDescription($description);
		$app['orm.em']->persist($todo);
		$app['orm.em']->flush();
		
		$app['session']->getFlashBag()->set('success', 'Todo added successfully.');
	}
	
	return $app->redirect($request->getBaseUrl().'/todo?page='.$page);
    
});

$app->match('/todo/delete/{id}', function ($id, Request $request) use ($app) {
    
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	
	$page = $request->get('page',1);
	
	$todo = $app['orm.em']->getRepository('App\Entity\Todos')->find($id);
	
	if (! $todo) {
		$app['session']->getFlashBag()->set('error', 'No Todo found for id '.$id);
	} else {
		$app['orm.em']->remove($todo);
		$app['orm.em']->flush();
		$app['session']->getFlashBag()->set('success', 'Todo deleted successfully.');
	}    
	
    return $app->redirect('/todo?page='.$page);
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
		
		//added user_id condition as all todos are private. User should not see todos of others.		
		$todo = $app['orm.em']->getRepository('App\Entity\Todos')->findBy([
			'user' => $user[0]->getId(),
			'id' => $id
		]);
		
		$jsonview = '';
		if($todo) {
			$todoArr = array('id' => $todo[0]->getId(),
						 'todo_status' => $todo[0]->getTodoStatus(),
						 'description' => $todo[0]->getDescription(),
						 'completed_date' => $todo[0]->getCompletedDate() ? $todo[0]->getCompletedDate()->format('m/d/Y h:i:s') : ''
						);
						
			$jsonview = json_encode($todoArr);
			return $app['twig']->render('todo.html', [
				'todo'     => $todoArr,
				'jsonview' => $jsonview
			]);
		} else {
			$app['session']->getFlashBag()->set('error', 'No Todo found for id '.$id);
			return $app->redirect('/todo/'.$id);
		}
    } else {
        $app['session']->getFlashBag()->set('error', 'No Todo found for id '.$id);
		return $app->redirect('/todo/'.$id);
    }
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {
	
	if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	
	$todoArr = array();
	$todo = $app['orm.em']->getRepository('App\Entity\Todos')->find($id);
	
	if (! $todo) {
		$app['session']->getFlashBag()->set('error', 'No Todo found for id '.$id);
	} else {		
		$updated = new \DateTime("now");
		$todo->setTodoStatus('Completed');
		$todo->setCompletedDate($updated);
		
		$app['orm.em']->persist($todo);
		$app['orm.em']->flush();
		$app['session']->getFlashBag()->set('success', 'Todo marked as Completed successfully.');
		
		$todoArr = array('id' => $todo->getId(),
						 'todo_status' => $todo->getTodoStatus(),
						 'description' => $todo->getDescription(),
						 'completed_date' => $todo->getCompletedDate() ? $todo->getCompletedDate()->format('m/d/Y h:i:s') : ''
					);
	}
	
	return $app['twig']->render('todo.html', [
		'todo'     => $todoArr,
		'jsonview' => ''
	]);
});