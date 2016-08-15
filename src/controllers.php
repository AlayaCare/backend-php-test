<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FranMoreno\Silex\Twig\PagerfantaExtension;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

/**************************************/
/*  main TODO application controller  */
/**************************************/

/* globals */
$INSERT_DESCRIPTION = "add";
$DELETE_DESCRIPTION = "remove";
$ERROR_EMPTY_DESCRIPTION = "empty description is not granted. Please fill in with relevant content";

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$app['twig']->addExtension(new PagerfantaExtension($app));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


/* route by which users come to log in to current application */
$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $user = $app['dao.user']->findUser($username, $password);
        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }
    return $app['twig']->render('login.html', array());
});

/* route by which users come to log out current application */
$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

/* route by which descriptions are listed */
$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todo = $app['dao.user']->findDescriptionById($id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $todos = $app['dao.user']->findDescriptionByUser($user);
        // #TODO: maintain displaying flashBag in case pagination happens...
        $adapter = new ArrayAdapter($todos);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $app['twig']->render('todos.paginated.html', array(
            'pager' => $pagerfanta
        ));

    }
})
->value('id', null);

/* route by which description is served in JSON format */
$app->get('/todo/{id}/json', function ($id) use ($app) {
    $user = $app['session']->get('user');

    if ($id){
        $todo = $app['dao.user']->findDescriptionById($id);
        $jsonData = json_encode($todo);
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $response = new Response($jsonData, 200, $headers);
        return $response;
    }
})
->value('id', null);

/* route by which a new description is added in the list */
$app->post('/todo/add', function (Request $request) use ($app) {
    global $INSERT_DESCRIPTION, $DELETE_DESCRIPTION, $ERROR_EMPTY_DESCRIPTION;
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    if ($description != '' && (strlen(trim($description)) != 0)) {
        $todos = $app['dao.user']->addDescription($user_id, $description, 0);
    } else {
        error_log($ERROR_EMPTY_DESCRIPTION);
    }
    $app['flashbag.manager']->displayDescriptionWarning($INSERT_DESCRIPTION, $description);

    return $app->redirect('/todo');
});

/* route by which previous description is removed from the list */
$app->match('/todo/delete/{id}', function ($id) use ($app) {
    global $DELETE_DESCRIPTION;
    $description = $app['dao.user']->getDescription($id);
    $app['dao.user']->deleteDescription($id);
    $app['flashbag.manager']->displayDescriptionWarning($DELETE_DESCRIPTION, $description);

    return $app->redirect('/todo');
});

/* route by which any description gets marked as done */
$app->match('/todo/complete/{id}', function ($id) use ($app) {
    $app['dao.user']->markDescriptionAsCompleted($id);

    return $app->redirect('/todo');
});
