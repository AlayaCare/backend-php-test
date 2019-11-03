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
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

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

// get a single todo or list todos
$app->get('/todo/{id}/{type}', function (Request $request, $id, $type) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
        $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);

        if ('json' == $type) {
            return $app->json([
                'id' => $todo['id'],
                'user_id' => $todo['user_id'],
                'description' => $todo['description']
            ]);
        }

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {

        $page = $request->query->get('page');
        if (!isset($page)) {
            $page = 1;
        }

        $perPage = (int) ($app['config']['paginator']['per_page'] ?? 2);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT COUNT(*) FROM todos WHERE user_id = ?";
        $count = $app['db']->fetchColumn($sql,[$user['id']]);

        $sql = "SELECT * FROM todos WHERE user_id = ? LIMIT ?, ?";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, (int) $user['id'], \Doctrine\DBAL\ParameterType::INTEGER);
        $stmt->bindValue(2, (int) $offset, \Doctrine\DBAL\ParameterType::INTEGER);
        $stmt->bindValue(3, $perPage, \Doctrine\DBAL\ParameterType::INTEGER);
        $stmt->execute();

        $todos = $stmt->fetchAll();

        $url = $request->getBaseUrl();

        $paginator = new \Utils\Paginator($count, $page, $perPage, $url);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'paginator' => $paginator
        ]);
    }
})
    ->value('id', null)
    ->value('type', 'html');


// add a todo
$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // validate description
    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) == 0) {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $app['db']->executeUpdate($sql, [$user_id, $description]);

        $app['session']->getFlashBag()->add('success', 'Added a todo successfully.');
    } else {
        $app['session']->getFlashBag()->add('error', 'Please type description.');
    }

    return $app->redirect('/todo');
});

// delete a todo
$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
    $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);

    if ($todo) {
        $sql = "DELETE FROM todos WHERE id = ? AND user_id = ?";
        $app['db']->executeUpdate($sql, [$id, $user['id']]);
        return $app->json([
            'message' => "Deleted a todo successfully."
        ],200);
    } else {
        return $app->json([
            'message' => "Could not find such todo."
        ], 404);
    }

});

// complete a todo
$app->match('/todo/complete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
    $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);

    if ($todo) {
        $sql = "UPDATE todos SET completed_at = ? WHERE id = ? AND user_id = ?";
        $app['db']->executeUpdate($sql,
            [
                (new DateTime())->format('Y-m-d H:i:s'),
                $id,
                $user['id']
            ]);
        return $app->json([
            'message' => "Completed a todo successfully."
        ],200);

    } else {
        return $app->json([
            'message' => "Could not find such todo."
        ], 404);
    }

});
