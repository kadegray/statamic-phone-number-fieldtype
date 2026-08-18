<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Fieldtypes;

use Illuminate\Support\Facades\App;
use Statamic\Fields\Fieldtype;
use Sokil\IsoCodes\IsoCodesFactory;

class PhoneNumberFieldtype extends Fieldtype
{

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    protected function configFieldItems(): array
    {
        $countryCodes = $this->getCountryCodes();

        return [
            'show_country_select' => [
                'display' => __('Show Country Select'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'initial_country' => [
                'display' => __('Initial Country'),
                'type' => 'select',
                'options' => $countryCodes,
                'clearable' => true,
                'placeholder' => 'Select a an initial country..',
                'width' => 50,
            ],
            'preferred_countries' => [
                'display' => __('Preferred Countries'),
                'type' => 'select',
                'options' => $countryCodes,
                'multiple' => true,
                'placeholder' => 'Select a preferred country..',
                'width' => 50,
            ],
            'exclude_countries' => [
                'display' => __('Exclude Countries'),
                'type' => 'select',
                'options' => $countryCodes,
                'multiple' => true,
                'placeholder' => 'Select a country to exclude..',
                'width' => 50,
            ],
            'only_countries' => [
                'display' => __('Only Countries'),
                'type' => 'select',
                'options' => $countryCodes,
                'multiple' => true,
                'placeholder' => 'Select a country to be displayed..',
                'width' => 50,
            ],
        ];
    }

    private function getCountryCodes()
    {
        $locale = App::getLocale();
        putenv("LANGUAGE=$locale.UTF-8");
        putenv("LC_ALL=$locale.UTF-8");
        setlocale(LC_ALL, "$locale.UTF-8");

        $countries = [];
        $isoCodes = new IsoCodesFactory();
        foreach ($isoCodes->getCountries() as $country) {
            $countries[$country->getAlpha2()] = "{$country->getLocalName()} ({$country->getAlpha2()})";
        }

        return $countries;
    }
}
