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
        $username = $this->request->get('username');
        $password = $this->request->get('password');
        
        if ($username) {
            $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
            $user = $this->app['db']->fetchAssoc($sql);

            if ($user){
                $this->app['session']->set('user', $user);
                return $this->app->redirect('/todos');
            }
        }

        return $this->app['twig']->render('login.html.twig', array());
    }

    /**
     * Logout user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction()
    {
        $this->app['session']->set('user', null);
        return $this->app->redirect('/');
    }
}
