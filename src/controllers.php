<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Todo\InvalidCredentials;
use Todo\User;

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
    if ($request->isMethod(Request::METHOD_POST)) {
        try {
            //attempt to login a user using the provided credentials
            $userData = User::login(
                $request->get('username'),
                $request->get('password'),
                $app['db']
            );

            //store user data in session
            $app['session']->set('user', $userData);

            //redirect to reminder list
            return $app->redirect('/todo');
        } catch (InvalidCredentials $e) {
            //incorrect login credentials, prepare the error message
            $app['session']->getFlashBag()->add('problemMessage', $e->getMessage());

            //redirect to GET login to avoid re-posting with refresh
            return $app->redirect('/login');
        }
    }

    //display login form
    return $app['twig']->render('login.html');
})->method("GET|POST");


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $userData = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $loggedInUser = new User($userData, $app['db']);

    //obtain reminders
    $reminders = $loggedInUser->getReminders($id);

    if (null !== $id) {
        //a collection with one reminder expected
        if (empty($reminders)) {
            //invalid id provided return a 404 Not Found response
            return $app->abort(
                Response::HTTP_NOT_FOUND,
                'Unknown reminder id: ' . $id
            );
        }

        //render the reminder
        return $app['twig']->render('todo.html', ['todo' => reset($reminders)]);
    } else {
        //render all reminders
        return $app['twig']->render('todos.html', ['todos' => $reminders]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $userData = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $loggedInUser = new User($userData, $app['db']);

    //logged in user adds a reminder
    $loggedInUser->addReminder($request->get('description'));

    //flash message
    $app['session']->getFlashBag()->add('successMessage', 'Reminder created successfully.');

    return $app->redirect('/todo');
});


$app->post('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $userData = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $loggedInUser = new User($userData, $app['db']);

    //logged in user deletes a reminder
    $loggedInUser->deleteReminder($id);

    //flash message
    $app['session']->getFlashBag()->add('successMessage', 'Reminder deleted successfully.');

    return $app->redirect('/todo');
});