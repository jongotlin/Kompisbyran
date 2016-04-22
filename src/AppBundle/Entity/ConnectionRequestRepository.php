<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\City;
use AppBundle\Entity\ConnectionRequest;
use Doctrine\ORM\NoResultException;

/**
 * Class ConnectionRequestRepository
 * @package AppBundle\Entity
 */
class ConnectionRequestRepository extends EntityRepository
{
    /**
     * @param ConnectionRequest $connectionRequest
     * @return ConnectionRequest
     */
    public function save(ConnectionRequest $connectionRequest)
    {
        $this->getEntityManager()->persist($connectionRequest);
        $this->getEntityManager()->flush();

        return $connectionRequest;
    }

    /**
     * @param ConnectionRequest $connectionRequest
     */
    public function remove(ConnectionRequest $connectionRequest)
    {
        $this->getEntityManager()->remove($connectionRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * @param City $city
     * @param bool $wantToLearn
     * @param bool $musicFriend
     * @return ConnectionRequest[]
     * @deprecated
     */
    public function findForCity(City $city, $wantToLearn, $musicFriend)
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.wantToLearn = :wantToLearn')
            ->andWhere('cr.city = :city')
            ->andWhere('cr.musicFriend = :musicFriend')
            ->setParameters([
                'wantToLearn' => $wantToLearn,
                'city' => $city,
                'musicFriend' => $musicFriend,
            ])
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @param User $user
     * @return null|object
     */
    public function findOneByUser(User $user)
    {
        return $this->findOneBy(array('user' => $user, 'disqualified' => false));
    }

    /**
     * @param $userId
     * @return null|object
     */
    public function findOneUnpendingByUserId($userId)
    {
        return $this->findOneBy(array('user' => $userId, 'pending' => false));
    }

    /**
     * @param $userId
     * @return null|object
     */
    public function findOneByUserId($userId)
    {
        return $this->findOneBy(array('user' => $userId));
    }

    /**
     * @deprecated
     *
     * @param User $user
     * @return bool
       */
    public function hasActiveRequest(User $user)
    {
        return $this->findOneByUser($user) instanceof ConnectionRequest? true: false;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.disqualified     = false')
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param City $city
     * @param $wantToLearn
     * @param $musicFriend
     * @return array
     */
    public function findByCityWantToLearnAndMusicFriend(City $city, $wantToLearn, $musicFriend)
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.wantToLearn         = :wantToLearn')
            ->andWhere('cr.city             = :city')
            ->andWhere('cr.disqualified     = false')
            //->andWhere('cr.musicFriend  = :musicFriend')
            ->setParameters([
                'city'          => $city,
                'wantToLearn'   => $wantToLearn,
                //'musicFriend'   => $musicFriend,
            ])
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param City $city
     * @return array
     */
    public function findNewWithinCity(City $city)
    {
        return $this->findByCityWantToLearnAndMusicFriend($city, true, true);
    }

    /**
     * @param City $city
     * @return array
     */
    public function findEstablishedWithinCity(City $city)
    {
        return $this->findByCityWantToLearnAndMusicFriend($city, false, true);
    }

    /**
     * @param City $city
     * @return array
     */
    public function findCityStats(City $city)
    {
        return $this
            ->createQueryBuilder('cr')
            ->select('SUM(IF(cr.want_to_learn = 0,0,1)) AS new, SUM(IF(cr.want_to_learn = 0,1,0)) AS established')
            ->where('cr.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @param User $user
     * @return int
     */
    public function countUserActiveRequests(User $user)
    {
        $qb = $this->createQueryBuilder('cr');

        $qb
            ->select('COUNT(cr.id)')
            ->where('cr.user = :user')
            ->andWhere('cr.disqualified = false')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param City $city
     * @return mixed
     */
    public function findByCity(City $city)
    {
        return $this
            ->findByCityQueryBuilder($city)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param City $city
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findByCityQueryBuilder(City $city)
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.city        = :city')
            ->andWhere('cr.disqualified = false')
            ->andWhere('cr.pending = false')
            ->groupBy('cr.user')
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->setParameter('city', $city)
        ;
    }

    /**
     * @return array
     */
    public function findAllPending()
    {
        return $this->findBy(['pending' => true]);
    }

    /**
     * @param $inspected
     * @return array
     */
    public function findAllByInspected($inspected)
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.inspected        = :inspected')
            ->groupBy('cr.user')
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->setParameter('inspected', $inspected)
            ->getQuery()
            ->getResult()
        ;
    }
}
