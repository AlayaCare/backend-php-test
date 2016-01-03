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


$app->get('/todo/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    //Number of todos per page defined in config.yml
    $todosPerPage = intval($app['config']['paginate']['todo_per_page'], 10);

    $sqlCount = 'SELECT count(*) AS nb FROM todos WHERE user_id ='. $user['id'];
    $recordCount = $app['db']->fetchAssoc($sqlCount);
    $totalTodos = $recordCount['nb'];

    $lastPage = ceil($totalTodos / $todosPerPage);
    $previousPage = ($page > 1) ? $page - 1 : 1;
    $nextPage = ($page < $lastPage) ? $page + 1 : $lastPage;

    $qb = $app['db']->createQueryBuilder();

    $qb->select('*');
    $qb->from('todos', 't');
    $qb->where('t.user_id = :userId');
    $qb->setParameter('userId', $user['id']);
    $qb->orderBy('t.id', 'ASC');
    $qb->setMaxResults($todosPerPage);
    $qb->setFirstResult(($page - 1) * $todosPerPage);

    $stmt = $qb->execute();
    $todos = $stmt->fetchAll();

    return $app['twig']->render('todos.html',
        array(
            'todos' => $todos,
            'lastPage' => $lastPage,
            'previousPage' => $previousPage,
            'currentPage' => $page,
            'nextPage' => $nextPage
        )
    );

})->value('page', 1);

$app->get('/todo/show/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        //Avoid id invalid
        if (!$todo) {
            return $app->abort(404);
        }

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    }

    return $app->abort(404);
});

//Get the json of a todo
$app->get('/todo/show/{id}/json', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($request->attributes->has('id')){
        $id = $request->get('id');
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        //Avoid id invalid
        if (!$todo) {
            return $app->json(null, 404);
        }

        return $app->json($todo, 200);
    }

    return $app->abort(404);
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    //we avoid to insert a description with only spaces
    $description = trim($description);

    //we avoid to create a new todo with a description empty
    if (!empty($description)) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);

        $app['session']->getFlashBag()->add('success', 'Todo "' . $description . '" added');
    } else {
        $app['session']->getFlashBag()->add('error', 'Error : Description is required');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'Todo #' . $id . ' deleted');

    return $app->redirect('/todo');
});

//Method used to mark a todo completed
$app->put('/todo/update/{id}', function (Request $request) use ($app) {

    if ($request->attributes->has('id') && $request->request->has('completed') ) {
        $completed = $request->request->get('completed');
        $id = $request->get('id');

        $sql = "UPDATE todos set completed = '$completed' WHERE id = '$id'";
        $app['db']->executeUpdate($sql);
    }

    return new Response('OK', 200);
});