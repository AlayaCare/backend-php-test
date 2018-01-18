<?php

// Register homepage route
$app->get('/', 'Controller.HomepageController:indexAction')->bind('home');

// Register todo routes
$app->post('/todo/delete/{id}', 'Controller.TodoController:deleteAction');
$app->match('/todo', 'Controller.TodoController:indexAction')->bind('todo');

$app->get('/todo/{id}', 'Controller.TodoController:singleAction');
$app->post('/todo/{id}', 'Controller.TodoController:singleAction');
$app->get('/todo/{id}/json/{method}', 'Controller.TodoController:viewActionJSON');

$app->get('/todo/api/index', 'Controller.TodoController:indexActionJSON');

// Register user routes
$app->get('/login', 'Controller.UserController:loginAction')->bind('login');
$app->get('/logout', 'Controller.UserController:logoutAction');

// Controllers
$app['Controller.HomepageController'] = function ($app) {
    return new Controller\HomepageController($app, $app['request_stack']);
};
$app['Controller.UserController'] = function ($app) {
    return new Controller\UserController($app, $app['request_stack']);
};
$app['Controller.TodoController'] = function ($app) {
    return new Controller\TodoController($app, $app['request_stack'], $app['entity_manager']);
};
