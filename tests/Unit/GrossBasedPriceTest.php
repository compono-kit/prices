<?php declare(strict_types=1);

namespace Hansel23\Prices\Tests\Unit;

use Hansel23\Prices\Exceptions\InvalidPriceException;
use Hansel23\Prices\GrossBasedPrice;
use Hansel23\Prices\Interfaces\RepresentsPrice;
use Hansel23\Prices\Tests\Unit\fakes\FakePriceImplementation;
use Hansel23\Prices\VatRate;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

class GrossBasedPriceTest extends TestCase
{
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
	 *
	 * @param int   $unitGrossAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedTotalNetAmount
	 * @param int   $expectedTotalGrossAmount
	 * @param int   $expectedUnitNetAmount
	 */
	public function testCalculatingTaxAfterMultiplyingByQuantityFromGross(
		int $unitGrossAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitNetAmount
	): void
	{
		$unitPrice  = GrossBasedPrice::fromGrossAmount( new Money( $unitGrossAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
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
	 *
	 * @param int   $unitNetAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedTotalNetAmount
	 * @param int   $expectedTotalGrossAmount
	 * @param int   $expectedUnitGrossAmount
	 */
	public function testCalculatingTaxAfterMultiplyingByQuantityFromNet(
		int $unitNetAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitGrossAmount
	): void
	{
		$unitPrice  = GrossBasedPrice::fromNetAmount( new Money( $unitNetAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
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
	 *
	 * @param int   $totalGrossAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedUnitNetAmount
	 * @param int   $expectedUnitGrossAmount
	 * @param int   $expectedTotalNetAmount
	 */
	public function testCalculatingTaxAfterDividingByQuantityFromGross(
		int $totalGrossAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalNetAmount
	): void
	{
		$totalPrice = GrossBasedPrice::fromGrossAmount( new Money( $totalGrossAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
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
	 *
	 * @param int   $totalNetAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedUnitNetAmount
	 * @param int   $expectedUnitGrossAmount
	 * @param int   $expectedTotalGrossAmount
	 */
	public function testCalculatingTaxAfterDividingByQuantityFromNet(
		int $totalNetAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalGrossAmount
	): void
	{
		$totalPrice = GrossBasedPrice::fromNetAmount( new Money( $totalNetAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
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
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 4990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( -2990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 5090, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 1100, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider AddingPriceDataProvider
	 *
	 * @param GrossBasedPrice $originalPrice
	 * @param RepresentsPrice $additionalPrice
	 * @param GrossBasedPrice $expectedPrice
	 */
	public function testAddingPrice( GrossBasedPrice $originalPrice, RepresentsPrice $additionalPrice, GrossBasedPrice $expectedPrice ): void
	{
		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertEquals( $expectedPrice, $summedPrice );
	}

	public function testAddingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 19 ) );
		$price->add( GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 7 ) ) );
	}

	public function SubtractingPriceDataProvider(): array
	{
		return [
			[
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 2990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( -4990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 5000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( -1010, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				GrossBasedPrice::fromGrossAmount( new Money( -1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( new Money( -1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider SubtractingPriceDataProvider
	 *
	 * @param GrossBasedPrice $originalPrice
	 * @param RepresentsPrice $priceToSubtract
	 * @param GrossBasedPrice $expectedPrice
	 */
	public function testSubtractingPrice( GrossBasedPrice $originalPrice, RepresentsPrice $priceToSubtract, GrossBasedPrice $expectedPrice ): void
	{
		$priceResult = $originalPrice->subtract( $priceToSubtract );

		self::assertEquals( $expectedPrice, $priceResult );
	}

	public function testSubtractingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 19 ) );
		$price->subtract( GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 7 ) ) );
	}

	public function AllocateToTargetsDataProvider(): array
	{
		return [
			[
				GrossBasedPrice::fromGrossAmount( new Money( 99, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				10,
				[
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					GrossBasedPrice::fromGrossAmount( new Money( 9, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				],
			],
		];
	}

	/**
	 * @dataProvider AllocateToTargetsDataProvider
	 *
	 * @param RepresentsPrice $price
	 * @param int             $targetCount
	 * @param array           $expectedResult
	 */
	public function testAllocatingPriceToTargets( RepresentsPrice $price, int $targetCount, array $expectedResult ): void
	{
		self::assertEquals( $expectedResult, iterator_to_array( $price->allocateToTargets( $targetCount ) ) );
	}

	public function testAllocatingPriceByRatios(): void
	{
		$price          = GrossBasedPrice::fromGrossAmount( new Money( 5, new Currency( 'EUR' ) ), new VatRate( 19 ) );
		$allocatedPrice = iterator_to_array( $price->allocateByRatios( [ 3, 7 ] ) );

		self::assertEquals(
			[
				GrossBasedPrice::fromGrossAmount( new Money( 2, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( new Money( 3, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			$allocatedPrice
		);
	}
}
