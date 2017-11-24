<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Module\Generalmodel\Gmodel;
use Module\Generalmodel\Usermodel;
use Custom\Pagination;
use Custom\MyConstant;

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
		$userObj = new Usermodel($app);
		$user = $userObj->select_user($username, $password);
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


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    $user = login_check($app);

    if ($id){
		$user_id = "${user['id']}";
		$objGolb = new Gmodel($app, $user_id);
		$todo = $objGolb->get_todos_by_userid($id);
		if(empty($todo)) {
				return $app->redirect('/todo');
		}
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
		$user_id = "${user['id']}";
		$currentpage = !empty($request->query->get('currentpage')) ? (int)$request->query->get('currentpage') : 1;
		$retunPagination = custom_pagination($currentpage, $user_id, $app);
		$returnPagination = json_decode($retunPagination);
        return $app['twig']->render('todos.html', [
            'todos' => $returnPagination->todo, 'page_link' => $returnPagination->pagination
        ]);

    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
	$user = login_check($app);
	
	try {
		$user_id = $user['id'];
		$description = trim($request->get('description'));
		
		if(empty($description)) {
			$error = "Description field is required.";
			throw new Exception($error);
		} 
		else {
			$user_id = "${user['id']}";
			$objGolb = new Gmodel($app, $user_id);
			$successFlag = $objGolb->insert_todo($description);
			if(!empty($successFlag)) 
				$app['session']->getFlashBag()->add('success_message', "Todo is added successfully");
			else
				die("Something gone Wrong!");
		}
	} catch (Exception $e) {
		$app['session']->getFlashBag()->add('description_blank', $e->getMessage());
	}
	
	return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
	$user = login_check($app);
	$user_id = "${user['id']}";
	
	$objGolb = new Gmodel($app, $user_id);
	$returnFlag = $objGolb->delete_todo_by_id($id);
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

function custom_pagination($currentpage, $user_id, $app)
{
		$objGolb = new Gmodel($app, $user_id);
		$todos = $objGolb->get_todos_by_userid_new();
		$countvalue = count($todos);
		$rowsperpage = 2;
		$range = 3;
		$obj_pagination = new Pagination($rowsperpage, $currentpage , $countvalue); //current page 1
		$totalpages = $obj_pagination->get_total_page();
		
		if ($currentpage > $totalpages) {
			$currentpage = $totalpages;
		}
		if ($currentpage < 1) {
		    $currentpage = 1;
		} 
		
		$offset = $obj_pagination->offset();
		$objGolb = new Gmodel($app, $user_id);
		$todos = $objGolb->get_todos_by_userid_withlimit($offset, $rowsperpage);
		$first_prev = first_and_pre($currentpage);
		$displayPageNumber = pagenumber($currentpage, $range, $totalpages);
		$displayNextLast = next_and_last($currentpage, $totalpages);
		
		$paginationLink = $first_prev.$displayPageNumber.$displayNextLast;
		
		$pagination = array(array('link'=>$paginationLink));
		return json_encode(array('todo'=>$todos,'pagination'=>$pagination));
		
}

function first_and_pre($currentpage)
{
	$returnFPValue = "
	<nav aria-label=\"Page navigation example\">
		  <ul class=\"pagination\">";
	if ($currentpage > 1) {
	   $returnFPValue .= "<li class=\"page-item\"> <a class=\"page-link\" href='".BASE_URL."/todo?currentpage=1'>First</a> ";
	   $prevpage = $currentpage - 1;
	   $returnFPValue .= "<li class=\"page-item\"> <a class=\"page-link\" href='".BASE_URL."/todo?currentpage=$prevpage'>Prev</a> ";
	} 
	return $returnFPValue;
}
function pagenumber($currentpage, $range, $totalpages) 
{ 
	$returnPageNumberValue = "";
	for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
	   if (($x > 0) && ($x <= $totalpages)) {
		  if ($x == $currentpage) {
			 $returnPageNumberValue .=  "<li class=\"page-item active\"> <a class=\"active\" href='#'>$x </a> ";
		  } else {
			 $returnPageNumberValue .= "<li class=\"page-item\"> <a class=\"page-link\" href='".BASE_URL."/todo?currentpage=$x'>$x</a> ";
		  } 
	   }  
	}
	return $returnPageNumberValue;	
}

function next_and_last($currentpage, $totalpages)
{   
	$returnNextLast = "";
	if ($currentpage != $totalpages) {
	   $nextpage = $currentpage + 1;
	   $returnNextLast .= "<li class=\"page-item\"> <a class=\"page-link\" href='".BASE_URL."/todo?currentpage=$nextpage'>Next</a> ";
	   $returnNextLast .= "<li class=\"page-item\"> <a class=\"page-link\" href='".BASE_URL."/todo?currentpage=$totalpages'>Last</a> ";
	} 
	$returnNextLast .= "</ul>
					</nav>";
	return $returnNextLast;
}

