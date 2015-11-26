<?php
namespace AppBundle\Tests\Phpunit\Extension;

trait RepositoryExtensionTrait
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getUserRepository()
    {
        return $this->getEntityManager()->getRepository('AppBundle:User');
    }
}
