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
                $sql = "SELECT * FROM todos WHERE id = ?";
                $todo = $app['db']->fetchAssoc($sql, [
                    $id
                ]);

                if ($todo){
                    return $app['twig']->render('todo.html', [
                        'todo' => $todo,
                    ]);
                } else {
                    $app['session']->getFlashBag()->add('errors', 'Todo not found');
                    return $app->redirect('/todo');
                }
            } else {
                $sql = "SELECT COUNT(id) AS count FROM todos WHERE user_id = ?";
                $count = $app['db']->fetchAssoc($sql, [
                    (int) $user['id']
                ]);

                $pagination = $app['pagination']($count['count'], $page);

                $sql = "SELECT * FROM todos WHERE user_id = ? LIMIT ? ";
                $todos = $app['db']->fetchAll($sql,
                    [
                        (int) $user['id'],
                        5
                    ],
                    [
                        \PDO::PARAM_INT,
                        \PDO::PARAM_INT
                    ]
                );

                return $app['twig']->render('todos.html', [
                    'todos' => $todos,
                    'pages' => $pagination->build(),
                    'current' => $pagination->currentPage(),
                ]);
            }
        })
        ->value('id', 0)
        ->assert('id', '\d+')
        ->convert(
            'id',
            function ($id) {
                return (int) $id;
            }
        )
        ->value('page', 1)
        ->assert('page', '\d+')
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

            $user_id = (int) $user['id'];
            $todo = array(
                'description' => $app->escape($request->get('description')),
            );

            $constraint = new Assert\Collection([
                'description' => new Assert\NotBlank(),
            ]);
            $errors = $app['validator']->validate($todo, $constraint);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $app['session']->getFlashBag()->add('errors', $error->getPropertyPath().' '.$error->getMessage());
                }
            } else {
                $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
                $app['db']->executeUpdate($sql, [
                    $user_id,
                    $todo['description']
                ]);
                $app['session']->getFlashBag()->add('success', 'The todo was successfully created');
            }

            return $app->redirect('/todo');
        });

        /**
         * Todo's Delete Route
         */
        $todo->post('/delete/{id}', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $user_id = (int) $user['id'];

            if ($id) {
                $sql = "DELETE FROM todos WHERE id = ? AND user_id = ?";
                $app['db']->executeUpdate($sql, [
                    $id,
                    $user_id
                ]);
                $app['session']->getFlashBag()->add('success', 'The todo was successfully removed');
            } else {
                $app['session']->getFlashBag()->add('errors', 'Error to delete the Todo');
            }

            return $app->redirect('/todo');
        })
        ->value('id', 0)
        ->assert('id', '\d+')
        ->convert(
            'id',
            function ($id) {
                return (int) $id;
            }
        );

        /**
         * Mark a Todo as Completed Route
         */
        $todo->post('/completed/{id}', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $user_id = (int) $user['id'];

            if ($id) {
                $sql = "UPDATE todos SET completed = ? WHERE id = ? AND user_id = ?";
                $app['db']->executeUpdate($sql, [
                    1,
                    $id,
                    $user_id
                ]);

                $app['session']->getFlashBag()->add('success', 'The todo was marked as completed!');
            } else {
                $app['session']->getFlashBag()->add('errors', 'Error to mark the Todo as completed');
            }

            return $app->redirect('/todo');
        })
        ->value('id', 0)
        ->assert('id', '\d+')
        ->convert(
            'id',
            function ($id) {
                return (int) $id;
            }
        );

        /**
         * Set Todo as Not Completed Route
         */
        $todo->post('/notcompleted/{id}', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $user_id = (int) $user['id'];

            if ($id) {
                $sql = "UPDATE todos SET completed = ? WHERE id = ? AND user_id = ?";
                $app['db']->executeUpdate($sql, [
                    0,
                    $id,
                    $user_id
                ]);

                $app['session']->getFlashBag()->add('success', 'The todo was marked as NOT completed!');
            } else {
                $app['session']->getFlashBag()->add('errors', 'Error to mark the Todo as NOT completed');
            }

            return $app->redirect('/todo');
        })
        ->value('id', 0)
        ->assert('id', '\d+')
        ->convert(
            'id',
            function ($id) {
                return (int) $id;
            }
        );

        /**
         * Todo's JSON View Route
         */
        $todo->get('/{id}/json', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            if ($id){
                $user_id = (int) $user['id'];

                $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
                $todo = $app['db']->fetchAssoc($sql, [
                    $id,
                    $user_id
                ]);
                if ($todo){
                    return $app->json($todo);
                } else {
                    return 'Error to generate the Json';
                }
            } else {
                return 'Error to generate the Json';
            }
        })
        ->value('id', 0)
        ->assert('id', '\d+')
        ->convert(
            'id',
            function ($id) {
                return (int) $id;
            }
        );

        /**
         * Todo's Pagination Route
         */
        $todo->get('/{page}/list', function ($page) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }
            $user_id = (int) $user['id'];

            $sql = "SELECT COUNT(id) AS count FROM todos WHERE user_id = ?";
            $count = $app['db']->fetchAssoc($sql, [
                $user_id
            ]);

            $pagination = $app['pagination']($count['count'], $page);

            $offset = ($page - 1) * 5;
            $sql = "SELECT * FROM todos WHERE user_id = ? LIMIT ? OFFSET ?";
            $todos = $app['db']->fetchAll($sql,
                [
                    $user_id,
                    5,
                    $offset
                ],
                [
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT
                ]
            );

            return $app['twig']->render('todos.html', [
                'todos' => $todos,
                'pages' => $pagination->build(),
                'current' => $pagination->currentPage(),
            ]);
        })
        ->value('page', 1)
        ->assert('page', '\d+')
        ->convert(
            'page',
            function ($page) {
                return (int) $page;
            }
        );

        return $todo;
    }
}