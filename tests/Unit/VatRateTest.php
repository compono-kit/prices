<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\Exceptions\InvalidVatRateException;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\TestCase;

class VatRateTest extends TestCase
{
	public function FloatValueProvider(): array
	{
		return [
			[ 19, 1900, '19' ],
			[ 7.5, 750, '7.5' ],
			[ 0, 0, '0' ],
			[ 100, 10000, '100' ],
		];
	}

	/**
	 * @dataProvider FloatValueProvider
	 */
	public function testInstantiatingVatRate( float $vatRateValue, int $expectedInt, string $expectedString ): void
	{
		$vatRate = new VatRate( $vatRateValue );

		self::assertEquals( $vatRateValue, $vatRate->toFloat() );
		self::assertEquals( $expectedInt, $vatRate->toInt() );
		self::assertEquals( $expectedString, (string)$vatRate );
	}

	public function testIfNegativeVatRateThrowsException(): void
	{
		$this->expectException( InvalidVatRateException::class );
		$this->expectExceptionMessage( 'VAT rate must not be negative' );

		new VatRate( -1 );
	}

	public function IntegerValueProvider(): array
	{
		return [
			[ 1900, 19, '19' ],
			[ 750, 7.5, '7.5' ],
			[ 0, 0, '0' ],
			[ 10000, 100, '100' ],
		];
	}

	/**
	 * @dataProvider IntegerValueProvider
	 */
	public function testInstantiatingFromInt( int $vatRateValue, float $expectedFloat, string $expectedString ): void
	{
		$vatRate = VatRate::fromInt( $vatRateValue );

		self::assertEquals( $vatRateValue, $vatRate->toInt() );
		self::assertEquals( $expectedFloat, $vatRate->toFloat() );
		self::assertEquals( $expectedString, (string)$vatRate );
	}

	public function testEqualsAnotherVatRate(): void
	{
		$vatRate       = new VatRate( 19 );
		$equalVatRate  = new VatRate( 19 );
		$lowerVatRate  = new VatRate( 18.5 );
		$higherVatRate = new VatRate( 19.5 );

		self::assertTrue( $vatRate->equals( $equalVatRate ) );
		self::assertFalse( $vatRate->equals( $lowerVatRate ) );
		self::assertFalse( $vatRate->equals( $higherVatRate ) );
	}

	public function testCompareWithAnotherVatRate(): void
	{
		$vatRate       = new VatRate( 19 );
		$equalVatRate  = new VatRate( 19 );
		$lowerVatRate  = new VatRate( 18.5 );
		$higherVatRate = new VatRate( 19.5 );

		self::assertEquals( 0, $vatRate->compare( $equalVatRate ) );
		self::assertEquals( 1, $vatRate->compare( $lowerVatRate ) );
		self::assertEquals( -1, $vatRate->compare( $higherVatRate ) );
	}

	public function testGreaterThanAnotherVatRate(): void
	{
		$vatRate       = new VatRate( 19 );
		$equalVatRate  = new VatRate( 19 );
		$lowerVatRate  = new VatRate( 18.5 );
		$higherVatRate = new VatRate( 19.5 );

		self::assertFalse( $vatRate->greaterThan( $equalVatRate ) );
		self::assertTrue( $vatRate->greaterThan( $lowerVatRate ) );
		self::assertFalse( $vatRate->greaterThan( $higherVatRate ) );
	}

	public function testGreaterThanOrEqualAnotherVatRate(): void
	{
		$vatRate       = new VatRate( 19 );
		$equalVatRate  = new VatRate( 19 );
		$lowerVatRate  = new VatRate( 18.5 );
		$higherVatRate = new VatRate( 19.5 );

		self::assertTrue( $vatRate->greaterThanOrEqual( $equalVatRate ) );
		self::assertTrue( $vatRate->greaterThanOrEqual( $lowerVatRate ) );
		self::assertFalse( $vatRate->greaterThanOrEqual( $higherVatRate ) );
	}

	public function testLessThanAnotherVatRate(): void
	{
		$vatRate       = new VatRate( 19 );
		$equalVatRate  = new VatRate( 19 );
		$lowerVatRate  = new VatRate( 18.5 );
		$higherVatRate = new VatRate( 19.5 );

		self::assertFalse( $vatRate->lessThan( $equalVatRate ) );
		self::assertFalse( $vatRate->lessThan( $lowerVatRate ) );
		self::assertTrue( $vatRate->lessThan( $higherVatRate ) );
	}

	public function testLessThanOrEqualAnotherVatRate(): void
	{
		$vatRate       = new VatRate( 19 );
		$equalVatRate  = new VatRate( 19 );
		$lowerVatRate  = new VatRate( 18.5 );
		$higherVatRate = new VatRate( 19.5 );

		self::assertTrue( $vatRate->lessThanOrEqual( $equalVatRate ) );
		self::assertFalse( $vatRate->lessThanOrEqual( $lowerVatRate ) );
		self::assertTrue( $vatRate->lessThanOrEqual( $higherVatRate ) );
	}
}
