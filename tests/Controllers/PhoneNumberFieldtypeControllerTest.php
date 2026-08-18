<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests\Controllers;

use Kadegray\StatamicPhoneNumberFieldtype\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PhoneNumberFieldtypeControllerTest extends TestCase
{
    private function countriesUrl(string $lang): string
    {
        return '/!/statamic-phone-number-fieldtype/'.$lang.'/countries';
    }

    #[Test]
    public function it_returns_localized_country_names()
    {
        $response = $this->get($this->countriesUrl('fr'));

        $response->assertOk();
        $countries = $response->json();

        $this->assertGreaterThan(100, count($countries));
        $this->assertArrayHasKey('fr', $countries);
        $this->assertSame('France', $countries['fr']);
        $this->assertArrayHasKey('de', $countries);
        $this->assertSame('Allemagne', $countries['de']);
    }

    /**
     * Regression test for the gettext process-caching bug: the underlying
     * sokil/php-isocodes package's default driver caches translations at the
     * process level, so once one locale's catalog loaded, every later
     * request in that PHP process kept returning it regardless of what was
     * actually requested. Requesting several different locales in sequence,
     * within the same test (and therefore the same PHP process), is what
     * actually exercises that failure mode.
     */
    #[Test]
    public function sequential_requests_for_different_locales_do_not_bleed_into_each_other()
    {
        $de = $this->get($this->countriesUrl('de'))->json();
        $this->assertSame('Frankreich', $de['fr']);
        $this->assertSame('Deutschland', $de['de']);

        $fr = $this->get($this->countriesUrl('fr'))->json();
        $this->assertSame('France', $fr['fr']);
        $this->assertSame('Allemagne', $fr['de']);

        $es = $this->get($this->countriesUrl('es'))->json();
        $this->assertSame('Francia', $es['fr']);
        $this->assertSame('Alemania', $es['de']);

        // Back to the first locale again, to prove nothing got "stuck".
        $deAgain = $this->get($this->countriesUrl('de'))->json();
        $this->assertSame('Frankreich', $deAgain['fr']);
        $this->assertSame('Deutschland', $deAgain['de']);
    }

    #[Test]
    public function it_returns_english_country_names_for_the_en_locale()
    {
        $countries = $this->get($this->countriesUrl('en'))->json();

        $this->assertSame('France', $countries['fr']);
        $this->assertSame('Germany', $countries['de']);
        $this->assertSame('United States', $countries['us']);
    }
}
