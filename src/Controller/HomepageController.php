<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class HomepageController extends BaseController
{

    /**
     * Homepage view
     * @param RequestStack $requestStack
     * @param Application $app
     * @return string Twig template
     */
    public function indexAction()
    {

        $data = array(
            'readme' => file_get_contents('../README.md'),
        );
        return $this->app['twig']->render('index.html', $data);
    }
}
