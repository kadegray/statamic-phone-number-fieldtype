<?php

namespace Kadegray\StatamicPhoneNumberFieldtype;

use Statamic\Providers\AddonServiceProvider;
use Kadegray\StatamicPhoneNumberFieldtype\Fieldtypes\PhoneNumberFieldtype;
use Kadegray\StatamicPhoneNumberFieldtype\Modifiers\E164ToNationalPhoneFormat;
use Kadegray\StatamicPhoneNumberFieldtype\Modifiers\E164ToInternationPhoneFormat;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__ . '/../routes/actions.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $publishables = [
        __DIR__ . '/../public/images' => 'images',
    ];

    protected $modifiers = [
        E164ToNationalPhoneFormat::class,
        E164ToInternationPhoneFormat::class
    ];

    public function bootAddon()
    {
        PhoneNumberFieldtype::register();
    }
}
