<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Tests\Unit\fakes\AnotherFakePriceImplementation;
use ComponoKit\Prices\Tests\Unit\fakes\BuildingFakeMoneys;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\VatRate;
use PHPUnit\Framework\TestCase;

class AbstractPriceTest extends TestCase
{
	use BuildingFakeMoneys;

	public function FromGrossAmountProvider(): array
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

	/**
	 * @dataProvider FromGrossAmountProvider
	 */
	public function testCalculatingNetAndVatAmountWhenInstantiatingFromGrossAmount( int $grossAmount, int $vatRate, string $currencyCode, int $expectedNetAmount, int $expectedVatAmount ): void
	{
		$price = FakePriceImplementation::fromGrossAmount( $this->buildMoney( $grossAmount, $currencyCode ), new VatRate( $vatRate ) );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertEquals( $grossAmount, $price->getGrossAmount()->getAmount() );
		self::assertEquals( $expectedNetAmount, $price->getNetAmount()->getAmount() );
		self::assertEquals( $expectedVatAmount, $price->getVatAmount()->getAmount() );
		self::assertEquals( new VatRate( $vatRate ), $price->getVatRate() );
		self::assertEquals( $currencyCode, $price->getCurrency()->getIsoCode() );
	}

	public function FromNetAmountProvider(): array
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

	/**
	 * @dataProvider FromNetAmountProvider
	 */
	public function testCalculatingNetAndVatAmountWhenInstantiatingFromNetAmount( int $netAmount, int $vatRate, string $currencyCode, int $expectedGrossAmount, int $expectedVatAmount ): void
	{
		$price = FakePriceImplementation::fromNetAmount( $this->buildMoney( $netAmount, $currencyCode ), new VatRate( $vatRate ) );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertEquals( $this->buildMoney( $expectedGrossAmount, $currencyCode ), $price->getGrossAmount() );
		self::assertEquals( $this->buildMoney( $netAmount, $currencyCode ), $price->getNetAmount() );
		self::assertEquals( $this->buildMoney( $expectedVatAmount, $currencyCode ), $price->getVatAmount() );
		self::assertEquals( new VatRate( $vatRate ), $price->getVatRate() );
	}

	public function FromNetAndGrossAmountExceptionDataProvider(): array
	{
		return [
			[ 0 ],
			[ 1 ],
			[ -1 ],
		];
	}

	public function FromPriceProvider(): array
	{
		return [
			[ AnotherFakePriceImplementation::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ) ],
			[ AnotherFakePriceImplementation::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ) ],
			[ AnotherFakePriceImplementation::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ) ],
			[ AnotherFakePriceImplementation::fromGrossAmount( $this->buildMoney( 3990, 'EUR' ), new VatRate( 19 ) ) ],
		];
	}

	/**
	 * @dataProvider FromPriceProvider
	 */
	public function testInstantiatingFromAnotherPrice( RepresentsPrice $price ): void
	{
		$price = FakePriceImplementation::fromPrice( $price );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertEquals( $price->getGrossAmount(), $price->getGrossAmount() );
		self::assertEquals( $price->getNetAmount(), $price->getNetAmount() );
		self::assertEquals( $price->getVatAmount(), $price->getVatAmount() );
		self::assertEquals( $price->getVatRate(), $price->getVatRate() );
	}

	public function testJsonSerialize(): void
	{
		self::assertEquals(
			'{"currency-code":"EUR","netAmount":100,"grossAmount":119,"vatAmount":19,"vatRate":1900}',
			json_encode( GrossBasedPrice::fromNetAmount( $this->buildMoney( 100, 'EUR' ), new VatRate( 19 ) ), JSON_THROW_ON_ERROR )
		);
	}
}
