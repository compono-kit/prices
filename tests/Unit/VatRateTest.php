<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\Exceptions\InvalidVatRateException;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VatRateTest extends TestCase
{
	public static function FloatValueProvider(): array
	{
		return [
			[ 19, 19, 1900, '19' ],
			[ 7.5, 7.5, 750, '7.5' ],
			[ 0, 0, 0, '0' ],
			[ 100, 100, 10000, '100' ],
			[ 22.3589, 22.36, 2236, '22.36' ],
			[ 0.29, 0.29, 29, '0.29' ],
		];
	}

	#[DataProvider( 'FloatValueProvider' )]
	public function testInstantiatingVatRate( float $vatRateValue, float $expectedFloat, int $expectedInt, string $expectedString ): void
	{
		$vatRate = new VatRate( $vatRateValue );

		self::assertEqualsWithDelta( $expectedFloat, $vatRate->toFloat(), 0.000001 );
		self::assertSame( $expectedInt, $vatRate->toInt() );
		self::assertSame( $expectedString, (string)$vatRate );
	}

	public function testIfNegativeVatRateThrowsException(): void
	{
		$this->expectException( InvalidVatRateException::class );
		$this->expectExceptionMessage( 'VAT rate must not be negative' );

		new VatRate( -1 );
	}

	public static function IntegerValueProvider(): array
	{
		return [
			[ 1900, 19, '19' ],
			[ 750, 7.5, '7.5' ],
			[ 0, 0, '0' ],
			[ 10000, 100, '100' ],
			[ 2236, 22.36, '22.36' ],
		];
	}

	#[DataProvider( 'IntegerValueProvider' )]
	public function testInstantiatingFromInt( int $vatRateValue, float $expectedFloat, string $expectedString ): void
	{
		$vatRate = VatRate::fromInt( $vatRateValue );

		self::assertSame( $vatRateValue, $vatRate->toInt() );
		self::assertEqualsWithDelta( $expectedFloat, $vatRate->toFloat(), 0.000001 );
		self::assertSame( $expectedString, (string)$vatRate );
	}

	public function testVatRatesWithSameRoundedValueAreEqual(): void
	{
		$vatRate = new VatRate( 22.3589 );

		self::assertTrue( $vatRate->equals( VatRate::fromInt( 2236 ) ) );
		self::assertSame( 0, $vatRate->compare( VatRate::fromInt( 2236 ) ) );
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

		self::assertSame( 0, $vatRate->compare( $equalVatRate ) );
		self::assertSame( 1, $vatRate->compare( $lowerVatRate ) );
		self::assertSame( -1, $vatRate->compare( $higherVatRate ) );
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
