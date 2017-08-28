<?php

namespace App\Controller;

class IndexController extends BaseController
{
    /**
     * Redirect to homepage view with README content
     *
     * @return mixed
     */
    public function indexAction()
    {
        $content = file_get_contents(__DIR__.'/../../../README.md');

        return $this->app['twig']->render('index.html.twig', [
            'readme' => $content
        ]);
    }
}
