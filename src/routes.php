<?php

// Home page
$app->get('/', 'controller.index:indexAction')
    ->bind('homepage');

// Authentication routes
$app->match('/login', 'controller.authentication:loginAction')
    ->bind('login');

$app->get('/logout', 'controller.authentication:logoutAction')
    ->bind('logout');

// TodoList management
$app->get('/account/todos', 'controller.todo:indexAction')
    ->bind('todos-index');

$app->get('/account/todos/{id}', 'controller.todo:viewAction')
    ->bind('todos-view');

$app->post('/account/todos/add', 'controller.todo:addAction')
    ->bind('todos-add');

$app->match('/account/todos/delete/{id}', 'controller.todo:deleteAction')
    ->bind('todos-delete');

$app->match('/account/todos/complete/{id}', 'controller.todo:completeAction')
    ->bind('todos-complete');

// Api
$app->get('/api/todos/{id}', 'controller.api.todo:viewAction');
