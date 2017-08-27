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
        $limit = 5;

        $this->isUserLoggedIn();

        $userId = $this->getUserSession()['id'];

        $total = $this->em
            ->getRepository('App\Entity\Todo')
            ->getListCountByUser($userId);

        // Pagination
        $numPages = ceil($total / $limit);
        $currentPage = $this->request->query->get('page', 1);
        $offset = ($currentPage - 1) * $limit;

        $todos = $this->em->getRepository('App\Entity\Todo')->findListByUser($userId, $limit, $offset);

        $data = array(
            'todos' => $todos ? $todos : [],
            'currentPage' => $currentPage,
            'itemsPerPage' => $limit,
            'numPages' => $numPages,
        );

        return $this->app['twig']->render('todos.html.twig', $data);
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

        $page = $this->request->query->get('page');
        $description = $this->request->get('description');

        $user = $this->em->getRepository('App\Entity\User')->find($this->getUserSession()['id']);

        $todo = new Todo();
        $todo->setDescription($description);
        $todo->setUser($user);

        $this->em->persist($todo);
        $this->em->flush();

        $this->app['session']->getFlashBag()->add('success', 'Todo has been added successfully');

        return $this->app->redirect($this->app['url_generator']
            ->generate('todos-index', array('page' => $page)));
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
        $page = $this->request->query->get('page');

        if (!$id) {
            $this->app->abort(404, 'The requested todo was not found.');
        }

        $todo = $this->em->getRepository('App\Entity\Todo')->find($id);

        if ($this->cannotUpdate($todo->getUser()->getId())) {
            return $this->login();
        }

        $this->em->remove($todo);
        $this->em->flush();

        $this->app['session']->getFlashBag()->add('success', 'Todo has been deleted successfully');

        if ($page) {
            return $this->app->redirect($this->app['url_generator']
                ->generate('todos-index', array('page' => $page)));
        }

        return $this->app->redirect($this->app['url_generator']->generate('todos-index'));
    }

    /**
     * Complete or incomplete a Todo
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function completeAction()
    {
        $this->isUserLoggedIn();

        $id = $this->request->attributes->get('id');
        $page = $this->request->query->get('page');

        if (!$id) {
            $this->app->abort(404, 'The requested todo was not found.');
        }

        $todo = $this->em->getRepository('App\Entity\Todo')->find($id);

        if ($this->cannotUpdate($todo->getUser()->getId())) {
            return $this->login();
        }

        $todo->setCompleted(!$todo->getCompleted());
        $this->em->persist($todo);
        $this->em->flush();

        if ($page) {
            return $this->app->redirect($this->app['url_generator']
                ->generate('todos-index', array('page' => $page)));
        }

        return $this->app->redirect($this->app['url_generator']->generate('todos-index'));
    }
}
