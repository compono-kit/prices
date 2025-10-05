<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\Interfaces\RepresentsPrice;

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
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): static
	{
		$this->validatePrice( $price );

		return self::fromNetAmount( $this->netAmount->add( $price->getNetAmount() ), $price->getVatRate() );
	}

	/**
	 * @throws InvalidPriceException
	 */
	public function subtract( RepresentsPrice $price ): static
	{
		$this->validatePrice( $price );

		return self::fromNetAmount( $this->netAmount->subtract( $price->getNetAmount() ), $price->getVatRate() );
	}

	/**
	 * @return \Iterator<int,static>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		foreach ( $this->netAmount->allocateToTargets( $numberOfTargets ) as $allocatedMoney )
		{
			yield static::fromNetAmount( $allocatedMoney, $this->vatRate );
		}
	}

	/**
	 * @param array<int,int> $ratios
	 *
	 * @return \Iterator<int, static>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		foreach ( $this->netAmount->allocateByRatios( $ratios ) as $allocatedMoney )
		{
			yield static::fromNetAmount( $allocatedMoney, $this->vatRate );
		}
	}
}
