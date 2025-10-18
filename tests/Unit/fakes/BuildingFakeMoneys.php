<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit\fakes;

use ComponoKit\Money\Interfaces\BuildsMoneys;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use PHPUnit\Framework\MockObject\MockObject;

trait BuildingFakeMoneys
{
	protected function buildCurrency( string $isoCode ): RepresentsCurrency|MockObject
	{
		$currency = $this->getMockBuilder( RepresentsCurrency::class )
		                 ->getMockForAbstractClass();

		$currency->method( 'getIsoCode' )->willReturn( $isoCode );

		return $currency;
	}

	protected function buildMoney( int $amount, string $currencyCode ): RepresentsMoney|MockObject
	{
		$money = $this->getMockBuilder( RepresentsMoney::class )
		              ->getMockForAbstractClass();

		$money->method( 'getAmount' )->willReturn( $amount );
		$money->method( 'getCurrency' )->willReturn( $this->buildCurrency( $currencyCode ) );
		$money->method( 'add' )->willReturnCallback(
			function ( RepresentsMoney $other ) use ( $currencyCode, $amount )
			{
				return $this->buildMoney( $amount + $other->getAmount(), $currencyCode );
			}
		);
		$money->method( 'subtract' )->willReturnCallback(
			function ( RepresentsMoney $other ) use ( $currencyCode, $amount )
			{
				return $this->buildMoney( $amount - $other->getAmount(), $currencyCode );
			}
		);
		$money->method( 'multiply' )->willReturnCallback(
			function ( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ) use ( $currencyCode, $amount )
			{
				return $this->buildMoney( (int)round( $amount * $factor, 0, $roundingMode ), $currencyCode );
			}
		);
		$money->method( 'divide' )->willReturnCallback(
			function ( float $divisor, int $roundingMode = PHP_ROUND_HALF_UP ) use ( $currencyCode, $amount )
			{
				return $this->buildMoney( (int)round( $amount / $divisor, 0, $roundingMode ), $currencyCode );
			}
		);

		return $money;
	}

	protected function buildMoneyFactory( string $currencyCode ): BuildsMoneys
	{
		$factory = $this->getMockBuilder( BuildsMoneys::class )
		                ->getMockForAbstractClass();
		$factory->method( 'build' )->willReturnCallback(
			function ( int $amount ) use ( $currencyCode ): RepresentsMoney
			{
				return $this->buildMoney( $amount, $currencyCode );
			}
		);

		return $factory;
	}
}
