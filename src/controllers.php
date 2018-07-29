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
    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
		$sth = $app['db']->prepare("SELECT id, description FROM todos WHERE user_id = ?");
		$sth->bindValue(1, $user['id'], PDO::PARAM_INT);
		$sth->execute();
		$todosdb = $sth->fetchAll();
		
		//this is preparation for pagination task.
		$todos = array();
		for ($i = 0; $i < count($todosdb); $i++) {
			$todos[] = array(				
				'id' => $i+1,
				'description' => "<a href='".$request->getBaseUrl()."/todo/".$todosdb[$i]["id"]."'>".$todosdb[$i]['description']."</a>",
				'action'      => "<form method='post' action='".$request->getBaseUrl()."/todo/delete/".$todosdb[$i]["id"]."' id='deleteentity".$todosdb[$i]["id"]."'>
										<button type='submit' title='Delete' class='btn btn-xs btn-danger'><span class='glyphicon glyphicon-remove glyphicon-white'></span></button>
								  </form>"
			);
		}
		return $app['twig']->render('todos.html', [
			'pagination' => $todos
        ]);
    }
})
->value('id', null);
$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
	$description = $request->get('description');
	
	//server side validation is necessary, client side is convenience. 
	if (empty($description)) {
	
		//TASK 1: As a user I can't add a todo without a description.		
		$sth = $app['db']->prepare("SELECT id, description FROM todos WHERE user_id = ?");
		$sth->bindValue(1, $user['id'], PDO::PARAM_INT);
		$sth->execute();
		$todosdb = $sth->fetchAll();
		
		//this is preparation for pagination task.
		$todos = array();
		for ($i = 0; $i < count($todosdb); $i++) {
			$todos[] = array(				
				'id' => $i+1,
				'description' => "<a href='".$request->getBaseUrl()."/todo/".$todosdb[$i]["id"]."'>".$todosdb[$i]['description']."</a>",
				'action'      => "<form method='post' action='".$request->getBaseUrl()."/todo/delete/".$todosdb[$i]["id"]."' id='deleteentity".$todosdb[$i]["id"]."'>
										<button type='submit' title='Delete' class='btn btn-xs btn-danger'><span class='glyphicon glyphicon-remove glyphicon-white'></span></button>
								  </form>"
			);
		}
		// add flash messages
		$app['session']->getFlashBag()->set('error', 'Description is required.');
		return $app['twig']->render('todos.html', [
			'pagination' => $todos
        ]);
	} else {    
		$sql = "INSERT INTO todos (user_id, description) VALUES (?,?)";
		$stmt = $app['db']->prepare($sql);
		$stmt->execute(array($user['id'], $description));

		$app['session']->getFlashBag()->set('success', 'Todo added successfully.');
	}
		
    return $app->redirect($request->getBaseUrl().'/todo');
    
});
$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    return $app->redirect('/todo');
});