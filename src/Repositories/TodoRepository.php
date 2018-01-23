<?php
namespace Repositories;

use Entities\Todo;
use Entities\User;

/**
 * TodoRepository contains methods to separate logic from the Todo Model
 */
class TodoRepository
{
  /**
   * @param Object $entityManager the Doctrine EntityManager
   */
  public function __construct($entityManager)
  {
    $this->em = $entityManager;
  }

  /**
   * Save a new Todo to the logged user
   * @param  int    $userId
   * @param  string $description
   * @return boolean
   */
  public function save(int $userId, string $description)
  {
    try {
      $todo = new Todo;
      $user = $this->em->find(User::class, $userId);

      $todo->setUser($user)->setDescription($description);

      $this->em->persist($todo);
      $this->em->flush();

      $add = true;

    } catch (Exception $e) {
      $add = false;
    }

    return $add;
  }

  /**
   * Delete a Todo by the id on the logged User
   * @param  int    $userId
   * @param  int    $todoId
   * @return boolean
   */
  public function delete(int $userId, int $todoId)
  {
    $todo = $this->em->find(Todo::class, $todoId);

    if($todo->getUser()->getId() == $userId){
      try {
        $this->em->remove($todo);
        $this->em->flush();
        $delete = true;
      } catch (Exception $e) {
        $delete = false;
      }
      return $delete;
    }

    return false;
  }

  /**
   * Set a todo as completed
   * @param int $userId
   * @param int $todoId
   * @return boolean
   */
  public function setAsCompleted(int $userId, int $todoId)
  {
    $todo = $this->em->find(Todo::class, $todoId);

    if($todo->getUser()->getId() == $userId){
      try {
        $todo->setAsCompleted();

        $this->em->merge($todo);
        $this->em->flush();

        $complete = true;

      } catch(Exception $e) {
        $complete = false;
      }

      return $complete;
    }
    return false;
  }

  /**
   * Returns an array with info about the pagination
   * @param  int    $page   current page
   * @param  int    $limit  offset value for the query
   * @param  int    $userId logged user id
   * @return array  $pagination['type']   = 'small' | 'big' | null
   *                $pagination['pages']  = {how many pages of todos the user has}
   *                $pagination['page']   = {the current page}
   *                $pagination['todos']  = {query result for the page} 
   */
  public function paginate(int $page, int $limit, int $userId)
  {
    $total = $this->em->createQueryBuilder()
          ->select("Count('*')")
          ->from(Todo::class, 't')
          ->where("t.user = " . $this->em->find(User::class, $userId)->getId());

    $total = $total->getQuery()->getSingleScalarResult();

    $pages = ceil($total / $limit);

    $pagination = array();

    if($pages < 5 && $pages > 1) {
      $pagination['type']   = 'small';
    } elseif($pages >= 5) {
      $pagination['type'] = 'big';
    } else {
      $pagination['type'] = null;
    }

    $pagination['pages']  = $pages;
    $pagination['page'] = $page;

    $pagination['todos'] = $this->em->createQueryBuilder()
          ->select("t")
          ->from(Todo::class, 't')
          ->where("t.user = " .$this->em->find(User::class, $userId)->getId())
          ->setFirstResult(($page - 1) * $limit)
          ->setMaxResults($limit);
    $pagination['todos'] = $pagination['todos']->getQuery()->getResult();

    return $pagination;
  }
}
