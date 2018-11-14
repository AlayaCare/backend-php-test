<?php

require 'dao.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Entity\Todo;
use Entity\User;

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
        $user = userLogin($app,$username,$password);
        if ($user) {
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

    if ($id) {
        $todo = getTodoById($app,$id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $todos = getTodoList($app);
        $page = 1;
        $lastPage = ceil($todos->count()/5);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'page' => $page,
            'lastPage' => $lastPage
        ]);
    }
})
->value('id', null);


$app->get('/todo/page/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todos = getTodoList($app,$page);
    $lastPage = ceil($todos->count()/5);
    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'page' => $page,
        'lastPage' => $lastPage
    ]);
})
->value('id', null);


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todo = getTodoById($app,$id);
    if ($todo) {
        return $app->json($todo);
    }
    return $app->json(null);
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if (null == $request->get('description') || '' == $request->get('description'))
    {
        $app['session']->getFlashBag()->add('danger', 'Error! You must inform a Description!');
    } else {
        if (addTodo($app,$request->get('description'))) {
            $app['session']->getFlashBag()->add('success', 'ToDo successfully added to your list!');
        
        } else {
            $app['session']->getFlashBag()->add('danger', 'Error! ToDo not added!');
        }
    }

    return $app->redirect('/todo');
});

$app->post('/todo/{id}/completed/{completed}', function ($id,$completed) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if(!changeTodoCompletion($app,$id,$completed)) {
        $app['session']->getFlashBag()->add('danger', 'Error! ToDo status not updated!');    
    } else {
        if ($completed == 1) {
            $app['session']->getFlashBag()->add('success', 'ToDo #'.$id.' marked as completed!');
        } else {
            $app['session']->getFlashBag()->add('info', 'ToDo #'.$id.' marked as pending!');
        }
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if(deleteTodo($app,$id)) {
        $app['session']->getFlashBag()->add('success', 'ToDo #'.$id.' successfully removed!');
    
    } else {
        $app['session']->getFlashBag()->add('danger', 'Error! ToDo #'.$id.' not removed!');    
    }

    return $app->redirect('/todo');
});