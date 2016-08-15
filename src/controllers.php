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
        $sql = "SELECT * FROM todos LEFT JOIN todosdone ON todosdone.todo_id=todos.id WHERE id = '$id'";
        //$sql = "SELECT * FROM todos LEFT JOIN todosdone ON todosdone.todo_id=todos.id WHERE user_id = '${user['id']}'";
        $todo = $app['db']->fetchAssoc($sql);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos LEFT JOIN todosdone ON todosdone.todo_id=todos.id WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        // TODO: Pagination

        return $app['twig']->render('todos.html', [
            'todos' => $todos
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
    if(!empty(str_replace(' ','',$description))) { // Validate field. TODO: use Silex Validator Service
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $request->getSession()->getFlashBag()->add('success', 'Item added.');
    } else {
        $request->getSession()->getFlashBag()->add('error', 'Please include a description.');
    }
    return $app->redirect('/todo');
});

$app->post('/todo/done/{id}',  function (Request $request,$id) use ($app) {
    $sql = "INSERT INTO todosdone (todo_id) VALUES ('$id')";
    $app['db']->executeUpdate($sql);
    $request->getSession()->getFlashBag()->add('success', 'Item done.');
    return $app->redirect('/todo');
});
$app->match('/todo/delete/{id}', function (Request $request,$id) use ($app) {
    $sql_1 = "DELETE FROM todosdone WHERE todo_id = '$id' ";
    $app['db']->executeUpdate($sql_1);
    $sql_2 = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql_2);
    $request->getSession()->getFlashBag()->add('success', 'Item deleted.');
    return $app->redirect('/todo');
});
$app->post('/todo/undone/{id}',  function (Request $request,$id) use ($app) {
    $sql = "DELETE FROM todosdone WHERE todo_id = '$id'";
    $app['db']->executeUpdate($sql);
    $request->getSession()->getFlashBag()->add('success', 'Item undone.');
    return $app->redirect('/todo');
});
$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    if ($id){
        $user_id = $user['id'];
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);
        $json = '';
        foreach($todo as $k => $p){
            $json .= ', '.$k.': '.$p;
        }
        return '{'.substr($json,2).'}';
    }
});

