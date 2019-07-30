<?php

use Silex\WebTestCase;

class ControllersTest extends WebTestCase {

    public function createApplication() {
        $app['session.test'] = true;

        return require __DIR__ . '/../src/app.php';
    }

    public function testHomePage() {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('AskNicely', $crawler->filter('body')->text());
        $this->assertCount(1, $crawler->filter('h1:contains("README")'));
    }

    public function testLoginPage() {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/login');

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $data = array('username' => 'user1', 'password' => 'user1');
        $client->submit($form, $data);

        $crawler = $this->client->followRedirect();
        $crawler = $client->request('GET', '/todo-list');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('Todo List', $crawler->filter('body')->text());
    }
}