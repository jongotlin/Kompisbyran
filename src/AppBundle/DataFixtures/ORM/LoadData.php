<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Category;
use AppBundle\Entity\City;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadCategories($manager);
        $this->loadCities($manager);
        $this->loadUsers($manager);
        $this->loadConnectionRequests($manager);
        $this->loadConnections($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadCategories(ObjectManager $manager)
    {
        $categories = [
            'Fika',
            'Bada',
            'Symfony',
        ];
        foreach ($categories as $i => $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $this->addReference(sprintf('category-%s', $i), $category);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadCities(ObjectManager $manager)
    {
        $cities = [
            'Örebro',
            'Stockholm',
            'Göteborg',
        ];
        foreach ($cities as $i => $cityName) {
            $city = new City();
            $city->setName($cityName);
            $manager->persist($city);
            $this->addReference(sprintf('city-%s', $i), $city);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadUsers(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('learner@example.com');
        $user->setFirstName('Kalle');
        $user->setLastName('Anka');
        $user->setEnabled(true);
        $user->addRole('ROLE_COMPLETE_USER');
        $user->setWantToLearn(true);
        $user->setAge(35);
        $user->setAbout('Sportintresserad man med 3 barn');
        $user->setCategories([$this->getReference('category-1'), $this->getReference('category-2')]);
        $user->setFrom('SY');
        $user->setGender('M');
        $user->setLanguages(['ar', 'en']);
        $user->setProfilePicture('http://api.randomuser.me/portraits/thumb/men/1.jpg');
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword('asdf123', $user->getSalt()));
        $manager->persist($user);
        $this->addReference('user-learner', $user);

        $user = new User();
        $user->setEmail('fluentspeaker@example.com');
        $user->setFirstName('Kal p');
        $user->setLastName('Dal');
        $user->setEnabled(true);
        $user->setRoles(['ROLE_COMPLETE_USER', 'ROLE_ADMIN']);
        $user->setWantToLearn(false);
        $user->setAge(40);
        $user->setAbout('Sportintresserad lärare');
        $user->setCategories([$this->getReference('category-0'), $this->getReference('category-1')]);
        $user->setFrom('SE');
        $user->setGender('M');
        $user->setLanguages(['sv', 'en']);
        $user->setProfilePicture('http://api.randomuser.me/portraits/thumb/men/2.jpg');
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword('asdf123', $user->getSalt()));
        $manager->persist($user);
        $this->addReference('user-fluentspeaker', $user);

        $user = new User();
        $user->setEmail('glenn@example.com');
        $user->setFirstName('Glenn');
        $user->setLastName('GBG');
        $user->setEnabled(true);
        $user->setRoles(['ROLE_COMPLETE_USER', 'ROLE_ADMIN']);
        $user->setWantToLearn(false);
        $user->setAge(20);
        $user->setAbout('Göteborgare');
        $user->setCategories([$this->getReference('category-1'), $this->getReference('category-2')]);
        $user->setFrom('SE');
        $user->setGender('M');
        $user->setLanguages(['sv', 'en']);
        $user->setProfilePicture('http://api.randomuser.me/portraits/thumb/men/3.jpg');
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword('asdf123', $user->getSalt()));
        $manager->persist($user);
        $this->addReference('user-glenn', $user);

        $user = new User();
        $user->setEmail('incomplete@example.com');
        $user->setEnabled(true);
        $user->setPassword($encoder->encodePassword('asdf123', $user->getSalt()));
        $manager->persist($user);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadConnectionRequests(ObjectManager $manager)
    {
        $connectionRequest = new ConnectionRequest();
        $connectionRequest->setWantToLearn(true);
        $connectionRequest->setCity($this->getReference('city-1'));
        $connectionRequest->setUser($this->getReference('user-learner'));
        $connectionRequest->setComment('Jag vill fika');
        $manager->persist($connectionRequest);

        $connectionRequest = new ConnectionRequest();
        $connectionRequest->setWantToLearn(false);
        $connectionRequest->setCity($this->getReference('city-1'));
        $connectionRequest->setUser($this->getReference('user-fluentspeaker'));
        $connectionRequest->setComment('Jag vill dricka kaffe');
        $manager->persist($connectionRequest);

        $connectionRequest = new ConnectionRequest();
        $connectionRequest->setWantToLearn(false);
        $connectionRequest->setCity($this->getReference('city-2'));
        $connectionRequest->setUser($this->getReference('user-glenn'));
        $connectionRequest->setComment('Jag vill träffas ute');
        $manager->persist($connectionRequest);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadConnections(ObjectManager $manager)
    {
        foreach (range(1,25) as $i) {
            $connection = new Connection();
            $connection->setCity($this->getReference('city-1'));
            $connection->setFluentSpeaker($this->getReference('user-fluentspeaker'));
            $connection->setLearner($this->getReference('user-learner'));
            $manager->persist($connection);
        }
    }
}
