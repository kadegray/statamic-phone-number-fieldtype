<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests;

use Kadegray\StatamicPhoneNumberFieldtype\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
