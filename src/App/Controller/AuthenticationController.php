<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends BaseController
{
    /**
     * Entity Manager
     * @var EntityManager
     */
    protected $em;

    /**
     * AuthenticationController constructor.
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
     * Login user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginAction()
    {
        $form = $this->app['form.factory']->createBuilder('form')
            ->add('username', 'text', array('label' => 'Username', 'data' => $this->app['session']->get('_security.last_username')))
            ->add('password', 'password', array('label' => 'Password'))
            ->add('login', 'submit')
            ->getForm();

        $data = array(
            'form'  => $form->createView(),
            'error' => $this->app['security.last_error']($this->request),
        );

        return $this->app['twig']->render('login.html.twig', $data);
    }

    /**
     * Logout user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction()
    {
        $this->app['session']->clear();
        return $this->app->redirect($this->app['url_generator']->generate('homepage'));
    }
}
