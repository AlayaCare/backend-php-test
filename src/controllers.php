<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Kosinix\Paginator;
use Kosinix\Pagination;

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
            return $app->redirect('/todos');
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

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);
})
->value('id', null);


$app->get('/todos/{page}/{sort_by}/{sorting}', function (Request $request, $page, $sort_by, $sorting) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = 'SELECT COUNT(*) AS `total` FROM todos WHERE user_id='.$user['id'] ;
    $count = $app['db']->fetchAssoc($sql);
    $count = (int) $count['total'];

    /** @var \Kosinix\Paginator $paginator */
    $paginator =  $app['paginator']($count, $page);

    $sql = sprintf('SELECT * FROM todos WHERE user_id='. $user['id']. '
                    ORDER BY %s %s
                    LIMIT %d,%d',
            $sort_by, 
            strtoupper($sorting), 
            $paginator->getStartIndex(), 
            $paginator->getPerPage());

    $todos = $app['db']->fetchAll($sql);

    $pagination = new Pagination($paginator, $app['url_generator'], 'templates', $sort_by, $sorting);

    return $app['twig']->render('todos.html', array(
        'todos' => $todos,
        'pagination' => $pagination
    ));
})
->value('page', 1)
->value('sort_by', 'id')
->value('sorting', 'asc')
->assert('page', '\d+') // Numbers only
->assert('sort_by','[a-zA-Z_]+') // Match a-z, A-Z, and "_"
->assert('sorting','(\basc\b)|(\bdesc\b)') // Match "asc" or "desc"
->bind('templates');


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($todo) {
            if($todo['user_id'] === $user['id']) {
                return new JsonResponse($todo);
            } else {
                return new JsonResponse(array('error' => "You are not authorized to see this ToDo."));
            }      
        } else {
            return new JsonResponse(array('error' => 'No ToDo found with the id '. $id));
        } 
    } else {
        return new JsonResponse(array('error' => 'You need to provide an id.'));
    }
})
->value('id', null); 


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    if($description) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        
        if($app['db']->executeUpdate($sql)) {
            $app['session']->getFlashBag()->add('success', 'Success!! ToDo added to your list!');
        } else {
            $app['session']->getFlashBag()->add('error', 'Fail! ToDo couldn\'t be added to your list. Try again later!');
        }
    
    } else {
        $app['session']->getFlashBag()->add('error_messages', 'Error! A ToDo can\'t be created without a description.');
    }
    return $app->redirect('/todos');
});

$app->post('/todo/done/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET status = 1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todos');
});


$app->post('/todo/undone/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET status = 0 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todos');
}); 


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($todo) {
            if($todo['user_id'] === $user['id']) {
                $sql = "DELETE FROM todos WHERE id = '$id'";
                
                if ($app['db']->executeUpdate($sql)) {
                    $app['session']->getFlashBag()->add('info' , 'Success! The ToDo with id '. $id . ' was DELETED from your list.');
                } else {
                    $app['session']->getFlashBag()->add('error', 'Error! ToDo couldn\'t be deleted from your list. Try again later!');
                }

            } else {
                $app['session']->getFlashBag()->add('error' , "Error! You are not authorized to DELETE this ToDo.");
            }      
        } else {
            $app['session']->getFlashBag()->add('error' , 'Error! No ToDo found with the id '. $id);
        } 
    } else {
        $app['session']->getFlashBag()->add('error' , 'You need to provide an id.');
    }

    return $app->redirect('/todos');
});