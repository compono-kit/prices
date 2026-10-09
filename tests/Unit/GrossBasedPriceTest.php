<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\AnotherFakePriceImplementation;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GrossBasedPriceTest extends TestCase
{
	use BuildingFakeMoneys;

	public static function UnitPriceLevelFromGrossMultiplyDataProvider(): array
	{
		return [
			[ 100, 19, 1, 84, 100, 84 ],
			[ 108, 19, 10, 908, 1080, 91 ],
			[ 10808, 19, 10, 90824, 108080, 9082 ],
			[ -108, 19, 10, -908, -1080, -91 ],
			[ -10808, 19, 10, -90824, -108080, -9082 ],
			[ 200, 19, 1, 168, 200, 168 ],
			[ 1, 19, 50, 42, 50, 1 ],
			[ 490, 19, 1, 412, 490, 412 ],
			[ 129, 19, 3, 325, 387, 108 ],
			[ -129, 19, 3, -325, -387, -108 ],
			[ 129, 19, 1.45, 157, 187, 108 ],
			[ -129, 19, 1.45, -157, -187, -108 ],
		];
	}

	#[DataProvider( 'UnitPriceLevelFromGrossMultiplyDataProvider' )]
	public function testCalculatingTaxBeforeMultiplyingByQuantityFromGross(
		int $unitGrossAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitNetAmount
	): void
	{
		$unitPrice  = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $unitGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertInstanceOf( GrossBasedPrice::class, $totalPrice );
		self::assertSame( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertSame( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertSame( $unitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
	}

	public static function UnitPriceLevelFromNetMultiplyDataProvider(): array
	{
		return [
			[ 84, 19, 1, 84, 100, 100 ],
			[ 91, 19, 10, 908, 1080, 108 ],
			[ 9082, 19, 10, 90824, 108080, 10808 ],
			[ -91, 19, 10, -908, -1080, -108 ],
			[ -9082, 19, 10, -90824, -108080, -10808 ],
			[ 168, 19, 1, 168, 200, 200 ],
			[ 412, 19, 1, 412, 490, 490 ],
			[ 108, 19, 3, 325, 387, 129 ],
			[ -108, 19, 3, -325, -387, -129 ],
			[ 108, 19, 1.45, 157, 187, 129 ],
			[ -108, 19, 1.45, -157, -187, -129 ],
		];
	}

	#[DataProvider( 'UnitPriceLevelFromNetMultiplyDataProvider' )]
	public function testCalculatingTaxBeforeMultiplyingByQuantityFromNet(
		int $unitNetAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitGrossAmount
	): void
	{
		$unitPrice  = GrossBasedPrice::fromNetAmount( $this->buildMoney( $unitNetAmount, 'EUR' ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertSame( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertSame( $unitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
	}

	public static function UnitPriceLevelFromGrossDivideDataProvider(): array
	{
		return [
			[ 100, 19, 1, 84, 100, 84 ],
			[ 1080, 19, 10, 91, 108, 908 ],
			[ 108080, 19, 10, 9082, 10808, 90824 ],
			[ -1080, 19, 10, -91, -108, -908 ],
			[ -108080, 19, 10, -9082, -10808, -90824 ],
			[ 200, 19, 1, 168, 200, 168 ],
			[ 50, 19, 50, 1, 1, 42 ],
			[ 490, 19, 1, 412, 490, 412 ],
			[ 387, 19, 3, 108, 129, 325 ],
			[ -387, 19, 3, -108, -129, -325 ],
			[ 187, 19, 1.45, 108, 129, 157 ],
			[ -187, 19, 1.45, -108, -129, -157 ],
		];
	}

	#[DataProvider( 'UnitPriceLevelFromGrossDivideDataProvider' )]
	public function testCalculatingTaxBeforeDividingByQuantityFromGross(
		int $totalGrossAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalNetAmount
	): void
	{
		$totalPrice = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $totalGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
		$unitPrice  = $totalPrice->divide( $quantity );

		self::assertInstanceOf( GrossBasedPrice::class, $unitPrice );
		self::assertSame( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertSame( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $totalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
	}

	public static function UnitPriceLevelFromNetDivideDataProvider(): array
	{
		return [
			[ 84, 19, 1, 84, 100, 100 ],
			[ 908, 19, 10, 91, 108, 1081 ],
			[ 90824, 19, 10, 9082, 10808, 108081 ],
			[ -908, 19, 10, -91, -108, -1081 ],
			[ -90824, 19, 10, -9082, -10808, -108081 ],
			[ 168, 19, 1, 168, 200, 200 ],
			[ 412, 19, 1, 412, 490, 490 ],
			[ 325, 19, 3, 108, 129, 387 ],
			[ -325, 19, 3, -108, -129, -387 ],
			[ 157, 19, 1.45, 108, 129, 187 ],
			[ -157, 19, 1.45, -108, -129, -187 ],
		];
	}

	#[DataProvider( 'UnitPriceLevelFromNetDivideDataProvider' )]
	public function testCalculatingTaxBeforeDividingByQuantityFromNet(
		int $totalNetAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalGrossAmount
	): void
	{
		$totalPrice = GrossBasedPrice::fromNetAmount( $this->buildMoney( $totalNetAmount, 'EUR' ), new VatRate( $vatRate ) );
		$unitPrice  = $totalPrice->divide( $quantity );

		self::assertSame( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertSame( $totalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
	}

	public static function AddingPriceDataProvider(): array
	{
		return [
			[ 3990, 1000, 4990 ],
			[ -3990, 1000, -2990 ],
			[ -3990, 5090, 1100 ],
			[ -3990, 3990, 0 ],
			[ 3990, 0, 3990 ],
		];
	}

	#[DataProvider( 'AddingPriceDataProvider' )]
	public function testAddingPrice( int $originalGrossAmount, int $additionalGrossAmount, int $expectedGrossAmount ): void
	{
		$originalPrice   = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $originalGrossAmount, 'EUR' ), new VatRate( 19 ) );
		$additionalPrice = AnotherFakePriceImplementation::fromGrossAmount( $this->buildMoney( $additionalGrossAmount, 'EUR' ), new VatRate( 19 ) );
		$expectedPrice   = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $expectedGrossAmount, 'EUR' ), new VatRate( 19 ) );

		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertInstanceOf( GrossBasedPrice::class, $summedPrice );
		self::assertSame( $expectedPrice->getGrossAmount()->getAmount(), $summedPrice->getGrossAmount()->getAmount() );
		self::assertSame( $expectedPrice->getNetAmount()->getAmount(), $summedPrice->getNetAmount()->getAmount() );
		self::assertTrue( $expectedPrice->getVatRate()->equals( $summedPrice->getVatRate() ) );
	}

	public function testAddingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->add( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}

	public static function SubtractingPriceDataProvider(): array
	{
		return [
			[ 3990, 1000, 2990 ],
			[ -3990, 1000, -4990 ],
			[ 3990, 5000, -1010 ],
			[ 3990, 3990, 0 ],
			[ 3990, 0, 3990 ],
			[ -1000, -1000, 0 ],
		];
	}

	#[DataProvider( 'SubtractingPriceDataProvider' )]
	public function testSubtractingPrice( int $originalGrossAmount, int $subtractedGrossAmount, int $expectedGrossAmount ): void
	{
		$originalPrice   = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $originalGrossAmount, 'EUR' ), new VatRate( 19 ) );
		$subtractedPrice = AnotherFakePriceImplementation::fromGrossAmount( $this->buildMoney( $subtractedGrossAmount, 'EUR' ), new VatRate( 19 ) );
		$expectedPrice   = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $expectedGrossAmount, 'EUR' ), new VatRate( 19 ) );

		$priceResult = $originalPrice->subtract( $subtractedPrice );

		self::assertInstanceOf( GrossBasedPrice::class, $priceResult );
		self::assertSame( $expectedPrice->getGrossAmount()->getAmount(), $priceResult->getGrossAmount()->getAmount() );
		self::assertSame( $expectedPrice->getNetAmount()->getAmount(), $priceResult->getNetAmount()->getAmount() );
		self::assertTrue( $expectedPrice->getVatRate()->equals( $priceResult->getVatRate() ) );
	}

	public function testSubtractingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->subtract( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}
}
