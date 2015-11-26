<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Tests\Phpunit\DatabaseTestCase;
use AppBundle\Tests\Phpunit\Extension\AuthenticationExtensionTrait;
use AppBundle\Tests\Phpunit\Extension\RepositoryExtensionTrait;

class RegistrationControllerTest extends DatabaseTestCase
{
    use AuthenticationExtensionTrait;
    use RepositoryExtensionTrait;

    /**
     * @test
     */
    public function shouldLoadPage()
    {
        $crawler = static::$client->request('GET', '/register/');

        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Gå med")')->count() > 0);
    }

    /**
     * @test
     */
    public function shouldCreateUser()
    {
        $userCount = count($this->getUserRepository()->findAll());
        $client = static::createClient();
        $crawler = $client->request('GET', '/register/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('.fos_user_registration_register')->form([
            'fos_user_registration_form[email]' => 'test@example.com',
            'fos_user_registration_form[plainPassword][first]' => 'foobar',
            'fos_user_registration_form[plainPassword][second]' => 'foobar',
            'fos_user_registration_form[termsAccepted]' => true,
        ]);

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan($userCount, count($this->getUserRepository()->findAll()));

        $newUser = $this->getUserRepository()->findOneBy(['email' => 'test@example.com']);
        $this->assertEquals(1, count($newUser->getRoles()));
        $this->assertEquals('ROLE_USER', $newUser->getRoles()[0]);
    }

    /**
     * @test
     */
    public function shouldCreateCompleteUser()
    {
        $this->authenticateUser(
            $this->getUserRepository()->findOneBy(['email' => 'incomplete@example.com']),
            'ROLE_USER'
        );

        $client = static::$client;
        $crawler = $client->request('GET', '/user/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=user]')->form([
            'user[firstName]' => 'John',
            'user[lastName]' => 'Doe',
            'user[age]' => '35',
            'user[gender]' => 'M',
            'user[about]' => 'About me',
            'user[district]' => 'Södermalm',
        ]);

        $crawler = $client->submit($form);
        $this->assertEquals(0, $crawler->filter('.has-error')->count());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
