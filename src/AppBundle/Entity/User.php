<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use AppBundle\Enum\Countries;

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
     * @ORM\OneToMany(targetEntity="ConnectionRequest", mappedBy="user")
     */
    protected $connectionRequests;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $wantToLearn = false;

    /**
     * @var GeneralCategory[]
     *
     * @Assert\Count(
     *     min=1,
     *     max=5,
     *     minMessage="Du måste välja minst ett intresse",
     *     maxMessage="Du kan inte välja fler än 5 intressen",
     *     groups={"settings"}
     * )
     * @ORM\ManyToMany(targetEntity="GeneralCategory", inversedBy="users")
     * @ORM\JoinTable(
     *     name="users_categories",
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $categories;

    /**
     * @var MusicCategory[]
     *
     * @Assert\Expression(
     *     "this.getType() != 'music' || (value.count() > 0 && value.count() <= 4)",
     *     message="Du måste välja minst ett och max fyra musikintressen",
     *     groups={"settings"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="MusicCategory", inversedBy="users")
     * @ORM\JoinTable(
     *     name="users_music_categories",
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $musicCategories;

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
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $internalComment;

    /**
     * @var string
     *
     * Might be removed after music friend campaign
     * //Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $district;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hasChildren = false;

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
    protected $type = 'friend';

    /**
     * @var Municipality
     *
     * @Assert\NotBlank(groups={"settings"})
     * @ORM\ManyToOne(targetEntity="Municipality", inversedBy="users")
     */
    protected $municipality;

    /**
     * @ORM\ManyToMany(targetEntity="City", inversedBy="users")
     * @ORM\JoinTable(name="users_cities")
     */
    private $cities;

    public function __construct()
    {
        $this->fluentSpeakerConnections = new ArrayCollection();
        $this->learnerConnections       = new ArrayCollection();
        $this->connectionRequests       = new ArrayCollection();
        $this->createdConnections       = new ArrayCollection();
        $this->categories               = new ArrayCollection();
        $this->musicCategories          = new ArrayCollection();
        $this->createdAt                = new \DateTime();
        $this->comments                 = new ArrayCollection();
        $this->cities                   = new ArrayCollection();

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
     * @return \AppBundle\Entity\ConnectionRequest[]
     */
    public function getConnectionRequests()
    {
        return $this->connectionRequests;
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
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param string $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
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
     * @return MusicCategory[]
     */
    public function getMusicCategories()
    {
        return $this->musicCategories;
    }

    /**
     * @param MusicCategory[] $musicCategories
     */
    public function setMusicCategories($musicCategories)
    {
        $this->musicCategories = $musicCategories;
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

        foreach($this->getMusicCategories() as $category) {
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
     * @return string
     */
    public function getFirstConnectionRequestComment()
    {
        if ($this->connectionRequests->count()) {
            return $this->connectionRequests->first()->getComment();
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
     * @return array
     */
    public function getMusicCategoryIds()
    {
        $ids = [];

        foreach ($this->musicCategories as $category) {
            $ids[] = $category->getId();
        }

        return $ids;
    }

    /**
     * @return string
     */
    public function getFirstConnectionRequest()
    {
        return $this->connectionRequests->first();
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
     * Add city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return User
     */
    public function addCity(\AppBundle\Entity\City $city)
    {
        $this->cities[] = $city;

        return $this;
    }

    /**
     * Remove city
     *
     * @param \AppBundle\Entity\City $city
     */
    public function removeCity(\AppBundle\Entity\City $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection
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
}
