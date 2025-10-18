<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\TestCase;

class GrossBasedPriceTest extends TestCase
{
	use BuildingFakeMoneys;

	public function UnitPriceLevelFromGrossMultiplyDataProvider(): array
	{
		return [
			[ 'unitGross' => 100, 'vatRate' => 19, 'quantity' => 1, 'totalNet' => 84, 'totalGross' => 100, 'unitNet' => 84 ],
			[ 'unitGross' => 108, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => 908, 'totalGross' => 1080, 'unitNet' => 91 ],
			[ 'unitGross' => 10808, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => 90824, 'totalGross' => 108080, 'unitNet' => 9082 ],
			[ 'unitGross' => -108, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => -908, 'totalGross' => -1080, 'unitNet' => -91 ],
			[ 'unitGross' => -10808, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => -90824, 'totalGross' => -108080, 'unitNet' => -9082 ],
			[ 'unitGross' => 200, 'vatRate' => 19, 'quantity' => 1, 'totalNet' => 168, 'totalGross' => 200, 'unitNet' => 168 ],
			[ 'unitGross' => 1, 'vatRate' => 19, 'quantity' => 50, 'totalNet' => 42, 'totalGross' => 50, 'unitNet' => 1 ],
			[ 'unitGross' => 490, 'vatRate' => 19, 'quantity' => 1, 'totalNet' => 412, 'totalGross' => 490, 'unitNet' => 412 ],
			[ 'unitGross' => 129, 'vatRate' => 19, 'quantity' => 3, 'totalNet' => 325, 'totalGross' => 387, 'unitNet' => 108 ],
			[ 'unitGross' => -129, 'vatRate' => 19, 'quantity' => 3, 'totalNet' => -325, 'totalGross' => -387, 'unitNet' => -108 ],
			[ 'unitGross' => 129, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => 157, 'totalGross' => 187, 'unitNet' => 108 ],
			[ 'unitGross' => -129, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => -157, 'totalGross' => -187, 'unitNet' => -108 ],
		];
	}

	/**
	 * @dataProvider UnitPriceLevelFromGrossMultiplyDataProvider
	 */
	public function testCalculatingTaxAfterMultiplyingByQuantityFromGross(
		int $unitGrossAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitNetAmount
	): void
	{
		$unitPrice  = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $unitGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertEquals( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertEquals( $unitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
	}

	public function UnitPriceLevelFromNetMultiplyDataProvider(): array
	{
		return [
			[ 'unitNet' => 84, 'vatRate' => 19, 'quantity' => 1, 'totalNet' => 84, 'totalGross' => 100, 'unitGross' => 100 ],
			[ 'unitNet' => 91, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => 908, 'totalGross' => 1080, 'unitGross' => 108 ],
			[ 'unitNet' => 9082, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => 90824, 'totalGross' => 108080, 'unitGross' => 10808 ],
			[ 'unitNet' => -91, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => -908, 'totalGross' => -1080, 'unitGross' => -108 ],
			[ 'unitNet' => -9082, 'vatRate' => 19, 'quantity' => 10, 'totalNet' => -90824, 'totalGross' => -108080, 'unitGross' => -10808 ],
			[ 'unitNet' => 168, 'vatRate' => 19, 'quantity' => 1, 'totalNet' => 168, 'totalGross' => 200, 'unitGross' => 200 ],
			[ 'unitNet' => 412, 'vatRate' => 19, 'quantity' => 1, 'totalNet' => 412, 'totalGross' => 490, 'unitGross' => 490 ],
			[ 'unitNet' => 108, 'vatRate' => 19, 'quantity' => 3, 'totalNet' => 325, 'totalGross' => 387, 'unitGross' => 129 ],
			[ 'unitNet' => -108, 'vatRate' => 19, 'quantity' => 3, 'totalNet' => -325, 'totalGross' => -387, 'unitGross' => -129 ],
			[ 'unitNet' => 108, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => 157, 'totalGross' => 187, 'unitGross' => 129 ],
			[ 'unitNet' => -108, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => -157, 'totalGross' => -187, 'unitGross' => -129 ],
		];
	}

	/**
	 * @dataProvider UnitPriceLevelFromNetMultiplyDataProvider
	 */
	public function testCalculatingTaxAfterMultiplyingByQuantityFromNet(
		int $unitNetAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitGrossAmount
	): void
	{
		$unitPrice  = GrossBasedPrice::fromNetAmount( $this->buildMoney( $unitNetAmount, 'EUR' ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertEquals( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $unitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
	}

	public function UnitPriceLevelFromGrossDivideDataProvider(): array
	{
		return [
			[ 'totalGross' => 100, 'vatRate' => 19, 'quantity' => 1, 'unitNet' => 84, 'unitGross' => 100, 'totalNet' => 84 ],
			[ 'totalGross' => 1080, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => 91, 'unitGross' => 108, 'totalNet' => 908 ],
			[ 'totalGross' => 108080, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => 9082, 'unitGross' => 10808, 'totalNet' => 90824 ],
			[ 'totalGross' => -1080, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => -91, 'unitGross' => -108, 'totalNet' => -908 ],
			[ 'totalGross' => -108080, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => -9082, 'unitGross' => -10808, 'totalNet' => -90824 ],
			[ 'totalGross' => 200, 'vatRate' => 19, 'quantity' => 1, 'unitNet' => 168, 'unitGross' => 200, 'totalNet' => 168 ],
			[ 'totalGross' => 50, 'vatRate' => 19, 'quantity' => 50, 'unitNet' => 1, 'unitGross' => 1, 'totalNet' => 42 ],
			[ 'totalGross' => 490, 'vatRate' => 19, 'quantity' => 1, 'unitNet' => 412, 'unitGross' => 490, 'totalNet' => 412 ],
			[ 'totalGross' => 387, 'vatRate' => 19, 'quantity' => 3, 'unitNet' => 108, 'unitGross' => 129, 'totalNet' => 325 ],
			[ 'totalGross' => -387, 'vatRate' => 19, 'quantity' => 3, 'unitNet' => -108, 'unitGross' => -129, 'totalNet' => -325 ],
			[ 'totalGross' => 187, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => 108, 'unitGross' => 129, 'totalNet' => 157 ],
			[ 'totalGross' => -187, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => -108, 'unitGross' => -129, 'totalNet' => -157 ],
		];
	}

	/**
	 * @dataProvider UnitPriceLevelFromGrossDivideDataProvider
	 */
	public function testCalculatingTaxAfterDividingByQuantityFromGross(
		int $totalGrossAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalNetAmount
	): void
	{
		$totalPrice = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $totalGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
		$unitPrice  = $totalPrice->divide( $quantity );

		self::assertEquals( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $totalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
	}

	public function UnitPriceLevelFromNetDivideDataProvider(): array
	{
		return [
			[ 'totalNet' => 84, 'vatRate' => 19, 'quantity' => 1, 'unitNet' => 84, 'unitGross' => 100, 'totalGross' => 100 ],
			[ 'totalNet' => 908, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => 91, 'unitGross' => 108, 'totalGross' => 1081 ],
			[ 'totalNet' => 90824, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => 9082, 'unitGross' => 10808, 'totalGross' => 108081 ],
			[ 'totalNet' => -908, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => -91, 'unitGross' => -108, 'totalGross' => -1081 ],
			[ 'totalNet' => -90824, 'vatRate' => 19, 'quantity' => 10, 'unitNet' => -9082, 'unitGross' => -10808, 'totalGross' => -108081 ],
			[ 'totalNet' => 168, 'vatRate' => 19, 'quantity' => 1, 'unitNet' => 168, 'unitGross' => 200, 'totalGross' => 200 ],
			[ 'totalNet' => 412, 'vatRate' => 19, 'quantity' => 1, 'unitNet' => 412, 'unitGross' => 490, 'totalGross' => 490 ],
			[ 'totalNet' => 325, 'vatRate' => 19, 'quantity' => 3, 'unitNet' => 108, 'unitGross' => 129, 'totalGross' => 387 ],
			[ 'totalNet' => -325, 'vatRate' => 19, 'quantity' => 3, 'unitNet' => -108, 'unitGross' => -129, 'totalGross' => -387 ],
			[ 'totalNet' => 157, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => 108, 'unitGross' => 129, 'totalGross' => 187 ],
			[ 'totalNet' => -157, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => -108, 'unitGross' => -129, 'totalGross' => -187 ],
		];
	}

	/**
	 * @dataProvider UnitPriceLevelFromNetDivideDataProvider
	 */
	public function testCalculatingTaxAfterDividingByQuantityFromNet(
		int $totalNetAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalGrossAmount
	): void
	{
		$totalPrice = GrossBasedPrice::fromNetAmount( $this->buildMoney( $totalNetAmount, 'EUR' ), new VatRate( $vatRate ) );
		$unitPrice  = $totalPrice->divide( $quantity );

		self::assertEquals( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $totalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
	}

	public function AddingPriceDataProvider(): array
	{
		return [
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 4990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -2990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 5090, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 1100, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider AddingPriceDataProvider
	 */
	public function testAddingPrice( GrossBasedPrice $originalPrice, RepresentsPrice $additionalPrice, GrossBasedPrice $expectedPrice ): void
	{
		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertEquals( $expectedPrice, $summedPrice );
	}

	public function testAddingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->add( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}

	public function SubtractingPriceDataProvider(): array
	{
		return [
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 2990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -4990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 5000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -1010, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -1000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( -1000, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider SubtractingPriceDataProvider
	 */
	public function testSubtractingPrice( GrossBasedPrice $originalPrice, RepresentsPrice $priceToSubtract, GrossBasedPrice $expectedPrice ): void
	{
		$priceResult = $originalPrice->subtract( $priceToSubtract );

		self::assertEquals( $expectedPrice, $priceResult );
	}

	public function testSubtractingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->subtract( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}

	public function testAllocatingPriceToTargets(): void
	{
		$money = $this->buildMoney( 99, 'EUR' );
		$money->expects( self::once() )
		      ->method( 'allocateToTargets' );

		iterator_to_array( GrossBasedPrice::fromGrossAmount( $money, new VatRate( 19 ) )->allocateToTargets( 10 ) );
	}

	public function testAllocatingPriceByRatios(): void
	{
		$money = $this->buildMoney( 5, 'EUR' );
		$money->expects( self::once() )
		      ->method( 'allocateByRatios' );

		iterator_to_array( GrossBasedPrice::fromGrossAmount( $money, new VatRate( 19 ) )->allocateByRatios( [ 3, 7 ] ) );
	}
}
