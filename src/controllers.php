<?php

use Symfony\Component\HttpFoundation\Request;
use Kilte\Pagination\Pagination;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->match('/', function () use ($app) {
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


$app->match('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/page/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
    $todos = $app['db']->fetchAll($sql);
    $totalItems = count($todos);
    $pagination = new Pagination($totalItems, $page, 10);
    $offset = $pagination->offset();
    $limit = $pagination->limit();
    $listing = array_slice($todos, $offset, $limit);
    $pages = $pagination->build();

    return $app['twig']->render('todos.html', [
        'todos' => $listing,
        'pages' => $pages,
        'current' => $pagination->currentPage()
    ]);
})
    ->value('page', 1)
    ->convert(
        'page',
        function ($page) {
            return (int) $page;
        }
    );


// Task 3
/*
Test cases:
/todos --> displays all todos
/todos/ --> displays all todos
/todos/id (id exists) --> displays task with given id
/todos/id (id NOT exists) --> displays 404
/todos/id/json (id exists) --> displays json of task with given id
/todos/id/json (id NOT exists) --> displays 404
/todos/id/gibberish (whether id exists or NOT exists) --> displays all todos
/todos/gibberish --> displays 404
*/
$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id && empty($format)) {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($todo) {
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        } else {
            $app->abort(404, "Todo: $id does not exist.");
        }

    } elseif ($id && $format=="json") {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($todo) {
            return $app->json($todo);
        } else {
            $app->abort(404, "Todo: $id does not exist.");
        }

    } else {
        return $app->redirect('/todo/page/1');
    }
})
    ->value('id', null)
    ->value('format', null);


$app->match('/todo/', function () use ($app) {
    return $app->redirect('/todo');
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // Task 1
    if (trim($description) == "") {
        $app['session']->getFlashBag()->add('error', 'Todo not added. Description cannot be empty.');
        return $app->redirect('/todo');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ($user_id, ?)";
    $result = $app['db']->executeUpdate($sql, array($description));

    // Task 4
    if ($result) {
        $app['session']->getFlashBag()->add('success', 'Todo added!');
    } else {
        $app->abort(404, "Unable to add Task: $description.");
    }

    return $app->redirect('/todo');
});


// Task 2
$app->post('/todo/update/{id}/{status}', function ($id, $status) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id && ($status == 0 || $status == 1)) {
        $sql = "UPDATE todos SET status = ? WHERE id = ?";
        $result = $app['db']->executeUpdate($sql, array((bool) $status, (int) $id));

        if ($result) {
            $app['session']->getFlashBag()->add('success', 'Todo status updated!');
        } else {
            $app->abort(404, "Unable to update Task: $id with Status: $status.");
        }
    }

    return $app->redirect('/todo');
});


$app->post('/todo/delete/{id}', function ($id) use ($app) {
    $sql = "DELETE FROM todos WHERE id = ?";
    $result = $app['db']->executeUpdate($sql, array((int) $id));

    // Task 4
    if ($result) {
        $app['session']->getFlashBag()->add('success', 'Todo deleted!');
        return $app->redirect('/todo');
    } else {
        $app->abort(404, "Unable to delete Task: $id.");
    }
});