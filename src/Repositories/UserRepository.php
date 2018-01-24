<?php
namespace Repositories;

use Entities\User;

/**
 * UserRepository contains methods to separate logic from the User Model
 */
class UserRepository
{
  /**
   * @param Object $entityManager the Doctrine EntityManager
   */
  public function __construct($entityManager)
  {
    $this->em = $entityManager;
  }

  /**
   * Try to login with the username and password given
   * @param  string $username
   * @param  string $password
   * @return boolean|array   false when could not login or
   *                         array with the id and password when true
   */
  public function tryLogin(string $username, string $password)
  {
    $user = false;

    $login = $this->em->getRepository(User::class)->findOneBy(
      [
        "username"  =>  $username,
        "password"  =>  $password,
      ]
    );

    if($login){
      $user['id'] =  $login->getId();
      $user['name'] = $login->getUsername();
    }

    return $user;
  }
}
