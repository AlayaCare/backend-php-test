<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
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
            $user = false;

            $rules = new Assert\Collection([
              "username" => [new Assert\NotBlank(), new Assert\Type("string")],
              "password" => [new Assert\NotBlank(), new Assert\Type("string")]
            ]);

            $errors = $app['validator']->validate(["username" => $username, "password" => $password], $rules);

            if(count($errors) == 0){
              $LoginModel = new LoginModel($app);
              $user = $LoginModel->tryLogin($username, $password);
            }

            if ($user){
                $app['session']->set('user', $user);
                return $app->redirect('/todo/list');
            } else {
              $app['session']->getFlashBag()->add('danger', 'Username and/or password invalid!');
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
