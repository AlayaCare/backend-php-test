<?php

namespace Repository;

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
        // retrieve a single todo record
        return $this->db->fetchAssoc("SELECT * FROM todos WHERE id = ?", array($id));
    }

    public function getAllbyUser($userId) {
        // return all todo records for this user
        return $this->db->fetchAll("SELECT id FROM todos WHERE user_id = ?", array($userId));
    }

    public function getPagebyUser($userId, $num_rows, $offset) {
        // return subset of todos for this user
        $stmt = $this->db->prepare("SELECT * FROM todos WHERE user_id = :user_id LIMIT :num_rows OFFSET :offset");
        $stmt->bindValue(":user_id", $userId, \PDO::PARAM_INT);
        $stmt->bindValue(":num_rows", $num_rows, \PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function add($userId, $description) {
        // INSERT INTO todos (user_id, description) VALUES (?, ?) ($userId, $description) 
        $this->db->insert("todos", array(
            "user_id" => $userId, 
            "description" => $description
            ));
    }

    public function toggleDone($id) {
        // toggle the completed attribute for the particular todo id
        $this->db->executeUpdate("UPDATE todos SET completed = !completed WHERE id = :id", array(
            "id" => $id
        ));
    }

    public function delete($id) {
        // DELETE FROM todos WHERE id = ? ($id)
        $this->db->delete('todos', array(
            "id" => $id
        ));
    }

    public function count($userId) {
        // return total number of records for a particular user
        return $this->db->fetchColumn("SELECT COUNT(*) FROM todos WHERE user_id = ?", array($userId), 0);   
    }

}

?>