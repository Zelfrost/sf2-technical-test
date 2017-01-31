<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testIndexWhenDisconnected()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Connexion")')->count()
        );
    }

    public function testRegistration()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $crawler = $client->click($crawler->selectLink('Dépêchez-vous')->link());

        $form = $crawler->selectButton('Confirmer')->form([
            'register[username]' => 'test',
            'register[plainPassword][first]' => 'test',
            'register[plainPassword][second]' => 'test',
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Votre compte a bien été créé.")')->count()
        );
    }

    public function testLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $crawler = $client->followRedirect();

        $form = $crawler->selectButton('Confirmer')->form([
            'login[username]' => 'test',
            'login[password]' => 'test',
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Bienvenue !")')->count()
        );
    }

    public function testSearchWithOneResult()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Confirmer')->form([
            'login[username]' => 'test',
            'login[password]' => 'test',
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        $form = $crawler->selectButton('Confirmer')->form([
            'search_user[username]' => 'Zelfrost',
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Laisser un commentaire à Zelfrost")')->count()
        );
    }

    public function testSearchWithMultipleResults()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Confirmer')->form([
            'login[username]' => 'test',
            'login[password]' => 'test',
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        $form = $crawler->selectButton('Confirmer')->form([
            'search_user[username]' => 'Zelfr',
        ]);

        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Bienvenue")')->count()
        );
        $this->assertGreaterThan(
            1,
            $crawler->filter('li')->count()
        );
    }
}
