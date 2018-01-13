<?php

use Symfony\Component\HttpFoundation\Request;
use Kilte\Pagination\Pagination;
// TODO change mode to Production from Development

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
        $user = User::getOne($username, $password);

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

    // Task 6
    $todos = Todo::getAll($user['id']);

    // Task 5
    $totalItems = count($todos);
    $pagination = new Pagination($totalItems, $page, 10);
    $offset = $pagination->offset();
    $limit = $pagination->limit();
    $listing = array_slice($todos, $offset, $limit);
    $pages = $pagination->build();
    $currPage = $pagination->currentPage();
    $prevPage = $currPage-1;
    $lastPage = $pagination->totalPages();

    if ($currPage==0||$prevPage==0||$lastPage==0) {
        $currPage=1;
        $prevPage=1;
        $lastPage=1;
    }

    $app['session']->set('currentPage', $currPage);
    $app['session']->set('previousPage', $prevPage);
    $app['session']->set('lastPage', $lastPage);
    $app['session']->set('totalItems', $totalItems);
    $app['session']->set('itemsPerPage', $limit);

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


$app->match('/todo/delete/{id}', function ($id, Request $request) use ($app) {
    if($request->getMethod() == 'GET') {
        $app->abort(404, "Unable to delete Task: $id.");
    }

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        // Task 6
        $result = Todo::delete($id, $user['id']);

        // Task 4
        if ($result) {
            $app['session']->getFlashBag()->add('success', 'Todo deleted!');
            $app['session']->set('totalItems', $app['session']->get('totalItems') - 1);

            if (strpos($app['session']->get('currentPage'), "todo") !== false) {
                return $app->redirect('/todo/page/1');
            }

            if ($app['session']->get('totalItems') % $app['session']->get('itemsPerPage') > 0) {
                return $app->redirect('/todo/page/' . $app['session']->get('currentPage'));
            }

            if ($app['session']->get('totalItems') % $app['session']->get('itemsPerPage') == 0) {
                return $app->redirect('/todo/page/' . $app['session']->get('previousPage'));
            }
        } else {
            $app->abort(404, "Unable to delete Task: $id.");
        }
    }
});


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
        // Task 6
        $todo = Todo::getOne($id, $user['id']);

        if ($todo) {
            $app['session']->set('currentPage', "/todo/$id");
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        } else {
            $app->abort(404, "Todo: $id does not exist.");
        }

    } elseif ($id && $format=="json") {
        // Task 6
        $todo = Todo::getOne($id, $user['id']);

        if ($todo) {
            $app['session']->set('currentPage', "/todo/$id/json");
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

//Test cases
/*
Add blank description on page 1
Add on page 1 where there are no items
Add on page 1 when # of items <= 10-1
Add on page 1 when # of items = 10
*/
$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // Task 1
    if (trim($description) == "") {
        $app['session']->getFlashBag()->add('error', 'Todo not added. Description cannot be empty.');

        if ($app['session']->get('currentPage') == 1) {
            return $app->redirect('/todo/page/1');
        }

        return $app->redirect($app['session']->get('currentPage'));
    }

    // Task 6
    $todo = new Todo($user_id, $description);
    $result = $todo->save();

    // Task 4
    if ($result) {
        $app['session']->getFlashBag()->add('success', 'Todo added!');
        $app['session']->set('totalItems', $app['session']->get('totalItems') + 1);
    } else {
        $app->abort(404, "Unable to add Task: $description.");
    }

    if ($app['session']->get('totalItems') % $app['session']->get('itemsPerPage') == 1) {
        return $app->redirect('/todo/page/' . ($app['session']->get('lastPage')+1));
    }

    return $app->redirect('/todo/page/' . $app['session']->get('lastPage'));
});


// Task 2
$app->post('/todo/update/{id}/{status}', function ($id, $status) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id && ($status == 0 || $status == 1)) {
        // Task 6
        $result = Todo::update($id, $user['id'], $status);

        if ($result) {
            $app['session']->getFlashBag()->add('success', 'Todo status updated!');
        } else {
            $app->abort(404, "Unable to update Task: $id with Status: $status.");
        }
    }

    if (strpos($app['session']->get('currentPage'), "todo") !== false) {
        return $app->redirect($app['session']->get('currentPage'));
    }

    return $app->redirect('/todo/page/' . $app['session']->get('currentPage'));
});