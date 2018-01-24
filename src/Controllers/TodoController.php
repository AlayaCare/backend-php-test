<?php
namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Entities\User;
use Entities\Todo;
use Repositories\TodoRepository;

/**
 * TodoController controlls all actions on the user's todos
 * like show all, show one, create, delete, and complete
 * All the methods should be in the guest middleware
 */
class TodoController
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
    $this->em  = $app['orm.em'];
  }

  /**
   * Todos Home pages
   * @param  int    $page
   * @return view
   */
  public function index(int $page)
  {
    $userId = $this->app['session']->get('user')['id'];

    $todoRepo = new TodoRepository($this->app['orm.em']);
    $paginatedTodos = $todoRepo->paginate($page, 5, $userId);

    return $this->app['twig']->render('todos.html', [
      'todos' => $paginatedTodos['todos'],
      'pages' => $paginatedTodos['pages'],
      'type'  => $paginatedTodos['type'],
      'page'  => $paginatedTodos['page'],
    ]);
  }

  /**
   * Show one single todo by its Id
   * @param  int    $id todo's id
   * @return view|redirect
   */
  public function single(int $id)
  {
    $userId = $this->app['session']->get('user')['id'];

    $todo = $this->em->find(Todo::class, $id);

    if($todo && $todo->getUser()->getId() == $userId){
      return $this->app['twig']->render('todo.html',['todo' => $todo]);
    }

    return $this->app->redirect('/todo');
  }

  /**
   * Show a single todo with a Json Format
   * @param  int    $id todo's Id
   * @return view|redirect
   */
  public function singleJSON(int $id)
  {
    $userId = $this->app['session']->get('user')['id'];

    $todo = $this->em->find(Todo::class, $id);

    if($todo && $todo->getUser()->getId() == $userId){
      $json = new \StdClass();
      $json->id = $todo->getId();
      $json->user_id = $todo->getUser()->getId();
      $json->description = $todo->getDescription();

      return $this->app['twig']->render('todoJson.html',['todo' => json_encode($json)]);
    }

    return $this->app->redirect('/todo');
  }

  /**
   * Creates a new Todo for the logged user
   * @param  Request $request
   * @return redirect
   */
  public function create(Request $request)
  {
    $userId = $this->app['session']->get('user')['id'];

    $description = $request->get('description');
    $errors = $this->app['validator']->validate($description, [new Assert\NotBlank(), new Assert\Type("string")]);

    if(count($errors) === 0){
        $todoRepo = new TodoRepository($this->em);
        $save = $todoRepo->save($userId, $description);
    } else {
      $this->app['session']->getFlashBag()->add('danger', 'A task must have a description!');
      return $this->app->redirect('/todo');
    }

    if($save){
      $this->app['session']->getFlashBag()->add('success', "Task $description added with success!");
    } else {
      $this->app['session']->getFlashBag()->add('danger', 'Unable to create your task, try later!');
    }

    return $this->app->redirect('/todo');
  }

  /**
   * Delete a todo by its id
   * @param  int    $id todo's id
   * @return redirect
   */
  public function delete(int $id)
  {
    $userId = $this->app['session']->get('user')['id'];

    $todoRepo = new TodoRepository($this->em);
    $delete = $todoRepo->delete($userId, $id);

    if($delete){
      $this->app['session']->getFlashBag()->add('danger', 'Task deleted!');
    } else {
      $app['session']->getFlashBag()->add('danger', 'Task was not deleted!');
    }

    return $this->app->redirect('/todo');
  }

  /**
   * Set a td as complete by its id
   * @param  int    $id todo's id
   * @return redirect
   */
  public function complete(int $id)
  {
    $userId = $this->app['session']->get('user')['id'];

    $todoRepo = new TodoRepository($this->em);

    $complete = $todoRepo->setAsCompleted($userId, $id);

    if($complete){
      $this->app['session']->getFlashBag()->add('success', 'Congratulations, you completed a task!');
    } else {
      $this->app['session']->getFlashBag()->add('danger', 'Sorry, unable to set as complete, try again later');
    }

    return $this->app->redirect('/todo');
  }

}
