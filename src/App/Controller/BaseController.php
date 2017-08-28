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

    protected function getUserSession() {
        return $this->app['security.token_storage']->getToken()->getUser();
    }

    protected function cannotUpdate($id) {
        $user = $this->getUserSession();
        return null === $user || ($user && $id && intval($user->getId()) !== $id);
    }

    protected function login() {
        return $this->app->redirect($this->app['url_generator']->generate('login'));
    }
}
