<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ConnectionRepository")
 */
class Connection
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="fluentSpeakerConnections")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $fluentSpeaker;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="learnerConnections")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $learner;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="City", inversedBy="connections")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $learnerComment;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fluentSpeakerComment;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="createdConnections")
     * @ORM\JoinColumn(nullable=true, name="created_by")
     */
    protected $createdBy;

    /**
     * @var ConnectionComment[]
     *
     * @ORM\OneToMany(targetEntity="ConnectionComment", mappedBy="connection")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $comments;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type = 'friend';

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $learnerConnectionRequestCreatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $fluentSpeakerConnectionRequestCreatedAt;

    public function __construct(User $user = null)
    {
        $this->createdAt = new \DateTime();
        $this->createdBy = $user;
        $this->comments = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \AppBundle\Entity\City $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return \AppBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param \AppBundle\Entity\User $fluentSpeaker
     */
    public function setFluentSpeaker($fluentSpeaker)
    {
        $this->fluentSpeaker = $fluentSpeaker;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    public function getFluentSpeaker()
    {
        return $this->fluentSpeaker;
    }

    /**
     * @param \AppBundle\Entity\User $learner
     */
    public function setLearner($learner)
    {
        $this->learner = $learner;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    public function getLearner()
    {
        return $this->learner;
    }

    /**
     * @param string $fluentSpeakerComment
     */
    public function setFluentSpeakerComment($fluentSpeakerComment)
    {
        $this->fluentSpeakerComment = $fluentSpeakerComment;
    }

    /**
     * @return string
     */
    public function getFluentSpeakerComment()
    {
        return $this->fluentSpeakerComment;
    }

    /**
     * @param string $learnerComment
     */
    public function setLearnerComment($learnerComment)
    {
        $this->learnerComment = $learnerComment;
    }

    /**
     * @return string
     */
    public function getLearnerComment()
    {
        return $this->learnerComment;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setCreatedBy(User $user)
    {
        $this->createdBy = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return ConnectionComment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return \DateTime
     */
    public function getLearnerConnectionRequestCreatedAt()
    {
        return $this->learnerConnectionRequestCreatedAt;
    }

    /**
     * @param \DateTime $learnerConnectionRequestCreatedAt
     */
    public function setLearnerConnectionRequestCreatedAt($learnerConnectionRequestCreatedAt)
    {
        $this->learnerConnectionRequestCreatedAt = $learnerConnectionRequestCreatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getFluentSpeakerConnectionRequestCreatedAt()
    {
        return $this->fluentSpeakerConnectionRequestCreatedAt;
    }

    /**
     * @param \DateTime $fluentSpeakerConnectionRequestCreatedAt
     */
    public function setFluentSpeakerConnectionRequestCreatedAt($fluentSpeakerConnectionRequestCreatedAt)
    {
        $this->fluentSpeakerConnectionRequestCreatedAt = $fluentSpeakerConnectionRequestCreatedAt;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
