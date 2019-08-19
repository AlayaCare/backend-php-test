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
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

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


$app->get('/todo/{id}/{format}', function (Request $request, $id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = ?";
        $todo = $app['db']->fetchAssoc($sql, [$id]);

        if ($format === 'json') {
            return $app->json($todo, Response::HTTP_OK)
                ->setEncodingOptions(JSON_NUMERIC_CHECK);
        }
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $limit = 5;
        $page = $request->get('page', 1) ;
        $start = $limit * ($page - 1);

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM todos WHERE user_id = ? LIMIT ?, ?";
        $todos = $app['db']->fetchAll(
            $sql,
            [$user['id'], $start, $limit],
            [\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT]
        );

        $totalPages = ceil($app['db']->fetchColumn('SELECT FOUND_ROWS()') / $limit);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    }
})
->value('id', null)
->value('format', null)
;


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    if ($description !== '') {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $app['db']->executeUpdate($sql, [
            $user_id, $description
        ]);
        $app['session']->getFlashBag()->add('info', 'You have added an item.');
    } else {
        $app['session']->getFlashBag()->add('error', 'A description cannot be empty.');
    }

    return $app->redirect('/todo');
});

$app->post('/todo/done/{id}', function ($id) use ($app) {
    $sql = "UPDATE todos SET status = ? WHERE id = ?";
    $app['db']->executeUpdate($sql, [
        'done', $id
    ]);
    $app['session']->getFlashBag()->add('info', 'You marked item ' . $id . ' as done');

    return $app->redirect('/todo#' . $id);
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = ?";
    $app['db']->executeUpdate($sql, [$id]);
    $app['session']->getFlashBag()->add('info', 'You have delete item ' . $id);

    return $app->redirect('/todo');
});
