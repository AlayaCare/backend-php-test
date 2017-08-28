<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ApiTodoController extends BaseController
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
     * Get Todo
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function viewAction()
    {
        $id = $this->request->attributes->get('id');

        if (!$id) {
            $this->notFound();
        }

        $todo = $this->em->getRepository('App\Entity\Todo')->find($id);

        if ($this->cannotUpdate($todo->getUser()->getId())) {
            // We don't return a 403, this way when an unauthorized user tries to access
            // a resource, they don't know its existence
            return $this->app->json('Not found', 404);
        }

        $data = array(
            'id' => $todo->getId(),
            'user_id' => $todo->getUser()->getId(),
            'description' => $todo->getDescription(),
        );

        return $this->app->json($data);
    }
}
