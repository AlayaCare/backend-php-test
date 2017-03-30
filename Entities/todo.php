<?php
/**
 * @Entity @Table(name="products")
 */
class Todo
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
     /** @Id @Column(type="integer")*/
    protected $user_id;
	/** @Column(type="string") */
	protected $description;
	/** @Column(type="boolean") */
	protected $mark;

    public function getId()
    {
        return $this->id;
    }

    public function getuser_id()
    {
        return $this->user_id;
    }

    public function setuser_id($name)
    {
        $this->name = $user_id;
    }

    public function getdescription()
    {
        return $this->description;
    }

    public function setdescription($description)
    {
        $this->name = $description;
    }

     public function getmark()
    {
        return $this->mark;
    }

    public function setmark($mark)
    {
         $this->mark=$mark;
    }

    // get a todo 
    public static function getTodo ($app,$id,$userId)
    {
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id='$userId'";
        $todo = $app['db']->fetchAssoc($sql);
        return $todo; 
    }
    // get all todos from userid
    public static function getAllTodo ($app,$userId)
    {
        $sql = "SELECT * FROM todos WHERE user_id = '$userId' ";
        $todos = $app['db']->fetchAll($sql);
        return $todos ;
    }
    // mark a todo
     public static function markTodo ($app,$id,$user_id)
    {
          $sql = "UPDATE todos SET mark=NOT mark WHERE id='$id' AND user_id = '$user_id'";
          $app['db']->executeUpdate($sql);
    }

    public static function insertTodo ($app,$user_id,$description)
    {
       $app['db']->insert('todos', array(
        'user_id' => $user_id,
        'description' => $description,
            ));
    }

      public static function deleteTodo ($app,$id,$user_id)
    {
        $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id' ";
        $app['db']->executeUpdate($sql);
    }

       
}