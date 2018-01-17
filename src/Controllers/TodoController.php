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

    $controllers->get('/{id}', function ($id) use ($app) {
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


    $controllers->post('/add', function (Request $request) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $user_id = $user['id'];
        $description = $request->get('description');

        $errors = $app['validator']->validate($description, new Assert\NotBlank());

        if(count($errors) === 0){
          $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
          $app['db']->executeUpdate($sql);

          $app['session']->getFlashBag()->add('success', "Task $description added with success!");
        } else {
          $app['session']->getFlashBag()->add('danger', 'A task must have a description!');
        }

        return $app->redirect('/todo');
    });


    $controllers->match('/delete/{id}', function ($id) use ($app) {
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login');
      }

        $sql = "DELETE FROM todos WHERE id = '$id'";
        $app['db']->executeUpdate($sql);

        $app['session']->getFlashBag()->add('danger', 'Task deleted!');

        return $app->redirect('/todo');
    });

    $controllers->post('/complete/{id}', function ($id) use ($app){
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login');
      }

      $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
      $app['db']->executeUpdate($sql);

      $app['session']->getFlashBag()->add('success', 'Congratulations, you completed a task!');

      return $app->redirect('/todo');

    });

    $controllers->get('{id}/json', function ($id) use ($app){
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login');
      }

      $sql = "SELECT * FROM todos WHERE id='$id'";
      $todo = $app['db']->fetchAssoc($sql);

      return $app['twig']->render('todoJson.html', [
          'todo' => $todo,
      ]);

    });

    return $controllers;
  }
}
