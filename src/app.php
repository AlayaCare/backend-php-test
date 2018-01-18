<?php

use Silex\Application;
use Entity\UserEntity;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

use Silex\Provider\DoctrineServiceProvider;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager; 

use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\CsrfServiceProvider;

use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;


class App extends \Silex\Application

{
    /**
     * Class constructor
     *
     * Registering the App Service Providers
     */
    public function __construct()

    {
        parent::__construct();

        $this->registerDoctrineServiceProviders();
        $this->registerSecurityProviders();
        $this->registerViewRelatedServiceProviders();
        $this->registerControllerServiceProviders();
    }

    /**
     * Register Doctrine and Doctrine ORM service providers
     *
     */
    private function registerDoctrineServiceProviders()
    {

        $this->register(new DoctrineServiceProvider);

        $this['entity_manager'] = function () {
           $ORMdbParams = $this['db'];
           $ORMconfig = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . '/Entity/'), true, null, null, false);

           return EntityManager::create($ORMdbParams, $ORMconfig);

        };

    }

    /**
     * Register User and Firewall providers
     *
     */
    private function registerSecurityProviders()
    {

        $this['user.provider'] = function ($app)
        {
            return new Repository\UserProvider($this['entity_manager']->getConnection() , $app);
        };

        $this->register(new SecurityServiceProvider());
        $this->register(new CsrfServiceProvider());
    }

    /**
     * Register view related service providers
     *
     */
    private function registerViewRelatedServiceProviders()
    {

        $this->register(new ValidatorServiceProvider());
        $this->register(new FormServiceProvider());
        $this->register(new TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
        $this->register(new HttpFragmentServiceProvider());
        $this->register(new AssetServiceProvider());
        $this->register(new TwigServiceProvider());
        $this['twig'] = $this->extend('twig', function($twig, $app) {
            $twig->addGlobal('user', $app['session']->get('user'));

            return $twig;
        });

    }

    /**
     * Register remaining service providers
     *
     */
    private function registerControllerServiceProviders()
    {

        $this->register(new ServiceControllerServiceProvider());
        $this->register(new SessionServiceProvider());
    }

}


$app = new App();

return $app;
