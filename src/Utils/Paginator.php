<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: ricky
 * Date: 29/10/19
 * Time: 10:34 PM
 */

namespace Utils;


class Paginator
{

    /**
     * @var
     */
    private $perPage;

    /**
     * @var
     */
    private $total;

    /**
     * @var
     */
    private $curPage;

    /**
     * @var
     */
    private $url;

    /**
     * Paginator constructor.
     * @param $total
     * @param $curPage
     * @param $perPage
     * @param $url
     */
    public function __construct($total, $curPage, $perPage, $url)
    {
        $this->total = $total;
        $this->curPage = $curPage;
        $this->perPage = $perPage;
        $this->url = $url;
    }


    /**
     * @return mixed
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param mixed $perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getCurPage()
    {
        return $this->curPage;
    }

    /**
     * @param mixed $curPage
     */
    public function setCurPage($curPage)
    {
        $this->curPage = $curPage;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return int
     */
    public function getPageCount(): int {
        return (int) ceil($this->getTotal() / $this->getPerPage());
    }

    /**
     * @return int
     */
    private function getPrePage(): int {
        return $this->getCurPage() - 1;
    }

    /**
     * @return int
     */
    private function getNextPage(): int {
        return $this->getCurPage() + 1;
    }

    /**
     * @return string
     */
    public function getPreUrl(): string {

        return $this->url .'?page='. $this->getPrePage();
    }

    /**
     * @return sting
     */
    public function getNextUrl(): string {
        return $this->url .'?page='. $this->getNextPage();
    }

    /**
     * @return string
     */
    public function getPrePageOutput(): string
    {
        if ($this->getCurPage() - 1 <= 0) {
            return 'Pre';
        } else {
            return "<a href='". $this->getPreUrl() ."'>Pre</a>";
        }
    }

    /**
     * @return string
     */
    public function getNextPageOutput(): string
    {
        if ($this->getCurPage() + 1 > $this->getPageCount()) {
            return 'Next';
        } else {
            return "<a href='". $this->getNextUrl() ."'>Next</a>";
        }
    }




}