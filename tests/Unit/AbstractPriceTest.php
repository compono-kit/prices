<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\AbstractPrice;
use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\NetBasedPrice;
use ComponoKit\Prices\Tests\Unit\fakes\AnotherFakePriceImplementation;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractPriceTest extends TestCase
{
	use BuildingFakeMoneys;

	public static function FromGrossAmountProvider(): array
	{
		return [
			[ 3990, 19, 'EUR', 3353, 637 ],
			[ 1990, 19, 'EUR', 1672, 318 ],
			[ -5000, 19, 'EUR', -4202, -798 ],
			[ 4990, 7, 'EUR', 4664, 326 ],
			[ 0, 20, 'USD', 0, 0 ],
			[ 999, 19, 'EUR', 839, 160 ],
		];
	}

	#[DataProvider( 'FromGrossAmountProvider' )]
	public function testCalculatingNetAndVatAmountWhenInstantiatingFromGrossAmount( int $grossAmount, int $vatRate, string $currencyCode, int $expectedNetAmount, int $expectedVatAmount ): void
	{
		$price = FakePriceImplementation::fromGrossAmount( $this->buildMoney( $grossAmount, $currencyCode ), new VatRate( $vatRate ) );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertSame( $grossAmount, $price->getGrossAmount()->getAmount() );
		self::assertSame( $expectedNetAmount, $price->getNetAmount()->getAmount() );
		self::assertSame( $expectedVatAmount, $price->getVatAmount()->getAmount() );
		self::assertTrue( $price->getVatRate()->equals( new VatRate( $vatRate ) ) );
		self::assertSame( $currencyCode, $price->getCurrency()->getIsoCode() );
	}

	public static function FromNetAmountProvider(): array
	{
		return [
			[ 3353, 19, 'EUR', 3990, 637 ],
			[ 1672, 19, 'EUR', 1990, 318 ],
			[ -4202, 19, 'EUR', -5000, -798 ],
			[ 4664, 7, 'EUR', 4990, 326 ],
			[ 0, 20, 'USD', 0, 0 ],
			[ 839, 19, 'EUR', 998, 159 ],
		];
	}

	#[DataProvider( 'FromNetAmountProvider' )]
	public function testCalculatingGrossAndVatAmountWhenInstantiatingFromNetAmount( int $netAmount, int $vatRate, string $currencyCode, int $expectedGrossAmount, int $expectedVatAmount ): void
	{
		$price = FakePriceImplementation::fromNetAmount( $this->buildMoney( $netAmount, $currencyCode ), new VatRate( $vatRate ) );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertSame( $expectedGrossAmount, $price->getGrossAmount()->getAmount() );
		self::assertSame( $netAmount, $price->getNetAmount()->getAmount() );
		self::assertSame( $expectedVatAmount, $price->getVatAmount()->getAmount() );
		self::assertTrue( $price->getVatRate()->equals( new VatRate( $vatRate ) ) );
		self::assertSame( $currencyCode, $price->getCurrency()->getIsoCode() );
	}

	public static function FromPriceProvider(): array
	{
		return [
			[ 3990, 19 ],
			[ -3990, 19 ],
			[ 999, 7 ],
			[ 0, 0 ],
		];
	}

	#[DataProvider( 'FromPriceProvider' )]
	public function testInstantiatingFromAnotherPrice( int $netAmount, int $vatRate ): void
	{
		$originalPrice = AnotherFakePriceImplementation::fromNetAmount( $this->buildMoney( $netAmount, 'EUR' ), new VatRate( $vatRate ) );

		$copiedPrice = FakePriceImplementation::fromPrice( $originalPrice );

		self::assertInstanceOf( FakePriceImplementation::class, $copiedPrice );
		self::assertSame( $originalPrice->getGrossAmount()->getAmount(), $copiedPrice->getGrossAmount()->getAmount() );
		self::assertSame( $originalPrice->getNetAmount()->getAmount(), $copiedPrice->getNetAmount()->getAmount() );
		self::assertSame( $originalPrice->getVatAmount()->getAmount(), $copiedPrice->getVatAmount()->getAmount() );
		self::assertTrue( $originalPrice->getVatRate()->equals( $copiedPrice->getVatRate() ) );
	}

	public static function ZeroAmountWithDifferentVatRateProvider(): array
	{
		return [
			[ 0, 0, 119, 19, 119, 19 ],
			[ 119, 19, 0, 0, 119, 19 ],
			[ 0, 7, 0, 19, 0, 19 ],
		];
	}

	#[DataProvider( 'ZeroAmountWithDifferentVatRateProvider' )]
	public function testAddingZeroAmountIgnoresDifferentVatRate(
		int $originalGrossAmount, int $originalVatRate, int $additionalGrossAmount, int $additionalVatRate, int $expectedGrossAmount, int $expectedVatRate
	): void
	{
		$originalPrice   = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $originalGrossAmount, 'EUR' ), new VatRate( $originalVatRate ) );
		$additionalPrice = GrossBasedPrice::fromGrossAmount( $this->buildMoney( $additionalGrossAmount, 'EUR' ), new VatRate( $additionalVatRate ) );

		$summedPrice = $originalPrice->add( $additionalPrice );

		self::assertSame( $expectedGrossAmount, $summedPrice->getGrossAmount()->getAmount() );
		self::assertTrue( $summedPrice->getVatRate()->equals( new VatRate( $expectedVatRate ) ) );
	}

	public function testAddingNonZeroAmountWithZeroVatRateToDifferentVatRateThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );
		$this->expectExceptionMessage( "VAT rates don't match (0 !== 1900)" );

		$price = GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 0 ) );
		$price->add( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 119, 'EUR' ), new VatRate( 19 ) ) );
	}

	public function testAddingPriceWithDifferentCurrencyThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );
		$this->expectExceptionMessage( "Currencies don't match (EUR !== USD)" );

		$price = GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->add( GrossBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'USD' ), new VatRate( 19 ) ) );
	}

	public function testSubtractingPriceWithDifferentCurrencyThrowsException(): void
	{
		$this->expectException( InvalidPriceException::class );

		$price = NetBasedPrice::fromGrossAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) );
		$price->subtract( NetBasedPrice::fromGrossAmount( $this->buildMoney( 0, 'USD' ), new VatRate( 19 ) ) );
	}

	public static function AllocationProvider(): array
	{
		return [
			[ GrossBasedPrice::class, 100, 19 ],
			[ NetBasedPrice::class, 100, 19 ],
			[ GrossBasedPrice::class, 999, 7 ],
			[ NetBasedPrice::class, -1990, 19 ],
		];
	}

	/**
	 * @param class-string<AbstractPrice> $priceClass
	 */
	#[DataProvider( 'AllocationProvider' )]
	public function testAllocatingPriceToTargetsKeepsTotals( string $priceClass, int $grossAmount, int $vatRate ): void
	{
		$price = $priceClass::fromGrossAmount( $this->buildMoney( $grossAmount, 'EUR' ), new VatRate( $vatRate ) );

		$allocatedPrices = iterator_to_array( $price->allocateToTargets( 3 ) );

		self::assertCount( 3, $allocatedPrices );
		self::assertContainsOnlyInstancesOf( $priceClass, $allocatedPrices );
		self::assertSame( $price->getGrossAmount()->getAmount(), $this->sumGrossAmounts( $allocatedPrices ) );
		self::assertSame( $price->getNetAmount()->getAmount(), $this->sumNetAmounts( $allocatedPrices ) );
	}

	/**
	 * @param class-string<AbstractPrice> $priceClass
	 */
	#[DataProvider( 'AllocationProvider' )]
	public function testAllocatingPriceByRatiosKeepsTotals( string $priceClass, int $grossAmount, int $vatRate ): void
	{
		$price = $priceClass::fromGrossAmount( $this->buildMoney( $grossAmount, 'EUR' ), new VatRate( $vatRate ) );

		$allocatedPrices = iterator_to_array( $price->allocateByRatios( [ 3, 7 ] ) );

		self::assertCount( 2, $allocatedPrices );
		self::assertContainsOnlyInstancesOf( $priceClass, $allocatedPrices );
		self::assertSame( $price->getGrossAmount()->getAmount(), $this->sumGrossAmounts( $allocatedPrices ) );
		self::assertSame( $price->getNetAmount()->getAmount(), $this->sumNetAmounts( $allocatedPrices ) );
	}

	public function testJsonSerialize(): void
	{
		self::assertSame(
			'{"currencyCode":"EUR","netAmount":100,"grossAmount":119,"vatAmount":19,"vatRate":1900}',
			json_encode( GrossBasedPrice::fromNetAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) ), JSON_THROW_ON_ERROR )
		);
	}

	/**
	 * @param array<int, RepresentsPrice> $prices
	 */
	private function sumGrossAmounts( array $prices ): int
	{
		return array_sum( array_map( fn( RepresentsPrice $price ): int => $price->getGrossAmount()->getAmount(), $prices ) );
	}

	/**
	 * @param array<int, RepresentsPrice> $prices
	 */
	private function sumNetAmounts( array $prices ): int
	{
		return array_sum( array_map( fn( RepresentsPrice $price ): int => $price->getNetAmount()->getAmount(), $prices ) );
	}
}
