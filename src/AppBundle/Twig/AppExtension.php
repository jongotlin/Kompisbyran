<?php

namespace AppBundle\Twig;

use AppBundle\Entity\PreMatch;
use AppBundle\Enum\Countries;
use AppBundle\Enum\OccupationTypes;
use AppBundle\Manager\PreMatchManager;
use AppBundle\Manager\UserManager;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Entity\User;
use AppBundle\Util\Util;

/**
 * Class AppExtension
 * @package AppBundle\Twig
 */
class AppExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var PreMatchManager
     */
    private $preMatchManager;

    /**
     * @param TranslatorInterface $translator
     * @param UserManager $userManager
     */
    public function __construct(
        TranslatorInterface $translator,
        UserManager $userManager,
        PreMatchManager $preMatchManager
    )
    {
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->preMatchManager = $preMatchManager;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('country_name', [$this, 'countryName']),
            new \Twig_SimpleFilter('pronoun', [$this, 'pronoun']),
            new \Twig_SimpleFilter('gender', [$this, 'gender']),
            new \Twig_SimpleFilter('occupation', [$this, 'occupation']),
            new \Twig_SimpleFilter('meeting_time', [$this, 'meetingTime']),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'want_to_learn_name'        => new \Twig_Function_Method($this, 'wantToLearnName'),
            'user_category_name_string' => new \Twig_Function_Method($this, 'userCategoryNameString'),
            'user_matched_categories'   => new \Twig_Function_Method($this, 'userMatchedCategories'),
            'selected_city'             => new \Twig_Function_Method($this, 'selectedCity'),
            'google_map_link'           => new \Twig_Function_Method($this, 'googleMapLink', [
                'is_safe' => ['html']
            ]),
            'mark_matched_categories' => new \Twig_Function_Method($this, 'markMatchedCategories', [
                'is_safe' => ['html']
            ]),
            'translated_country'        => new \Twig_Function_Method($this, 'translatedCountry'),
        );
    }

    /**
     * @param string $countryCode
     *
     * @return string
     */
    public function countryName($countryCode)
    {
        if ($countryCode) {
            return Countries::getName($countryCode);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_extension';
    }

    /**
     * @param string $gender
     *
     * @return string
     */
    public function pronoun($gender)
    {
        if ('M' == $gender) {
            return 'han';
        } elseif ('F' == $gender) {
            return 'hon';
        }

        return 'hen';
    }

    /**
     * @param string $gender
     *
     * @return string
     */
    public function gender($gender)
    {
        if ('M' == $gender) {
            return $this->translator->trans('user.form.gender.m');
        } elseif ('F' == $gender) {
            return $this->translator->trans('user.form.gender.f');
        }

        return $this->translator->trans('user.form.gender.x');
    }

    /**
     * @param $occupationType
     *
     * @return string
     */
    public function occupation($occupationType)
    {
        return $this->translator->trans(OccupationTypes::tranlsationKey($occupationType));
    }

    /**
     * @param bool $wantToLearn
     * @return string
     */
    public function wantToLearnName($wantToLearn)
    {
        return $wantToLearn? $this->translator->trans('New'): $this->translator->trans('Established');
    }

    /**
     * @param User $user
     * @return string
     */
    public function userCategoryNameString(User $user)
    {
        $categoryNames  = array_values($user->getCategoryNames());

        return $this->toStringCategories($categoryNames);
    }

    /**
     * @param User $matchUser
     * @param User $user
     * @return string
     */
    public function userMatchedCategories(User $matchUser, User $user)
    {
        $matches = [];

        foreach ($matchUser->getCategoryNames() as $id => $name) {
            foreach ($user->getCategoryNames() as $userCatId => $userCatName) {
                if ($id == $userCatId) {
                    $matches[] = strtolower($userCatName);
                    break;
                }
            }
        }

        return $this->toStringCategories($matches);
    }

    /**
     * @param array $categories
     * @return array|string
     */
    private function toStringCategories(array $categories)
    {
        if (count($categories) > 1) {
            $lastCategory   = array_pop($categories);
            $categories = implode(', ', $categories) .' '.  $this->translator->trans('global.and') .' '. $lastCategory;
        } else {
            $categories = implode(', ', $categories);
        }

        return $categories;
    }

    /**
     * @param $string
     * @return string
     */
    public function googleMapLink($string)
    {
        return Util::googleMapLink($string);
    }

    /**
     * @param array $cities
     * @param $cityId
     * @return string|void
     */
    public function selectedCity($cities, $cityId)
    {
        foreach ($cities as $city) {
            if ($city->getId() == $cityId) {
                return 'SELECTED';
            }
        }

        return;
    }

    /**
     * @param $matchedUser
     * @param $user
     *
     * @return string
     */
    public function markMatchedCategories($matchedUser, $user)
    {
        return $this->userManager->getCategoriesExactMatchByUser($matchedUser, $user);
    }

    /**
     * @param User $user
     * @param string $locale
     * @return string
     */
    public function translatedCountry(User $user, $locale)
    {
        $country  = $user->getCountryName($locale);

        return $country;
    }

    /**
     * @param PreMatch $preMatch
     *
     * @return string
     */
    public function meetingTime(PreMatch $preMatch)
    {
        return $this->preMatchManager->getMeetingTime($preMatch);
    }
}
