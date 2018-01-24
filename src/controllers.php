<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Controllers\HomeController;
use Controllers\TodoController;
use Entities\User;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

/**
 * @var callback to check if the user is logged and exists
 */
$guestMiddleware = function () use ($app) {
  $userId = $app['session']->get('user')['id'];
  $em = $app['orm.em'];

  if($userId === null || $em->find(User::class, $userId) === null){
    return $app->redirect('/login');
  }
};


/**
 * Share the Home Controller
 * @var HomeController
 */
$app['home.controller'] = $app->share(function() use ($app){
  return new HomeController($app);
});

/**
 * Share the Todo Controller
 * @var TodoController
 */
$app['todo.controller'] = $app->share(function() use ($app){
  return new TodoController($app);
});

/**** HOME ROUTES ****/

/* Home Page */
$app->get('/', 'home.controller:index');

/* Login Page */
$app->match('/login', 'home.controller:login');

/* Logout Page */
$app->get('/logout', 'home.controller:logout');

/**** TODO ROUTES ****/

/* Todo Home Page */
$app->get('/todo/{page}', 'todo.controller:index')->value('page', 1)->before($guestMiddleware);

/* Single Todo Page */
$app->get('/todo/single/{id}', 'todo.controller:single')->value('id', null)->before($guestMiddleware);

/* Create New Todo */
$app->post('/todo/add', 'todo.controller:create')->before($guestMiddleware);

/* Delete Todo */
$app->post('/todo/delete/{id}', 'todo.controller:delete')->value('id', null);

/* Set Todo as Completed */
$app->post('/todo/complete/{id}', 'todo.controller:complete')->value('id', null)->before($guestMiddleware);

/* Singe JSON Todo Page */
$app->get('/todo/single/json/{id}', 'todo.controller:singleJSON')->value('id', null)->before($guestMiddleware);
