<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Entity\Todo;
use Entity\User;
use Doctrine\ORM\EntityManager;


class TodoController

{

    /** @var \Silex\Application */
    private $app;

    /** @var RequestStack */
    private $requestStack;

    /** @var \Doctrine\ORM\EntityManager */
    private $orm_em;

    /** @var \Entity\User */
    private $user;


    /**
     * Class constructor
     * @param Application $app
     * @param RequestStack $requestStack
     * @param EntityManager $orm_em
     * @param User $user
     */
    public function __construct(Application $app, RequestStack $requestStack, EntityManager $orm_em, User $user)
    {
        $this->app = $app;
        $this->request = $requestStack->getCurrentRequest();
        $this->orm_em = $orm_em;
        $this->userid = $user->getId();
        $this->entity_class = 'Entity\Todo';
        $this->main_index_url = '/todo';

    }

    /**
     * User todos index view
     *
     * Not adding doc to the TodoController methods yet as some may soon change
     */
    public function indexAction()
    {

        $todos = $this->orm_em->getRepository($this->entity_class)->getUserTodos($this->userid);
        return $this->app['twig']->render('todos.html', ['todos' => $todos, ]);
    }

    /**
     * Todo single view
     *
     */
    public function viewAction()
    {

        // bug squash: check if todo id exists and not if request id
        $todo = $this->getRequestedEntity();
        if ($todo) {
            if ($this->isEntityOwner($todo)) {
                return $this->app['twig']->render('todo.html', ['todo' => $todo, ]);
            }
            else {

                $message = 'You are not authorized to view this todo!';

            }
        }
        else {

            $message = 'The specified todo does not exist!';

        }

        $this->hasMessage($message);

        return $this->mainIndexRedirect();
    }


    /**
     * Todo single view in JSON
     *
     */
    public function viewActionJSON()

    {
        $todo = $this->getRequestedEntity();
        if ($todo) {
            if ($this->isEntityOwner($todo)) {
                $todo = array(
                    'id' => $todo->getId() ,
                    'description' => $todo->getDescription() ,
                    'user_id' => $todo->getuser_id() ,
                );
                $method = $this->request->get('method');
                if ($method == 'inline') {
                    $todo = $this->app->json($todo);
                    return $this->app['twig']->render('todo-json.html', ['todo' => $todo, ]);

                }
                else if ($method == 'raw') {
                    return $this->app->json($todo);
                }
                else {
                    $message = 'An error occured, sorry';
                }
            }
            else {
                $message = 'You are not authorized to view this todo!';
            }
        }
        else {
            $message = 'The specified todo does not exist!';
        }

        $this->hasMessage($message);
        return $this->mainIndexRedirect();
    }

    /**
     * Add a todo
     *
     */
    public function addAction()
    {

        $description = $this->request->get('description');
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());
        if (count($errors) > 0) {

            $message = 'The description cannot be blank.';

        }
        else {
            $todo = new Todo();
            $todo->setDescription($description);
            $todo->setuser_id($this->userid);
            $this->orm_em->persist($todo);
            $this->orm_em->flush();

            $message = 'The todo has been added!';

        }

        $this->hasMessage($message);
        return $this->mainIndexRedirect();
    }

    /**
     * Edit a todo
     *
     */
    public function editAction()
    {

        // this will be for task 2
        /**

        $todo = $this->getRequestedEntity();
        if ($todo) {
            if ($this->isEntityOwner($todo)) {

                // make changes

            }
            else {

                $message = 'You are not authorized to view this todo!');

            }
        }
        else {

            $message = 'The specified todo does not exist!');

        }

        $this->hasMessage($message);
        return $this->mainIndexRedirect();

        */
    }

    /**
     * Delete a todo
     *
     */
    public function deleteAction()
    {

        $id = $this->request->get('id');

        // bug squash: check if todo id exists and not if request id
        $todo = $this->getRequestedEntity();
        if ($todo) {

            // add a check - if todo belongs to this user

            if ($this->isEntityOwner($todo)) {
                $this->orm_em->remove($todo);
                $this->orm_em->flush();

                $message = 'The todo has been removed!';

            }
            else {
			 
                // Todo: NotFoundHttpException thrown when accessed via direct url
                $message = 'You are not authorized to perform this action';

            }
        }
        else {
			// Todo: NotFoundHttpException thrown in this case - must rethink this 
            //$message = 'The specified todo does not exist!');

        }

        $this->hasMessage($message);
        return $this->mainIndexRedirect();
    }


    // In another next step / commit we may wish to move the following methods to a new (parent or abstract) class. They could be reused for
    // adding other entities such as categories or projects. We start moving away from the 'todo' language into a more generic 'entity' one

    /**
     * Get requested entity object if it exists
     *
     * @return Todo|null
     */
    public function getRequestedEntity()

    {

        $entity_id = $this->request->get('id');
        $entity = $this->orm_em->find($this->entity_class, $entity_id);  
        if ($entity) {

            return $entity;
        }

        return;
    }


    /**
     * Check if user is entity owner
     *
     * @param Todo $owned_entity
     * @return Boolean
     */
    public function isEntityOwner(Todo $owned_entity)

    {

        return $this->userid === $owned_entity->getuser_id();

    }

    /**
     * Add flashbag message to session
     *
     * @param string $message The message
     * Don't use scalar type hints like as in 'hasMessage(string $message)' to preserve compatibility with PHP 5 
     * @return string|null
     */
    public function hasMessage($message)

    {

        if ($message) {

            return $this->app['session']->getFlashBag()->add('message', $message);
        }

        return;

    }

    /**
     * Redirect to the Entity main index page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function mainIndexRedirect()

    {

        return $this->app->redirect($this->main_index_url);
    }


}
