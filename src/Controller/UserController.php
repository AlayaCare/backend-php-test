<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Form\LoginType;

class UserController extends BaseController
{

    /**
     * Login action.
     *
     * @param Request $request
     * @return string Twig template
     */
    function loginAction()
    {
        $form = $this->app['form.factory']->create(LoginType::class);

        return $this->app['twig']->render('login.html', array(
            'form' => $form->createView() ,
            'error' => $this->app['security.last_error']($this->request) ,
            'last_username' => $this->app['session']->get('_security.last_username') ,
            'allowRememberMe' => isset($this->app['security.remember_me.response_listener']),
        ));
    }

    /**
     * Logout action.
     *
     * @return null
     */
    function logoutAction()
    {

        $this->app['session']->set('user', null);

    }
}
