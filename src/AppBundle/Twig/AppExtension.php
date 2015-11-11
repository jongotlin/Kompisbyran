<?php

namespace AppBundle\Twig;

use AppBundle\Enum\Countries;
use AppBundle\Enum\Languages;

class AppExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('country_name', [$this, 'countryName']),
            new \Twig_SimpleFilter('language_names', [$this, 'languageNames']),
            new \Twig_SimpleFilter('pronoun', [$this, 'pronoun']),
        ];
    }

    /**
     * @param string $countryCode
     *
     * @return string
     */
    public function countryName($countryCode)
    {
        return Countries::getName($countryCode);
    }

    /**
     * @param string[] $languageCodes
     *
     * @return string[]
     */
    public function languageNames($languageCodes)
    {
        $languageNames = [];
        foreach ($languageCodes as $languageCode) {
            $languageNames[] = Languages::getName($languageCode);
        }

        return $languageNames;
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
}
