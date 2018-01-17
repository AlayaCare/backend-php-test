<?php

namespace Providers;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Services\FPaginationService as Pagination;

class FPaginationServiceProvider implements ServiceProviderInterface
{
  public function register(\Silex\Application $app)
  {
    $app['fpagination'] = new Pagination($app);
  }

  public function boot(\Silex\Application $app){}

}
