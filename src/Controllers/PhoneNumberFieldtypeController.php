<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Sokil\IsoCodes\IsoCodesFactory;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class PhoneNumberFieldtypeController extends BaseController
{
    public function getCountries($lang)
    {
        $translationDriver = new SymfonyTranslationDriver();
        $translationDriver->setLocale($lang);

        $countries = [];
        $isoCodes = new IsoCodesFactory(null, $translationDriver);
        foreach ($isoCodes->getCountries() as $country) {
            $countries[strtolower($country->getAlpha2())] = $country->getLocalName();
        }

        return $countries;
    }
}
