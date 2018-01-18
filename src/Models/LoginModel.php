<?php

namespace Models;

class LoginModel
{
  public $app;
  private $sql;

  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
    $this->sql = $this->app['db']->createQueryBuilder();
  }

  public function tryLogin(String $username, String $password)
  {
    $this->sql
            ->select("*")
            ->from("users")
            ->where("username = '$username'")
            ->andWhere("password = '$password'");

    return $user = $this->app['db']->fetchAssoc($this->sql);
  }

}
