<?php
namespace models;
class Todo extends Model{
	private $id = 0;
	private $user_id = "";
	private $description = "";
	private $is_complete = "";
	
	public function getId(){return $this->id;}
	public function setId($v){$this->id = $v;}
	
	public function getUserId(){return $this->user_id;}
	public function setUserId($v){$this->user_id = $v;}

	public function getDescription(){return $this->description;}
	public function setDescription($v){$this->description = $v;}

	public function getIsComplete(){return $this->is_complete;}
	public function setIsComplete($v){$this->is_complete = $v;}
	
	public function __construct($id = null){
		if ($id){
			$sql = "SELECT * FROM todos WHERE id = '$id'";
			$todo = self::$db->fetchAssoc($sql);
			if ($todo){
				$this->fillObjectFromArray($todo);
			}
		}
	}
	
	public function fillObjectFromArray($array){
        $this->setId(isset($array["id"]) ? $array["id"] : null);
        $this->setUserId(isset($array["user_id"]) ? $array["user_id"] : null);
        $this->setDescription(isset($array["description"]) ? $array["description"] : null);
        $this->setIsComplete(isset($array["is_complete"]) ? $array["is_complete"] : null);
	}
	
	public function getArrayFromObject(){
		return array("id" => $this->getId(),
					"user_id" => $this->getUserId(),
					"description" => $this->getDescription(),
					"is_complete" => $this->getIsComplete(),
		);
	}
	
	public static function getUserTasks($userId, $limitFrom, $limitOffset){
        $sql = "SELECT * FROM todos WHERE user_id = '{$userId}' order by is_complete, id desc limit {$limitFrom}, {$limitOffset}";
        $records = self::$db->fetchAll($sql);
        $objects = array();
        foreach ($records as $oneRecord){
        	$oneObject = new Todo();
        	$oneObject->fillObjectFromArray($oneRecord);
        	$objects[] = $oneObject;
        }
        return $objects;
	}
	public static function getNumberOfUserTasks($userId){
		$sql = "SELECT count(*) as nb FROM todos WHERE user_id = '{$userId}'";
    	$nbOfRecords = self::$db->fetchAll($sql);
    	return $nbOfRecords[0]["nb"];
	}
	public function save(){
		if ($this->getId()){//Update
			$sql = "update todos set user_id = '{$this->getUserId()}', description = '{$this->getDescription()}', is_complete = '{$this->getIsComplete()}' WHERE id = '{$this->getId()}'";
			self::$db->executeUpdate($sql);
		}
		else{//Insert
        	$sql = "INSERT INTO todos (user_id, description, is_complete) VALUES ('{$this->getUserId()}', '{$this->getDescription()}', '{$this->getIsComplete()}')";
        	self::$db->executeUpdate($sql);
			$id = self::$db->lastInsertId();
			$this->setId($id);
		}
	}
	public function delete(){
       $sql = "DELETE FROM todos WHERE id = '{$this->getId()}'";
       self::$db->executeUpdate($sql);
       $this->setId(null);
	}
}
