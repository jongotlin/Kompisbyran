<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Phpunit\DatabaseTestCase;
use AppBundle\Tests\Phpunit\Extension\AuthenticationExtensionTrait;
use AppBundle\Tests\Phpunit\Extension\RepositoryExtensionTrait;

class ConnectionControllerTest extends DatabaseTestCase
{
    use AuthenticationExtensionTrait;
    use RepositoryExtensionTrait;

    public function testShouldConnectionPage()
    {
        $this->authenticateUser(
            $this->getUserRepository()->findOneBy(['email' => 'fluentspeaker@example.com']),
            ['ROLE_ADMIN']
        );

        $client = static::$client;
        $client->request('GET', '/admin/connections/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
