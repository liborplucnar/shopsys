<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Money;

use DomainException;
use Iterator;
use Litipk\BigNumbers\DecimalConstants;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Exception\MoneyException;
use Shopsys\FrameworkBundle\Component\Money\Money;

final class MoneyTest extends TestCase
{
    /**
     * @param int|string $value
     * @param string $expectedAmount
     */
    #[DataProvider('createProvider')]
    public function testCreate($value, string $expectedAmount): void
    {
        $money = Money::create($value);

        $this->assertSame($expectedAmount, $money->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function createProvider(): Iterator
    {
        yield ['0', '0'];

        yield ['-0', '0'];

        yield ['-0.0', '0.0'];

        yield ['1', '1'];

        yield ['-1', '-1'];

        yield ['+1', '1'];

        yield ['1.1', '1.1'];

        yield ['0.0', '0.0'];

        yield ['0.00', '0.00'];

        yield ['000', '0'];

        yield ['000.0', '0.0'];

        yield ['010', '10'];

        yield ['1e1', '10'];

        yield [0, '0'];

        yield [-0, '0'];

        yield [1, '1'];

        yield [-1, '-1'];

        yield [10, '10'];

        yield [PHP_INT_MAX, (string)PHP_INT_MAX];
    }

    /**
     * @param int|string $value
     */
    #[DataProvider('invalidValuesCreateProvider')]
    public function testInvalidValuesInCreate($value): void
    {
        $this->expectException(MoneyException::class);

        Money::create($value);
    }

    /**
     * @return \Iterator
     */
    public static function invalidValuesCreateProvider(): Iterator
    {
        yield [''];

        yield ['xxx'];

        yield ['1,00'];

        yield ['0.'];

        yield ['.0'];

        yield ['1.0.0'];

        yield [' 0'];

        yield ['0 '];

        yield ['1+1'];

        yield ['1 000'];

        yield ['0x0'];

        yield ['+-1'];

        yield ['++1'];

        yield ['--1'];

        yield [0.0];

        yield [1.0];

        yield [-1.0];

        yield [DecimalConstants::zero()];

        yield [Money::zero()];
    }

    /**
     * @param float $float
     * @param int $scale
     */
    #[DataProvider('invalidValuesCreateFromFloatProvider')]
    public function testInvalidValuesInCreateFromFloat(float $float, int $scale): void
    {
        $this->expectException(MoneyException::class);

        Money::createFromFloat($float, $scale);
    }

    /**
     * @return \Iterator
     */
    public static function invalidValuesCreateFromFloatProvider(): Iterator
    {
        yield [NAN, 0];

        yield [INF, 0];

        yield [-INF, 0];

        yield [NAN, 1];

        yield [INF, 1];

        yield [-INF, 1];

        yield [0.0, -1];
    }

    /**
     * @param float $float
     * @param int $scale
     * @param string $expectedAmount
     */
    #[DataProvider('createFromFloatProvider')]
    public function testCreateFromFloat(float $float, int $scale, string $expectedAmount): void
    {
        $money = Money::createFromFloat($float, $scale);

        $this->assertSame($expectedAmount, $money->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function createFromFloatProvider(): Iterator
    {
        yield [0.0, 0, '0'];

        yield [-0.0, 0, '0'];

        yield [0.0, 1, '0.0'];

        yield [0.0, 10, '0.0000000000'];

        yield [1.0, 0, '1'];

        yield [-1.0, 0, '-1'];

        yield [10.0, 0, '10'];

        yield [0.05, 1, '0.1'];

        yield [0.5, 0, '1'];

        yield [0.0001, 3, '0.000'];

        yield [1.0001, 3, '1.000'];
    }

    public function testZero(): void
    {
        $zeroMoney = Money::zero();

        $this->assertSame('0', $zeroMoney->getAmount());
    }

    public function testAddIsImmutable(): void
    {
        $money = Money::create(1);

        $money->add(Money::create(1));

        $this->assertSame('1', $money->getAmount());
    }

    /**
     * @param string $a
     * @param string $b
     * @param string $expectedAmount
     */
    #[DataProvider('addProvider')]
    public function testAdd(string $a, string $b, string $expectedAmount): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->add($moneyB);

        $this->assertSame($expectedAmount, $result->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function addProvider(): Iterator
    {
        yield ['1', '1', '2'];

        yield ['12.15', '34.965', '47.115'];

        yield ['10', '-2', '8'];

        yield ['1', '0.01', '1.01'];

        yield ['0.5', '0.5', '1.0'];

        yield ['1.525', '0.475', '2.000'];

        yield ['1.00', '1.000', '2.000'];

        yield ['-1', '1', '0'];

        yield ['1', '-1', '0'];

        yield ['-0', '0', '0'];

        yield ['-0.0', '0', '0.0'];
    }

    public function testSubtractIsImmutable(): void
    {
        $money = Money::create(1);

        $money->subtract(Money::create(1));

        $this->assertSame('1', $money->getAmount());
    }

    /**
     * @param string $a
     * @param string $b
     * @param string $expectedAmount
     */
    #[DataProvider('subtractProvider')]
    public function testSubtract(string $a, string $b, string $expectedAmount): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->subtract($moneyB);

        $this->assertSame($expectedAmount, $result->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function subtractProvider(): Iterator
    {
        yield ['2', '1', '1'];

        yield ['12.15', '34.965', '-22.815'];

        yield ['10', '-2', '12'];

        yield ['1', '0.01', '0.99'];

        yield ['0.5', '0.5', '0.0'];

        yield ['1.525', '0.475', '1.050'];

        yield ['1.00', '1.000', '0.000'];

        yield ['1.000', '1.00', '0.000'];

        yield ['-1', '-1', '0'];

        yield ['-0', '0', '0'];

        yield ['-0.0', '0', '0.0'];
    }

    public function testMultiplyIsImmutable(): void
    {
        $money = Money::create(1);

        $money->multiply('2');
        $money->multiply(2);

        $this->assertSame('1', $money->getAmount());
    }

    /**
     * @param string $a
     * @param int|string $b
     * @param string $expectedAmount
     */
    #[DataProvider('multiplyProvider')]
    public function testMultiply(string $a, $b, string $expectedAmount): void
    {
        $moneyA = Money::create($a);

        $result = $moneyA->multiply($b);

        $this->assertSame($expectedAmount, $result->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function multiplyProvider(): Iterator
    {
        yield ['2', '1', '2'];

        yield ['12.15', '34.965', '424.82475'];

        yield ['10', '-2', '-20'];

        yield ['1', '0.01', '0.01'];

        yield ['0.5', '0.5', '0.25'];

        yield ['1.525', '0.475', '0.724375'];

        yield ['1.00', '1.000', '1.00000'];

        yield ['-0', '1', '0'];

        yield ['0', '-1', '0'];

        yield ['-0.0', '1', '0.0'];

        yield ['0', '-1.0', '0.0'];

        yield ['-1', '0.5', '-0.5'];

        yield ['-2', '-1', '2'];

        yield ['1', 2, '2'];

        yield ['0.5', 2, '1.0'];
    }

    /**
     * @param int|string $multiplier
     */
    #[DataProvider('invalidMultipliersProvider')]
    public function testInvalidMultipliers($multiplier): void
    {
        $money = Money::create(1);

        $this->expectException(MoneyException::class);

        $money->multiply($multiplier);
    }

    /**
     * @return \Iterator
     */
    public static function invalidMultipliersProvider(): Iterator
    {
        yield from static::invalidValuesCreateProvider();
    }

    public function testDivideIsImmutable(): void
    {
        $money = Money::create(1);

        $money->divide('2', 1);
        $money->divide(2, 1);

        $this->assertSame('1', $money->getAmount());
    }

    /**
     * @param string $a
     * @param int|string $b
     * @param int $scale
     * @param string $expectedAmount
     */
    #[DataProvider('divideProvider')]
    public function testDivide(string $a, $b, int $scale, string $expectedAmount): void
    {
        $moneyA = Money::create($a);

        $result = $moneyA->divide($b, $scale);

        $this->assertSame($expectedAmount, $result->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function divideProvider(): Iterator
    {
        yield ['1', '2', 0, '1'];

        yield ['1', '2', 1, '0.5'];

        yield ['1', '2', 2, '0.50'];

        yield ['1', '3', 3, '0.333'];

        yield ['2', '3', 3, '0.667'];

        yield ['1000', '3', 3, '333.333'];

        yield ['3.33', '3', 2, '1.11'];

        yield ['0.1', '0.1', 0, '1'];

        yield ['-0', '1', 0, '0'];

        yield ['-0.0', '1', 1, '0.0'];

        yield ['-1', '3', 0, '0'];

        yield ['-2', '-1', 0, '2'];

        yield ['-1', '0.5', 0, '-2'];

        yield ['10', '-4', 2, '-2.50'];

        yield ['1', 2, 1, '0.5'];

        yield ['0.5', 2, 2, '0.25'];

        yield ['0.5', 2, 1, '0.3'];
    }

    /**
     * @param int|string $divisor
     */
    #[DataProvider('invalidDivisorProvider')]
    public function testInvalidDivisors($divisor): void
    {
        $money = Money::create(1);

        $this->expectException(MoneyException::class);

        $money->divide($divisor, 0);
    }

    /**
     * @return \Iterator
     */
    public static function invalidDivisorProvider(): Iterator
    {
        yield from self::invalidValuesCreateProvider();
    }

    /**
     * @param int|string $divisor
     */
    #[DataProvider('cannotDivideByZeroProvider')]
    public function testCannotDivideByZero($divisor): void
    {
        $money = Money::create(1);

        $this->expectException(DomainException::class);

        $money->divide($divisor, 0);
    }

    /**
     * @return \Iterator
     */
    public static function cannotDivideByZeroProvider(): Iterator
    {
        yield ['0'];

        yield ['-0'];

        yield ['0.0'];

        yield [0];
    }

    public function testRoundIsImmutable(): void
    {
        $money = Money::create('1.5');

        $money->round(0);

        $this->assertSame('1.5', $money->getAmount());
    }

    /**
     * @param string $amount
     * @param int $scale
     * @param string $expectedAmount
     */
    #[DataProvider('roundProvider')]
    public function testRound(string $amount, int $scale, string $expectedAmount): void
    {
        $money = Money::create($amount);

        $result = $money->round($scale);

        $this->assertSame($expectedAmount, $result->getAmount());
    }

    /**
     * @return \Iterator
     */
    public static function roundProvider(): Iterator
    {
        yield ['0.4', 0, '0'];

        yield ['-0.4', 0, '0'];

        yield ['0.5', 0, '1'];

        yield ['-0.5', 0, '-1'];

        yield ['1.5', 0, '2'];

        yield ['-1.5', 0, '-2'];

        yield ['-0.04', 1, '0.0'];

        yield ['0.000000', 2, '0.00'];

        yield ['1.000000', 2, '1.00'];

        yield ['0.00', 2, '0.00'];

        yield ['1.00', 2, '1.00'];

        yield ['0.0', 2, '0.0'];

        yield ['1.0', 2, '1.0'];

        yield ['0', 2, '0'];

        yield ['1', 2, '1'];

        yield ['1.999', 2, '2.00'];

        yield ['1.999', 3, '1.999'];
    }

    /**
     * @param string $a
     * @param string $b
     * @param int $expectedResult
     */
    #[DataProvider('compareProvider')]
    public function testCompare(string $a, string $b, int $expectedResult): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->compare($moneyB);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return \Iterator
     */
    public static function compareProvider(): Iterator
    {
        yield ['0', '0', 0];

        yield ['1', '1', 0];

        yield ['0.0', '0.0', 0];

        yield ['0', '0.0', 0];

        yield ['0.0', '0', 0];

        yield ['0', '0.000', 0];

        yield ['0.000', '0', 0];

        yield ['0.0001', '0.0001000', 0];

        yield ['0', '-0', 0];

        yield ['1', '0', 1];

        yield ['0', '1', -1];

        yield ['1', '-1', 1];

        yield ['-1', '1', -1];

        yield ['0.000001', '0', 1];

        yield ['0', '0.000001', -1];
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $expectedResult
     */
    #[DataProvider('equalsProvider')]
    public function testEquals(string $a, string $b, bool $expectedResult): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->equals($moneyB);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return \Iterator
     */
    public static function equalsProvider(): Iterator
    {
        yield ['0', '0', true];

        yield ['1', '1', true];

        yield ['0.0', '0.0', true];

        yield ['0', '0.0', true];

        yield ['0.0', '0', true];

        yield ['0', '0.000', true];

        yield ['0.000', '0', true];

        yield ['0.0001', '0.0001000', true];

        yield ['0', '-0', true];

        yield ['1', '0', false];

        yield ['0', '1', false];

        yield ['1', '-1', false];

        yield ['-1', '1', false];

        yield ['0.000001', '0', false];

        yield ['0', '0.000001', false];
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $expectedResult
     */
    #[DataProvider('greaterThanProvider')]
    public function testGreaterThan(string $a, string $b, bool $expectedResult): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->isGreaterThan($moneyB);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return \Iterator
     */
    public static function greaterThanProvider(): Iterator
    {
        yield ['0', '0', false];

        yield ['1', '1', false];

        yield ['0.0', '0.0', false];

        yield ['0', '0.0', false];

        yield ['0.0', '0', false];

        yield ['0', '0.000', false];

        yield ['0.000', '0', false];

        yield ['0.0001', '0.0001000', false];

        yield ['0', '-0', false];

        yield ['1', '0', true];

        yield ['0', '1', false];

        yield ['1', '-1', true];

        yield ['-1', '1', false];

        yield ['0.000001', '0', true];

        yield ['0', '0.000001', false];
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $expectedResult
     */
    #[DataProvider('greaterThanOrEqualToProvider')]
    public function testGreaterThanOrEqualTo(string $a, string $b, bool $expectedResult): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->isGreaterThanOrEqualTo($moneyB);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return \Iterator
     */
    public static function greaterThanOrEqualToProvider(): Iterator
    {
        yield ['0', '0', true];

        yield ['1', '1', true];

        yield ['0.0', '0.0', true];

        yield ['0', '0.0', true];

        yield ['0.0', '0', true];

        yield ['0', '0.000', true];

        yield ['0.000', '0', true];

        yield ['0.0001', '0.0001000', true];

        yield ['0', '-0', true];

        yield ['1', '0', true];

        yield ['0', '1', false];

        yield ['1', '-1', true];

        yield ['-1', '1', false];

        yield ['0.000001', '0', true];

        yield ['0', '0.000001', false];
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $expectedResult
     */
    #[DataProvider('lessThanProvider')]
    public function testLessThan(string $a, string $b, bool $expectedResult): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->isLessThan($moneyB);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return \Iterator
     */
    public static function lessThanProvider(): Iterator
    {
        yield ['0', '0', false];

        yield ['1', '1', false];

        yield ['0.0', '0.0', false];

        yield ['0', '0.0', false];

        yield ['0.0', '0', false];

        yield ['0', '0.000', false];

        yield ['0.000', '0', false];

        yield ['0.0001', '0.0001000', false];

        yield ['0', '-0', false];

        yield ['1', '0', false];

        yield ['0', '1', true];

        yield ['1', '-1', false];

        yield ['-1', '1', true];

        yield ['0.000001', '0', false];

        yield ['0', '0.000001', true];
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $expectedResult
     */
    #[DataProvider('lessThanOrEqualToProvider')]
    public function testLessThanOrEqualTo(string $a, string $b, bool $expectedResult): void
    {
        $moneyA = Money::create($a);
        $moneyB = Money::create($b);

        $result = $moneyA->isLessThanOrEqualTo($moneyB);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return \Iterator
     */
    public static function lessThanOrEqualToProvider(): Iterator
    {
        yield ['0', '0', true];

        yield ['1', '1', true];

        yield ['0.0', '0.0', true];

        yield ['0', '0.0', true];

        yield ['0.0', '0', true];

        yield ['0', '0.000', true];

        yield ['0.000', '0', true];

        yield ['0.0001', '0.0001000', true];

        yield ['0', '-0', true];

        yield ['1', '0', false];

        yield ['0', '1', true];

        yield ['1', '-1', false];

        yield ['-1', '1', true];

        yield ['0.000001', '0', false];

        yield ['0', '0.000001', true];
    }

    /**
     * @param string $a
     * @param bool $expectedResult
     */
    #[DataProvider('isPositiveProvider')]
    public function testIsPositive(string $a, bool $expectedResult): void
    {
        $moneyA = Money::create($a);

        $this->assertSame($expectedResult, $moneyA->isPositive());
    }

    /**
     * @return \Iterator
     */
    public static function isPositiveProvider(): Iterator
    {
        yield ['0', false];

        yield ['-0', false];

        yield ['+0', false];

        yield ['0.0', false];

        yield ['-0.0', false];

        yield ['1', true];

        yield ['0.55', true];

        yield ['-1', false];

        yield ['-0.55', false];
    }

    /**
     * @param string $a
     * @param bool $expectedResult
     */
    #[DataProvider('isNegativeProvider')]
    public function testIsNegative(string $a, bool $expectedResult): void
    {
        $moneyA = Money::create($a);

        $this->assertSame($expectedResult, $moneyA->isNegative());
    }

    /**
     * @return \Iterator
     */
    public static function isNegativeProvider(): Iterator
    {
        yield ['0', false];

        yield ['-0', false];

        yield ['+0', false];

        yield ['0.0', false];

        yield ['-0.0', false];

        yield ['1', false];

        yield ['0.55', false];

        yield ['-1', true];

        yield ['-0.55', true];
    }

    /**
     * @param string $a
     * @param bool $expectedResult
     */
    #[DataProvider('isZeroProvider')]
    public function testIsZero(string $a, bool $expectedResult): void
    {
        $moneyA = Money::create($a);

        $this->assertSame($expectedResult, $moneyA->isZero());
    }

    /**
     * @return \Iterator
     */
    public static function isZeroProvider(): Iterator
    {
        yield ['0', true];

        yield ['-0', true];

        yield ['+0', true];

        yield ['0.0', true];

        yield ['-0.0', true];

        yield ['1', false];

        yield ['0.55', false];

        yield ['-1', false];

        yield ['-0.55', false];
    }
}
