<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="ConnectionRequestRepository")
 */
class ConnectionRequest
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="connectionRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * @var City
     *
     * @Assert\NotBlank
     *
     * @ORM\ManyToOne(targetEntity="City", inversedBy="connectionRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $city;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $wantToLearn = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $comment;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $sortOrder;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $musicFriend = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $disqualified = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $disqualifiedComment;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $pending = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $inspected = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->sortOrder = 0;
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
     * @param \AppBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param boolean $wantToLearn
     */
    public function setWantToLearn($wantToLearn)
    {
        $this->wantToLearn = $wantToLearn;
    }

    /**
     * @return boolean
     */
    public function getWantToLearn()
    {
        return $this->wantToLearn;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return boolean
     */
    public function isMusicFriend()
    {
        return $this->musicFriend;
    }

    /**
     * @param boolean $musicFriend
     */
    public function setMusicFriend($musicFriend)
    {
        $this->musicFriend = $musicFriend;
    }

    /**
     * Get musicFriend
     *
     * @return boolean
     */
    public function getMusicFriend()
    {
        return $this->musicFriend;
    }

    /**
     * Set disqualified
     *
     * @param boolean $disqualified
     *
     * @return ConnectionRequest
     */
    public function setDisqualified($disqualified)
    {
        $this->disqualified = $disqualified;

        return $this;
    }

    /**
     * Get disqualified
     *
     * @return boolean
     */
    public function getDisqualified()
    {
        return $this->disqualified;
    }

    /**
     * Set disqualifiedComment
     *
     * @param string $disqualifiedComment
     *
     * @return ConnectionRequest
     */
    public function setDisqualifiedComment($disqualifiedComment)
    {
        $this->disqualifiedComment = $disqualifiedComment;

        return $this;
    }

    /**
     * Get disqualifiedComment
     *
     * @return string
     */
    public function getDisqualifiedComment()
    {
        return $this->disqualifiedComment;
    }

    /**
     * @param boolean $pending
     */
    public function setPending($pending)
    {
        $this->pending = $pending;
    }

    /**
     * @return boolean
     */
    public function getPending()
    {
        return $this->pending;
    }

    /**
     * @param boolean $inspected
     */
    public function setInspected($inspected)
    {
        $this->inspected = $inspected;
    }

    /**
     * @return boolean
     */
    public function getInspected()
    {
        return $this->inspected;
    }

    /**
     * @return string
     */
    public function getMusicFriendType()
    {
        return $this->musicFriend? 'filter.form.music_buddy': 'filter.form.fika_buddy';
    }
}
