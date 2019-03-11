<?php

namespace AC\Controller;

use AC\Core\ErrorCode;
use AC\Core\StatusEnum;
use AC\Entity\Todo;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TodoController
{
    public function getTodoList (Request $request, Application $app, $sort_by, $page, $sorting){
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }
        $count = $app['repository.todos']->countByUser($user['id']);
        $paginator = $app['paginator']($count, $page);
        $todos = $app['repository.todos']->findAllPaginator($paginator, $user['id'], $sort_by, $sorting);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'page' => $page,
            'pagination' => $paginator
        ]);
    }

    public function getById(Request $request, Application $app, $id)
    {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        if ($id) {
            $todo = $app['repository.todos']->findByIdAndUserId($id, $user['id']);
            if ($todo) {
                return $app['twig']->render('todo.html', [
                    'todo' => $todo,
                ]);
            } else {
                return $app['twig']->render('error.html', [
                    'error' => ErrorCode::UNAUTHORIZED,
                ]);
            }
        } else {
            return $app->redirect('/todo/list');
        }
    }

    public function getTodoJson(Request $request, Application $app, $id)
    {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }
        if ($id) {
            $todo = $app['repository.todos']->findByIdAndUserId($id, $user['id']);
            if ($todo) {
                return new JsonResponse($todo->toArray(), 200);
            } else {
                return new JsonResponse(['error' => ErrorCode::UNAUTHORIZED], 200);
            }
        } else {
            return new JsonResponse(['error' => ErrorCode::DOES_NOT_EXIST], 200);
        }
    }

    public function addTodo(Request $request, Application $app)
    {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $user_id = $user['id'];
        $description = $request->get('description');

        $todo = new Todo();
        $todo->fill(['description' => $description, 'user_id' => $user_id]);
        $errors = $app["validator"]->validate($todo);
        if (count($errors) > 0) {
            $app['session']->getFlashBag()->add('todo_errors', 'A Todo can not be created without a description.');
        } else {
            $app['repository.todos']->insert($todo);
            $app['session']->getFlashBag()->add('todo_messages', 'A Todo was created successfully.');
        }
        return $app->redirect('/todo');
    }

    public function removeTodo(Request $request, Application $app, $id)
    {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }
        $todo = $app['repository.todos']->findByIdAndUserId($id, $user['id']);
        if ($todo) {
            $app['repository.todos']->remove($id);
            $app['session']->getFlashBag()->add('todo_messages', 'A Todo was removed successfully.');
        } else {
            return $app['twig']->render('error.html', [
                'error' => ErrorCode::DOES_NOT_EXIST,
            ]);
        }
        return $app->redirect('/todo');
    }

    public function markAsCompleted(Request $request, Application $app, $id)
    {

        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }
        $todo = $app['repository.todos']->findByIdAndUserId($id, $user['id']);
        if ($todo) {
            $todo->setStatus(StatusEnum::COMPLETED);
            $app['repository.todos']->update($todo);
            $app['session']->getFlashBag()->add('todo_messages', 'A Todo was completed successfully.');
        } else {
            return $app['twig']->render('error.html', [
                'error' => ErrorCode::DOES_NOT_EXIST,
            ]);
        }
        return $app->redirect('/todo');
    }

}