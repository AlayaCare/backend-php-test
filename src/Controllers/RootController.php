<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Models\LoginModel;

class RootController implements ControllerProviderInterface
{

  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    $controllers->get('/', function () use ($app) {
        return $app['twig']->render('index.html', [
            'readme' => file_get_contents('../README.md'),
        ]);
    });

    $controllers->match('/login', function (Request $request) use ($app) {
        $username = $request->get('username');
        $password = $request->get('password');

        if ($username) {
            $LoginModel = new LoginModel($app);
            $user = $LoginModel->tryLogin($username, $password);

            if ($user){
                $app['session']->set('user', $user);
                return $app->redirect('/todo/list');
            }
        }

        return $app['twig']->render('login.html', array());
    });

    $controllers->get('/logout', function () use ($app) {
        $app['session']->set('user', null);
        return $app->redirect('/');
    });

    return $controllers;
  }

}
