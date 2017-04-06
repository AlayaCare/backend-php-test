<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

boot();


$app->get('/', function () use ($app) {
    return twig()->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');  // TODO: Needs to be encrypted

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = db()->fetchAssoc($sql);

        if ($user) {
            session()->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return twig()->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    session()->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = session()->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = db()->fetchAssoc($sql);

    if ($format=='json') {
        return $app->json($todo);
    }

    return twig()->render('todo.html', [
        'todo' => $todo,
    ]);
});


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = session()->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = db()->fetchAssoc($sql);

        return twig()->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        /**
         * @todo a) this query fetches all records as it's not page-aware
         *          make this page-aware
         *       b) Currently the display is similar to Google's Keep
         *          it can be changed to a tabbed page with a tab each for
         *          Open, Completed and All todos.
         */
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' ORDER BY id DESC";
        $todos = db()->fetchAll($sql);

        $adapter = new ArrayAdapter($todos);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        /* A little dirty flashbag for errors! */
        $errors = [];
        if (session()->has('errors')) {
            $errors = session()->get('errors');
            session()->set('errors', null);
        }
        
        return twig()->render('todos.html', [
            'errors' => $errors,
            'todos_pager' => $pagerfanta
        ]);
    }
})->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = session()->get('user')) {
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
        session()->set('errors', $errors);
        return $app->redirect('/todo');
    } else {
        session()->set('errors', null);
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    db()->executeUpdate($sql);

    flashbag()->add('user.messages', 'Todo has been added.');
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    /**
     * User needs to be logged in to delete
     * and the todo should be owned by this user
     */
    if (null === $user = session()->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = " . $user['id'];
    db()->executeUpdate($sql);

    flashbag()->add('user.messages', 'Todo has been deleted.');
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
    if (null === $user = session()->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed_at = NOW() WHERE id = '$id' AND user_id = " . $user['id'];
    db()->executeUpdate($sql);

    flashbag()->add('user.messages', 'Todo has been checked.');
    return $app->redirect('/todo');
});


$app->match('/todo/uncheck/{id}', function ($id) use ($app) {
    /**
     * User needs to be logged in to update
     * and the todo should be owned by this user
     */
    if (null === $user = session()->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "UPDATE todos SET completed_at = NULL WHERE id = '$id' AND user_id = " . $user['id'];
    db()->executeUpdate($sql);

    flashbag()->add('user.messages', 'Todo has been unchecked.');
    return $app->redirect('/todo');
});
