<?php

namespace App\Controller;

use App\Entity\Todo;
use Doctrine\ORM\EntityManager;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class TodoController extends BaseController
{
    /**
     * Entity Manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * TodoController constructor.
     * @param Application $app
     * @param Request $request
     * @param EntityManager $em
     */
    public function __construct(Application $app, Request $request, EntityManager $em)
    {
        parent::__construct($app, $request);
        $this->em = $em;
    }

    /**
     * Display all todos in a list
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction()
    {
        $this->isUserLoggedIn();

        $todos = $this->em->getRepository('\App\Entity\Todo')->findByUser($user['id']);

        return $this->app['twig']->render('todos.html.twig', ['todos' => $todos]);
    }

    /**
     * View Todo detail
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewAction()
    {
        $this->isUserLoggedIn();

        $id = $this->request->attributes->get('id');
        $todo = $this->em->getRepository('App\Entity\Todo')->find($id);

        if ($this->cannotUpdate($todo->getUser()->getId())) {
            return $this->login();
        }

        return $this->app['twig']->render('todo.html.twig', ['todo' => $todo]);
    }

    /**
     * Add A Todo
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction()
    {
        $this->isUserLoggedIn();

        $description = $this->request->get('description');
        $user = $this->em->getRepository('App\Entity\User')->find($this->getUserSession()['id']);

        $todo = new Todo();
        $todo->setDescription($description);
        $todo->setUser($user);

        $this->em->persist($todo);
        $this->em->flush();

        return $this->app->redirect($this->app['url_generator']->generate('todos-index'));
    }

    /**
     * Delete a Todo
     *
     * @return mixed
     */
    public function deleteAction()
    {
        $this->isUserLoggedIn();

        $id = $this->request->attributes->get('id');

        if (!$id) {
            $this->app->abort(404, 'The requested artist was not found.');
        }

        $todo = $this->em->getRepository('App\Entity\Todo')->find($id);

        if (!$this->cannotUpdate($todo->getUser()->getId())) {
            return $this->login();
        }

        $this->em->remove($todo);
        $this->em->flush();

        return $this->app->redirect($this->app['url_generator']->generate('todos-index'));
    }
}
