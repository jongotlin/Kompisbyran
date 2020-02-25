<?php

namespace AppBundle\Entity;

use AppBundle\Enum\FriendTypes;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use AppBundle\Enum\Countries;
use JGI\IdentityNumberValidatorBundle\Validator\Constraints as IdentityNumberAssert;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="fos_user")
 * @UniqueEntity(fields="email", message="Epostadressen är redan registrerad")
 **/
class User extends BaseUser
{
    const GENDER_MALE   = 'M';

    const GENDER_FEMALE = 'F';

    const GENDER_X      = 'X';

    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $profilePicture;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="fluentSpeaker")
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    protected $fluentSpeakerConnections;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="learner")
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    protected $learnerConnections;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="createdBy")
     */
    protected $createdConnections;

    /**
     * @var ConnectionRequest[]
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(targetEntity="ConnectionRequest", mappedBy="user")
     */
    protected $connectionRequests;

    /**
     * @var ConnectionRequest
     *
     * @Assert\Valid
     */
    protected $newConnectionRequest;

    /**
     * @var bool
     *
     * @Assert\NotNull(groups={"settings"})
     *
     * @ORM\Column(type="boolean")
     */
    protected $wantToLearn = false;

    /**
     * @var Category[]
     *
     * @Assert\Count(
     *     min=2,
     *     max=5,
     *     minMessage="Du måste välja minst 2 intressen",
     *     maxMessage="Du kan inte välja fler än 5 intressen",
     *     groups={"settings"}
     * )
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="users")
     * @ORM\JoinTable(
     *     name="users_categories",
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $categories;

    /**
     * @var int
     *
     * @Assert\Range(min=18, max=100, minMessage="Du måste vara minst 18 år", groups={"settings"})
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $age;

    /**
     * @var string
     *
     * @Assert\NotNull(groups={"settings"})
     *
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    protected $gender;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", name="from_country", nullable=true)
     */
    protected $from;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     * @Assert\Length(min=10, max=300, groups={"settings"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string")
     */
    protected $occupation = false;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $occupationDescription;

    /**
     * @var bool
     *
     * @Assert\NotNull(groups={"settings"})
     *
     * @ORM\Column(type="boolean")
     */
    protected $education = false;

    /**
     * @var string
     *
     * @Assert\Expression(
     *     "!this.hasEducation() || this.getEducationDescription() != ''",
     *     message="Du måste beskriva din utbildning",
     *     groups={"settings"}
     * )
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $educationDescription;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $timeInSweden;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $internalComment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var bool
     *
     * @Assert\NotNull(groups={"settings"})
     *
     * @ORM\Column(type="boolean")
     */
    protected $hasChildren = false;

    /**
     * @var string
     *
     * @Assert\Expression(
     *     "!this.hasChildren() || this.getChildrenAge() != ''",
     *     message="Du måste fylla i ålder på barn",
     *     groups={"settings"}
     * )
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $childrenAge;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $phoneNumber;

    /**
     * @var string
     * @Assert\Email
     */
    protected $email;

    /**
     * @var ConnectionComment[]
     *
     * @ORM\OneToMany(targetEntity="ConnectionComment", mappedBy="connection")
     */
    protected $comments;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $newlyArrived = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $atArbetsformedlingen = false;

    /**
     * @var string
     *
     * @IdentityNumberAssert\IdentityNumber(allowCoordinationNumber=true, groups={"settings"})
     * @Assert\Expression(
     *     "!this.getWantToLearn() || !this.isNewlyArrived() || this.getIdentityNumber() != ''",
     *     message="Du måste fylla i ditt personnummer",
     *     groups={"settings"}
     * )
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $identityNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $uuid;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmedKeepDataAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $inactiveEmailSentAt;

    /**
     * @var Municipality
     *
     * @Assert\NotBlank(groups={"settings"})
     * @ORM\ManyToOne(targetEntity="Municipality", inversedBy="users")
     */
    protected $municipality;

    /**
     * @var City
     *
     * @Assert\NotBlank(groups={"settings"})
     * @ORM\ManyToOne(targetEntity="City")
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity="City", inversedBy="users")
     * @ORM\JoinTable(name="users_cities")
     */
    private $cities;

    /**
     * @var Municipality[]
     *
     * @ORM\ManyToMany(targetEntity="Municipality", inversedBy="adminUsers")
     */
    private $adminMunicipalities;

    public function __construct()
    {
        $this->fluentSpeakerConnections = new ArrayCollection();
        $this->learnerConnections       = new ArrayCollection();
        $this->connectionRequests       = new ArrayCollection();
        $this->createdConnections       = new ArrayCollection();
        $this->categories               = new ArrayCollection();
        $this->createdAt                = new \DateTime();
        $this->comments                 = new ArrayCollection();
        $this->cities                   = new ArrayCollection();
        $this->type = FriendTypes::FRIEND;
        $this->adminMunicipalities = new ArrayCollection();
        $this->uuid = Uuid::uuid4();

        parent::__construct();
    }

    /**
     * @return Connection[]|ArrayCollection
     */
    public function getConnections()
    {
        return new ArrayCollection(array_merge(
            $this->fluentSpeakerConnections->toArray(),
            $this->learnerConnections->toArray()
        ));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return trim(sprintf('%s %s', $this->getFirstName(), $this->getLastName()));
    }
    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $profilePicture
     */
    public function setProfilePicture($profilePicture)
    {
        $this->profilePicture = $profilePicture;
    }

    /**
     * @return string
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @param mixed $fluentSpeakerConnections
     */
    public function setFluentSpeakerConnections($fluentSpeakerConnections)
    {
        $this->fluentSpeakerConnections = $fluentSpeakerConnections;
    }

    /**
     * @return mixed
     */
    public function getFluentSpeakerConnections()
    {
        return $this->fluentSpeakerConnections;
    }

    /**
     * @param \AppBundle\Entity\Connection[] $learnerConnections
     */
    public function setLearnerConnections($learnerConnections)
    {
        $this->learnerConnections = $learnerConnections;
    }

    /**
     * @return \AppBundle\Entity\Connection[]
     */
    public function getLearnerConnections()
    {
        return $this->learnerConnections;
    }

    /**
     * @param \AppBundle\Entity\ConnectionRequest[] $connectionRequests
     */
    public function setConnectionRequests($connectionRequests)
    {
        $this->connectionRequests = $connectionRequests;
    }

    /**
     * @return ConnectionRequest[]|ArrayCollection
     */
    public function getConnectionRequests()
    {
        return $this->connectionRequests;
    }

    /**
     * @return ConnectionRequest|mixed|null
     */
    public function getMostRecentConnectionRequest()
    {
        $connectionRequest = null;
        foreach ($this->connectionRequests as $loopedConnectionRequest) {
            if ($connectionRequest == null || $loopedConnectionRequest->getCreatedAt() > $loopedConnectionRequest->getCreatedAt()) {
                $connectionRequest = $loopedConnectionRequest;
            }
        }

        return $connectionRequest;
    }

    /**
     * @return ConnectionRequest|null
     */
    public function getOpenConnectionRequest()
    {
        foreach ($this->connectionRequests as $connectionRequest) {
            if (!$connectionRequest->getConnection()) {
                return $connectionRequest;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasOpenConnectionRequest()
    {
        return !is_null($this->getOpenConnectionRequest());
    }

    /**
     * @param ConnectionRequest $connectionRequest
     */
    public function addConnectionRequest(ConnectionRequest $connectionRequest)
    {
        $this->connectionRequests->add($connectionRequest);
        $connectionRequest->setUser($this);
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
     * @param Category[] $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getInternalComment()
    {
        return $this->internalComment;
    }

    /**
     * @param string $internalComment
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->username = $email;
    }

    /**
     * @param string $emailCanonical
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
        $this->usernameCanonical = $emailCanonical;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return $this->hasChildren;
    }

    /**
     * @param boolean $hasChildren
     */
    public function setHasChildren($hasChildren)
    {
        $this->hasChildren = $hasChildren;
    }

    /**
     * @return Municipality
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * @param Municipality $municipality
     */
    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName .' '. $this->lastName;
    }

    /**
     * @return array
     */
    public static function getGenders()
    {
        return [
            self::GENDER_MALE   => 'user.form.gender.m',
            self::GENDER_FEMALE => 'user.form.gender.f',
            self::GENDER_X      => 'user.form.gender.x'
        ];
    }

    /**
     * @return array
     */
    public function getCategoryNames()
    {
        $categories = [];

        foreach($this->getCategories() as $category) {
            $categories[$category->getId()] = $category->getName();
        }

        asort($categories);

        return $categories;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        if ($this->from) {
            return Countries::getName($this->from);
        }

        return '';
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        $ids = [];

        foreach ($this->categories as $category) {
            $ids[] = $category->getId();
        }

        return $ids;
    }

    /**
     * @return string
     */
    public function getGenderName()
    {
        $genders = self::getGenders();

        return isset($genders[$this->getGender()])? $genders[$this->getGender()]: '';
    }

    /**
     * @param City $city
     */
    public function addCity(City $city)
    {
        $this->cities[] = $city;
    }

    /**
     * @param City $city
     */
    public function removeCity(City $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * @return ArrayCollection|City[]
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param City $city
     * @return bool
     */
    public function hasAccessToCity(City $city)
    {
        foreach ($this->cities as $userCity) {
            if ($userCity->getId() == $city->getId()) {
                return true;
            }
        }

        return false;
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

    /**
     * @return Municipality[]|ArrayCollection
     */
    public function getAdminMunicipalities()
    {
        return $this->adminMunicipalities;
    }

    /**
     * @param Municipality[] $adminMunicipalities
     */
    public function setAdminMunicipalities($adminMunicipalities)
    {
        $this->adminMunicipalities = $adminMunicipalities;
    }

    /**
     * @param Municipality $adminMunicipality
     */
    public function addAdminMunicipality(Municipality $adminMunicipality)
    {
        $this->adminMunicipalities->add($adminMunicipality);
    }

    /**
     * @param Municipality $municipality
     */
    public function removeAdminMunicipality(Municipality $municipality)
    {
        $this->adminMunicipalities->removeElement($municipality);
    }

    /**
     * @return string
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * @param string $occupation
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;
    }

    /**
     * @return string
     */
    public function getOccupationDescription()
    {
        return $this->occupationDescription;
    }

    /**
     * @param string $occupationDescription
     */
    public function setOccupationDescription($occupationDescription)
    {
        $this->occupationDescription = $occupationDescription;
    }

    /**
     * @return boolean
     */
    public function hasEducation()
    {
        return $this->education;
    }

    /**
     * @param boolean $education
     */
    public function setEducation($education)
    {
        $this->education = $education;
    }

    /**
     * @return string
     */
    public function getEducationDescription()
    {
        return $this->educationDescription;
    }

    /**
     * @param string $educationDescription
     */
    public function setEducationDescription($educationDescription)
    {
        $this->educationDescription = $educationDescription;
    }

    /**
     * @return string
     */
    public function getTimeInSweden()
    {
        return $this->timeInSweden;
    }

    /**
     * @param string $timeInSweden
     */
    public function setTimeInSweden($timeInSweden)
    {
        $this->timeInSweden = $timeInSweden;
    }

    /**
     * @return string
     */
    public function getChildrenAge()
    {
        return $this->childrenAge;
    }

    /**
     * @param string $childrenAge
     */
    public function setChildrenAge($childrenAge)
    {
        $this->childrenAge = $childrenAge;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getIdentityNumber()
    {
        return $this->identityNumber;
    }

    /**
     * @param string $identityNumber
     */
    public function setIdentityNumber($identityNumber)
    {
        $this->identityNumber = $identityNumber;
    }

    /**
     * @return bool
     */
    public function isNewlyArrived()
    {
        return $this->newlyArrived;
    }

    /**
     * @param bool $newlyArrived
     */
    public function setNewlyArrived($newlyArrived)
    {
        $this->newlyArrived = $newlyArrived;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return ConnectionRequest
     */
    public function getNewConnectionRequest()
    {
        return $this->newConnectionRequest;
    }

    /**
     * @param ConnectionRequest $newConnectionRequest
     */
    public function setNewConnectionRequest($newConnectionRequest)
    {
        $this->newConnectionRequest = $newConnectionRequest;
        $newConnectionRequest->setUser($this);
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param City $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return \DateTime|null
     */
    public function getConfirmedKeepDataAt()
    {
        return $this->confirmedKeepDataAt;
    }

    /**
     * @param \DateTime|null $confirmedKeepDataAt
     */
    public function setConfirmedKeepDataAt($confirmedKeepDataAt)
    {
        $this->confirmedKeepDataAt = $confirmedKeepDataAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getInactiveEmailSentAt()
    {
        return $this->inactiveEmailSentAt;
    }

    /**
     * @param \DateTime|null $inactiveEmailSentAt
     */
    public function setInactiveEmailSentAt($inactiveEmailSentAt)
    {
        $this->inactiveEmailSentAt = $inactiveEmailSentAt;
    }

    /**
     * @return bool
     */
    public function isAtArbetsformedlingen()
    {
        return $this->atArbetsformedlingen;
    }

    /**
     * @param bool $atArbetsformedlingen
     */
    public function setAtArbetsformedlingen($atArbetsformedlingen)
    {
        $this->atArbetsformedlingen = $atArbetsformedlingen;
    }
}
