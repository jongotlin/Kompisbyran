<?php

namespace AppBundle\Manager;

use AppBundle\Enum\FriendTypes;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use AppBundle\Entity\ConnectionRequestRepository;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\City;
use AppBundle\Entity\User;
use Pagerfanta\Pagerfanta;
use AppBundle\Manager\UserManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Service("connection_request_manager")
 */
class ConnectionRequestManager implements ManagerInterface
{
    /**
     * @var ConnectionRequestRepository
     */
    private $connectionRequestRepository;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @InjectParams({
     *     "connectionRequestRepository" = @Inject("connection_request_repository"),
     *     "userManager" = @Inject("user_manager"),
     *     "translator" = @Inject("translator")
     * })
     */
    public function __construct(ConnectionRequestRepository $connectionRequestRepository, UserManager $userManager, TranslatorInterface $translator)
    {
        $this->connectionRequestRepository  = $connectionRequestRepository;
        $this->userManager                  = $userManager;
        $this->translator                   = $translator;
    }

    /**
     * @return ConnectionRequest
     */
    public function createNew()
    {
        return new ConnectionRequest();
    }

    /**
     * @param $entity
     * @return ConnectionRequest
     */
    public function save($entity)
    {
        return $this->connectionRequestRepository->save($entity);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function getFind($id)
    {
        return $this->connectionRequestRepository->find($id);
    }

    /**
     * @param $entity
     */
    public function remove($entity)
    {
        $this->connectionRequestRepository->remove($entity);
    }

    /**
     * @param City $city
     * @return array
     */
    public function getCountNewWithinCity(City $city)
    {
        return $this->connectionRequestRepository->countNewWithinCity($city);
    }

    /**
     * @param City $city
     * @return array
     */
    public function getCountNewMusicFriendWithinCity(City $city)
    {
        return $this->connectionRequestRepository->countNewMusicFriendWithinCity($city);
    }

    /**
     * @param City $city
     * @return array
     */
    public function getCountEstablishedWithinCity(City $city)
    {
        return $this->connectionRequestRepository->countEstablishedWithinCity($city);
    }

    /**
     * @param City $city
     * @return array
     */
    public function getCountEstablishedMusicFriendWithinCity(City $city)
    {
        return $this->connectionRequestRepository->countEstablishedMusicFriendWithinCity($city);
    }

    /**
     * @param User $user
     * @return null|object
     */
    public function getFindOneOpenByUser(User $user)
    {
        return $this->connectionRequestRepository->findOneOpenByUser($user);
    }

    /**
     * @return array
     */
    public function getFindAll()
    {
        return $this->connectionRequestRepository->findAll();
    }

    /**
     * @param City $city
     * @return array
     */
    public function getFindCityStats(City $city)
    {
        return $this->connectionRequestRepository->findCityStats($city);
    }

    /**
     * @param City $city
     * @return array
     */
    public function getFindCity(City $city)
    {
        return $this->connectionRequestRepository->findCity($city);
    }

    /**
     * @param City $city
     * @param int $page
     * @return array
     */
    public function getFindPaginatedByCityResults(City $city, $page = 1)
    {
        return [
            'success'                       => true,
            'newUsers'                      => $this->getCountNewWithinCity($city),
            'newMusicFriendUsers'           => $this->getCountNewMusicFriendWithinCity($city),
            'establishedUsers'              => $this->getCountEstablishedWithinCity($city),
            'establishedMusicFriendUsers'   => $this->getCountEstablishedMusicFriendWithinCity($city),
            'results'                       => $this->getCityResults($this->connectionRequestRepository->findOpenByCity($city, FriendTypes::START)),
            'next'                          => false
        ];
    }

    /**
     * @param ConnectionRequest[] $connectionRequests
     *
     * @return array
     */
    private function getCityResults($connectionRequests)
    {
        $datas              = [];
        $establishedTrans   = $this->translator->trans('Established');
        $newTrans           = $this->translator->trans('New');

        foreach ($connectionRequests as $connectionRequest) {
            $user               = $connectionRequest->getUser();
            $pending            = $connectionRequest->getPending()? 1: 0;
            $wantToLearnText    = $user->getWantToLearn()? $newTrans: $establishedTrans;

            $datas[]    = [
                'request_date'  => $connectionRequest->getCreatedAt()->format('Y-m-d'),
                'name'          => $user->getFullName(),
                'email'         => $user->getEmail(),
                'category'      => $wantToLearnText,
                'action'        => $connectionRequest->getUser()->getId().'|'.$connectionRequest->getId().'|'.$pending, //user_id|request_id|pending
                'extra' => $user->isNewlyArrived() ? 'Nyanländ' : '',
            ];
        }

        return $datas;
    }

    /**
     * @param $userId
     * @return null|ConnectionRequest
     */
    public function getFindOneUnpendingByUserId($userId)
    {
        return $this->connectionRequestRepository->findOneUnpendingByUserId($userId);
    }

    /**
     * @param $userId
     * @return null|object
     */
    public function getFindOneByUserId($userId)
    {
        return $this->connectionRequestRepository->findOneByUserId($userId);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getFindAllPending(User $user)
    {
        return $this->connectionRequestRepository->findAllPending($user);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getFindAllUninspected(User $user)
    {
        return $this->connectionRequestRepository->findAllByInspected($user, false);
    }

    /**
     * @param $id
     * @return bool
     */
    public function markAsInspected($id)
    {
        $connectionRequest = $this->getFind($id);

        if ($connectionRequest instanceof ConnectionRequest) {
            $connectionRequest->setInspected(true);

            $this->save($connectionRequest);

            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function markAsPendingOrUnpending($id)
    {
        $connectionRequest = $this->getFind($id);

        if ($connectionRequest instanceof ConnectionRequest) {

            if ($connectionRequest->getPending()) {
                $connectionRequest->setPending(false);
            } else {
                $connectionRequest->setPending(true);
            }

            $this->save($connectionRequest);

            return $connectionRequest;
        }

        return false;
    }
}
