<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Kosinix\Paginator;
use Kosinix\Pagination;
use Models\Todo;
use Models\User;

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
        $user = User::findByUsername($username, $app);

        if ($user && ($user['password'] === $password)) {
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

    $todo = Todo::findById($id, $app);

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);
})
->value('id', null);


$app->get('/todos/{page}/{sort_by}/{sorting}', function (Request $request, $page, $sort_by, $sorting) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $count = (int) Todo::countById($user, $app);

    /** @var \Kosinix\Paginator $paginator */
    $paginator =  $app['paginator']($count, $page);

    $todos = Todo::list($user, $sort_by, $sorting, $paginator, $app);

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
        $todo = Todo::findById($id, $app);

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
        if (Todo::create($user, $description, $app)) {
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

    Todo::updateDone($id, $app);

    return $app->redirect('/todos');
});


$app->post('/todo/undone/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    Todo::updateUndone($id, $app);

    return $app->redirect('/todos');
}); 


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todo = Todo::findById($id, $app);

        if ($todo) {

            if($todo['user_id'] === $user['id']) {

                if (Todo::delete($id, $app)) {
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