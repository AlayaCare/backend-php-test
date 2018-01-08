<?php

namespace Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class UserController
{


    /** @var \Silex\Application */
    protected $app;


    /**
     * Class constructor
     * @param Application $app
     * 
     */
    public function __construct(Application $app)

    {
        $this->app = $app;
    }

    /**
     * Login action.
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    function loginAction(Request $request)
    {

        $form = $this->app['form.factory']->createNamedBuilder(null, FormType::class , array(

            /*'_username' => '',
            '_password' => '',
            '_target_path' => '',
            '_submit' => ''*/
        ))->add('_username', TextType::class , array(

            'label' => 'Username',
            'attr' => array(
                'name' => '_username',
                'class' => 'form-control',
                'placeholder' => 'myusername'
            ) ,
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))->add('_password', PasswordType::class , array(

            'label' => 'Password',
            'attr' => array(
                'name' => '_password',
                'class' => 'form-control',
                'placeholder' => 'mysecret'
            ) ,
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))->add('_target_path', HiddenType::class , array(

            'attr' => array(
                'name' => '_target_path',
                'value' => '/todo'
            )
        ))->add('_csrf_token', HiddenType::class , array(

            'attr' => array(
                'name' => '_csrf_token'
            )
            /*
        ))->add('_submit', ButtonType::class , array(
            'attr' => array(
                'name' => '_submit',
                'class' => 'btn btn-primary pull-right'
            )*/
        ))->getForm();
        return $this->app['twig']->render('login.html', array(
            'form' => $form->createView() ,
            'error' => $this->app['security.last_error']($request) ,
            'last_username' => $this->app['session']->get('_security.last_username') ,
            'allowRememberMe' => isset($this->app['security.remember_me.response_listener']),
        ));
    }

}
