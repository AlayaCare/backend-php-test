<?php
// Register homepage route
$app->get('/', 'Controller\HomepageController::indexAction');

// Register todo routes
$app->post('/todo/add', 'Controller.TodoController:addAction');
$app->post('/todo/delete/{id}', 'Controller.TodoController:deleteAction');
$app->get('/todo', 'Controller.TodoController:indexAction');

$app->get('/todo/{id}', 'Controller.TodoController:viewAction');
$app->get('/todo/{id}/json/{method}', 'Controller.TodoController:viewActionJSON');

$app->get('/todo/api/index', 'Controller.TodoController:indexActionJSON');
// Register user routes
$app->match('/login', 'Controller.UserController:loginAction');
$app->match('/logout', 'Controller.UserController:logoutAction');


$app['Controller.UserController'] = function ($app) {
    return new Controller\UserController($app);
};
$app['Controller.TodoController'] = function ($app) {
    return new Controller\TodoController($app, $app['request_stack'], $app['entity_manager'], $app['user']);
};
