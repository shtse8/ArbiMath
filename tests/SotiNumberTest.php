<?php

declare(strict_types=1);

namespace Shtse8\SotiMath\Tests;

use PHPUnit\Framework\TestCase;
use Shtse8\SotiMath\SotiNumber;

final class SotiNumberTest extends TestCase
{
    /**
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testCanBeCreatedFromString(): void
    {
        $num = new SotiNumber('123.456');
        $this->assertInstanceOf(SotiNumber::class, $num);
    }

    /**
     * @covers \Shtse8\SotiMath\SotiNumber::toString
     * @covers \Shtse8\SotiMath\SotiNumber::__toString
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
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


    // --- Arithmetic Tests ---

    /**
     * @dataProvider additionProvider
     * @covers \Shtse8\SotiMath\SotiNumber::add
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testAddition(string $a, string $b, string $expected): void
    {
        $numA = new SotiNumber($a);
        $numB = new SotiNumber($b);
        $this->assertSame($expected, $numA->add($numB)->toString());
        // Test with string argument as well
        $this->assertSame($expected, $numA->add($b)->toString());
    }

    public static function additionProvider(): array
    {
        return [
            'positive integers' => ['5', '3', '8'],
            'negative integers' => ['-5', '-3', '-8'],
            'mixed integers' => ['5', '-3', '2'],
            'positive decimals' => ['1.23', '4.56', '5.79'],
            'negative decimals' => ['-1.23', '-4.56', '-5.79'],
            'mixed decimals' => ['10.5', '-2.1', '8.4'],
            'zero' => ['10', '0', '10'],
            'scientific notation' => ['1.5E+2', '50', '200'], // 150 + 50
            'high precision' => ['0.12345678901234567890', '0.09876543210987654321', '0.22222222112222222211'],
        ];
    }

    /**
     * @dataProvider subtractionProvider
     * @covers \Shtse8\SotiMath\SotiNumber::sub
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testSubtraction(string $a, string $b, string $expected): void
    {
        $numA = new SotiNumber($a);
        $numB = new SotiNumber($b);
        $this->assertSame($expected, $numA->sub($numB)->toString());
        $this->assertSame($expected, $numA->sub($b)->toString());
    }

    public static function subtractionProvider(): array
    {
        return [
            'positive integers' => ['5', '3', '2'],
            'negative integers' => ['-5', '-3', '-2'],
            'mixed integers' => ['5', '-3', '8'],
            'positive decimals' => ['4.56', '1.23', '3.33'],
            'negative decimals' => ['-1.23', '-4.56', '3.33'],
            'mixed decimals' => ['10.5', '-2.1', '12.6'],
            'to zero' => ['10', '10', '0'],
            'scientific notation' => ['2E+2', '50', '150'], // 200 - 50
        ];
    }

    /**
     * @dataProvider multiplicationProvider
     * @covers \Shtse8\SotiMath\SotiNumber::mul
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testMultiplication(string $a, string $b, string $expected): void
    {
        $numA = new SotiNumber($a);
        $numB = new SotiNumber($b);
        $this->assertSame($expected, $numA->mul($numB)->toString());
        $this->assertSame($expected, $numA->mul($b)->toString());
    }

    public static function multiplicationProvider(): array
    {
        return [
            'positive integers' => ['5', '3', '15'],
            'negative integers' => ['-5', '-3', '15'],
            'mixed integers' => ['5', '-3', '-15'],
            'positive decimals' => ['1.5', '2.5', '3.75'],
            'mixed decimals' => ['-1.5', '2', '-3'],
            'by zero' => ['10', '0', '0'],
            'scientific notation' => ['1.5E+1', '2', '30'], // 15 * 2
        ];
    }

    /**
     * @dataProvider divisionProvider
     * @covers \Shtse8\SotiMath\SotiNumber::div
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testDivision(string $a, string $b, string $expected): void
    {
        $numA = new SotiNumber($a);
        $numB = new SotiNumber($b);
        $this->assertSame($expected, $numA->div($numB)->toString());
        $this->assertSame($expected, $numA->div($b)->toString());
    }

    public static function divisionProvider(): array
    {
        // Note: bcdiv truncates, scale is 20
        return [
            'positive integers' => ['6', '3', '2'],
            'negative integers' => ['-6', '-3', '2'],
            'mixed integers' => ['6', '-3', '-2'],
            'positive decimals' => ['7.5', '2.5', '3'],
            'repeating decimal' => ['10', '3', '3.33333333333333333333'],
            'scientific notation' => ['3E+2', '2', '150'], // 300 / 2
        ];
    }

    /**
     * @dataProvider modulusProvider
     * @covers \Shtse8\SotiMath\SotiNumber::mod
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testModulus(string $a, string $b, string $expected): void
    {
        $numA = new SotiNumber($a);
        $numB = new SotiNumber($b);
        $this->assertSame($expected, $numA->mod($numB)->toString());
        $this->assertSame($expected, $numA->mod($b)->toString());
    }

    public static function modulusProvider(): array
    {
        return [
            'positive integers' => ['10', '3', '1'],
            'negative dividend' => ['-10', '3', '-1'], // bcmod behavior
            'negative divisor' => ['10', '-3', '1'],   // bcmod behavior
            'decimals' => ['10.5', '2', '0.5'], // bcmod handles decimals
            'zero result' => ['9', '3', '0'],
        ];
    }

    /**
     * @dataProvider powerProvider
     * @covers \Shtse8\SotiMath\SotiNumber::pow
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testPower(string $base, string $exp, string $expected): void
    {
        $numBase = new SotiNumber($base);
        $numExp = new SotiNumber($exp);
        $this->assertSame($expected, $numBase->pow($numExp)->toString());
        $this->assertSame($expected, $numBase->pow($exp)->toString());
    }

    public static function powerProvider(): array
    {
        return [
            'positive integer exponent' => ['2', '3', '8'],
            'negative integer exponent' => ['2', '-2', '0.25'],
            'zero exponent' => ['10', '0', '1'],
            'base zero' => ['0', '5', '0'],
            'base one' => ['1', '100', '1'],
            'decimal base' => ['1.5', '2', '2.25'],
            // bcpow does NOT support fractional exponents, even with scale, throws ValueError in PHP 8+
            // 'fractional exponent (sqrt)' => ['9', '0.5', '3'], // Removed due to bcpow limitation
            'negative base, odd exponent' => ['-2', '3', '-8'],
            // Note: bcpow with negative base and non-integer exponent might be undefined or inconsistent
            // 'negative base, even exponent' => ['-2', '2', '4'], // This works
        ];
    }

    // --- Rounding and Absolute Value Tests ---

    /**
     * @dataProvider floorProvider
     * @covers \Shtse8\SotiMath\SotiNumber::floor
     * @covers \Shtse8\SotiMath\SotiNumber::isNegative
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     */
    public function testFloor(string $value, string $expected): void
    {
        $num = new SotiNumber($value);
        $this->assertSame($expected, $num->floor()->toString());
    }

    public static function floorProvider(): array
    {
        return [
            'positive integer' => ['5', '5'],
            'positive decimal' => ['5.7', '5'],
            'positive decimal .1' => ['5.1', '5'],
            'negative integer' => ['-5', '-5'],
            'negative decimal' => ['-5.3', '-6'],
            'negative decimal .9' => ['-5.9', '-6'],
            'zero' => ['0', '0'],
            'zero decimal' => ['0.8', '0'],
            'negative zero decimal' => ['-0.8', '-1'],
        ];
    }

    /**
     * @dataProvider ceilProvider
     * @covers \Shtse8\SotiMath\SotiNumber::ceil
     * @covers \Shtse8\SotiMath\SotiNumber::isPositive
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     */
    public function testCeil(string $value, string $expected): void
    {
        $num = new SotiNumber($value);
        $this->assertSame($expected, $num->ceil()->toString());
    }

    public static function ceilProvider(): array
    {
        return [
            'positive integer' => ['5', '5'],
            'positive decimal' => ['5.3', '6'],
            'positive decimal .9' => ['5.9', '6'],
            'negative integer' => ['-5', '-5'],
            'negative decimal' => ['-5.7', '-5'],
            'negative decimal .1' => ['-5.1', '-5'],
            'zero' => ['0', '0'],
            'zero decimal' => ['0.2', '1'],
            'negative zero decimal' => ['-0.2', '0'],
        ];
    }

    /**
     * @dataProvider absoluteProvider
     * @covers \Shtse8\SotiMath\SotiNumber::abs
     * @covers \Shtse8\SotiMath\SotiNumber::isNegative
     * @covers \Shtse8\SotiMath\SotiNumber::mul
     * @covers \Shtse8\SotiMath\SotiNumber::duplicate
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     */
    public function testAbsolute(string $value, string $expected): void
    {
        $num = new SotiNumber($value);
        $this->assertSame($expected, $num->abs()->toString());
    }

    public static function absoluteProvider(): array
    {
        return [
            'positive integer' => ['5', '5'],
            'negative integer' => ['-5', '5'],
            'positive decimal' => ['1.23', '1.23'],
            'negative decimal' => ['-1.23', '1.23'],
            'zero' => ['0', '0'],
            'negative zero' => ['-0', '0'], // abs should handle this via bccomp
        ];
    }


    // --- Comparison Tests ---

    /**
     * @dataProvider comparisonProvider
     * @covers \Shtse8\SotiMath\SotiNumber::isEqual
     * @covers \Shtse8\SotiMath\SotiNumber::isSmaller
     * @covers \Shtse8\SotiMath\SotiNumber::isGreater
     * @covers \Shtse8\SotiMath\SotiNumber::isSmallerOrEqual
     * @covers \Shtse8\SotiMath\SotiNumber::isGreaterOrEqual
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     * @covers \Shtse8\SotiMath\SotiNumber::normalizeFloat
     */
    public function testComparisons(string $a, string $b, bool $eq, bool $lt, bool $gt, bool $lte, bool $gte): void
    {
        $numA = new SotiNumber($a);
        $numB = new SotiNumber($b);

        $this->assertSame($eq, $numA->isEqual($numB));
        $this->assertSame($eq, $numA->isEqual($b));

        $this->assertSame($lt, $numA->isSmaller($numB));
        $this->assertSame($lt, $numA->isSmaller($b));

        $this->assertSame($gt, $numA->isGreater($numB));
        $this->assertSame($gt, $numA->isGreater($b));

        $this->assertSame($lte, $numA->isSmallerOrEqual($numB));
        $this->assertSame($lte, $numA->isSmallerOrEqual($b));

        $this->assertSame($gte, $numA->isGreaterOrEqual($numB));
        $this->assertSame($gte, $numA->isGreaterOrEqual($b));
    }

    public static function comparisonProvider(): array
    {
        return [
            // a, b, ==, <, >, <=, >=
            'equal integers' => ['5', '5', true, false, false, true, true],
            'equal decimals' => ['1.23', '1.230', true, false, false, true, true],
            'a < b integers' => ['3', '5', false, true, false, true, false],
            'a < b decimals' => ['1.23', '1.24', false, true, false, true, false],
            'a > b integers' => ['5', '3', false, false, true, false, true],
            'a > b decimals' => ['1.24', '1.23', false, false, true, false, true],
            'negative equal' => ['-5', '-5', true, false, false, true, true],
            'negative a < b' => ['-5', '-3', false, true, false, true, false],
            'negative a > b' => ['-3', '-5', false, false, true, false, true],
            'mixed signs a < b' => ['-3', '5', false, true, false, true, false],
            'mixed signs a > b' => ['5', '-3', false, false, true, false, true],
            'scientific equal' => ['1.5E+2', '150', true, false, false, true, true],
            'scientific a < b' => ['1.5E+2', '200', false, true, false, true, false],
            'scientific a > b' => ['1.5E+2', '100', false, false, true, false, true],
        ];
    }

    /**
     * @dataProvider signProvider
     * @covers \Shtse8\SotiMath\SotiNumber::isNegative
     * @covers \Shtse8\SotiMath\SotiNumber::isPositive
     * @covers \Shtse8\SotiMath\SotiNumber::__construct
     */
    public function testSignMethods(string $val, bool $isNegative, bool $isPositive): void
    {
        $num = new SotiNumber($val);
        $this->assertSame($isNegative, $num->isNegative());
        $this->assertSame($isPositive, $num->isPositive());
    }

    public static function signProvider(): array
    {
        return [
            'positive integer' => ['5', false, true],
            'negative integer' => ['-5', true, false],
            'positive decimal' => ['0.1', false, true],
            'negative decimal' => ['-0.1', true, false],
            'zero' => ['0', false, false],
            'zero decimal' => ['0.000', false, false],
        ];
    }

    // TODO: Add tests for all other public methods (add, sub, mul, div, mod, pow, ln, log, floor, ceil, abs, truncate, round, comparisons, format, human units etc.)
}