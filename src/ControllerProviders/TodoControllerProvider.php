<?php

namespace ControllerProviders;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class TodoControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $todo = $app['controllers_factory'];

        /**
         * Todo's View and List Route
         */
        $todo->get('/{id}', function ($id, $page) use ($app) {
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
                $sql = "SELECT COUNT(id) AS count FROM todos WHERE user_id = '${user['id']}'";
                $count = $app['db']->fetchAssoc($sql);
                $pagination = $app['pagination']($count['count'], $page);

                $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT 5 ";
                $todos = $app['db']->fetchAll($sql);

                return $app['twig']->render('todos.html', [
                    'todos' => $todos,
                    'pages' => $pagination->build(),
                    'current' => $pagination->currentPage(),
                ]);
            }
        })
        ->value('id', null)
        ->value('page', 1)
        ->convert(
            'page',
            function ($page) {
                return (int) $page;
            }
        );


        /**
         * Todo's Create Route
         */
        $todo->post('/add', function (Request $request) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $user_id = $user['id'];
            $todo = array(
                'description' => $request->get('description'),
            );

            $constraint = new Assert\Collection(array(
                'description' => new Assert\NotBlank(),
            ));
            $errors = $app['validator']->validate($todo, $constraint);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $app['session']->getFlashBag()->add('errors', $error->getPropertyPath().' '.$error->getMessage());
                }
            } else {
                $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '{$todo['description']}')";
                $app['db']->executeUpdate($sql);
                $app['session']->getFlashBag()->add('success', 'The todo was successfully created');
            }

            return $app->redirect('/todo');
        });

        /**
         * Todo's Delete Route
         */
        $todo->match('/delete/{id}', function ($id) use ($app) {

            $sql = "DELETE FROM todos WHERE id = '$id'";
            $app['db']->executeUpdate($sql);
            $app['session']->getFlashBag()->add('success', 'The todo was successfully removed');

            return $app->redirect('/todo');
        });

        /**
         * Set Todo as Completed Route
         */
        $todo->post('/completed/{id}', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
            $app['db']->executeUpdate($sql);

            $app['session']->getFlashBag()->add('success', 'The todo was marked as completed!');

            return $app->redirect('/todo');
        });

        /**
         * Set Todo as Not Completed Route
         */
        $todo->post('/notcompleted/{id}', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $sql = "UPDATE todos SET completed = 0 WHERE id = '$id'";
            $app['db']->executeUpdate($sql);

            $app['session']->getFlashBag()->add('success', 'The todo was marked as NOT completed!');

            return $app->redirect('/todo');
        });

        /**
         * Todo's JSON View Route
         */
        $todo->get('/{id}/json', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            if ($id){
                $sql = "SELECT * FROM todos WHERE id = '$id'";
                $todo = $app['db']->fetchAssoc($sql);

                return $app->json($todo);
            } else {
                $app['session']->getFlashBag()->add('errors', 'Error to generate the Json');
                return $app->redirect('/todo');
            }
        })
        ->value('id', null);

        /**
         * Todo's Pagination Route
         */
        $todo->get('/{page}/list', function ($page) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $sql = "SELECT COUNT(id) AS count FROM todos WHERE user_id = '${user['id']}'";
            $count = $app['db']->fetchAssoc($sql);
            $pagination = $app['pagination']($count['count'], $page);

            $offset = ($page - 1) * 5;
            $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT 5 OFFSET $offset ";
            $todos = $app['db']->fetchAll($sql);

            return $app['twig']->render('todos.html', [
                'todos' => $todos,
                'pages' => $pagination->build(),
                'current' => $pagination->currentPage(),
            ]);
        })
        ->value('page', 1)
        ->convert(
            'page',
            function ($page) {
                return (int) $page;
            }
        );

        return $todo;
    }
}