<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RequestStack;
use Entity\Todo;
use Entity\User;
use Doctrine\ORM\EntityManager;
use Form\TodoType;

class TodoController extends EntityController

{

    /** @var \Silex\Application */
    protected $app;

    /** @var RequestStack */
    protected $requestStack;

    /** @var \Doctrine\ORM\EntityManager */
    protected $orm_em;

    /** @var \Entity\User */
    protected $user;


    /**
     * Class constructor
     * @param Application $app
     * @param RequestStack $requestStack
     * @param EntityManager $orm_em
     * @param User $user
     */
    public function __construct(Application $app, RequestStack $requestStack, EntityManager $orm_em)
    {
        $this->app = $app;
        $this->request = $requestStack->getCurrentRequest();
        $this->orm_em = $orm_em;
        $this->userid = $this->app['user']->getID();
        $this->entity_class = 'Entity\Todo';
        $this->entity_name = 'todo';
        $this->main_index_url = '/' . $this->entity_name;
        $this->main_index_bind = $this->entity_name;
    }

    /**
     * User todos index view. Also returns and handles the form for adding todos
     *
     * @return string twig template
     */
    public function indexAction()
    {

        $todo = new Todo();

        $form = $this->app['form.factory']->create(TodoType::class, $todo);
        $this->handleForm($form, $todo, 'added');

        $todos = $this->indexActionJSON()->getContent();

        return $this->app['twig']->render('todos.html', ['todos' => $todos, 'form' => $form->createView() ]);
    }

    /**
     * User todos index as JSON response
     *
     * @return string JSON response
     */
    public function indexActionJSON()
    {

        $todos = $this->orm_em->getRepository($this->entity_class)->getUserTodos($this->userid);
        if (!$todos) {

            return $this->app->json([]);
        }
        foreach($todos as $todo) {

            $api[] = $this->getAPIContent($todo);
        }

        return $this->app->json($api);
    }

    /**
     * Todo single view and edit
     *
     */
    public function singleAction()
    {

        // bug squash: check if todo id exists and not if request id
        $todo = $this->getRequestedEntity();
        if ($todo) {
            if ($this->isEntityOwner($todo)) {
                $form = $this->app['form.factory']->create(TodoType::class, $todo);
                $this->handleForm($form, $todo, 'edited');
                return $this->app['twig']->render('todo.html', [$this->entity_name => $todo, 'form' => $form->createView() ]);              

            }
            else {

                $message = "You are not authorized to view this $this->entity_name!";

            }
        }
        else {

            $message = "The specified $this->entity_name does not exist!";

        }

        $this->hasMessage($message);

        return $this->mainIndexRedirect();
    }


    /**
     * Todo single view in JSON
     *
     * @return string|RedirectResponse JSON|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewActionJSON()

    {
        $todo = $this->getRequestedEntity();
        if ($todo) {
            if ($this->isEntityOwner($todo)) {
                $todo = $this->getAPIContent($todo);

                $method = $this->request->get('method');
                if ($method == 'inline') {
                    $todo = $this->app->json($todo)->getContent();
                    return $this->app['twig']->render('todo-json.html', [$this->entity_name => $todo, ]);

                }
                else if ($method == 'raw') {
                    return $this->app->json($todo);
                }
                else {
                    $message = 'An error occured, sorry';
                }
            }
            else {
                $message = "You are not authorized to view this $this->entity_name!";
            }
        }
        else {
            $message = "The specified $this->entity_name does not exist!";
        }

        $this->hasMessage($message);
        return $this->mainIndexRedirect();
    }

    /**
     * Delete a todo
     *
     */
    public function deleteAction()
    {

        // bug squash: check if todo id exists and not if request id
        $todo = $this->getRequestedEntity();
        if ($todo) {

            // add a check - if todo belongs to this user

            if ($this->isEntityOwner($todo)) {
                $this->orm_em->remove($todo);
                $this->orm_em->flush();

                $message = "The $this->entity_name has been removed!";

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
     * Get API content
     *
     * @param Todo $todo
     * @return array
     */
    public function getAPIContent(Todo $todo)
    {

        $api = array(
            'id' => $todo->getId(),
            'title' => $todo->getTitle(),
            'status' => $todo->getStatus(),
            'description' => $todo->getDescription(),
            'url' => $this->main_index_url . '/' . $todo->getId(),
            'delete' => $this->main_index_url . '/delete/' . $todo->getId(),
            'created' => $todo->getCreatedDateTime(),
            'updated' => $todo->getUpdatedDateTime(),
        );

        return $api;
    }

    /**
     * handleForm() hook for Entity specific requests to be executed before saving to db
     *
     * @param Entity $entity
     * @return null
     */
    public function on_save_hook($entity)
    {

        if ($entity->getuser_id() === null) {
            $entity->setuser_id($this->userid);
        }

        return;
    }
}
