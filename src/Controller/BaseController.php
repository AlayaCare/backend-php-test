<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseController

{

    /** @var \Silex\Application */
    protected $app;

    /** @var RequestStack */
    protected $requestStack;


    /**
     * Class constructor
     * @param Application $app
     * @param RequestStack $requestStack
     */
    public function __construct(Application $app, RequestStack $requestStack)
    {
        $this->app = $app;
        $this->request = $requestStack->getCurrentRequest();
        $this->main_index_bind = 'home';
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

        return $this->app->redirect(($this->app['url_generator']->generate($this->main_index_bind)));

    }

}
