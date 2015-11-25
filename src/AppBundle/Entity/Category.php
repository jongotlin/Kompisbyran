<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity
 */
class Category implements Translatable
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
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var User[]
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="categories")
     */
    private $users;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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
     * @param \AppBundle\Entity\User[] $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return \AppBundle\Entity\User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
