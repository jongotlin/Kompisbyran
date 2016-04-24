<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use AppBundle\Entity\Connection;

/**
 * Class ConnectionRepository
 * @package AppBundle\Entity
 */
class ConnectionRepository extends EntityRepository
{

    /**
     * @param String $city
     * @param String $year
     * @param String $type
     * @return array
     */
    public function getMatches($city, $year, $type)
    {
        $query = "
            SELECT c.created_at
            FROM connection c
            WHERE YEAR(c.created_at) = :year";

        $params['year'] = $year;

        if ($city !== "") {
            $query .= " AND c.city_id = :city";
            $params['city'] = $city;
        }

        if ($type !== "") {
            $query .= " AND c.music_friend = :type";
            $params['type'] = $type;
        }

        $query .= " ORDER BY c.created_at ASC";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public function getYearSpan()
    {
        // get the years in a unique list
        $query = "
            SELECT DISTINCT(SUBSTRING(c.created_at, 1, 4)) as year
            FROM connection c
            ORDER BY year";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'year');
    }

    /**
     * @param Connection $connection
     * @return Connection
     */
    public function save(Connection $connection)
    {
        $this->getEntityManager()->persist($connection);
        $this->getEntityManager()->flush();

        return $connection;
    }

    /**
     * @param Connection $connection
     */
    public function remove(Connection $connection)
    {
        $this->getEntityManager()->remove($connection);
        $this->getEntityManager()->flush();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getFindAllQueryBuilder($searchString)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('c, f, l, cb, c2, u')
            ->innerJoin('c.fluentSpeaker', 'f')
            ->innerJoin('c.learner', 'l')
            ->innerJoin('c.createdBy', 'cb')
            ->leftJoin('c.comments', 'c2')
            ->leftJoin('c2.user', 'u')
        ;

        if ($searchString) {
            $qb
                ->where('f.email LIKE :searchString')
                ->orWhere('l.email LIKE :searchString')
                ->orWhere("CONCAT(CONCAT(f.firstName, ' '), f.lastName) LIKE :searchString")
                ->orWhere("CONCAT(CONCAT(l.firstName, ' '), l.lastName) LIKE :searchString")
                ->setParameter('searchString', '%'.trim($searchString).'%')
            ;
        }

        return $qb
            ->orderBy('c.id', 'desc')
        ;
    }

    /**
     * @param User $user1
     * @param User $user2
     * @return Connection[]
     */
    public function findForUsers(User $user1, User $user2)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.fluentSpeaker = :user1 and c.learner = :user2')
            ->orWhere('c.fluentSpeaker = :user2 and c.learner = :user1')
            ->setParameters([
                'user1' => $user1,
                'user2' => $user2,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @param User $user
     * @return int
     */
    public function isUserConnectionExists(User $user1, User $user2)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('COUNT(c.id)')
            ->where('c.fluentSpeaker = :user1 and c.learner = :user2')
            ->orWhere('c.fluentSpeaker = :user2 and c.learner = :user1')
            ->setParameters([
                'user1' => $user1,
                'user2' => $user2,
            ])
        ;

        return $qb->getQuery()->getSingleScalarResult()? true: false;
    }

    /**
     * @param $days
     * @param $numSent
     * @return array
     */
    public function findCreatedDaysAgo($days, $numSent)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('DATE_DIFF(:now, c.createdAt) >= :days')
            ->andWhere('c.numSent = :numSent')
            ->setParameter('now'        , new \DateTime())
            ->setParameter('days'       , $days)
            ->setParameter('numSent'    , $numSent)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}