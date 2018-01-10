<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class HomepageController
{

    /**
     * Homepage view
     * @param RequestStack $requestStack
     * @param Application $app
     * @return string Twig template
     */
    public function indexAction(Request $request, Application $app)
    {
        $data = array(
            'readme' => file_get_contents('../README.md'),
        );
        return $app['twig']->render('index.html', $data);
    }
}
