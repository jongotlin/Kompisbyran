<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CityRepository")
 */
class City
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var ConnectionRequest[]
     *
     * @ORM\OneToMany(targetEntity="ConnectionRequest", mappedBy="city")
     */
    protected $connectionRequests;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="city")
     */
    protected $connections;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $senderEmail;

    public function __construct()
    {
        $this->connectionRequests = new ArrayCollection();
        $this->connections = new ArrayCollection();
    }
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \AppBundle\Entity\ConnectionRequest[] $connectionRequests
     */
    public function setConnectionRequests($connectionRequests)
    {
        $this->connectionRequests = $connectionRequests;
    }

    /**
     * @return \AppBundle\Entity\ConnectionRequest[]
     */
    public function getConnectionRequests()
    {
        return $this->connectionRequests;
    }

    /**
     * Set senderEmail
     *
     * @param string $senderEmail
     *
     * @return City
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    /**
     * Get senderEmail
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Add connectionRequest
     *
     * @param \AppBundle\Entity\ConnectionRequest $connectionRequest
     *
     * @return City
     */
    public function addConnectionRequest(\AppBundle\Entity\ConnectionRequest $connectionRequest)
    {
        $this->connectionRequests[] = $connectionRequest;

        return $this;
    }

    /**
     * Remove connectionRequest
     *
     * @param \AppBundle\Entity\ConnectionRequest $connectionRequest
     */
    public function removeConnectionRequest(\AppBundle\Entity\ConnectionRequest $connectionRequest)
    {
        $this->connectionRequests->removeElement($connectionRequest);
    }

    /**
     * Add connection
     *
     * @param \AppBundle\Entity\Connection $connection
     *
     * @return City
     */
    public function addConnection(\AppBundle\Entity\Connection $connection)
    {
        $this->connections[] = $connection;

        return $this;
    }

    /**
     * Remove connection
     *
     * @param \AppBundle\Entity\Connection $connection
     */
    public function removeConnection(\AppBundle\Entity\Connection $connection)
    {
        $this->connections->removeElement($connection);
    }

    /**
     * Get connections
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnections()
    {
        return $this->connections;
    }
}
