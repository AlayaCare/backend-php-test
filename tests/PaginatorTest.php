<?php

namespace PHPTest\Test;


use PHPUnit\Framework\TestCase;
use Utils\Paginator;

class PaginatorTest extends TestCase
{
    public function testPageCount()
    {
        $paginator = new Paginator(20, 1, 3,'http://localhost');

        $this->assertSame(7, $paginator->getPageCount(), 'Page count is unexpected');
    }

    public function testPrePageUrl()
    {
        $paginator = new Paginator(20, 2, 5,'http://localhost');

        $this->assertSame('http://localhost?page=1', $paginator->getPreUrl(), 'Pre page url is unexpected');
    }
}
