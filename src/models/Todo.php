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
			$todos = self::$db->fetchAll('SELECT * FROM todos WHERE id = ?', array($id), array(\PDO::PARAM_INT));
			if (!empty($todos)){
				$this->fillObjectFromArray($todos[0]);
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
        $records = self::$db->fetchAll("SELECT * FROM todos WHERE user_id = ? order by is_complete, id desc limit ?, ?", array($userId, $limitFrom, $limitOffset), array(\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT));
        $objects = array();
        foreach ($records as $oneRecord){
        	$oneObject = new Todo();
        	$oneObject->fillObjectFromArray($oneRecord);
        	$objects[] = $oneObject;
        }
        return $objects;
	}
	public static function getNumberOfUserTasks($userId){
    	$nbOfRecords = self::$db->fetchAll("SELECT count(*) as nb FROM todos WHERE user_id = ?", array($userId), array(\PDO::PARAM_INT));
    	return $nbOfRecords[0]["nb"];
	}
	public function save(){
		if ($this->getId()){//Update
			$stmt = self::$db->prepare("update todos set user_id = ?, description = ?, is_complete = ? WHERE id = ?");
			$stmt->bindValue(1, $this->getUserId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $this->getDescription(), \PDO::PARAM_STR);
			$stmt->bindValue(3, $this->getIsComplete(), \PDO::PARAM_INT);
			$stmt->bindValue(4, $this->getId(), \PDO::PARAM_INT);
			$stmt->execute();
		}
		else{//Insert
			$stmt = self::$db->prepare("INSERT INTO todos (user_id, description, is_complete) VALUES ( ?, ?, ?)");
			$stmt->bindValue(1, $this->getUserId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $this->getDescription(), \PDO::PARAM_STR);
			$stmt->bindValue(3, $this->getIsComplete(), \PDO::PARAM_INT);
			$stmt->execute();
			
			$id = self::$db->lastInsertId();
			$this->setId($id);
		}
	}
	public function delete(){
		if ($this->getId()){
	        $stmt = self::$db->prepare("DELETE FROM todos WHERE id = ?");
	        $stmt->bindValue(1, $this->getId(), \PDO::PARAM_INT);
	        $stmt->execute();
	        $this->setId(null);
		}
	}
}
