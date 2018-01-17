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
    $offset = isset($args["first"]) ? $args["first"] : "0";
    $this->sql
            ->select($args['col'])
            ->from("todos")
            ->where($args['filterCol'] ." = ". $args['filterVal'])
            ->setFirstResult($args['offset']);

    if(isset($args["limit"]))
      $this->sql->setMaxResults($args['limit']);

    return $fetch = $this->app['db']->fetchAll($this->sql);

  }

  public function selectAllByUser(array $args)
  {
    return $this->select(["col" => $args["col"], "filterCol" => "user_id", "filter_val" => $args["user_id"]]);
  }

}
