<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
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

        if ($user) {
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

$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    if ($format=='json') {
        return $app->json($todo);
    }

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);
});

$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND completed_at IS NULL";
        $todos = $app['db']->fetchAll($sql);

        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND completed_at IS NOT NULL";
        $checked_todos = $app['db']->fetchAll($sql);

        /* A little dirty flashbag for errors! */
        $errors = [];
        if ($app['session']->has('errors')) {
            $errors = $app['session']->get('errors');
            $app['session']->set('errors', null);
        }
        
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'checked_todos' => $checked_todos,
            'errors' => $errors,
        ]);
    }
})->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Input Validation
    $constraint = new Assert\Collection(array(
        'description' => new Assert\NotBlank(),
    ));

    /**
     * Doing this only because I'm new to Silex!
     */
    $errors = $app['validator']->validate($request->request->all(), $constraint);
    if (count($errors) > 0) {
        $app['session']->set('errors', $errors);
        return $app->redirect('/todo');
    } else {
        $app['session']->set('errors', null);
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('user.messages', 'Todo has been added.');
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    /**
     * User needs to be logged in to delete
     * and the todo should be owned by this user
     */
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = " . $user['id'];
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('user.messages', 'Todo has been deleted.');
    return $app->redirect('/todo');
});


/**
 * @todo These needs to be AJAXed to avoid too many page loads
 */
$app->match('/todo/check/{id}', function ($id) use ($app) {
    /**
     * User needs to be logged in to update
     * and the todo should be owned by this user
     */
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed_at = NOW() WHERE id = '$id' AND user_id = " . $user['id'];
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('user.messages', 'Todo has been checked.');
    return $app->redirect('/todo');
});

$app->match('/todo/uncheck/{id}', function ($id) use ($app) {
    /**
     * User needs to be logged in to update
     * and the todo should be owned by this user
     */
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed_at = NULL WHERE id = '$id' AND user_id = " . $user['id'];
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('user.messages', 'Todo has been unchecked.');
    return $app->redirect('/todo');
});
