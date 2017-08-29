<?php
/**
 * Created by PhpStorm.
 * User: kienhungtran
 * Date: 2017-08-28
 * Time: 5:15 AM
 */

namespace App\Repositories;

use App\Objects\PaginatedObjects;
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
     * TodoRepository constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * @param $userId
     * @param int $page
     * @return PaginatedObjects
     */
    public function getAllTodos($userId, $page = 1)
    {
        $countSql = "SELECT COUNT(*) FROM todos WHERE user_id = ?";
        $countResult  = $this->app['db']->executeQuery($countSql, [$userId]);
        $count = (int) $countResult->fetchColumn();

        $offset = ($page * self::TODO_LIMIT) - self::TODO_LIMIT;
        $sql = "SELECT * FROM todos WHERE user_id = ? LIMIT " . self::TODO_LIMIT . " OFFSET " . $offset;

        $items = $this->app['db']->fetchAll($sql, [$userId]);

        return new PaginatedObjects(self::TODO_LIMIT, $count, $items, $page);
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