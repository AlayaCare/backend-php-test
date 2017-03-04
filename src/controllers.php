<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


/**
 * Retrieve and display the TODO list
 * If there is an issue in parameters, pass it to twig to gracefully display it on frontend and help user
 *
 * @param  Array    $app        Silex $app
 * @param  String   $API_error  [Optional] If set, pass an error message to display on frontend
 * @return The twig template
 */
function displayTodoListPage($app, $API_error = null) {
  if (null === $user = $app['session']->get('user')) {
      return $app->redirect('/login');
  }

  $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
  $todos = $app['db']->fetchAll($sql);

  return $app['twig']->render('todos.html', [
      'todos' => $todos,
      'error' => $API_error
  ]);
}



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

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        return displayTodoListPage($app);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {


    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // If no description provided, set a usefull error message and redirect to the todo page
    if (empty($description)) {
      return displayTodoListPage($app, 'Please add a description.');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});
