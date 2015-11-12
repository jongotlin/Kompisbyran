<?php

namespace AppBundle\Twig;

use AppBundle\Enum\Countries;

class AppExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('country_name', [$this, 'countryName']),
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
