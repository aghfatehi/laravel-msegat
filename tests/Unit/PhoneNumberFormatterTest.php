<?php

namespace Aghfatehi\Msegat\Tests\Unit;

use Aghfatehi\Msegat\Support\PhoneNumberFormatter;
use PHPUnit\Framework\TestCase;

class PhoneNumberFormatterTest extends TestCase
{
    public function test_format_saudi_number_with_05_prefix(): void
    {
        $this->assertSame('966512345678', PhoneNumberFormatter::format('0512345678'));
    }

    public function test_format_saudi_number_without_prefix(): void
    {
        $this->assertSame('966512345678', PhoneNumberFormatter::format('512345678'));
    }

    public function test_format_saudi_number_with_country_code(): void
    {
        $this->assertSame('966512345678', PhoneNumberFormatter::format('966512345678'));
    }

    public function test_format_saudi_number_with_plus_country_code(): void
    {
        $this->assertSame('966512345678', PhoneNumberFormatter::format('+966512345678'));
    }

    public function test_format_international_number(): void
    {
        $this->assertSame('971501234567', PhoneNumberFormatter::format('971501234567'));
    }

    public function test_format_number_with_special_chars(): void
    {
        $this->assertSame('966512345678', PhoneNumberFormatter::format('+966 51 234 5678'));
    }
}
