<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
require "franmomu/silex-pagerfanta-provider";


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
    
    // TASK 6: Implementing Implement an ORM database access layer so we don’t have SQL in the controller code.
    $user_id = (int) $user['id'];
    $todo = new Todo( $app );

    if ($id){
    	/* Changing sql code
        $sql = "SELECT * FROM todos WHERE id = '$id'";
         $todo = $app['db']->fetchAssoc($sql); */
    	
    	//Accomplishing Task 6
    	$todo = $todo->getvals( ['id' => $id, 'user_id' => $user_id] );
       //Task 6 done
          //TASK-5:User can see my list of todos paginated.
        
        $pagerfanta = new Pagerfanta\Pagerfanta($sql);
        $ipp = 3;
        $p = $app['request']->get('p', 1);
        $pagerfanta->setMaxPerPage($ipp);
        $pagerfanta->setCurrentPage($p);
        $view = new Pagerfanta\View\DefaultView;
        $html = $view->render($pagerfanta, function($p) use ($app) {
        	return $app['url_generator']->generate('todos', array('p' => $p));
        }, array(
        		'proximity'         => 3,
        		'previous_message'  => '« Previous',
        		'next_message'      => 'Next »'
        ));

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        		'pagerfanta' => $pagerfanta,
        		'html' => $html
        ]);
        
    } else {
    	$todos = $todo->getvals( ['user_id' => $user_id] );
        return $app['twig']->render('todos.html', [
            'todos' => $todos
        		
        ]);
    }
})
->bind('todos');




$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    
    //TASK 1:User can't add a todo without a description
    
    if(trim($description!=""))//checking if description is added
    {

   // $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    //$app['db']->executeUpdate($sql);
    
    	$todo = new Todo( $app );
    	$sql= $todo->add( ['user_id' => $user_id, 'description' => $description] );
    
    //TASK-4: Confirmation message on adding a todo
    
    $session = new Session();
    $session->start();
    
    $msg= "TODO Added Successfully";
    $session->getFlashBag()->add('notice1', $msg);//using flashBag
    
     
    foreach ($session->getFlashBag()->get('notice1', array()) as $msg) {
    	echo $msg;
    }//done

    return $app->redirect('/todo');
    }
    
    else {
    	
    	echo "Pleae add a descrption";
    	exit;
    
    }
});



$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $todo = new Todo( $app );
    $sql = $todo->delete( ['id' => $id, 'user_id' => $user['id']] );
    
 //TASK-4: Confirmation message on deleting a todo
 
    $session = new Session();
    $session->start();
    
    $msg= "TODO Deleted Successfully";
    $session->getFlashBag()->add('notice', $msg);//using flashBag
    
   
    foreach ($session->getFlashBag()->get('notice', array()) as $msg) {
    	echo $msg;
    }//done

    return $app->redirect('/todo');
});

//TASK-3: View todo in JSON FORMAT

	$app->match('/todo/jsonview/{id}', function ($id) use ($app) {
		
		$todo = new Todo( $app );
	
		$sql= $todo->getvals( ['id' => $id, 'user_id' => $user_id] );
		 $todoarray = array();
		 while($row =mysqli_fetch_assoc($sql))
		 {
		 	$todoarray[] = $row;
		 }
		 echo json_encode($todoarray);
		
	
		return $app->redirect('/todo');
	});
	
	
		//TASK-4: Mark Todo as completed
		
		$app->match('/todo/todocomplete/{id}', function ($id) use ($app) {
		
			 $sql ="UPDATE todos SET status=1 WHERE id='$id'";
    $app['db']->executeUpdate($sql);
    
		});//done
		



