<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Enum\FriendTypes;
use AppBundle\Enum\OccupationTypes;
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
            'fos_user_registration_form[email][first]' => 'test@example.com',
            'fos_user_registration_form[email][second]' => 'test@example.com',
            'fos_user_registration_form[plainPassword][first]' => 'foobar',
            'fos_user_registration_form[plainPassword][second]' => 'foobar',
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

        $connectionRequestCount = count($this->getConnectionRequestRepository()->findAll());

        $client = static::$client;
        $crawler = $client->request('GET', '/user/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=user]')->form([
            'user[firstName]' => 'John',
            'user[lastName]' => 'Doe',
            'user[wantToLearn]' => 0,
            'user[categories]' => [0,1,2],
            'user[age]' => '35',
            'user[gender]' => 'M',
            'user[about]' => 'About me asdf asdf asdf asdf ',
            'user[from]' => 'SE',
            'user[hasChildren]' => 0,
            'user[type]' => FriendTypes::FRIEND,
            'user[municipality]' => $this->getMunicipalityRepository()->findAll()[0]->getId(),
            'user[activities]' => 'asdf asdf asfd asdf',
            'user[occupation]' => OccupationTypes::EMPLOYED,
            'user[occupationDescription]' => 'asdf asdf asdf asdf',
            'user[education]' => 0,
            'user[timeInSweden]' => 'From day one',
            'user[canSing]' => 0,
            'user[canPlayInstrument]' => 0,
            'user[professionalMusician]' => 0,
            'user[phoneNumber]' => '0701111111',
            'user[newConnectionRequest][city]' => $this->getCityRepository()->findAll()[0]->getId(),
            'user[newConnectionRequest][availableWeekday]' => 1,
            'user[newConnectionRequest][availableWeekend]' => 1,
            'user[newConnectionRequest][availableDay]' => 1,
            'user[newConnectionRequest][availableEvening]' => 1,
            'user[newConnectionRequest][extraPerson]' => 'false',
            'user[termsAccepted]' => true,
            'user[newlyArrived]' => 0,
        ]);

        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter('.has-error'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals($connectionRequestCount + 1, count($this->getConnectionRequestRepository()->findAll()));
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains("fbq('track', 'CompleteRegistration');", $client->getResponse()->getContent());
    }
}
