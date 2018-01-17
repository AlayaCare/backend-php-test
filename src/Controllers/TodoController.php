<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class TodoController implements ControllerProviderInterface
{
  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    $app->get('/todo/{id}', function ($id) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        if ($id){
            $sql = "SELECT * FROM todos WHERE id = '$id'";
            $todo = $app['db']->fetchAssoc($sql);

            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        } else {
            $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
            $todos = $app['db']->fetchAll($sql);

            return $app['twig']->render('todos.html', [
                'todos' => $todos,
            ]);
        }
    })
    ->value('id', null);


    $app->post('/todo/add', function (Request $request) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $user_id = $user['id'];
        $description = $request->get('description');

        $errors = $app['validator']->validate($description, new Assert\NotBlank());

        if(count($errors) === 0){
          $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
          $app['db']->executeUpdate($sql);

        }

        return $app->redirect('/todo');
    });


    $app->match('/todo/delete/{id}', function ($id) use ($app) {

        $sql = "DELETE FROM todos WHERE id = '$id'";
        $app['db']->executeUpdate($sql);

        return $app->redirect('/todo');
    });

    return $controllers;
  }
}
