<?php
/**
 * Created by PhpStorm.
 * User: kienhungtran
 * Date: 2017-08-28
 * Time: 5:15 AM
 */

namespace App\Repositories;

use App\Entities\Todo;
use App\Objects\PaginatedObjects;
use Doctrine\ORM\EntityManager;
use Silex\Application;

/**
 * Class TodoRepository
 * @package App\Repositories
 */
class TodoRepository
{
    const TODO_LIMIT = 5;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * TodoRepository constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
        $this->entityManager = $application['orm.em'];
    }

    /**
     * @param $userId
     * @param int $page
     * @return PaginatedObjects
     */
    public function getAllTodos($userId, $page = 1)
    {
        $qb = $this->entityManager->getRepository(Todo::class)->createQueryBuilder('n');
        $count = $qb->select('COUNT(n.id)')->where('n.userId = ?1')->setParameter(1,$userId)->getQuery()->getSingleScalarResult();

        $offset = ($page * self::TODO_LIMIT) - self::TODO_LIMIT;
        $todos = $qb->select('n')
            ->where('n.userId = ?1')
            ->setParameter(1,$userId)
            ->setFirstResult($offset)
            ->setMaxResults(self::TODO_LIMIT)
            ->getQuery()
            ->getResult();

        return new PaginatedObjects(self::TODO_LIMIT, $count, $todos, $page);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getTodo($id)
    {
        $sql = "SELECT * FROM todos WHERE id = ?";

        return $this->app['db']->fetchAssoc($sql, [$id]);
    }

    /**
     * @param $userId
     * @param $description
     */
    public function insertNewTodo($userId, $description)
    {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $this->app['db']->executeUpdate($sql, [$userId, $description]);
    }

    /**
     * @param $id
     */
    public function deleteTodo($id)
    {
        $sql = "DELETE FROM todos WHERE id = ?";
        $this->app['db']->executeUpdate($sql, [$id]);
    }

    /**
     * @param $id
     * @param $value
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateTodo($id, $value)
    {
        $sql = "UPDATE todos SET completed = ? WHERE id = ?";
        $this->app['db']->executeUpdate($sql, [$value, $id]);

        return $this->app->redirect('/todo');
    }
}