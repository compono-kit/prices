<?php declare(strict_types=1);

namespace Componium\Prices;

use Componium\Prices\Exceptions\InvalidPriceException;
use Componium\Prices\Interfaces\RepresentsPrice;

class NetBasedPrice extends AbstractPrice
{
	public function multiply( float $quantity ): RepresentsPrice
	{
		return self::fromNetAmount( $this->netAmount->multiply( $quantity ), $this->vatRate );
	}

	public function divide( float $quantity ): RepresentsPrice
	{
		return self::fromNetAmount( $this->netAmount->divide( $quantity ), $this->vatRate );
	}

	/**
	 * @param RepresentsPrice $price
	 *
	 * @return RepresentsPrice|static
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): static|RepresentsPrice
	{
		$this->validatePrice( $price );

		return self::fromNetAmount( $this->netAmount->add( $price->getNetAmount() ), $price->getVatRate() );
	}

	/**
	 * @param RepresentsPrice $price
	 *
	 * @return RepresentsPrice|static
	 * @throws InvalidPriceException
	 */
	public function subtract( RepresentsPrice $price ): static|RepresentsPrice
	{
		$this->validatePrice( $price );

		return self::fromNetAmount( $this->netAmount->subtract( $price->getNetAmount() ), $price->getVatRate() );
	}

	/**
	 * @param int $targetCount
	 *
	 * @return \Iterator<static>
	 */
	public function allocateToTargets( int $targetCount ): \Iterator
	{
		foreach ( $this->netAmount->allocateTo( $targetCount ) as $allocatedMoney )
		{
			yield static::fromNetAmount( $allocatedMoney, $this->vatRate );
		}
	}

	/**
	 * @param array|int[] $ratios
	 *
	 * @return \Iterator<static>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		foreach ( $this->netAmount->allocate( $ratios ) as $allocatedMoney )
		{
			yield static::fromNetAmount( $allocatedMoney, $this->vatRate );
		}
	}
}
