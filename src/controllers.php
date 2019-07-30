<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation;
use Parsedown;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    $Parsedown = new Parsedown();
    return $app['twig']->render('index.html', array(
        'readme' => $Parsedown->text(file_get_contents('../README.md'))
    ));
});

//login
$app->match('/login', function (Request $request) use ($app) {
    if (null !== $user = $app['session']->get('user')) {
        return $app->redirect('/todo-list');
    }

    $username = $request->get('username');
    $password = sha1($request->get('password'));

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo-list');
        }
    }

    return $app['twig']->render('login.html', array());
});

//logout
$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

//get todos list template
$app->get('/todo-list', function () use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    return $app['twig']->render('todo-list.html');
});

//get todos ajax
$app->get('/todos/{pid}', function ($pid) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        $error = array('message' => 'The user was not found.');
        return $app->json($error, 404);
    }

    if (is_numeric($pid) && is_int((int)$pid)) {
        $pid = (int)$pid;
    } else {
        $pid = 1;
    }

    $itemsPerPage = 10;
    $offset = $itemsPerPage * ($pid - 1);

    $totalCountSQL = "SELECT count(*) FROM todos WHERE user_id = '${user['id']}'";
    $totalCount = (int)$app['db']->fetchColumn($totalCountSQL);

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT {$itemsPerPage} OFFSET {$offset}";
    $todos = $app['db']->fetchAll($sql);

    if ($pid > ceil($totalCount / $itemsPerPage)) {
        $pid = ceil($totalCount / $itemsPerPage);
    }

    $pagination = array(
        'totalCount' => $totalCount,
        'totalPages' => ceil($totalCount / $itemsPerPage),
        'currentPage' => $pid,
        'currentPageItems' => count($todos),
        'itemsPerPage' => $itemsPerPage
    );

    return $app->json(
        array(
            'todos' => $todos,
            'pagination' => $pagination
        )
    );
})->value('pid', '1');

//get singleTodo
$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', array(
            'todo' => $todo
        ));
    } else {
        return $app->redirect('/todo-list');
    }
})
->value('id', null);

//get singleTodo with JSON format
$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        $error = array('message' => 'The user was not found.');
        return $app->json($error, 404);
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app->json($todo);
    } else {
        return $app->json(array('message' => "Todo not found"), 404);
    }
})
->value('id', null);

//add
$app->post('/todo/add/{description}', function ($description) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = filter_var(trim($description), FILTER_SANITIZE_STRING);

    // if not empty string
    if (!empty($description)) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $execResult = $app['db']->executeUpdate($sql);

        if ($execResult === 1) {
            $pid = 1;
            $itemsPerPage = 10;
            $offset = $itemsPerPage * ($pid - 1);

            $totalCountSQL = "SELECT count(*) FROM todos WHERE user_id = '${user['id']}'";
            $totalCount = (int)$app['db']->fetchColumn($totalCountSQL);

            $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT {$itemsPerPage} OFFSET {$offset}";
            $todos = $app['db']->fetchAll($sql);

            if ($pid > ceil($totalCount / $itemsPerPage)) {
                $pid = ceil($totalCount / $itemsPerPage);
            }

            $pagination = array(
                'totalCount' => $totalCount,
                'totalPages' => ceil($totalCount / $itemsPerPage),
                'currentPage' => $pid,
                'currentPageItems' => count($todos),
                'itemsPerPage' => $itemsPerPage
            );

            return $app->json(array(
                'type' => "success",
                'message' => "Todo: {$description} was successfully added.",
                'todos' => $todos,
                'pagination' => $pagination
            ));
        } else {
            return $app->json(array(
                'type' => "danger",
                'message' => "Failed to add new todo."
            ));
        }
    } else {
        return $app->json(array(
            'type' => "danger",
            'message' => "Please fill the description."
        ));
    }
});

//delete
$app->match('/todo/delete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $pid = 1;
    $itemsPerPage = 10;
    $offset = $itemsPerPage * ($pid - 1);

    $totalCountSQL = "SELECT count(*) FROM todos WHERE user_id = '${user['id']}'";
    $totalCount = (int)$app['db']->fetchColumn($totalCountSQL);

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT {$itemsPerPage} OFFSET {$offset}";
    $todos = $app['db']->fetchAll($sql);

    if ($pid > ceil($totalCount / $itemsPerPage)) {
        $pid = ceil($totalCount / $itemsPerPage);
    }

    $pagination = array(
        'totalCount' => $totalCount,
        'totalPages' => ceil($totalCount / $itemsPerPage),
        'currentPage' => $pid,
        'currentPageItems' => count($todos),
        'itemsPerPage' => $itemsPerPage
    );

    return $app->json(array(
        'type' => "success",
        'message' => "Todo #{$id} was successfully deleted.",
        'id' => $id,
        'todos' => $todos,
        'pagination' => $pagination
    ));
});

//complete
$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed = '1' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->json(array(
        'type' => "success",
        'message' => "Todo #{$id} was completed.",
        'id' => $id
    ));
});

//undo
$app->match('/todo/undo/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed = '0' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->json(array(
        'type' => "success",
        'message' => "Todo #{$id} was restored.",
        'id' => $id
    ));
});