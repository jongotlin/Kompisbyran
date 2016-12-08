<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Phpunit\DatabaseTestCase;
use AppBundle\Tests\Phpunit\Extension\AuthenticationExtensionTrait;
use AppBundle\Tests\Phpunit\Extension\RepositoryExtensionTrait;

class ConnectionRequestControllerTest extends DatabaseTestCase
{
    use AuthenticationExtensionTrait;
    use RepositoryExtensionTrait;

    public function testShouldLoadSearchResultJson()
    {
        $this->authenticateUser(
            $this->getUserRepository()->findOneBy(['email' => 'learner@example.com']),
            ['ROLE_ADMIN']
        );

        $client = static::$client;

        $client->request('GET', sprintf(
            '/admin2/connectionrequests/ajax-by-city/%s',
            $this->getEntityManager()->getRepository('AppBundle:City')->findOneBy(['name' => 'Stockholm'])->getId()
            ), [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertJson($content);
        $array = json_decode($content, true);
        $this->assertEquals(5, count($array['results']));
    }
}
