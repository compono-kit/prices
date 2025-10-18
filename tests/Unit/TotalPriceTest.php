<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\NetBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\TotalPrice;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\TestCase;

class TotalPriceTest extends TestCase
{
	use BuildingFakeMoneys;

	public function testInstantiatingWithPrices(): void
	{
		$prices = $this->createListOfPrices();

		$totalPrice = new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $prices );

		self::assertEquals( $prices, $totalPrice->getPrices() );
	}

	public function testInstantiatingFromAnotherTotalPrice(): void
	{
		self::assertEquals(
			new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $this->createListOfPrices() ),
			TotalPrice::fromTotalPrice( new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $this->createListOfPrices() ) )
		);
	}

	public function testAddingPricesToExistingPrices(): void
	{
		$prices = $this->createListOfPrices();

		$totalPrice = new TotalPrice( $this->buildMoneyFactory( 'EUR' ), [ $prices[0], $prices[1] ] );

		for ( $i = 2, $iMax = count( $prices ); $i < $iMax; $i++ )
		{
			$totalPrice = $totalPrice->addPrice( $prices[ $i ] );
		}

		self::assertEquals( $prices, $totalPrice->getPrices() );
	}

	public function testTotalPriceIsImmutable(): void
	{
		$prices        = $this->createListOfPrices();
		$initialPrices = [ $prices[0], $prices[1] ];

		$totalPrice = new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $initialPrices );

		for ( $i = 2, $iMax = count( $prices ); $i < $iMax; $i++ )
		{
			$totalPrice->addPrice( $prices[ $i ] );
		}

		self::assertEquals( $initialPrices, $totalPrice->getPrices() );
	}

	public function testAddingTotalPriceMergesAllPricesReturningNewInstance(): void
	{
		$prices                    = $this->createListOfPrices();
		$pricesOfAnotherTotalPrice = [ $prices[0], $prices[1] ];

		$totalPrice        = new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $prices );
		$anotherTotalPrice = new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $pricesOfAnotherTotalPrice );

		$mergedTotalPrices = $totalPrice->addTotalPrice( $anotherTotalPrice );

		self::assertEquals( array_merge( $prices, $pricesOfAnotherTotalPrice ), $mergedTotalPrices->getPrices() );
		self::assertEquals( $prices, $totalPrice->getPrices() );
	}

	public function testGettingAllVatRates(): void
	{
		self::assertEquals(
			[ new VatRate( 19 ), new VatRate( 7 ), new VatRate( 16.5 ) ],
			(new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $this->createListOfPrices() ))->getVatRates()
		);
	}

	public function testGettingPricesGroupedByVatRates(): void
	{
		self::assertEquals(
			[
				1900 => [
					GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) ),
					NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 19 ) ),
					FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 19 ) ),
				],
				700  => [
					GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ),
					NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 7 ) ),
					FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 7 ) ),
				],
				1650 => [
					GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 16.5 ) ),
					NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 16.5 ) ),
					FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 16.5 ) ),
				],
			],
			(new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $this->createListOfPrices() ))->getPricesGroupedByVatRates()
		);
	}

	public function testTotalPriceReturnsCorrectAmounts(): void
	{
		$totalPrice = new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $this->createListOfPrices() );

		self::assertEquals( 1800, $totalPrice->getTotalGrossAmount()->getAmount() );
		self::assertEquals( 1580, $totalPrice->getTotalNetAmount()->getAmount() );
		self::assertEquals( 220, $totalPrice->getTotalVatAmount()->getAmount() );
	}

	public function testJsonSerialize(): void
	{
		$prices = [
			GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) ),
			FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 19 ) ),
			NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 7 ) ),
		];
		self::assertEquals(
			'{"currency-code":"EUR","prices":{"1900":[{"gross":100,"net":84,"vat":16},{"gross":300,"net":252,"vat":48}],"700":[{"gross":200,"net":187,"vat":13}]}}',
			json_encode( new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $prices ), JSON_THROW_ON_ERROR )
		);
	}

	/**
	 * @return RepresentsPrice[]
	 */
	private function createListOfPrices(): array
	{
		return [
			GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) ),
			NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 19 ) ),
			FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 19 ) ),
			GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 7 ) ),
			NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 7 ) ),
			FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 7 ) ),
			GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 16.5 ) ),
			NetBasedPrice::fromGrossAmount( $this->buildMoney( 200, 'EUR' ), new VatRate( 16.5 ) ),
			FakePriceImplementation::fromGrossAmount( $this->buildMoney( 300, 'EUR' ), new VatRate( 16.5 ) ),
		];
	}
}
