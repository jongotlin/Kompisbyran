<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\City;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\GeneralCategory;
use AppBundle\Entity\Municipality;
use AppBundle\Entity\MusicCategory;
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
        $this->loadMusicCategories($manager);
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
            'Dans' => ['en' => 'Dancing'],
            'Djur och Natur' => ['en' => 'Animals and nature'],
            'Familj' => ['en' => 'Family'],
            'Fika' => ['en' => 'Film/ TV'],
            'Film' => ['en' => 'Literature'],
            'Konst' => ['en' => 'Cooking'],
            'Matlagning' => ['en' => 'Music'],
            'Musik' => ['en' => 'Politics'],
            'Politik' => ['en' => 'Go for a walk'],
            'Resor' => ['en' => 'Travelling'],
            'Sport' => ['en' => 'Sport'],
            'Träning' => ['en' => 'Training'],
        ];
        $i = 0;
        $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        foreach ($categories as $categoryName => $translations) {
            $category = new GeneralCategory();
            $category->setName($categoryName);

            foreach ($translations as $locale => $translation) {
                $repository->translate($category, 'name', $locale, $translation);
            }

            $manager->persist($category);
            $this->addReference(sprintf('category-%s', $i++), $category);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadMusicCategories(ObjectManager $manager)
    {
        $categories = [
            'Jazz' => ['en' => 'Jazz'],
            'Hip hop' => ['en' => 'Hip hop'],
            'Hårdrock' => ['en' => 'Hard rock'],
        ];
        $i = 0;
        $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        foreach ($categories as $categoryName => $translations) {
            $category = new MusicCategory();
            $category->setName($categoryName);

            foreach ($translations as $locale => $translation) {
                $repository->translate($category, 'name', $locale, $translation);
            }

            $manager->persist($category);
            $this->addReference(sprintf('music-category-%s', $i++), $category);
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
            $city->setSenderEmail('matchning@kompisbyran.se');
            $manager->persist($city);
            $this->addReference(sprintf('city-%s', $i), $city);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadUsers(ObjectManager $manager)
    {
        $municipality = new Municipality();
        $municipality->setName('Stockholms kommun');
        $manager->persist($municipality);
        $this->addReference('municipality-1', $municipality);

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
        $user->setProfilePicture('http://api.randomuser.me/portraits/thumb/men/1.jpg');
        $user->setDistrict('Södermalm');
        $user->setMunicipality($this->getReference('municipality-1'));
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
        $user->setProfilePicture('http://api.randomuser.me/portraits/thumb/men/2.jpg');
        $user->setDistrict('Årsta');
        $user->setMunicipality($this->getReference('municipality-1'));
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
        $user->setProfilePicture('http://api.randomuser.me/portraits/thumb/men/3.jpg');
        $user->setMunicipality($this->getReference('municipality-1'));
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
            $connection = new Connection($this->getReference('user-fluentspeaker'));
            $connection->setCity($this->getReference('city-1'));
            $connection->setFluentSpeaker($this->getReference('user-fluentspeaker'));
            $connection->setLearner($this->getReference('user-learner'));
            $manager->persist($connection);
        }
    }
}
