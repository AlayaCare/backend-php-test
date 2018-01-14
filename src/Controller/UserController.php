<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Form\LoginType;

class UserController
{


    /** @var \Silex\Application */
    protected $app;


    /**
     * Class constructor
     * @param Application $app
     * 
     */
    public function __construct(Application $app)

    {
        $this->app = $app;
    }

    /**
     * Login action.
     *
     * @param Request $request
     * @return string Twig template
     */
    function loginAction(Request $request)
    {
        $form = $this->app['form.factory']->create(LoginType::class);

        return $this->app['twig']->render('login.html', array(
            'form' => $form->createView() ,
            'error' => $this->app['security.last_error']($request) ,
            'last_username' => $this->app['session']->get('_security.last_username') ,
            'allowRememberMe' => isset($this->app['security.remember_me.response_listener']),
        ));
    }

}
