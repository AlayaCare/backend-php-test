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
        'title' => 'Readme'
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    if ($username) {
        $sql = "SELECT id, username, password FROM users WHERE username = '$username'";
        $user = $app['db']->fetchAssoc($sql);

        // There shouldn't be duplicates to loop through if we checked for duplicate usernames at signup.
        if (count($user) > 0 && password_verify($password, $user["password"])){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', ['title' => 'Login']);
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todos/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND completed = 0";
    $allTodos = $app['db']->fetchAll($sql);

    $totalResults = count($allTodos);
    $totalPages = ceil($totalResults/$app['nbPerPage']);
    $nbrows = $app['nbPerPage'];

    if (is_numeric($page)) {
        if ($page > $totalPages){
            // Can we rewrite the URL? Maybe I should redirect.
            $page = $totalPages;
        }
        $firstResult = ($page-1)*$app['nbPerPage'];
    } else {
        // Can we rewrite the URL? Maybe I should redirect.
        $page = $totalPages;
        // Move to the redirection after adding (instead of redirecting to "last")? Means making another query over there.
        $firstResult = ((ceil($totalResults/$app['nbPerPage'])-1)*$app['nbPerPage']);
    }

    // Make a subquery from $allTodos
    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND completed = 0 limit $firstResult, $nbrows";
    $todos = $app['db']->fetchAll($sql);

    $messages = $app['session']->getFlashBag()->all();

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'title' => 'Todos',
        'messages' => $messages,
        'page' => $page,
        'totalPages' => $totalPages,
    ]);
})
->value('page', 1);


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '${user['id']}' AND completed = 0";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
            'title' => $todo["description"],
        ]);
    } else {
        return $app->redirect('/todos/1');
    }
})
->value('id', null);


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '${user['id']}'";
        $todo = $app['db']->fetchAssoc($sql);

        echo json_encode($todo);
        return false;
    } else {
        return false;
    }
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    if ($description) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);

        $app['session']->getFlashBag()->add('success', 'Todo added.');
    } else {
        $page = 1;
        $app['session']->getFlashBag()->add('warning', 'You cannot add a todo without a description.');
    }

    return $app->redirect('/todos/last');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '${user['id']}'";
    $app['db']->executeUpdate($sql);
    
    $app['session']->getFlashBag()->add('success', 'Todo deleted.');

    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed = 1 WHERE id = '$id' AND user_id = '${user['id']}'";
    $app['db']->executeUpdate($sql);
    
    $app['session']->getFlashBag()->add('success', 'Todo completed.');

    return $app->redirect('/todo');
});