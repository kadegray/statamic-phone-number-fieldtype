<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests\Feature;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Kadegray\StatamicPhoneNumberFieldtype\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

/**
 * Regression coverage for the fatal error fixed in commit a30e620:
 * Statamic 6 requires filter classes to implement isComplete(), which the
 * addon's now-removed custom PhoneNumberFieldtypeFilter never had. Applying
 * or changing the field's filter in a collection listing threw
 * "Call to undefined method ...::isComplete()". These tests exercise the
 * filter the fieldtype now falls back to (Statamic's built-in
 * FieldtypeFilter) end-to-end against real saved entries.
 */
class CollectionListingFilterTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Blueprint::make('pages')
            ->setNamespace('collections.pages')
            ->setContents([
                'fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text']],
                    ['handle' => 'phone_number', 'field' => ['type' => 'phone_number']],
                ],
            ])
            ->save();

        Collection::make('pages')->save();

        Entry::make()->collection('pages')->id('home')->data([
            'title' => 'Home',
            'phone_number' => '+12025551234',
        ])->save();

        Entry::make()->collection('pages')->id('about')->data([
            'title' => 'About',
            'phone_number' => '+61412345678',
        ])->save();
    }

    #[Test]
    public function is_complete_does_not_throw_and_reports_completeness_correctly()
    {
        $filter = Fieldtype::find('phone_number')->filter();

        $this->assertTrue($filter->isComplete(['operator' => 'like', 'value' => '2025']));
        $this->assertFalse($filter->isComplete(['operator' => null, 'value' => '2025']));
    }

    #[Test]
    public function applying_the_filter_narrows_the_listing_to_matching_entries()
    {
        $filter = Fieldtype::find('phone_number')->filter();

        $query = Entry::query()->where('collection', 'pages');
        $filter->apply($query, 'phone_number', ['operator' => 'like', 'value' => '2025']);

        $results = $query->get()->map->id()->all();

        $this->assertSame(['home'], $results);
    }

    #[Test]
    public function applying_the_filter_with_a_non_matching_value_returns_no_entries()
    {
        $filter = Fieldtype::find('phone_number')->filter();

        $query = Entry::query()->where('collection', 'pages');
        $filter->apply($query, 'phone_number', ['operator' => 'like', 'value' => '9999']);

        $this->assertCount(0, $query->get());
    }
}
