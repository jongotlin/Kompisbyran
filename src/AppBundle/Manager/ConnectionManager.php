<?php

namespace AppBundle\Manager;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use AppBundle\Entity\ConnectionRepository;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\User;
use AppBundle\DomainEvents;
use AppBundle\Event\ConnectionCreatedEvent;

/**
 * @Service("connection_manager")
 */
class ConnectionManager implements ManagerInterface
{
    /**
     * @var ConnectionRepository
     */
    private $connectionRepository;

    private $dispatcher;

    /**
     * @InjectParams({
     *      "connectionRepository" = @Inject("connection_repository"),
     *      "dispatcher" = @Inject("event_dispatcher")
     * })
     */
    public function __construct(ConnectionRepository $connectionRepository, $dispatcher)
    {
        $this->connectionRepository = $connectionRepository;
        $this->dispatcher           = $dispatcher;
    }

    /**
     * @return Connection
     */
    public function createNew()
    {
        return new Connection();
    }

    /**
     * @param $entity
     * @return Connection
     */
    public function save($entity)
    {
        return $this->connectionRepository->save($entity);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function getFind($id)
    {
        return $this->connectionRepository->find($id);
    }

    /**
     * @return array
     */
    public function getFindAll()
    {
        return $this->connectionRepository->findAll();
    }

    /**
     * @param $entity
     */
    public function remove($entity)
    {
        return $this->connectionRepository->remove($entity);
    }

    /**
     * @param ConnectionRequest $userRequest
     * @param ConnectionRequest $matchUserRequest
     * @param User $loggedUser
     * @return mixed
     */
    public function saveByConnectionRequest(ConnectionRequest $userRequest, ConnectionRequest $matchUserRequest, User $loggedUser)
    {
        $connection                 = $this->createNew();
        list($learner, $speaker)    = $this->getLearnerSpeaker($userRequest, $matchUserRequest);

        $connection->setCreatedBy           ( $loggedUser               );
        $connection->setLearner             ( $learner->getUser()       );
        $connection->setFluentSpeaker       ( $speaker->getUser()       );
        $connection->setCity                ( $learner->getCity()       );
        $connection->setFluentSpeakerComment( $speaker->getComment()    );
        $connection->setLearnerComment      ( $learner->getComment()    );
        $connection->setMusicFriend         ( $learner->isMusicFriend() );

        $this->save($connection);

        return $connection;
    }

    /**
     * @param User $user
     * @param User $matchUser
     * @return int
     */
    public function getIsUserConnectionExists(User $user, User $matchUser)
    {
        return $this->connectionRepository->isUserConnectionExists($user, $matchUser);
    }

    /**
     * @param ConnectionRequest $userRequest
     * @param ConnectionRequest $matchUserRequest
     * @return array
     */
    public function getLearnerSpeaker(ConnectionRequest $userRequest, ConnectionRequest $matchUserRequest)
    {
        $learner    = $userRequest;
        $speaker    = $matchUserRequest;

        if (!$userRequest->getWantToLearn()) {
            $learner = $matchUserRequest;
            $speaker = $userRequest;
        }

        return [
            $learner,
            $speaker
        ];
    }

    /**
     * @param $days
     * @return array
     */
    public function getCreatedDaysAgo($days)
    {
        return $this->connectionRepository->findCreatedDaysAgo($days);
    }
}