<?php
/**
 * Created by PhpStorm.
 * User: kienhungtran
 * Date: 2017-08-28
 * Time: 1:22 AM
 */

namespace App\Controllers;

use App\Repositories\TodoRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class TodoController
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $user;

    /**
     * @var TodoRepository
     */
    protected $todoRepository;

    /**
     * TodoController constructor.
     * @param Application $app
     * @param TodoRepository $todoRepository
     * @codeCoverageIgnore
     */
    public function __construct(Application $app, TodoRepository $todoRepository)
    {
        $this->app = $app;
        $this->user = $app['session']->get('user');
        $this->todoRepository = $todoRepository;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index()
    {
        $todos = $this->todoRepository->getAllTodos($this->user['id']);

        return $this->app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }

    /**
     * @param int $id
     * @param string $view
     * @return mixed
     */
    public function show($id, $view)
    {
        $todo = $this->todoRepository->getTodo($id);

        if ($view == 'json') {
            return $this->app->json($todo);
        } else {
            return $this->app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        }
    }

    /**
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function add(Request $request)
    {
        $userId = $this->user['id'];
        $description = $request->get('description');
        // Validate if description is not empty
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->app['session']->getFlashBag()->add('add-todo-form', $error->getMessage());
            }
        } else {
            $this->todoRepository->insertNewTodo($userId, $description);
            $this->app['session']->getFlashBag()->add('add-todo-form', 'New task added: ' . $description);
        }
        return $this->app->redirect('/todo');
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete($id)
    {
        $this->todoRepository->deleteTodo($id);
        $this->app['session']->getFlashBag()->add('add-todo-form', 'Task ' . $id . ' deleted');
        return $this->app->redirect('/todo');
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setCompleted($id)
    {
        $this->todoRepository->updateTodo($id, 1);
        return $this->app->redirect('/todo');
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setUncompleted($id)
    {
        $this->todoRepository->updateTodo($id, 0);
        return $this->app->redirect('/todo');
    }



}