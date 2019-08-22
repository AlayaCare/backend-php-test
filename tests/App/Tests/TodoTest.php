<?php

namespace App\Tests;
use Silex\WebTestCase;

class TodoTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../../src/phpunit_app.php';
        $app['debug'] = true;
        $app['session.test'] = true;
        unset($app['exception_handler']);
        return $app;
    }

    public function testInitialPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET','/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("README")'));
    }

    /**
     * For now, let's just get the user and save it session
     */
    public function testTodoPage()
    {
        $client = $this->createClient();
        $session = $this->app['session'];

        $session->set('user', [
            'id' => 1,
            'username' => 'user1',
        ]);
        $session->save();

        $crawler = $client->request('GET','/todo');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Todo List")'));

        // You can do more assertions here depending on the HTML using filters, etc.
    }
}
