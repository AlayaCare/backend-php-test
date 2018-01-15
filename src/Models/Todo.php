<?php
/**
 * Created by PhpStorm.
 * User: ofaruk
 * Date: 2018-01-06
 * Time: 1:24 AM
 */

namespace Omar\Models;

use Omar\Pagination;
use \RedBeanPHP\R;
use Symfony\Component\BrowserKit\Request;

class Todo extends \RedBeanPHP\SimpleModel
{
    /**
     * table name
     * @var string
     */
    protected $table = 'todos';


    /**
     * Get all the todo's
     * based one condition
     * @param $userId
     * @param $limit
     * @param $page
     * @return array
     */
    public function getAll($userId, $limit, $page) {
        $result = [];
        $links = 3;
        //user_id taken from session so this query is safe
        $query = "SELECT * from ".$this->table." where user_id = $userId";
        $count =  R::getRow("select count(*) as num from ".$this->table." Where user_id=?",[$userId]);
        $total = $count["num"];
        $paginator  = new Pagination($query, $total);

        $todos = $paginator->getData( $limit, $page );
        $link = $paginator->createLinks( $links, 'pagination pagination-sm' );

        $result = ['todos' => $todos, 'link' => $link];

        return $result;
    }

    /**
     * To delete an todo
     * @param $id
     */
    public function delete($id, $userId){
        $query = R::load($this->table, intval($id));
        if($query->user_id == $userId) {
            $result = R::trash($query);
            return $result;
        } else {
            return false;
        }

    }

    /**
     * To insert an row
     * @param $userId
     * @param $description
     * @return int|string
     */
    public function add($userId, $description) {

        $todo = R::dispense($this->table);
        $todo->user_id = $userId;
        $todo->description = $description;
        $id = R::store($todo);

        return $id;
    }

    /**
     * To update an row
     * @param $id
     * @param $userId
     * @return int|string
     */
    public function update($id, $userId) {
        $todo = R::dispense($this->table);
        $todo->id = $id;
        $todo->user_id = $userId;
        $todo->complete = 1;
        $result = R::store($todo);

        return $result;
    }

    /**
     * To get an to single todo
     * @param $id
     * @param $userId
     * @return array
     */
    public function getTodoById($id, $userId){
        $todo = R::getRow( 'SELECT `id`,`user_id`,`description` FROM '.$this->table.' WHERE id = ? AND user_id = ?',
            [$id, $userId]);

        return $todo;
    }

}