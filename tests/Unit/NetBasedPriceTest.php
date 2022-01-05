<?php declare(strict_types=1);

namespace Hansel23\Prices\Tests\Unit;

use Hansel23\Prices\Exceptions\InvalidPriceException;
use Hansel23\Prices\Interfaces\RepresentsPrice;
use Hansel23\Prices\NetBasedPrice;
use Hansel23\Prices\Tests\Unit\fakes\FakePriceImplementation;
use Hansel23\Prices\VatRate;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

class NetBasedPriceTest extends TestCase
{
	public function LineItemLevelFromGrossMultiplyDataProvider(): array
	{
		return [
			[ 'unitGross' => 100, 'vatRat' => 19, 'quantity' => 1, 'totalNet' => 84, 'totalGross' => 100, 'unitNet' => 84 ],
			[ 'unitGross' => 108, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => 910, 'totalGross' => 1083, 'unitNet' => 91 ],
			[ 'unitGross' => 10808, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => 90820, 'totalGross' => 108076, 'unitNet' => 9082 ],
			[ 'unitGross' => -108, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => -910, 'totalGross' => -1083, 'unitNet' => -91 ],
			[ 'unitGross' => -10808, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => -90820, 'totalGross' => -108076, 'unitNet' => -9082 ],
			[ 'unitGross' => 200, 'vatRat' => 19, 'quantity' => 1, 'totalNet' => 168, 'totalGross' => 200, 'unitNet' => 168 ],
			[ 'unitGross' => 1, 'vatRat' => 19, 'quantity' => 50, 'totalNet' => 50, 'totalGross' => 60, 'unitNet' => 1 ],
			[ 'unitGross' => 490, 'vatRat' => 19, 'quantity' => 1, 'totalNet' => 412, 'totalGross' => 490, 'unitNet' => 412 ],
			[ 'unitGross' => 129, 'vatRat' => 19, 'quantity' => 3, 'totalNet' => 324, 'totalGross' => 386, 'unitNet' => 108 ],
			[ 'unitGross' => -129, 'vatRat' => 19, 'quantity' => 3, 'totalNet' => -324, 'totalGross' => -386, 'unitNet' => -108 ],
			[ 'unitGross' => 129, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => 157, 'totalGross' => 187, 'unitNet' => 108 ],
			[ 'unitGross' => -129, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => -157, 'totalGross' => -187, 'unitNet' => -108 ],
		];
	}

	/**
	 * @dataProvider LineItemLevelFromGrossMultiplyDataProvider
	 *
	 * @param int   $unitGrossAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedTotalNetAmount
	 * @param int   $expectedTotalGrossAmount
	 * @param int   $expectedUnitNetAmount
	 */
	public function testCalculatingTaxBeforeMultiplyingByQuantityFromGross(
		int $unitGrossAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitNetAmount
	): void
	{
		$unitPrice  = NetBasedPrice::fromGrossAmount( new Money( $unitGrossAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertEquals( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $unitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
	}

	public function LineItemLevelFromNetMultiplyDataProvider(): array
	{
		return [
			[ 'unitNet' => 84, 'vatRat' => 19, 'quantity' => 1, 'totalNet' => 84, 'totalGross' => 100, 'unitGross' => 100 ],
			[ 'unitNet' => 91, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => 910, 'totalGross' => 1083, 'unitGross' => 108 ],
			[ 'unitNet' => 9082, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => 90820, 'totalGross' => 108076, 'unitGross' => 10808 ],
			[ 'unitNet' => -91, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => -910, 'totalGross' => -1083, 'unitGross' => -108 ],
			[ 'unitNet' => -9082, 'vatRat' => 19, 'quantity' => 10, 'totalNet' => -90820, 'totalGross' => -108076, 'unitGross' => -10808 ],
			[ 'unitNet' => 168, 'vatRat' => 19, 'quantity' => 1, 'totalNet' => 168, 'totalGross' => 200, 'unitGross' => 200 ],
			[ 'unitNet' => 1, 'vatRat' => 19, 'quantity' => 50, 'totalNet' => 50, 'totalGross' => 60, 'unitGross' => 1 ],
			[ 'unitNet' => 412, 'vatRat' => 19, 'quantity' => 1, 'totalNet' => 412, 'totalGross' => 490, 'unitGross' => 490 ],
			[ 'unitNet' => 108, 'vatRat' => 19, 'quantity' => 3, 'totalNet' => 324, 'totalGross' => 386, 'unitGross' => 129 ],
			[ 'unitNet' => -108, 'vatRat' => 19, 'quantity' => 3, 'totalNet' => -324, 'totalGross' => -386, 'unitGross' => -129 ],
			[ 'unitNet' => 108, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => 157, 'totalGross' => 187, 'unitGross' => 129 ],
			[ 'unitNet' => -108, 'vatRate' => 19, 'quantity' => 1.45, 'totalNet' => -157, 'totalGross' => -187, 'unitGross' => -129 ],
		];
	}

	/**
	 * @dataProvider LineItemLevelFromNetMultiplyDataProvider
	 *
	 * @param int   $unitNetAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedTotalNetAmount
	 * @param int   $expectedTotalGrossAmount
	 * @param int   $expectedUnitGrossAmount
	 */
	public function testCalculatingTaxBeforeMultiplyingByQuantityFromNet(
		int $unitNetAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitGrossAmount
	): void
	{
		$unitPrice  = NetBasedPrice::fromNetAmount( new Money( $unitNetAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
		$totalPrice = $unitPrice->multiply( $quantity );

		self::assertEquals( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedTotalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $unitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
	}

	public function LineItemLevelFromGrossDivideDataProvider(): array
	{
		return [
			[ 'totalGross' => 100, 'vatRat' => 19, 'quantity' => 1, 'unitNet' => 84, 'unitGross' => 100, 'totalNet' => 84 ],
			[ 'totalGross' => 1083, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => 91, 'unitGross' => 108, 'totalNet' => 910 ],
			[ 'totalGross' => 108076, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => 9082, 'unitGross' => 10808, 'totalNet' => 90820 ],
			[ 'totalGross' => -1083, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => -91, 'unitGross' => -108, 'totalNet' => -910 ],
			[ 'totalGross' => -108076, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => -9082, 'unitGross' => -10808, 'totalNet' => -90820 ],
			[ 'totalGross' => 200, 'vatRat' => 19, 'quantity' => 1, 'unitNet' => 168, 'unitGross' => 200, 'totalNet' => 168 ],
			[ 'totalGross' => 60, 'vatRat' => 19, 'quantity' => 50, 'unitNet' => 1, 'unitGross' => 1, 'totalNet' => 50 ],
			[ 'totalGross' => 490, 'vatRat' => 19, 'quantity' => 1, 'unitNet' => 412, 'unitGross' => 490, 'totalNet' => 412 ],
			[ 'totalGross' => 386, 'vatRat' => 19, 'quantity' => 3, 'unitNet' => 108, 'unitGross' => 129, 'totalNet' => 324 ],
			[ 'totalGross' => -386, 'vatRat' => 19, 'quantity' => 3, 'unitNet' => -108, 'unitGross' => -129, 'totalNet' => -324 ],
			[ 'totalGross' => 187, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => 108, 'unitGross' => 129, 'totalNet' => 157 ],
			[ 'totalGross' => -187, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => -108, 'unitGross' => -129, 'totalNet' => -157 ],
		];
	}

	/**
	 * @dataProvider LineItemLevelFromGrossDivideDataProvider
	 *
	 * @param int   $totalGrossAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedUnitNetAmount
	 * @param int   $expectedUnitGrossAmount
	 * @param int   $expectedTotalNetAmount
	 */
	public function testCalculatingTaxBeforeDividingByQuantityFromGross(
		int $totalGrossAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalNetAmount
	): void
	{
		$totalPrice = NetBasedPrice::fromGrossAmount( new Money( $totalGrossAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
		$unitPrice  = $totalPrice->divide( $quantity );

		self::assertEquals( $expectedUnitNetAmount, $unitPrice->getNetAmount()->getAmount() );
		self::assertEquals( $expectedUnitGrossAmount, $unitPrice->getGrossAmount()->getAmount() );
		self::assertEquals( $expectedTotalNetAmount, $totalPrice->getNetAmount()->getAmount() );
		self::assertEquals( $totalGrossAmount, $totalPrice->getGrossAmount()->getAmount() );
	}

	public function LineItemLevelFromNetDivideDataProvider(): array
	{
		return [
			[ 'totalNet' => 84, 'vatRat' => 19, 'quantity' => 1, 'unitNet' => 84, 'unitGross' => 100, 'totalGross' => 100 ],
			[ 'totalNet' => 910, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => 91, 'unitGross' => 108, 'totalGross' => 1083 ],
			[ 'totalNet' => 90820, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => 9082, 'unitGross' => 10808, 'totalGross' => 108076 ],
			[ 'totalNet' => -910, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => -91, 'unitGross' => -108, 'totalGross' => -1083 ],
			[ 'totalNet' => -90820, 'vatRat' => 19, 'quantity' => 10, 'unitNet' => -9082, 'unitGross' => -10808, 'totalGross' => -108076 ],
			[ 'totalNet' => 168, 'vatRat' => 19, 'quantity' => 1, 'unitNet' => 168, 'unitGross' => 200, 'totalGross' => 200 ],
			[ 'totalNet' => 50, 'vatRat' => 19, 'quantity' => 50, 'unitNet' => 1, 'unitGross' => 1, 'totalGross' => 60 ],
			[ 'totalNet' => 412, 'vatRat' => 19, 'quantity' => 1, 'unitNet' => 412, 'unitGross' => 490, 'totalGross' => 490 ],
			[ 'totalNet' => 324, 'vatRat' => 19, 'quantity' => 3, 'unitNet' => 108, 'unitGross' => 129, 'totalGross' => 386 ],
			[ 'totalNet' => -324, 'vatRat' => 19, 'quantity' => 3, 'unitNet' => -108, 'unitGross' => -129, 'totalGross' => -386 ],
			[ 'totalNet' => 157, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => 108, 'unitGross' => 129, 'totalGross' => 187 ],
			[ 'totalNet' => -157, 'vatRate' => 19, 'quantity' => 1.45, 'unitNet' => -108, 'unitGross' => -129, 'totalGross' => -187 ],
		];
	}

	/**
	 * @dataProvider LineItemLevelFromNetDivideDataProvider
	 *
	 * @param int   $totalNetAmount
	 * @param float $vatRate
	 * @param float $quantity
	 * @param int   $expectedUnitNetAmount
	 * @param int   $expectedUnitGrossAmount
	 * @param int   $expectedTotalGrossAmount
	 */
	public function testCalculatingTaxBeforeDividingByQuantityFromNet(
		int $totalNetAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalGrossAmount
	): void
	{
		$totalPrice = NetBasedPrice::fromNetAmount( new Money( $totalNetAmount, new Currency( 'EUR' ) ), new VatRate( $vatRate ) );
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
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 4990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( -2990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 5090, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 1100, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider AddingPriceDataProvider
	 *
	 * @param NetBasedPrice   $originalPrice
	 * @param RepresentsPrice $additionalPrice
	 * @param NetBasedPrice   $expectedPrice
	 */
	public function testAddingPrice( NetBasedPrice $originalPrice, RepresentsPrice $additionalPrice, NetBasedPrice $expectedPrice ): void
	{
		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertEquals( $expectedPrice, $summedPrice );
	}

	public function testAddingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 19 ) );
		$price->add( NetBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 7 ) ) );
	}

	public function SubtractingPriceDataProvider(): array
	{
		return [
			[
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 2990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( -3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( -4990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 5000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( -1010, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( new Money( -1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( new Money( -1000, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider SubtractingPriceDataProvider
	 *
	 * @param NetBasedPrice   $originalPrice
	 * @param RepresentsPrice $priceToSubtract
	 * @param NetBasedPrice   $expectedPrice
	 */
	public function testSubtractingPrice( NetBasedPrice $originalPrice, RepresentsPrice $priceToSubtract, NetBasedPrice $expectedPrice ): void
	{
		$priceResult = $originalPrice->subtract( $priceToSubtract );

		self::assertEquals( $expectedPrice, $priceResult );
	}

	public function testSubtractingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 19 ) );
		$price->subtract( NetBasedPrice::fromGrossAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 7 ) ) );
	}

	public function AllocateToTargetsDataProvider(): array
	{
		return [
			[
				NetBasedPrice::fromNetAmount( new Money( 99, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				10,
				[
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 10, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
					NetBasedPrice::fromNetAmount( new Money( 9, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
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
		$price          = NetBasedPrice::fromNetAmount( new Money( 5, new Currency( 'EUR' ) ), new VatRate( 19 ) );
		$allocatedPrice = iterator_to_array( $price->allocateByRatios( [ 3, 7 ] ) );

		self::assertEquals(
			[
				NetBasedPrice::fromNetAmount( new Money( 2, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( new Money( 3, new Currency( 'EUR' ) ), new VatRate( 19 ) ),
			],
			$allocatedPrice
		);
	}
}
