<?php

declare(strict_types=1);

namespace Shtse8\SotiMath\Tests;

use PHPUnit\Framework\TestCase;
use Shtse8\SotiMath\SotiNumber;

final class SotiNumberTest extends TestCase
{
    public function testCanBeCreatedFromString(): void
    {
        $num = new SotiNumber('123.456');
        $this->assertInstanceOf(SotiNumber::class, $num);
    }

    public function testToStringReturnsCorrectString(): void
    {
        $num = new SotiNumber('123.456000');
        $this->assertSame('123.456', $num->toString());

        $numZero = new SotiNumber('0.000');
        $this->assertSame('0', $numZero->toString());

        $numInt = new SotiNumber('789');
        $this->assertSame('789', $numInt->toString());

        $numSci = new SotiNumber('1.23E+3'); // Should be normalized to 1230
        $this->assertSame('1230', $numSci->toString());

         $numNegSci = new SotiNumber('-5.6E-2'); // Should be normalized to -0.056
         $this->assertSame('-0.056', $numNegSci->toString());
    }

    // TODO: Add tests for all other public methods (add, sub, mul, div, mod, pow, ln, log, floor, ceil, abs, truncate, round, comparisons, format, human units etc.)
}