<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;

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
        $sql = "SELECT * FROM completed WHERE todo_id = '$id'";
        $completed = $app['db']->fetchAssoc($sql);

        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if (sizeof($completed) > 1 ){
        $todo += ["status" => 'done'];

        return $app['twig']->render('completed.html', [
            'todo' => $todo,
        ]);
        
        
        }else{    

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    
        }


    } else {
        
        $sql = "SELECT T.id as id, T.user_id AS user_id, T.description AS description, IF (C.id IS NOT NULL, 'DONE', 'todo') AS `status`
        FROM todos AS T
        LEFT JOIN completed AS C ON T.id = C.todo_id
        WHERE T.user_id=${user['id']}";

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


    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) > 0) {
        return (string) 'Description'.$errors;
    } else {

        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
    
        return $app->redirect('/todo');
    }


});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/done/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    $sql = "INSERT INTO `completed` (`id`, `user_id`, `todo_id`) VALUES (NULL, '${user['id']}', '$id')";

    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->get('/todo/{id}/json', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    if ($id){

        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);
        return json_encode($todo);
    }
});