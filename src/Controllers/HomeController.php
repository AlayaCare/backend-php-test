<?php
namespace Controllers;

use \Repositories\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * HomeController Controlls the HomePage, Login and Logout
 */
class HomeController
{
  /**
   * @var Application $app Silex Application object
   */
  private $app;

  /**
   * Get the Silex Application and the Doctrine EntityManager
   * @param SilexApplication $app Silex Application object
   */
  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
    $this->em = $app['orm.em'];
  }

  /**
   * Render the Home Page
   * @return view
   */
  public function index()
  {
    return $this->app['twig']->render('index.html', [
      'readme'  =>  file_get_contents('../README.md')
    ]);
  }

  /**
   * Show the Login form or try to login
   * @param  Request $request
   * @return view
   */
  public function login(Request $request)
  {
    $user = false;
    $username = $request->get('username');
    $password = $request->get('password');

    if($username){

      $rules = new Assert\Collection([
        "username" => [new Assert\NotBlank(), new Assert\Type("string")],
        "password" => [new Assert\NotBlank(), new Assert\Type("string")]
      ]);

      $errors = $this->app['validator']->validate(["username" => $username, "password" => $password], $rules);

      if(count($errors) == 0){
        $userRepo = new UserRepository($this->em);
        $user = $userRepo->tryLogin($username, $password);
      }

      if($user){
        $this->app["session"]->set('user', $user);
        return $this->app->redirect('/todo');
      } else {
        $this->app['session']->getFlashBag()->add('danger', 'Username and/or password invalid!');
      }

    }
    return $this->app['twig']->render('login.html', array());
  }

  /**
   * Logout the user
   * @return redirect
   */
  public function logout()
  {
    $this->app['session']->set('user', null);
    return $this->app->redirect('/');
  }
}
