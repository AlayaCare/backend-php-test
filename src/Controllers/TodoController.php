<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Models\TodoModel;

class TodoController implements ControllerProviderInterface
{
  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    $controllers->get('/single/{id}', function ($id) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $todoModel = new TodoModel($app);
        $todo = $todoModel->selectById($id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);

    });

    $controllers->get('/list/{page}', function($page) use ($app){
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login');
      }

      $todos = $app["fpagination"]->paginate(new TodoModel($app), ['col' => '*', 'filterCol' => 'user_id', 'filterVal' => $user['id'], "page" => $page, "limit" => 5]);

      return $app['twig']->render('todos.html', ['todos' => $todos["currentPage"], 'count' => $todos["PageNumbers"]]);

    })
    ->value('page', 1);


    $controllers->post('/add', function (Request $request) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $user_id = $user['id'];
        $description = $request->get('description');

        $errors = $app['validator']->validate($description, new Assert\NotBlank());

        if(count($errors) === 0){

          $todoModel = new TodoModel($app);
          $add = $todoModel->insert($user_id, $description);


          $app['session']->getFlashBag()->add('success', "Task $description added with success!");
        } else {
          $app['session']->getFlashBag()->add('danger', 'A task must have a description!');
        }

        return $app->redirect('/todo/list');
    });


    $controllers->match('/delete/{id}', function ($id) use ($app) {
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login');
      }

        $todoModel = new todoModel($app);
        $todoModel->deleteById($id);

        $app['session']->getFlashBag()->add('danger', 'Task deleted!');

        return $app->redirect('/todo/list');
    });

    $controllers->post('/complete/{id}', function ($id) use ($app){
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login');
      }

      $todoModel = new TodoModel($app);
      $update = $todoModel->update($id, [['col' => 'completed', 'val' => 1]]);

      $app['session']->getFlashBag()->add('success', 'Congratulations, you completed a task!');

      return $app->redirect('/todo/list');

    });

    $controllers->get('/single/{id}/json', function ($id) use ($app){
      if (null === $user = $app['session']->get('user')) {
          return $app->redirect('/login/list');
      }

      $todoModel = new TodoModel($app);

      $todo = $todoModel->selectById($id);

      return $app['twig']->render('todoJson.html', [
          'todo' => $todo,
      ]);

    });

    return $controllers;
  }
}
