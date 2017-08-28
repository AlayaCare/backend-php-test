<?php
/**
 * Created by PhpStorm.
 * User: kienhungtran
 * Date: 2017-08-28
 * Time: 1:22 AM
 */

namespace App\Controllers;

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
     * TodoController constructor.
     * @param Application $app
     * @codeCoverageIgnore
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->user = $app['session']->get('user');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index()
    {
        $sql = "SELECT * FROM todos WHERE user_id = '{$this->user['id']}'";
        $todos = $this->app['db']->fetchAll($sql);

        return $this->app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $this->app['db']->fetchAssoc($sql);

        return $this->app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    }

    /**
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function add(Request $request)
    {
        $user_id = $this->user['id'];
        $description = $request->get('description');
        // Validate if description is not empty
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->app['session']->getFlashBag()->add('add-todo-form', $error->getMessage());
            }
        } else {
            $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
            $this->app['db']->executeUpdate($sql);
        }
        return $this->app->redirect('/todo');
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete($id)
    {
        $sql = "DELETE FROM todos WHERE id = '$id'";
        $this->app['db']->executeUpdate($sql);

        return $this->app->redirect('/todo');
    }

}