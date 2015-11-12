<?php

namespace AppBundle\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testShouldLoadIndexPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Kompisbyrån")')->count() > 0);
    }
}
