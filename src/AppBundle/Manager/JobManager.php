<?php

namespace AppBundle\Manager;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use AppBundle\Entity\JobRepository;
use AppBundle\Entity\Job;

/**
 * @Service("job_manager")
 */
class JobManager implements ManagerInterface
{
    /**
     * @var JobRepository
     */
    private $jobRepository;

    /**
     * @InjectParams({
     *      "jobRepository" = @Inject("job_repository")
     * })
     */
    public function __construct(JobRepository $jobRepository)
    {
        $this->jobRepository   = $jobRepository;
    }

    /**
    * @return City
    */
    public function createNew()
    {
        return new Job();
    }

    /**
     * @param $entity
     * @return City
     */
    public function save($entity)
    {
        return $this->jobRepository->save($entity);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function getFind($id)
    {
        return $this->jobRepository->find($id);
    }

    /**
     * @return array
     */
    public function getFindAll()
    {
        return $this->jobRepository->findAll();
    }

    /**
     * @param $type
     * @return null|object
     */
    public function getFindByType($type)
    {
        return $this->jobRepository->findByType($type);
    }

    /**
     * @param $entity
     */
    public function remove($entity)
    {
        return $this->jobRepository->remove($entity);
    }

    /**
     * @param $type
     * @return City
     */
    public function start($type)
    {
        $job = $this->createNew();

        $job->setType($type);

        $this->save($job);

        return $job;
    }

    /**
     * @param Job $job
     */
    public function finish($type)
    {
        $job = $this->getFindByType($type);

        if ($job instanceof Job) {
            $this->remove($job);
        }
    }

    /**
     * @param $type
     * @return bool
     */
    public function isRunning($type)
    {
        return $this->getFindByType($type) instanceof Job;
    }
}