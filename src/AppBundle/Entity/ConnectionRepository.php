<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ConnectionRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function getFindAllQuery()
    {
        return $this
            ->createQueryBuilder('c')
            ->select('c, f, l, cb')
            ->innerJoin('c.fluentSpeaker', 'f')
            ->innerJoin('c.learner', 'l')
            ->innerJoin('c.createdBy', 'cb')
            ->orderBy('c.id', 'desc')
            ->getQuery()
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
}
