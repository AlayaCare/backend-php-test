<?php

namespace Repository;

use Doctrine\DBAL\Connection;
use Entity\Todo;

/**
 * Class TodoRepository
 *
 * @package Repository
 *
 * @author Jerome Catric
 */
class TodoRepository extends AbstractRepository
{

    /**
     * Constructor
     *
     * @param Connection $db the connection to the database
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->tableName = 'todos';
    }

    /**
     * Convert an associative array to an object
     *
     * @param array $data the associative array
     *
     * @return \Entity\TODO the object todo
     */
    protected function convertArrayToObject(array $data)
    {
        $todo = new Todo();
        $todo->setId($data['id']);
        $todo->setDescription($data['description']);
        $todo->setUserId($data['user_id']);
        $todo->setCompleted($data['completed']);

        return $todo;
    }

    /**
     * Convert an object todo to an associative array
     *
     * @param \Entity\TODO $entity The object todo
     *
     * @return array The associative array
     */
    protected function convertObjectToArray($entity)
    {
        $data = array();
        $data['id'] = $entity->getId();
        $data['description'] = $entity->getDescription();
        $data['user_id'] = $entity->getUserId();
        $data['completed'] = $entity->isCompleted();

        return $data;
    }

    /**
     * Mark a todo completed
     *
     * @param int $id
     * @param boolean $completed
     */
    public function markCompleted($id, $completed)
    {
        $data = array('completed' => $completed);
        $this->db->update($this->tableName, $data, array('id' => $id));
    }

}