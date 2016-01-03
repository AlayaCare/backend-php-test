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
        $user = $app['repository.user']->findOneBy(array('username' => $username, 'password' => $password));

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

    $totalTodos = $app['repository.todo']->count(array('user_id' => $user->getId()));

    $lastPage = ceil($totalTodos / $todosPerPage);
    $previousPage = ($page > 1) ? $page - 1 : 1;
    $nextPage = ($page < $lastPage) ? $page + 1 : $lastPage;
    $offset = ($page - 1) * $todosPerPage;

    $todos = $app['repository.todo']->findBy(
        array('user_id' => $user->getId()),
        array('id' => 'ASC'),
        $todosPerPage,
        $offset);

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
        $todo = $app['repository.todo']->find($id);

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
        $todo = $app['repository.todo']->find($id);

        //Avoid id invalid
        if (!$todo) {
            return $app->json(null, 404);
        }

        $data = array(
            'id' => $todo->getId(),
            'description' => $todo->getDescription(),
            'user_id' => $todo->getUserId(),
            'completed' => $todo->isCompleted()
        );

        return $app->json($data, 200);
    }

    return $app->abort(404);
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = $request->get('description');

    //we avoid to insert a description with only spaces
    $description = trim($description);

    //we avoid to create a new todo with a description empty
    if (!empty($description)) {
        $todo = new \Entity\Todo();
        $todo->setDescription($description);
        $todo->setUserId($user->getId());

        $app['repository.todo']->insert($todo);

        $app['session']->getFlashBag()->add('success', 'Todo "' . $description . '" added');
    } else {
        $app['session']->getFlashBag()->add('error', 'Error : Description is required');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $app['repository.todo']->deleteById($id);

    $app['session']->getFlashBag()->add('success', 'Todo #' . $id . ' deleted');

    return $app->redirect('/todo');
});

//Method used to mark a todo completed
$app->put('/todo/update/{id}', function (Request $request) use ($app) {

    if ($request->attributes->has('id') && $request->request->has('completed') ) {
        $app['repository.todo']->markCompleted($request->get('id'), $request->request->get('completed'));
    }

    return new Response('OK', 200);
});