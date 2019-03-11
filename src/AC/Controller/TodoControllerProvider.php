<?php


namespace AC\Controller;



use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;


class TodoControllerProvider implements ControllerProviderInterface
{


    public function connect(Application $app)
    {
        $controllers = $app["controllers_factory"];

        $controllers->get('/todo/list/{page}/{sort_by}/{sorting}', 'AC\\Controller\\TodoController::getTodoList')
            ->value('page', 1)
            ->value('sort_by', 'id')
            ->value('sorting', 'asc')
            ->assert('page', '\d+')
            ->assert('sorting', '(\basc\b)|(\bdesc\b)')// Match "asc" or "desc"
            ->bind('todo/list');

        $controllers->get('/todo/{id}', 'AC\\Controller\\TodoController::getById')
            ->value('id', null);

        $controllers->get('/todo/{id}/json', 'AC\\Controller\\TodoController::getTodoJson')
            ->value('id', null);

        $controllers->post('/todo/add', 'AC\\Controller\\TodoController::addTodo');

        $controllers->match('/todo/delete/{id}', 'AC\\Controller\\TodoController::removeTodo');

        $controllers->match('/todo/complete/{id}', 'AC\\Controller\\TodoController::markAsCompleted');

        return $controllers;
    }


}