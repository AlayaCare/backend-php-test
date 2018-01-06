<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController
{

    public function loginAction(Request $request, Application $app)
    {

		// Moved legacy controller code temporarily
    	$username = $request->get('username');
    	$password = $request->get('password');

    	if ($username) {
        	$sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        	$user = $app['db']->fetchAssoc($sql);

        	if ($user){
            	$app['session']->set('user', $user);
            	return $app->redirect('/todo');
        	}
    	}
    	return $app['twig']->render('login.html', array());

    }

    public function logoutAction(Request $request, Application $app)
    {

		// Moved legacy controller code temporarily
    	$app['session']->set('user', null);
    	return $app->redirect('/');
    }

}
