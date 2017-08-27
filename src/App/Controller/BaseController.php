<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class BaseController
{
    protected $app;
    protected $request;

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request->getCurrentRequest();
    }

    // TODO needs to be moved and secured
    protected function isUserLoggedIn() {
        if (null === $this->getUserSession()) {
            $this->login();
        }
    }

    // TODO needs to be moved and secured
    protected function getUserSession() {
        return $this->app['session']->get('user');
    }

    // TODO needs to be moved and secured
    protected function cannotUpdate($id) {
        $user = $this->getUserSession();
        return null === $user || ($user && $id && intval($user['id']) !== $id);
    }

    // TODO needs to be moved and secured
    protected function login() {
        return $this->app->redirect($this->app['url_generator']->generate('login'));
    }
}
