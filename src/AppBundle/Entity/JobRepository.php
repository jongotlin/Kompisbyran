<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use AppBundle\Entity\Job;

/**
 * Class JobRepository
 * @package AppBundle\Entity
 */
class JobRepository extends EntityRepository
{
    /**
     * @param Job $job
     * @return Job
     */
    public function save(Job $job)
    {
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();

        return $job;
    }
    /**
     * @param Job $job
     */
    public function remove(Job $job)
    {
        $this->getEntityManager()->remove($job);
        $this->getEntityManager()->flush();
    }

    /**
     * @param $type
     * @return null|object
     */
    public function findByType($type)
    {
        return $this->findOneBy([
            'type'  => $type
        ]);
    }
}
