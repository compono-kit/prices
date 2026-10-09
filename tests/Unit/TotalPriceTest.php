<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\AbstractPrice;
use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\NetBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\TotalPrice;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\Attributes\DataProvider;
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
		self::assertSame(
			'{"currencyCode":"EUR","prices":{"1900":[{"grossAmount":100,"netAmount":84,"vatAmount":16},{"grossAmount":300,"netAmount":252,"vatAmount":48}],"700":[{"grossAmount":200,"netAmount":187,"vatAmount":13}]}}',
			json_encode( new TotalPrice( $this->buildMoneyFactory( 'EUR' ), $prices ), JSON_THROW_ON_ERROR )
		);
	}

	public function testJsonSerializeWithoutPrices(): void
	{
		self::assertSame(
			'{"currencyCode":"EUR","prices":{}}',
			json_encode( new TotalPrice( $this->buildMoneyFactory( 'EUR' ) ), JSON_THROW_ON_ERROR )
		);
	}

	public function testEmptyTotalPriceReturnsZeroAmounts(): void
	{
		$totalPrice = new TotalPrice( $this->buildMoneyFactory( 'EUR' ) );

		self::assertSame( 0, $totalPrice->getTotalGrossAmount()->getAmount() );
		self::assertSame( 0, $totalPrice->getTotalNetAmount()->getAmount() );
		self::assertSame( 0, $totalPrice->getTotalVatAmount()->getAmount() );
		self::assertSame( [], $totalPrice->getVatRates() );
		self::assertSame( [], $totalPrice->getPricesGroupedByVatRates() );
	}

	public function testGettingCurrency(): void
	{
		self::assertSame( 'USD', (new TotalPrice( $this->buildMoneyFactory( 'USD' ) ))->getCurrency()->getIsoCode() );
	}

	public function testInstantiatingWithPriceOfDifferentCurrencyThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );
		$this->expectExceptionMessage( 'Price currency USD does not match total price currency EUR' );

		new TotalPrice(
			$this->buildMoneyFactory( 'EUR' ),
			[ GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'USD' ), new VatRate( 19 ) ) ]
		);
	}

	public function testAddingPriceOfDifferentCurrencyThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		(new TotalPrice( $this->buildMoneyFactory( 'EUR' ) ))->addPrice(
			GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'USD' ), new VatRate( 19 ) )
		);
	}

	public function testSummingPricesWithDifferentVatRates(): void
	{
		$totalPrice = (new TotalPrice( $this->buildMoneyFactory( 'EUR' ) ))
			->addPrice( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 119, 'EUR' ), new VatRate( 19 ) ) )
			->addPrice( NetBasedPrice::fromGrossAmount( $this->buildMoney( 107, 'EUR' ), new VatRate( 7 ) ) );

		self::assertSame( 226, $totalPrice->getTotalGrossAmount()->getAmount() );
		self::assertSame( 200, $totalPrice->getTotalNetAmount()->getAmount() );
		self::assertSame( 26, $totalPrice->getTotalVatAmount()->getAmount() );
	}

	public function testSubtractingPrice(): void
	{
		$totalPrice = (new TotalPrice( $this->buildMoneyFactory( 'EUR' ) ))
			->addPrice( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 119, 'EUR' ), new VatRate( 19 ) ) )
			->addPrice( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 107, 'EUR' ), new VatRate( 7 ) ) )
			->subtractPrice( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 119, 'EUR' ), new VatRate( 19 ) ) );

		self::assertCount( 3, $totalPrice->getPrices() );
		self::assertSame( 107, $totalPrice->getTotalGrossAmount()->getAmount() );
		self::assertSame( 100, $totalPrice->getTotalNetAmount()->getAmount() );
		self::assertSame( 7, $totalPrice->getTotalVatAmount()->getAmount() );
	}

	public static function TotalsGroupedByVatRatesProvider(): array
	{
		return [
			[ GrossBasedPrice::class, [ 1900 => [ 30, 25 ], 700 => [ 20, 19 ] ] ],
			[ NetBasedPrice::class, [ 1900 => [ 29, 24 ], 700 => [ 19, 18 ] ] ],
		];
	}

	/**
	 * @param class-string<AbstractPrice>    $priceClass
	 * @param array<int, array{0:int, 1:int}> $expectedAmounts
	 */
	#[DataProvider( 'TotalsGroupedByVatRatesProvider' )]
	public function testGettingTotalsGroupedByVatRates( string $priceClass, array $expectedAmounts ): void
	{
		$totalPrice = new TotalPrice(
			$this->buildMoneyFactory( 'EUR' ),
			[
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 10, 'EUR' ), new VatRate( 19 ) ),
				GrossBasedPrice::fromGrossAmount( $this->buildMoney( 10, 'EUR' ), new VatRate( 7 ) ),
				NetBasedPrice::fromGrossAmount( $this->buildMoney( 10, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( $this->buildMoney( 10, 'EUR' ), new VatRate( 19 ) ),
				FakePriceImplementation::fromGrossAmount( $this->buildMoney( 10, 'EUR' ), new VatRate( 7 ) ),
			]
		);

		$totals = $totalPrice->getTotalsGroupedByVatRates( $priceClass );

		self::assertSame( array_keys( $expectedAmounts ), array_keys( $totals ) );

		foreach ( $expectedAmounts as $vatRate => [ $expectedGrossAmount, $expectedNetAmount ] )
		{
			self::assertInstanceOf( $priceClass, $totals[ $vatRate ] );
			self::assertSame( $vatRate, $totals[ $vatRate ]->getVatRate()->toInt() );
			self::assertSame( $expectedGrossAmount, $totals[ $vatRate ]->getGrossAmount()->getAmount() );
			self::assertSame( $expectedNetAmount, $totals[ $vatRate ]->getNetAmount()->getAmount() );
		}
	}

	public function testGettingTotalsGroupedByVatRatesWithInvalidClassThrowsException(): void
	{
		$this->expectException( \InvalidArgumentException::class );

		(new TotalPrice( $this->buildMoneyFactory( 'EUR' ) ))->getTotalsGroupedByVatRates( VatRate::class );
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
