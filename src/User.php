<?php
class User{
  public $id = null;
  public $username = "";
  public $password = "description";
  private $app = null;
  function __construct($app) {
    $this->app = $app;
  }
   
   function login($username, $password){
      $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
      return $this->app['db']->fetchAssoc($sql);
   }
}