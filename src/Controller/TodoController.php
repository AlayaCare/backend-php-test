<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Entity\Todo;

class TodoController

{
    /**
     * Class constructor
     * @param Application $app
     * @param RequestStack $requestStack
     */
    public function __construct(Application $app, RequestStack $requestStack)
    {
        $this->orm_em = $app['entity_manager'];
        $user = $app['session']->get('user');
        $this->userid = $user['id'];
        $this->app = $app;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * User todos index view
     *
     */
    public function indexAction()
    {

        // removed login check redirect - to be implemented via firewall

        $todos = $this->orm_em->getRepository('Entity\Todo')->getUserTodos($this->userid);
        return $this->app['twig']->render('todos.html', ['todos' => $todos, ]);
    }

    /**
     * Todo single view
     *
     */
    public function viewAction()
    {

        // removed login check redirect - to be implemented via firewall

        $id = $this->request->get('id');
        if ($id) {
            $todo = $this->orm_em->find('Entity\Todo', $id);
            if ($this->userid == $todo->getuser_id()) {
                return $this->app['twig']->render('todo.html', ['todo' => $todo, ]);
            }
            else {

                // $this->app['session']->getFlashBag()->add('message', 'You are not autorized to view this todo!');

                return $this->app->redirect('/todo');
            }
        }
        else {

            // $this->app['session']->getFlashBag()->add('message', 'The specified todo does not exist!');

            return $this->app->redirect('/todo');
        }
    }

    /**
     * Add a todo
     *
     */
    public function addAction()
    {

        // removed login check redirect - to be implemented via firewall

        $description = $this->request->get('description');
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());
        if (count($errors) > 0) {

            // $this->app['session']->getFlashBag()->add('message', 'The description cannot be blank.');

        }
        else {
            $todo = new Todo();
            $todo->setDescription($description);
            $todo->setuser_id($this->userid);
            $this->orm_em->persist($todo);
            $this->orm_em->flush();

            // $this->app['session']->getFlashBag()->add('message', 'The todo has been added!');

        }

        return $this->app->redirect('/todo');
    }

    /**
     * Edit a todo
     *
     */
    public function editAction()
    {

        // this will be for task 2

    }

    /**
     * Delete a todo
     *
     */
    public function deleteAction()
    {

        // removed login check redirect - to be implemented via firewall

        $id = $this->request->get('id');

        // add todo check - if todo id does not exist notify user

        if ($id) {
            $todo = $this->orm_em->find('Entity\Todo', $id);

            // add a check - if todo belongs to this user

            if ($this->userid == $todo->getuser_id()) {
                $this->orm_em->remove($todo);
                $this->orm_em->flush();

                // $this->app['session']->getFlashBag()->add('message', 'The todo has been removed!');

            }
            else {

                // $this->app['session']->getFlashBag()->add('message', 'You are not authorised to perform this action');

            }
        }
        else {

            // $this->app['session']->getFlashBag()->add('message', 'The specified todo does not exist!');

        }

        return $this->app->redirect('/todo');
    }
}
