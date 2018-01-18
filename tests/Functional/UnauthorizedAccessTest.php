<?php

namespace Tests;

use Silex\WebTestCase;

class UnauthorizedAccessTest extends WebTestCase
{


    public function createApplication()
    {

        require __DIR__.'/../CreateApp.php';

        return $this->app = $app;
    }

    /**
     * @test
     */
    public function test()
    {
        $client = $this->createClient();
        $client->followRedirects(true);

        // test Regular Login Page
        $crawler = $client->request('GET', '/login');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('Login', $crawler->filter('body')->text());

        // test Unauthorized Todo Access
        $crawler = $client->request('GET', '/todo/2');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('Login', $crawler->filter('body')->text());

        // test Unauthorized Todo API index Access
        $crawler = $client->request('GET', '/todo/api/index');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('Login', $crawler->filter('body')->text());

    }


}


