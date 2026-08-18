<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests\Modifiers;

use Kadegray\StatamicPhoneNumberFieldtype\Modifiers\E164ToNationalPhoneFormat;
use Kadegray\StatamicPhoneNumberFieldtype\Tests\TestCase;
use libphonenumber\NumberParseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class E164ToNationalPhoneFormatTest extends TestCase
{
    #[Test]
    #[DataProvider('validE164Numbers')]
    public function it_formats_a_valid_e164_number_in_national_format(string $e164, string $expected)
    {
        $modifier = new E164ToNationalPhoneFormat();

        $this->assertSame($expected, $modifier->index($e164, [], []));
    }

    public static function validE164Numbers(): array
    {
        return [
            'US' => ['+12015550123', '(201) 555-0123'],
            'AU' => ['+61412345678', '0412 345 678'],
            'GB' => ['+442071838750', '020 7183 8750'],
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

        (new E164ToNationalPhoneFormat())->index('not-a-number', [], []);
    }

    #[Test]
    public function it_throws_on_an_empty_string()
    {
        $this->expectException(NumberParseException::class);

        (new E164ToNationalPhoneFormat())->index('', [], []);
    }
}
