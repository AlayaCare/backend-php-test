<?php

namespace Dal;


use Symfony\Component\Security\Acl\Exception\Exception;

class TodoRepo
{
    private $database;
    function __construct($database) {
        if(!$database){
            throw new \Exception("Argument database cannot be null");
        }
        $this->database = $database;
    }

    public function findById($user_id, $id){
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
        return $this->database->fetchAssoc($sql);
    }

    public function findAll($user_id){
        $sql = "SELECT * FROM todos WHERE user_id = '$user_id'";
        return $this->database->fetchAll($sql);
    }

    public function findLimited($user_id, $nb, $offset){
        $sql = "SELECT * FROM todos WHERE user_id = '$user_id' LIMIT $offset, $nb";
        return $this->database->fetchAll($sql);
    }

    public function countAll($user_id){
        $sqlCount = "SELECT COUNT(id) as elementTotal FROM todos WHERE user_id = '$user_id'";
        $elementTotal = $this->database->fetchAssoc($sqlCount);
        return $elementTotal['elementTotal'];
    }

    public function add($user_id, $description){
        if(!empty($description)) {
            $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
            $this->database->executeUpdate($sql);
            return true;
        }
        return false;
    }

    public function delete($user_id, $id){
        $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
        $this->database->executeUpdate($sql);
    }


    public function updateStatus($user_id, $id, $status){
        $datetime = new \DateTime();
        $datetimeStr =$datetime->format('Y\-m\-d\ h:i:s');
        $sql = "UPDATE todos SET status = b'$status', statusDate = '$datetimeStr' WHERE id = '$id' AND user_id = '$user_id'";
        $this->database->executeUpdate($sql);
    }
}