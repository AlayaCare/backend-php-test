<?php

namespace Models;

class TodoModel
{
  public $app;
  private $sql;

  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
    $this->sql = $this->app['db']->createQueryBuilder();
  }

  public function count(String $filterCol, $filterVal)
  {
    $this->sql
            ->select("COUNT(*)")
            ->from("todos")
            ->where("$filterCol = $filterVal");

    $count = $this->app['db']->fetchAll($this->sql);

    return $count[0]["COUNT(*)"];
  }

  public function select(array $args)
  {
    $offset = isset($args["offset"]) ? $args["offset"] : "0";
    $this->sql
            ->select($args['col'])
            ->from("todos")
            ->where($args['filterCol'] ." = ". $args['filterVal'])
            ->setFirstResult($offset);

    if(isset($args["limit"]))
      $this->sql->setMaxResults($args['limit']);

    return $fetch = $this->app['db']->fetchAll($this->sql);

  }

  public function insert(int $user_id, String $description)
  {
    $this->sql
            ->insert("todos")
            ->setValue("user_id", "'$user_id'")
            ->setValue("description", "'$description'");

    return $insert = $this->app['db']->executeUpdate($this->sql);
  }

  public function update(int $id,array $args)
  {
    $this->sql
            ->update("todos");

    foreach($args as $update)
      $this->sql->set($update['col'], $update['val']);

    $this->sql->where("id = $id");

    return $update = $this->app['db']->executeUpdate($this->sql);
  }

  public function deleteById(int $id)
  {
    $this->sql
            ->delete("todos")
            ->where("id = $id");
    return $delete = $this->app['db']->executeUpdate($this->sql);
  }

  public function selectAllByUser(array $args)
  {
    return $this->select(["col" => $args["col"], "filterCol" => "user_id", "filter_val" => $args["user_id"]]);
  }

  public function selectById(int $id)
  {
    return $this->select(["col" => "*", "filterCol" => "id", "filterVal" => $id])[0];
  }

}
