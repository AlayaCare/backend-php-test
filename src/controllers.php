<?php
$app['controller.index'] = $app->share(function ($app) {
    return new App\Controller\IndexController($app, $app['request']);
});

$app['controller.authentication'] = $app->share(function ($app) {
    return new App\Controller\AuthenticationController($app, $app['request'], $app['orm.em']);
});

$app['controller.todo'] = $app->share(function ($app) {
    return new App\Controller\TodoController($app, $app['request'], $app['orm.em']);
});

$app['controller.api.todo'] = $app->share(function ($app) {
    return new App\Controller\ApiTodoController($app, $app['request'], $app['orm.em']);
});
