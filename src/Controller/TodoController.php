<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RequestStack;
use Entity\Todo;
use Entity\User;
use Doctrine\ORM\EntityManager;
use Form\TodoType;
use Symfony\Component\Form\Form;

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
        $this->entity_name = 'todo';
        $this->main_index_url = '/' . $this->entity_name;

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
        foreach($todos as $todo) {
            // 'title' and 'status' are not yet implemented so we will use some temporary data
            $api[] = array(
                'id' => $todo->getId() ,
                'title' => $todo->getDescription() ,
                'status' => 'in progress' ,
                'description' => $todo->getDescription() ,
                'url' => $this->main_index_url . '/' . $todo->getId(),
                'delete' => $this->main_index_url . '/delete/' . $todo->getId(),
            );

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


    /**
     * Update database upon succesfull submission for both add new and edit entity requests.
     *
     * @param Symfony\Component\Form\Form $form
     * @param Todo $todo
     * @param string $msg
     * @return null
     */
    public function handleForm(Form $form, Todo $todo, $msg)
    {

        $form->handleRequest($this->request);

        if ($form->isSubmitted()) {

            //$this->handleErrors($form);

            if ($form->isValid()) {
                $todo->setuser_id($this->userid);
                $this->orm_em->persist($todo);
                $this->orm_em->flush();
                $this->hasMessage("The $this->entity_name has been $msg");
            }

        }

        return;
    }

    /**
     * Get form submission errors
     *
     * Not being used at the moment - using symfony forms' built in error messaging instead
     *
     * @param Symfony\Component\Form\Form $form
     * @return null
     */
    public function handleErrors(Form $form)
    {

        $errors = $this->app['validator']->validate($form);

        if (count($errors) > 0) {
            $x = 1;
            foreach ($errors as $error) {
                $err[] = $x . ': ' . $error->getMessage();
                $x++;
            }

            $msg = implode(" ",$err);
            //do something with errors
            //ex log them somewhere or show to user:
            //$this->hasMessage($msg);
        }

        return;
    }
}
