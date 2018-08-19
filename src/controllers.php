 <?php
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

// HOME PAGE
 $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));
      return $twig;
 }));
 
 
 $app->get('/', function () use ($app) {
     return $app['twig']->render('index.html', [
         'readme' => file_get_contents('..\README.md'),
     ]);
 });
 
// LOGIN FUNCTION
$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
     if ($username) {

       $user = ORM::getUser($app, $username, $password);
        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
            }
        }
     return $app['twig']->render('login.html', array());
});
// LOGOUT FUNCTION
 $app->get('/logout', function () use ($app) {
     $app['session']->set('user', null);
     return $app->redirect('/');
 });
 
// TODO OR TODOS VIEW PAGE
 $app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
   // TODO SECTION 
    if ($id){
         $todo = ORM::getTodo($app, $id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } 
	else {
     // TODOS SECTION
        if(isset($_GET['page']) && is_numeric($_GET['page']) ){
            $current_page = intval( $_GET['page']);
        } 
		else{
            $current_page = 1;
        }
        $page_size = 3;
        $offset = ($current_page-1) * $page_size;
        $total_todos = ORM::getTotal($app, $user['id']);
        $pagination = pagination($current_page,$page_size, $total_todos);
		$todos = ORM::getTodos($app, $user['id'], $offset,$page_size);
     
         return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'next' => $pagination['next'],
            'previous' => $pagination['previous'],
            'pages' => $pagination['pages']
         ]);
    }
})
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
	if (null === $user = $app['session']->get('user')){
		return $app->redirect('/login');
    }
    $user_id = $user['id'];
    $description = $request->get('description');
	// TASK 1 - As a user I can't add a todo without a description
    if($description != null){	
		ORM::addTodo($app, $description, $user_id);
		//TASK 4 - ADD FUNCTION WITH FLASH MESSAGE
        $app['session']->getFlashBag()->add("notice", "Todo has been Added!");
    }
	else{
       $app['session']->getFlashBag()->add("warning", "You need to write a description to add a new Todo");
    }
    
    return $app->redirect('/todo');
});

// DELETE FUNCTION
 $app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    if(ORM::delete_todo($app, $id, $user)){
		//TASK 4 - DELETE FUNCTION WITH FLASH MESSAGE
        $app['session']->getFlashBag()->add("warning", "Todo has been Deleted!");
    }
    return $app->redirect('/todo');
});

//MARK AS COMPLETED FUNCTION
 $app->match('/todo/completed/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	//TASK 2 - MARK TODO AS COMPLETED
    ORM::markCompleted($app, $id, $user);
    return $app->redirect('/todo');
});

//TASK 3 - TODOS IN JSON FORMAT
 $app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
	if ($id){
		$user_id = $user['id'];
		$total_json = ORM::getTotal($app, $user['id']);
		$todos = ORM::getTodos($app, $user['id'],0,$total_json);
		while ($id < $total_json){
		echo '<pre>'; 
		print_r(json_encode($todos, JSON_PRETTY_PRINT));
		echo '</pre>';
		return $app['twig']->render('json.html', ['todo' => $todos,
        ]);
		}
	}
	
});

// TASK 6 - PAGINATION FUNCTION
 function pagination($current_page,$page_size,$total_todos){ 
    $number_of_pages = ceil($total_todos / $page_size);
	$limit = 3;
	$start = ((($current_page - $limit) > 1) ? $current_page - $limit : 1);
	$end = ((($current_page + $limit) < $number_of_pages) ? $current_page + $limit : $number_of_pages);
    $pages = array();
	if($number_of_pages > 1 && $current_page <= $number_of_pages){
		for($i = $start; $i <= $end; $i++){
			$link_text = $i;
			$class = "";
				if($i == $current_page){
					$class = "active";
				}
			array_push($pages, array('link_text'=> $link_text , 'link' => '?page=' . $i, 'class' => $class));
		}
	}
	$previous = null;
    if(($current_page - 1)>0){
        $previous = '?page='.($current_page - 1);
    }
    $next = null;
    if($total_todos > ($current_page * $page_size)){
        $next = '?page='.($current_page + 1);
    }	
    return array('pages' => $pages,'current_page' => $current_page, 'previous' => $previous, 'next' => $next, 'number_of_pages' => $number_of_pages);
}