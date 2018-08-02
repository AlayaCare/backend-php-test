<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND completed!=1";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
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

    if(($user_id !=null) and ($description !=null)){
        try{
            $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
            $app['db']->executeUpdate($sql);
            $app['session']->getFlashBag()->add('success', 'Todo added successfully');
        }
        catch(Exception $e){
            $app['session']->getFlashBag()->add('error', 'There has been an error. Try again.');
        }
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    try{
        $sql = "DELETE FROM todos WHERE id = '$id'";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('success', 'Todo deleted');
    }
    catch(Exception $e){
        $app['session']->getFlashBag()->add('error', 'There has been an error. Try again.');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/mark/{id}', function ($id) use ($app) {
    try{
        $now = date_create('now')->format('Y-m-d H:i:s');
        $sql = "UPDATE todos SET completed=1, date_completed='" . $now . "' WHERE id = '$id'";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('success', 'Todo completed');
     }
    catch(Exception $e){
        $app['session']->getFlashBag()->add('error', 'There has been an error. Try again.');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {

    $sql = "SELECT id, user_id, description FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);
    return json_encode($todo);
});
