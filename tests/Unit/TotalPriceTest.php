<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\NetBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\Tests\Unit\fakes\FakeTotalPrice;
use ComponoKit\Prices\TotalPrice;
use ComponoKit\Prices\VatRate;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

class TotalPriceTest extends TestCase
{
	public function testInstantiatingWithPrices(): void
	{
		$prices = $this->createListOfPrices();

		$totalPrice = new TotalPrice( new Currency( 'EUR' ), $prices );

		self::assertEquals( $prices, $totalPrice->getPrices() );
	}

	public function testInstantiatingFromAnotherTotalPrice(): void
	{
		self::assertEquals(
			new TotalPrice( new Currency( 'EUR' ), $this->createListOfPrices() ),
			TotalPrice::fromTotalPrice( new FakeTotalPrice( new Currency( 'EUR' ), $this->createListOfPrices() ) )
		);
	}

	public function testAddingPricesToExistingPrices(): void
	{
		$prices = $this->createListOfPrices();

		$totalPrice = new TotalPrice( new Currency( 'EUR' ), [ $prices[0], $prices[1] ] );

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

		$totalPrice = new TotalPrice( new Currency( 'EUR' ), $initialPrices );

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

		$totalPrice        = new TotalPrice( new Currency( 'EUR' ), $prices );
		$anotherTotalPrice = new TotalPrice( new Currency( 'EUR' ), $pricesOfAnotherTotalPrice );

		$mergedTotalPrices = $totalPrice->addTotalPrice( $anotherTotalPrice );

		self::assertEquals( array_merge( $prices, $pricesOfAnotherTotalPrice ), $mergedTotalPrices->getPrices() );
		self::assertEquals( $prices, $totalPrice->getPrices() );
	}

	public function testGettingAllVatRates(): void
	{
		self::assertEquals(
			[ new VatRate( 19 ), new VatRate( 7 ), new VatRate( 16.5 ) ],
			(new TotalPrice( new Currency( 'EUR' ), $this->createListOfPrices() ))->getVatRates()
		);
	}

	public function testGettingPricesGroupedByVatRates(): void
	{
		self::assertEquals(
			[
				1900 => [
					GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 19 ) ),
					NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 19 ) ),
					FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 19 ) ),
				],
				700  => [
					GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 7 ) ),
					NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 7 ) ),
					FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 7 ) ),
				],
				1650 => [
					GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 16.5 ) ),
					NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 16.5 ) ),
					FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 16.5 ) ),
				],
			],
			(new TotalPrice( new Currency( 'EUR' ), $this->createListOfPrices() ))->getPricesGroupedByVatRates()
		);
	}

	public function testTotalPriceReturnsCorrectAmounts(): void
	{
		$totalPrice = new TotalPrice( new Currency( 'EUR' ), $this->createListOfPrices() );

		self::assertEquals( 1800, $totalPrice->getTotalGrossAmount()->getAmount() );
		self::assertEquals( 1580, $totalPrice->getTotalNetAmount()->getAmount() );
		self::assertEquals( 220, $totalPrice->getTotalVatAmount()->getAmount() );
	}

	public function testJsonSerialize(): void
	{
		$prices = [
			GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 19 ) ),
			FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 19 ) ),
			NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 7 ) ),
		];
		self::assertEquals(
			'{"1900":[{"gross":{"amount":"100","currency":"EUR"},"net":{"amount":"84","currency":"EUR"},"vat":{"amount":"16","currency":"EUR"}},{"gross":{"amount":"300","currency":"EUR"},"net":{"amount":"252","currency":"EUR"},"vat":{"amount":"48","currency":"EUR"}}],"700":[{"gross":{"amount":"200","currency":"EUR"},"net":{"amount":"187","currency":"EUR"},"vat":{"amount":"13","currency":"EUR"}}]}',
			json_encode( new TotalPrice( new Currency( 'EUR' ), $prices ), JSON_THROW_ON_ERROR )
		);
	}

	/**
	 * @return RepresentsPrice[]
	 */
	private function createListOfPrices(): array
	{
		return [
			GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 19 ) ),
			NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 19 ) ),
			FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 19 ) ),
			GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 7 ) ),
			NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 7 ) ),
			FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 7 ) ),
			GrossBasedPrice::fromGrossAmount( new Money( 100, new Currency('EUR') ), new VatRate( 16.5 ) ),
			NetBasedPrice::fromGrossAmount( new Money( 200, new Currency('EUR') ), new VatRate( 16.5 ) ),
			FakePriceImplementation::fromGrossAmount( new Money( 300, new Currency('EUR') ), new VatRate( 16.5 ) ),
		];
	}
}
