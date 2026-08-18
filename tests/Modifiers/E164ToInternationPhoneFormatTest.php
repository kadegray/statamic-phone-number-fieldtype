<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests\Modifiers;

use Kadegray\StatamicPhoneNumberFieldtype\Modifiers\E164ToInternationPhoneFormat;
use Kadegray\StatamicPhoneNumberFieldtype\Tests\TestCase;
use libphonenumber\NumberParseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class E164ToInternationPhoneFormatTest extends TestCase
{
    #[Test]
    #[DataProvider('validE164Numbers')]
    public function it_formats_a_valid_e164_number_in_international_format(string $e164, string $expected)
    {
        $modifier = new E164ToInternationPhoneFormat();

        $this->assertSame($expected, $modifier->index($e164, [], []));
    }

    public static function validE164Numbers(): array
    {
        return [
            'US' => ['+12015550123', '+1 201-555-0123'],
            'AU' => ['+61412345678', '+61 412 345 678'],
            'GB' => ['+442071838750', '+44 20 7183 8750'],
        ];
    }

    /**
     * Documents current behavior: the modifier does not catch parse
     * failures, so malformed input propagates as an uncaught exception
     * rather than failing gracefully.
     */
    #[Test]
    public function it_throws_on_an_unparsable_number()
    {
        $this->expectException(NumberParseException::class);

        (new E164ToInternationPhoneFormat())->index('not-a-number', [], []);
    }

    #[Test]
    public function it_throws_on_an_empty_string()
    {
        $this->expectException(NumberParseException::class);

        (new E164ToInternationPhoneFormat())->index('', [], []);
    }
}
