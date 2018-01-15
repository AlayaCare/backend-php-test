<?php

use Omar\Models\Todo;
use Omar\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    $username = trim(filter_var($request->get('username'),FILTER_SANITIZE_STRING));
    $password = trim(filter_var($request->get('password'), FILTER_SANITIZE_STRING));

    if ($username && $password) {
        $app['session']->getFlashBag()->clear();

        $user = new User();
        $loginResult = $user->login($username, $password);

        if (!empty($loginResult)){
            $app['session']->set('user', $loginResult);
            return $app->redirect('/todo');
        } else {
            $app['session']->getFlashBag()->add('login_message', "Username or password does not match!");
        }
    }else {
        $app['session']->getFlashBag()->add('login_message', "Username or password must not empty!");
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    $app['session']->getFlashBag()->clear();
    $app['session']->getFlashBag()->add('login_message', "Logout!");
    return $app->redirect('/');
});

$app->get('/todo', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $page = (int) $request->get('page');
    if($page == 0) {
        $page = 1;
    }
    $user = $app['session']->get('user');
    $userId = $user['id'];

    /*** pagination*/
    $limit = (int) ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 10;
    $page  = (int) ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;

    $todo  = new Todo();
    $result = $todo->getAll($userId, $limit, $page);
    /**paination**/

    return $app['twig']->render('todos.html', [
        'todos' => $result['todos']->data,
        'link' => $result['link']
    ]);
});


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todoObj = new Todo();
        $result = $todoObj->getTodoById(intval($id), $user['id']);

        if($result != null) {
            return $app['twig']->render('todo.html', [
                'todo' => $result,
            ]);
        }
    }
    $app['session']->getFlashBag()->clear();
    $app['session']->getFlashBag()->add('message', "You don't have access on this todo or it does not exist");
    return $app->redirect('/todo');
})->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = trim(filter_var($request->get('description'), FILTER_SANITIZE_STRING));
    if($description != "") {
        try {
            $todo = new Todo();
            $insertedId = $todo->add($user['id'], $description);
            $app['session']->getFlashBag()->clear();
            if($insertedId >0) {
                $app['session']->getFlashBag()->add('message', 'Todo has been added successfully.');
                return $app->redirect('/todo');
            } else {
                $app['session']->getFlashBag()->add('message', 'There is an error occurred. Please try again.');
                return $app->redirect('/todo');
            }
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

    } else {
        $app['session']->getFlashBag()->add('message', 'Your todo description filed must not be empty. ');
        return $app->redirect('/todo');
    }
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $app['session']->getFlashBag()->clear();

    $todo = new Todo();

    if($todo->delete($id, $user['id']) != null) {
        $app['session']->getFlashBag()->add('message', "You don't have access on this todo.");
    } else {
        $app['session']->getFlashBag()->add('message', 'Todo has been deleted.');
    }

    return $app->redirect('/todo');
});

$app->post('/todo/mark/{id}', function (Request $request, $id) use ($app){
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
     }

    $todoObj = new Todo();
    $todoObj->update($id, $user['id']);
    $app['session']->getFlashBag()->clear();
    $app['session']->getFlashBag()->add('message', 'Todo has been marked as complete.');
    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todoObj = new Todo();
        $result = $todoObj->getTodoById(intval($id), $user['id']);

        if($result != null) {
            return json_encode($result);
        }
    }
    $app['session']->getFlashBag()->clear();
    $app['session']->getFlashBag()->add('message', "You don't have access on this todo or it does not exist.");
    return $app->redirect('/todo');
})->value('id', null);