<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\NetBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NetBasedPriceTest extends TestCase
{
	use BuildingFakeMoneys;

	public static function LineItemLevelFromGrossMultiplyDataProvider(): array
	{
		return [
			[ 100, 19, 1, 84, 100, 84 ],
			[ 108, 19, 10, 910, 1083, 91 ],
			[ 10808, 19, 10, 90820, 108076, 9082 ],
			[ -108, 19, 10, -910, -1083, -91 ],
			[ -10808, 19, 10, -90820, -108076, -9082 ],
			[ 200, 19, 1, 168, 200, 168 ],
			[ 1, 19, 50, 50, 60, 1 ],
			[ 490, 19, 1, 412, 490, 412 ],
			[ 129, 19, 3, 324, 386, 108 ],
			[ -129, 19, 3, -324, -386, -108 ],
			[ 129, 19, 1.45, 157, 187, 108 ],
			[ -129, 19, 1.45, -157, -187, -108 ],
		];
	}

	#[DataProvider( 'LineItemLevelFromGrossMultiplyDataProvider' )]
	public function testCalculatingTaxAfterMultiplyingByQuantityFromGross(
		int $unitGrossAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitNetAmount
	): void
	{
		$unitPrice  = NetBasedPrice::fromGrossAmount( $this->buildMoney( $unitGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertInstanceOf( NetBasedPrice::class, $totalPrice );
		self::assertSame( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertSame( $unitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertSame( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
	}

	public static function LineItemLevelFromNetMultiplyDataProvider(): array
	{
		return [
			[ 84, 19, 1, 84, 100, 100 ],
			[ 91, 19, 10, 910, 1083, 108 ],
			[ 9082, 19, 10, 90820, 108076, 10808 ],
			[ -91, 19, 10, -910, -1083, -108 ],
			[ -9082, 19, 10, -90820, -108076, -10808 ],
			[ 168, 19, 1, 168, 200, 200 ],
			[ 1, 19, 50, 50, 60, 1 ],
			[ 412, 19, 1, 412, 490, 490 ],
			[ 108, 19, 3, 324, 386, 129 ],
			[ -108, 19, 3, -324, -386, -129 ],
			[ 108, 19, 1.45, 157, 187, 129 ],
			[ -108, 19, 1.45, -157, -187, -129 ],
		];
	}

	#[DataProvider( 'LineItemLevelFromNetMultiplyDataProvider' )]
	public function testCalculatingTaxAfterMultiplyingByQuantityFromNet(
		int $unitNetAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitGrossAmount
	): void
	{
		$unitPrice  = NetBasedPrice::fromNetAmount( $this->buildMoney( $unitNetAmount, 'EUR' ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertSame( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertSame( $unitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
	}

	public static function LineItemLevelFromGrossDivideDataProvider(): array
	{
		return [
			[ 100, 19, 1, 84, 100, 84 ],
			[ 1083, 19, 10, 91, 108, 910 ],
			[ 108076, 19, 10, 9082, 10808, 90820 ],
			[ -1083, 19, 10, -91, -108, -910 ],
			[ -108076, 19, 10, -9082, -10808, -90820 ],
			[ 200, 19, 1, 168, 200, 168 ],
			[ 60, 19, 50, 1, 1, 50 ],
			[ 490, 19, 1, 412, 490, 412 ],
			[ 386, 19, 3, 108, 129, 324 ],
			[ -386, 19, 3, -108, -129, -324 ],
			[ 187, 19, 1.45, 108, 129, 157 ],
			[ -187, 19, 1.45, -108, -129, -157 ],
		];
	}

	#[DataProvider( 'LineItemLevelFromGrossDivideDataProvider' )]
	public function testCalculatingTaxAfterDividingByQuantityFromGross(
		int $totalGrossAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalNetAmount
	): void
	{
		$totalPrice = NetBasedPrice::fromGrossAmount( $this->buildMoney( $totalGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
		$unitPrice  = $totalPrice->divide( $quantity );

		self::assertInstanceOf( NetBasedPrice::class, $unitPrice );
		self::assertSame( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertSame( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertSame( $totalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
	}

	public static function LineItemLevelFromNetDivideDataProvider(): array
	{
		return [
			[ 84, 19, 1, 84, 100, 100 ],
			[ 910, 19, 10, 91, 108, 1083 ],
			[ 90820, 19, 10, 9082, 10808, 108076 ],
			[ -910, 19, 10, -91, -108, -1083 ],
			[ -90820, 19, 10, -9082, -10808, -108076 ],
			[ 168, 19, 1, 168, 200, 200 ],
			[ 50, 19, 50, 1, 1, 60 ],
			[ 412, 19, 1, 412, 490, 490 ],
			[ 324, 19, 3, 108, 129, 386 ],
			[ -324, 19, 3, -108, -129, -386 ],
			[ 157, 19, 1.45, 108, 129, 187 ],
			[ -157, 19, 1.45, -108, -129, -187 ],
		];
	}

	#[DataProvider( 'LineItemLevelFromNetDivideDataProvider' )]
	public function testCalculatingTaxAfterDividingByQuantityFromNet(
		int $totalNetAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalGrossAmount
	): void
	{
		$totalPrice = NetBasedPrice::fromNetAmount( $this->buildMoney( $totalNetAmount, 'EUR' ), new VatRate( $vatRate ) );
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
	public function testAddingPrice( int $originalNetAmount, int $additionalNetAmount, int $expectedNetAmount ): void
	{
		$originalPrice   = NetBasedPrice::fromNetAmount( $this->buildMoney( $originalNetAmount, 'EUR' ), new VatRate( 19 ) );
		$additionalPrice = FakePriceImplementation::fromNetAmount( $this->buildMoney( $additionalNetAmount, 'EUR' ), new VatRate( 19 ) );
		$expectedPrice   = NetBasedPrice::fromNetAmount( $this->buildMoney( $expectedNetAmount, 'EUR' ), new VatRate( 19 ) );

		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertInstanceOf( NetBasedPrice::class, $summedPrice );
		self::assertSame( $expectedPrice->getNetAmount()->getAmount(), $summedPrice->getNetAmount()->getAmount() );
		self::assertSame( $expectedPrice->getGrossAmount()->getAmount(), $summedPrice->getGrossAmount()->getAmount() );
		self::assertTrue( $expectedPrice->getVatRate()->equals( $summedPrice->getVatRate() ) );
	}

	public function testAddingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->add( NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
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
	public function testSubtractingPrice( int $originalNetAmount, int $subtractedNetAmount, int $expectedNetAmount ): void
	{
		$originalPrice   = NetBasedPrice::fromNetAmount( $this->buildMoney( $originalNetAmount, 'EUR' ), new VatRate( 19 ) );
		$subtractedPrice = FakePriceImplementation::fromNetAmount( $this->buildMoney( $subtractedNetAmount, 'EUR' ), new VatRate( 19 ) );
		$expectedPrice   = NetBasedPrice::fromNetAmount( $this->buildMoney( $expectedNetAmount, 'EUR' ), new VatRate( 19 ) );

		$priceResult = $originalPrice->subtract( $subtractedPrice );

		self::assertInstanceOf( NetBasedPrice::class, $priceResult );
		self::assertSame( $expectedPrice->getNetAmount()->getAmount(), $priceResult->getNetAmount()->getAmount() );
		self::assertSame( $expectedPrice->getGrossAmount()->getAmount(), $priceResult->getGrossAmount()->getAmount() );
		self::assertTrue( $expectedPrice->getVatRate()->equals( $priceResult->getVatRate() ) );
	}

	public function testSubtractingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->subtract( NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}
}
