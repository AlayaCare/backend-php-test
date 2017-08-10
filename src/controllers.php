<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Tests\Demo;

if(!strchr($app['base_url'],'web'))
{
	$app['base_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/index.php';
}

//echo '<pre>'; print_r($app); exit;
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));
    return $twig;
}));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});



$app->match('login', function (Request $request) use ($app) {

    $username = $request->get('username');
    $password = $request->get('password');
    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'"; 
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect($app['base_url'].'/todo');
        }
		else
		{
			$app['session']->getFlashBag()->set('error', 'Invalid Username or Password');
			return $app->redirect($app['base_url'].'/login');
		}
    }

    return $app['twig']->render('login.html', array());
});


$app->get('logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect($app['base_url'].'/login');
});


$app->get('todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect($app['base_url'].'/login');
    }

    if ($id){
		
		
        $array  = array('id'=>$id);
		$obDemo = new Demo();
		$todo   =  $obDemo->getDataSingle($app,$array,'todos',$array);
		
		
		$json =  json_encode($todo);
        return $app['twig']->render('json.html', [
            'json' => $json,
			'todo' => $todo,
        ]);
    } else {
		foreach ($session->getFlashBag()->get('notice', array()) as $message) {
				echo '<div class="flash-notice">'.$message.'</div>';
			}
	
        $array = array('user_id'=>$user['id']);
		$obDemo = new Demo();
		$todos =  $obDemo->getData($app,$array,'todos',$array);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);

$app->get('todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect($app['base_url'].'/login');
    }

    if ($id){
		
		$array  = array('id'=>$id);
		$obDemo = new Demo();
		$todo   =  $obDemo->getDataSingle($app,$array,'todos',$array);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
      
		$array = array('user_id'=>$user['id']);
		$obDemo = new Demo();
		$todos =  $obDemo->getData($app,$array,'todos',$array);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);


$app->post('todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect($app['base_url'].'/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
	$array  = array('user_id'=>$user_id,'description'=>$description);
	$obDemo = new Demo();
	$obDemo->insertData($app,$array,'todos',$array);	
	
	$finder = new Finder();
	$finder->files()->in('../resources');

	foreach ($finder as $file) {
		$contents = $file->getContents('resources/fixtures.sql');
	}
    $contents = trim($contents,';');
	
	$fs = new Filesystem();
	$fs->dumpFile('../resources/fixtures.sql', $contents.",\n(".$user_id.",'$description');");
	
	$app['session']->set('user', $user);
	$app['session']->getFlashBag()->set('success', 'Description Added Successfully');
	
    return $app->redirect($app['base_url'].'/todo');
});


$app->match('todo/delete/{id}', function ($id) use ($app) {

    $array  = array('id'=>$id);
	$obDemo = new Demo();
	$todos   =  $obDemo->getDataSingle($app,$array,'todos',$array);
	$string1 = "\n(".$todos['user_id'].",'".$todos['description']."')";
	$string2 = "\n(".$todos['user_id'].",'".$todos['description']."'),"; 
	
    $obDemo->deleteData($app,$array,'todos',$array);	
	$finder = new Finder();
	$finder->files()->in('../resources');

	foreach ($finder as $file) {
		$contents = $file->getContents('resources/fixtures.sql');
	}
	
    $contents = str_replace($string1,'',$contents);
	$contents = str_replace($string2,'',$contents);
	$contents = str_replace(',,',',',$contents);	
	
	$contents = trim(trim($contents),',;').';';
	
	$fs = new Filesystem();
	$fs->dumpFile('../resources/fixtures.sql', $contents);
	
    $app['session']->getFlashBag()->set('success', 'Deleted Successfully');
    return $app->redirect($app['base_url'].'/todo');
});
