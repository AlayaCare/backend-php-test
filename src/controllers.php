<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
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

    if (null !== $id) {//id provided, display a single reminder

        //find the reminder
        $reminder = $loggedInUser->findReminder($id);

        if (empty($reminder)) {
            //invalid id provided return a 404 Not Found response
            return $app->abort(
                Response::HTTP_NOT_FOUND,
                'Unknown reminder id: ' . $id
            );
        }

        //render the reminder
        return $app['twig']->render('todo.html', ['todo' => $reminder]);
    } else {
        //prepare pagination params
        $page = $app['request']->get('page', 1);
        $totalCount = 0;
        $itemsPerPage = 10;

        //fetch reminders
        $reminders = $loggedInUser->getReminders($itemsPerPage, $page, $totalCount);

        //prepare paginator
        $paginator = new Paginator\Paginator($totalCount, $itemsPerPage, $page);

        //in case the page doesn't have any items to show, redirect to the last page
        if (empty($reminders) && $page > 1) {
            return $app->redirect('/todo?page=' . $paginator->count());
        }

        //render all reminders
        return $app['twig']->render(
            'todos.html',
            [
                'todos' => $reminders,
                'paginator' => $paginator,
                'page' => $page
            ]
        );
    }
})->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $userData = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $loggedInUser = new User($userData, $app['db']);

    //obtain reminders
    $reminder = $loggedInUser->findReminder($id);

    //a collection with one reminder expected
    if (empty($reminder)) {
        //invalid id provided return a 404 Not Found response
        return $app->abort(
            Response::HTTP_NOT_FOUND,
            'Unknown reminder id: ' . $id
        );
    }

    //render the reminder as JSON
    return new JsonResponse($reminder);
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $userData = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $loggedInUser = new User($userData, $app['db']);

    //get description from request
    $description = trim($request->get('description'));

    //validate description
    $errors = $app['validator']->validate($description, new NotBlank());

    if (count($errors) == 0) {
        //logged in user adds a reminder
        $loggedInUser->addReminder($description);

        //flash message
        $app['session']->getFlashBag()->add('successMessage', 'Reminder created successfully.');
    } else {
        //empty description provided
        $app['session']->getFlashBag()->add('problemMessage', 'Please provide a reminder description.');
    }

    //compute last page
    $loggedInUser->getReminders(10, 1, $totalCount);
    $lastPage = (int) ($totalCount / 10) + 1;

    //redirect to the last page
    return $app->redirect('/todo?page=' . $lastPage);
});

$app->post('/todo/{id}/toggle-status', function ($id) use ($app) {
    if (null === $userData = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $loggedInUser = new User($userData, $app['db']);

    //logged in toggles reminder status
    if ($loggedInUser->toggleReminderStatus($id)) {
        //flash success message
        $app['session']->getFlashBag()->add('successMessage', 'Reminder status changed.');

    } else {
        //flash error message
        $app['session']->getFlashBag()->add(
            'problemMessage',
            'There was a problem changing reminder status. ID: ' . $id
        );
    }

    //redirect back to the referer
    return $app->redirect($app['request']->headers->get('referer'));
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

    //check for pagination parts in the referer URL
    $referer = $app['request']->headers->get('referer');
    $parts = parse_url($referer);

    if (array_key_exists('query', $parts)) {
        parse_str($parts['query'], $query);

        if (array_key_exists('page', $query)) {
            //redirect to the same page the delete call came from
            return $app->redirect($referer);
        }
    }

    //redirect back to reminder list
    return $app->redirect('/todo');
});