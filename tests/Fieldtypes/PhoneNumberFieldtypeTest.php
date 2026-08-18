<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Kadegray\StatamicPhoneNumberFieldtype\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PhoneNumberFieldtypeTest extends TestCase
{
    #[Test]
    public function it_is_registered_under_the_phone_number_handle()
    {
        $this->assertNotNull(Fieldtype::find('phone_number'));
    }

    #[Test]
    public function default_value_is_null()
    {
        $this->assertNull(Fieldtype::find('phone_number')->defaultValue());
    }

    #[Test]
    public function pre_process_and_process_are_pass_throughs()
    {
        $fieldtype = Fieldtype::find('phone_number');

        $this->assertSame('+12015550123', $fieldtype->preProcess('+12015550123'));
        $this->assertSame('+12015550123', $fieldtype->process('+12015550123'));
        $this->assertNull($fieldtype->preProcess(null));
        $this->assertNull($fieldtype->process(null));
    }

    #[Test]
    public function config_fields_expose_the_expected_options()
    {
        $fields = Fieldtype::find('phone_number')->configFields();

        $this->assertTrue($fields->has('show_country_select'));
        $this->assertTrue($fields->has('initial_country'));
        $this->assertTrue($fields->has('preferred_countries'));
        $this->assertTrue($fields->has('exclude_countries'));
        $this->assertTrue($fields->has('only_countries'));

        $this->assertSame('toggle', $fields->get('show_country_select')->type());
        $this->assertTrue($fields->get('show_country_select')->get('default'));

        $this->assertSame('select', $fields->get('initial_country')->type());
        $this->assertTrue($fields->get('preferred_countries')->get('multiple'));
        $this->assertTrue($fields->get('exclude_countries')->get('multiple'));
        $this->assertTrue($fields->get('only_countries')->get('multiple'));
    }

    #[Test]
    public function config_country_options_contain_real_iso_countries()
    {
        $options = Fieldtype::find('phone_number')->configFields()->get('initial_country')->get('options');

        $this->assertArrayHasKey('US', $options);
        $this->assertStringContainsString('United States', $options['US']);
        $this->assertArrayHasKey('GB', $options);
        $this->assertArrayHasKey('AU', $options);
    }

    /**
     * Regression test: PhoneNumberFieldtype::filter() was removed because it
     * duplicated Statamic's own FieldtypeFilter but didn't implement
     * isComplete(), causing a fatal error under Statamic 6. Asserting the
     * fieldtype now falls back to Statamic's built-in filter guards against
     * that override being reintroduced.
     */
    #[Test]
    public function it_uses_statamics_built_in_fieldtype_filter()
    {
        $filter = Fieldtype::find('phone_number')->filter();

        $this->assertInstanceOf(\Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter::class, $filter);
        $this->assertTrue(method_exists($filter, 'isComplete'));
    }
}
