<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
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
    $todo = array(
        'description' => $request->get('description'),
    );

    $constraint = new Assert\Collection(array(
        'description' => new Assert\NotBlank(),
    ));
    $errors = $app['validator']->validate($todo, $constraint);

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $app['session']->getFlashBag()->add('errors', $error->getPropertyPath().' '.$error->getMessage());
        }
    } else {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '{$todo['description']}')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('success', 'The todo was successfully created');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('success', 'The todo was successfully removed');

    return $app->redirect('/todo');
});


$app->post('/todo/completed/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'The todo was marked as completed!');

    return $app->redirect('/todo');
});


$app->post('/todo/notcompleted/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed = 0 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'The todo was marked as NOT completed!');

    return $app->redirect('/todo');
});


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app->json($todo);
    } else {
        $app['session']->getFlashBag()->add('errors', 'Error to generate the Json');
        return $app->redirect('/todo');
    }
})
->value('id', null);