<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Entity\Entity;

abstract class EntityController extends BaseController

{

    /** @var \Silex\Application */
    protected $app;

    /** @var RequestStack */
    protected $requestStack;

    /** @var \Doctrine\ORM\EntityManager */
    protected $orm_em;


    /**
     * Class constructor
     * @param Application $app
     * @param RequestStack $requestStack
     * @param EntityManager $orm_em
     */
    public function __construct(Application $app, RequestStack $requestStack, EntityManager $orm_em)
    {
        $this->app = $app;
        $this->request = $requestStack->getCurrentRequest();
        $this->orm_em = $orm_em;
    }


    /**
     * Get requested entity object if it exists
     *
     * @return Entity|null
     */
    public function getRequestedEntity($id = 'id')

    {

        $entity_id = $this->request->get($id);
        $entity = $this->orm_em->find($this->entity_class, $entity_id);  
        if ($entity) {

            return $entity;
        }

        return;
    }

    /**
     * Update database upon succesfull submission for both add new and edit entity requests.
     *
     * @param Symfony\Component\Form\Form $form
     * @param Entity $entity
     * @param string $msg
     * @return null
     */
    public function handleForm(Form $form, Entity $entity, $msg)
    {

        $form->handleRequest($this->request);

        if ($form->isSubmitted()) {

            //$this->handleErrors($form);

            if ($form->isValid()) {
                $this->on_save_hook($entity);
                $this->orm_em->persist($entity);
                $this->orm_em->flush();
                $this->hasMessage("The $this->entity_name has been $msg");
            }

        }

        return;
    }


    /**
     * Get form submission errors
     *
     * Not being used at the moment - using symfony forms' built in error messaging for user feedback
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



    /**
     * handleForm() hook for Entity specific requests to be executed before saving to db
     *
     * @param Entity $entity
     * @return null
     */
    public function on_save_hook($entity)
    {

    }

}
