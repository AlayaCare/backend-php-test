<?php

use Silex\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

class LoggedInSingleView extends WebTestCase {


  private $client = null;


    public function createApplication()
    {

        require __DIR__.'/../CreateApp.php';

        return $this->app = $app;
    }


    /**
     * @test
     */
    public function access_my_todos_test()
    {

        $this->client = $this->createClient();
        $this->login($this->client);
        $this->client->followRedirects(true);

        $crawler = $this->client->request('GET', '/todo/6');

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertContains('Edit the Todo', $crawler->filter('body')->text());

    }

    private function logIn($client)
    {
        $username = 'user1';
        $session = $this->app['session'];
        $firewall = 'site';
        $token = new UsernamePasswordToken( $username, null, $firewall, array('ROLE_USER'));
        $session->set('_security_'.$firewall, serialize($token));
        $session_user = array(
            'id' => "1",
            'username' => $username,
        );
        $session->set('user', $session_user);
        $session->save();
        $this->app['user'] = $this->app['entity_manager']->getRepository('Entity\User')->findOneBy(array('username' => $username));
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

    }

}
