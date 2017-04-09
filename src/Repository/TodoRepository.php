<?php

namespace \Repository;

use Doctrine\DBAL\Connection;

class TodoRepository {
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    public function get($id) {
        return $db->fetchAssoc("SELECT * FROM todos WHERE id = ?", array($id));
    }

    public function getAllbyUser($userId) {
        return $db->fetchAll("SELECT id FROM todos WHERE user_id = ?", array($userId));
    }

    public function add($userId, $description) {
        // INSERT INTO todos (user_id, description) VALUES (?, ?) ($userId, $description) 
        $db->insert("todos", array(
            "user_id" => $userId, 
            "description" => $description
            ));
    }

    public function update($id, $description) {
        // UPDATE todos (description) VALUES (?) WHERE id = ? ($description, $id)
        $db->update("todos", array(
            "description" => $description,
            array(
                "id" => $id
            )
        ));
    }

    public function delete($id) {
        // DELETE FROM todos WHERE id = ? ($id)
        $db->delete('todos', array(
            "id" = $id
        ));
    }

}

?>