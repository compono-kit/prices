<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit;

use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Tests\Unit\fakes\AnotherFakePriceImplementation;
use ComponoKit\Prices\Tests\Unit\fakes\FakePriceImplementation;
use ComponoKit\Prices\VatRate;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

class AbstractPriceTest extends TestCase
{
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
	 *
	 * @param int    $grossAmount
	 * @param int    $vatRate
	 * @param string $currency
	 * @param int    $expectedNetAmount
	 * @param int    $expectedVatAmount
	 */
	public function testCalculatingNetAndVatAmountWhenInstantiatingFromGrossAmount( int $grossAmount, int $vatRate, string $currency, int $expectedNetAmount, int $expectedVatAmount ): void
	{
		$price = FakePriceImplementation::fromGrossAmount( new Money( $grossAmount, new Currency( $currency ) ), new VatRate( $vatRate ) );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertEquals( new Money( $grossAmount, new Currency( $currency ) ), $price->getGrossAmount() );
		self::assertEquals( new Money( $expectedNetAmount, new Currency( $currency ) ), $price->getNetAmount() );
		self::assertEquals( new Money( $expectedVatAmount, new Currency( $currency ) ), $price->getVatAmount() );
		self::assertEquals( new VatRate( $vatRate ), $price->getVatRate() );
		self::assertEquals( $currency, $price->getCurrency()->getCode() );
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
	 *
	 * @param int    $netAmount
	 * @param int    $vatRate
	 * @param string $currency
	 * @param int    $expectedGrossAmount
	 * @param int    $expectedVatAmount
	 */
	public function testCalculatingNetAndVatAmountWhenInstantiatingFromNetAmount( int $netAmount, int $vatRate, string $currency, int $expectedGrossAmount, int $expectedVatAmount ): void
	{
		$price = FakePriceImplementation::fromNetAmount( new Money( $netAmount, new Currency( $currency ) ), new VatRate( $vatRate ) );

		self::assertInstanceOf( FakePriceImplementation::class, $price );
		self::assertEquals( new Money( $expectedGrossAmount, new Currency( $currency ) ), $price->getGrossAmount() );
		self::assertEquals( new Money( $netAmount, new Currency( $currency ) ), $price->getNetAmount() );
		self::assertEquals( new Money( $expectedVatAmount, new Currency( $currency ) ), $price->getVatAmount() );
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
			[ AnotherFakePriceImplementation::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ) ],
			[ AnotherFakePriceImplementation::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ) ],
			[ AnotherFakePriceImplementation::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ) ],
			[ AnotherFakePriceImplementation::fromGrossAmount( new Money( 3990, new Currency( 'EUR' ) ), new VatRate( 19 ) ) ],
		];
	}

	/**
	 * @dataProvider FromPriceProvider
	 *
	 * @param RepresentsPrice $price
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
			'{"currency":"EUR","netAmount":"100","grossAmount":"119","vatAmount":"19","vatRate":19}',
			json_encode( GrossBasedPrice::fromNetAmount( new Money( 100, new Currency( 'EUR' ) ), new VatRate( 19 ) ), JSON_THROW_ON_ERROR )
		);
	}
}
