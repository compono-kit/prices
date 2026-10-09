<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit\fakes;

use ComponoKit\Money\Interfaces\BuildsMoneys;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use PHPUnit\Framework\MockObject\MockObject;

trait BuildingFakeMoneys
{
	protected function buildCurrency( string $isoCode ): RepresentsCurrency&MockObject
	{
		$currency = $this->createMock( RepresentsCurrency::class );

		$currency->method( 'getIsoCode' )->willReturn( $isoCode );

		return $currency;
	}

	protected function buildMoney( int $amount, string $currencyCode ): RepresentsMoney&MockObject
	{
		$money = $this->createMock( RepresentsMoney::class );

		$money->method( 'getAmount' )->willReturn( $amount );
		$money->method( 'getCurrency' )->willReturn( $this->buildCurrency( $currencyCode ) );
		$money->method( 'isZero' )->willReturn( $amount === 0 );
		$money->method( 'hasSameCurrency' )->willReturnCallback(
			function ( RepresentsMoney $other ) use ( $currencyCode ): bool
			{
				return $other->getCurrency()->getIsoCode() === $currencyCode;
			}
		);
		$money->method( 'add' )->willReturnCallback(
			function ( RepresentsMoney $other ) use ( $currencyCode, $amount ): RepresentsMoney
			{
				return $this->buildMoney( $amount + $other->getAmount(), $currencyCode );
			}
		);
		$money->method( 'subtract' )->willReturnCallback(
			function ( RepresentsMoney $other ) use ( $currencyCode, $amount ): RepresentsMoney
			{
				return $this->buildMoney( $amount - $other->getAmount(), $currencyCode );
			}
		);
		$money->method( 'multiply' )->willReturnCallback(
			function ( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ) use ( $currencyCode, $amount ): RepresentsMoney
			{
				return $this->buildMoney( (int)round( $amount * $factor, 0, $roundingMode ), $currencyCode );
			}
		);
		$money->method( 'divide' )->willReturnCallback(
			function ( float $divisor, int $roundingMode = PHP_ROUND_HALF_UP ) use ( $currencyCode, $amount ): RepresentsMoney
			{
				return $this->buildMoney( (int)round( $amount / $divisor, 0, $roundingMode ), $currencyCode );
			}
		);
		$money->method( 'allocateToTargets' )->willReturnCallback(
			function ( int $numberOfTargets ) use ( $currencyCode, $amount ): \Iterator
			{
				return $this->buildAllocatedMoneys( $amount, array_fill( 0, $numberOfTargets, 1 ), $currencyCode );
			}
		);
		$money->method( 'allocateByRatios' )->willReturnCallback(
			function ( array $ratios ) use ( $currencyCode, $amount ): \Iterator
			{
				return $this->buildAllocatedMoneys( $amount, $ratios, $currencyCode );
			}
		);

		return $money;
	}

	protected function buildMoneyFactory( string $currencyCode ): BuildsMoneys
	{
		$factory = $this->createMock( BuildsMoneys::class );
		$factory->method( 'build' )->willReturnCallback(
			function ( int $amount ) use ( $currencyCode ): RepresentsMoney
			{
				return $this->buildMoney( $amount, $currencyCode );
			}
		);

		return $factory;
	}

	/**
	 * @param array<int,int> $ratios
	 *
	 * @return \Iterator<int,RepresentsMoney>
	 */
	private function buildAllocatedMoneys( int $amount, array $ratios, string $currencyCode ): \Iterator
	{
		$totalRatio       = array_sum( $ratios );
		$allocatedAmounts = [];
		$remainder        = $amount;

		foreach ( $ratios as $ratio )
		{
			$allocatedAmount    = intdiv( $amount * $ratio, $totalRatio );
			$allocatedAmounts[] = $allocatedAmount;
			$remainder          -= $allocatedAmount;
		}

		for ( $index = 0; $remainder !== 0; $index++ )
		{
			$step                        = $remainder > 0 ? 1 : -1;
			$allocatedAmounts[ $index ] += $step;
			$remainder                  -= $step;
		}

		return new \ArrayIterator(
			array_map(
				fn( int $allocatedAmount ): RepresentsMoney => $this->buildMoney( $allocatedAmount, $currencyCode ),
				$allocatedAmounts
			)
		);
	}
}
