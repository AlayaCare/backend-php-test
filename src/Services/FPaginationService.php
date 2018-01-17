<?php
namespace Services;

class FPaginationService
{
  public $app;

  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
  }

  public function paginate($model, $args)
  {
    $count = $model->count($args["filterCol"], $args["filterVal"]);
    $count = ceil($count / $args["limit"]);

    $offset = ($args['page'] - 1) * $args["limit"];

    $currentPage = $model->select(['col' => $args["col"], 'filterCol' => $args['filterCol'], 'filterVal' => $args['filterVal'], "offset" => $offset, "limit" => $args["limit"]]);

    return ["currentPage" => $currentPage, "PageNumbers" => $count];

  }

}
