<?php
/**
 * Created by PhpStorm.
 * User: kienhungtran
 * Date: 2017-08-29
 * Time: 3:23 AM
 */

namespace App\Objects;


class PaginatedObjects
{
    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var
     */
    protected $nbPages;

    /**
     * PaginatedObjects constructor.
     * @param $limit
     * @param $total
     * @param $items
     * @param int $page
     */
    public function __construct($limit, $total, $items, $page = 1)
    {
        $this->limit = $limit;
        $this->total = $total;
        $this->items = $items;
        $this->page = $page;
        $this->nbPages = ceil($total / $limit);
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return float
     */
    public function getNbPages()
    {
        return $this->nbPages;
    }
}