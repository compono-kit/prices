<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\NetBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\TestCase;

class NetBasedPriceTest extends TestCase
{
	use BuildingFakeMoneys;

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
	 */
	public function testCalculatingTaxBeforeMultiplyingByQuantityFromGross(
		int $unitGrossAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitNetAmount
	): void
	{
		$unitPrice  = NetBasedPrice::fromGrossAmount( $this->buildMoney( $unitGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
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
	 */
	public function testCalculatingTaxBeforeMultiplyingByQuantityFromNet(
		int $unitNetAmount, float $vatRate, float $quantity, int $expectedTotalNetAmount, int $expectedTotalGrossAmount, int $expectedUnitGrossAmount
	): void
	{
		$unitPrice  = NetBasedPrice::fromNetAmount( $this->buildMoney( $unitNetAmount, 'EUR' ), new VatRate( $vatRate ) );
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
	 */
	public function testCalculatingTaxBeforeDividingByQuantityFromGross(
		int $totalGrossAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalNetAmount
	): void
	{
		$totalPrice = NetBasedPrice::fromGrossAmount( $this->buildMoney( $totalGrossAmount, 'EUR' ), new VatRate( $vatRate ) );
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
	 */
	public function testCalculatingTaxBeforeDividingByQuantityFromNet(
		int $totalNetAmount, float $vatRate, float $quantity, int $expectedUnitNetAmount, int $expectedUnitGrossAmount, int $expectedTotalGrossAmount
	): void
	{
		$totalPrice = NetBasedPrice::fromNetAmount( $this->buildMoney( $totalNetAmount, 'EUR' ), new VatRate( $vatRate ) );
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
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 4990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( -2990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 5090, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 1100, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider AddingPriceDataProvider
	 */
	public function testAddingPrice( NetBasedPrice $originalPrice, RepresentsPrice $additionalPrice, NetBasedPrice $expectedPrice ): void
	{
		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertEquals( $expectedPrice, $summedPrice );
	}

	public function testAddingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->add( NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}

	public function SubtractingPriceDataProvider(): array
	{
		return [
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 2990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( -3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 1000, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( -4990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 5000, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( -1010, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ),
			],
			[
				NetBasedPrice::fromNetAmount( $this->buildMoney( -1000, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromNetAmount( $this->buildMoney( -1000, 'EUR' ), new VatRate( 19 ) ),
				NetBasedPrice::fromNetAmount( $this->buildMoney( 0, 'EUR' ), new VatRate( 19 ) ),
			],
		];
	}

	/**
	 * @dataProvider SubtractingPriceDataProvider
	 */
	public function testSubtractingPrice( NetBasedPrice $originalPrice, RepresentsPrice $priceToSubtract, NetBasedPrice $expectedPrice ): void
	{
		$priceResult = $originalPrice->subtract( $priceToSubtract );

		self::assertEquals( $expectedPrice, $priceResult );
	}

	public function testSubtractingPriceWithDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->subtract( NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ) );
	}

	public function testAllocatingPriceToTargets(): void
	{
		$money = $this->buildMoney( 99, 'EUR' );
		$money->expects( self::once() )
		      ->method( 'allocateToTargets' );

		iterator_to_array( NetBasedPrice::fromNetAmount( $money, new VatRate( 19 ) )->allocateToTargets( 10 ) );
	}

	public function testAllocatingPriceByRatios(): void
	{
		$money = $this->buildMoney( 5, 'EUR' );
		$money->expects( self::once() )
		      ->method( 'allocateByRatios' );

		iterator_to_array( NetBasedPrice::fromNetAmount( $money, new VatRate( 19 ) )->allocateByRatios( [ 3, 7 ] ) );
	}
}
