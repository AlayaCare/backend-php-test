<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Todo;


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

$app->post('/todo', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
        $page = $request->get('page');
        $prepage = $request->get('prepage');
        $page = intval($page);
        $prepage = intval($prepage);
        if($page==0)
        {
            $page = 1;
        }
        if($prepage==0)
        {
            $prepage = 10;
        }
        $startRecord = ($page-1)*$prepage;
        $countSql = "SELECT count(*) as total FROM todos WHERE user_id = '${user['id']}'";
        $count = $app['db']->fetchAll($countSql);
        $totalrecords = intval($count[0]["total"]);
        $totalpages = ceil($totalrecords/$prepage);
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' limit $startRecord,$prepage";
        $todos = $app['db']->fetchAll($sql);
        $pageinfo = Array(
                        "totalrecords" =>$totalrecords,
                        "totalpages" => $totalpages,
                        "page"=>$page,
                        "prepage"=>$prepage,
                        "start"=>$startRecord+1,
                        "end"=>$startRecord+count($todos),
                            );

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'pageinfo' => $pageinfo
        ]);  
})
->value('id', null);


$app->get('/todo/{id}', function ($id) use ($app) {
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
        
        $page = 1;
        $prepage = 10;
        $startRecord = ($page-1)*$prepage;
        $countSql = "SELECT count(*) as total FROM todos WHERE user_id = '${user['id']}'";
        $count = $app['db']->fetchAll($countSql);
        $totalrecords = intval($count[0]["total"]);
        $totalpages = ceil($totalrecords/$prepage);
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' limit $startRecord,$prepage";
        $todos = $app['db']->fetchAll($sql);
        $pageinfo = Array(
                        "totalrecords" =>$totalrecords,
                        "totalpages" => $totalpages,
                        "page"=>$page,
                        "prepage"=>$prepage,
                        "start"=>$startRecord+1,
                        "end"=>$startRecord+count($todos),
                            );

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'pageinfo' => $pageinfo
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
    $errors = $app['validator']->validate(trim($description), new Assert\NotBlank());
     if (count($errors) > 0) {
        $app['session']->getFlashBag()->add('message', "The description should not be blank.");    
    } else {
        //$sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        //$app['db']->executeUpdate($sql); 
        $todo = new Todo();
        $todo->setUserId($user_id);
        $todo->setDescription($description);
        $todo->setStatus(0);
        $em = $app['orm.em'];
        $em->persist($todo);
        $em->flush();
         $app['session']->getFlashBag()->add('message', "New Todo $description has been added to your todo list");  
    }
     return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

     if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $em = $app['orm.em'];
    $todo = $em->find('\App\Entity\Todo', $id);
     if($todo->getUserId()==$user['id'])
    {
        $em->remove($todo); 
        $em->flush(); 
        $app['session']->getFlashBag()->add('message', "Todo No.$id has been deleted");
    }
    else
    {
          $app['session']->getFlashBag()->add('message', "You can't delete Todo No.$id");
    }
    return $app->redirect('/todo');
});

$app->post('/todo/changestatus/{id}', function (Request $request,$id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];
    $status = $request->get('status');
    $em = $app['orm.em'];
    $todo = $em->find('\App\Entity\Todo', $id);
     if($todo->getUserId()==$user['id'])
    {
        $todo->setStatus($status);
        $em->persist($todo); 
        $em->flush(); 
    }
     return $app->redirect('/todo/'.$id);
});

$app->match('/todo/{id}/json',function ($id) use ($app) {
     if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
        $em = $app['orm.em'];
        $todo = $em->find('\App\Entity\Todo', $id);

        if($todo->getUserId()==$user['id'])
        {
              $todoArray = Array("id"=>$todo->getId(),"user_id"=>$todo->getUserId(),"description"=>$todo->getDescription());
        }
        else
        {
            $todoArray = Array();
        }
        return json_encode($todoArray);
    

});