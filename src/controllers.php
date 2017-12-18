<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});

$app->match('/login', function (Request $request) use ($app) {
    
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $db = new DB($app);
        $user = $db->table('users')
                    ->select()
                    ->where(['username', '=', "'{$username}'"])
                    ->where(['password', '=', "'{$password}'"])
                    ->find();

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


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $db = new DB($app);

    if ($id){
        $todo = $db->table('todos')->findById($id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $params = $request->query->all();
        $page = (isset($params['page']) && $params['page'] > 1) ? $params['page'] : 1;
        //$max = (isset($params['max']) && $params['max'] > 1) ? $params['max'] : 1; // default
        $max = 1;

        $todos = $db->table('todos')
                    ->select()
                    ->where(['user_id', '=', $user['id']]);

        $pages_total = ceil(count($todos->findAll()) / $max);
        $offset = ($page - 1) * $max;

        $todos = $todos->paginate($offset, $max)->findAll();


        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'pages' => $pages_total,
            'page'  => $page
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

    /**************************
     *
     * TASK 1
     *
     **************************/
    if (isset($description) && !empty($description)) {
        $db = new DB($app);
        $db->table('todos')
            ->insert(['user_id' => $user_id, 'description' => "'{$description}'"])
            ->execute();

    } else {
        /**************************
         *
         * TASK 4.a
         *
         **************************/
        $app['session']->getFlashBag()->add('message', 'Cannot add empty description.');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $db = new DB($app);
    $db->table('todos')
        ->delete()
        ->where(['id', '=', $id])
        ->execute();


    /**************************
     *
     * TASK 4.b
     *
     **************************/
    $app['session']->getFlashBag()->add('message', 'Todo item has been deleted.');

    return $app->redirect('/todo');
});


/**************************
 *
 * TASK 2 + migration
 *
 **************************/
$app->match('/todo/update/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $completed = ($request->get('completed') != null) ?: 0;

    $db = new DB($app);
    $db->table('todos')
        ->update(['completed', $completed])
        ->where(['id', '=', $id])
        ->execute();

    $sql = "UPDATE todos SET completed = '$completed' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


/**************************
 *
 * TASK 3
 *
 **************************/
$app->match('/todo/{id}/json', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $db = new DB($app);
    $todo = $db->table('todos')
                ->select()
                ->where(['id', '=', $id])
                ->where(['user_id', '=', $user['id']])
                ->find();

    return $app->json($todo, Response::HTTP_OK)->setEncodingOptions(JSON_NUMERIC_CHECK);
});