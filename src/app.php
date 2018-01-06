<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Lokhman\Silex\Provider\ConfigServiceProvider;


class App extends Silex\Application

{
    /**
     * Class constructor
     *
     * Registering the App Service Providers
     */
    public function __construct()

    {
        parent::__construct();

        $this->registerConfigServiceProvider();
        $this->registerDoctrineServiceProvider();
        $this->registerAssetServiceProvider();
        $this->registerOtherServiceProviders();
    }

    /**
     * Register Config service provider
     *
     *  If a web server deployment environment 'SILEX_ENV' is not setup, it will default to local:
     *  $app['config.env.default'] = 'local';
     *  $app['config.varname.default'] = 'SILEX_ENV';
     *  You can override both server and default values by uncommenting/setting the 'config.env' below
     *  See https://github.com/lokhman/silex-config under Global environment variable and public function register(Container $app))
     */
    private function registerConfigServiceProvider()
    {
        $this->register(new ConfigServiceProvider() , array(
            'config.dir' => __DIR__ . '/../config/',
            // 'config.env' => 'prod',
        ]);
    }

    /**
     * Register Doctrine service provider
     *
     */
    private function registerDoctrineServiceProvider()
    {
        $this->register(new DoctrineServiceProvider, array(
            'db.options' => $this['config']['database'],
        ));
    }

    /**
     * Register asset service provider
     *
     */
    private function registerAssetServiceProvider()
    {

        $this->register(new AssetServiceProvider() , array(
            'assets.version' => $this['config']['assets']['version'],
            'assets.version_format' => $this['config']['assets']['version_format'],
            'assets.named_packages' => $this['config']['assets']['named_packages'],
        ));
    }

    /**
     * Register other service providers
     *
     * Well since we're not passing any parameters with these, let's just register them in one go for now
     */
    private function registerOtherServiceProviders()
    {

        $this->register(new ServiceControllerServiceProvider());
        $this->register(new SessionServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new TwigServiceProvider());
        $this->register(new HttpFragmentServiceProvider());

    }
}


$app = new App();

return $app;
