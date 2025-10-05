<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\Interfaces\RepresentsPrice;

class GrossBasedPrice extends AbstractPrice
{
	public function multiply( float $quantity ): RepresentsPrice
	{
		return self::fromGrossAmount( $this->grossAmount->multiply( $quantity ), $this->vatRate );
	}

	public function divide( float $quantity ): RepresentsPrice
	{
		return self::fromGrossAmount( $this->grossAmount->divide( $quantity ), $this->vatRate );
	}

	/**
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): static
	{
		$this->validatePrice( $price );

		return self::fromGrossAmount( $this->grossAmount->add( $price->getGrossAmount() ), $price->getVatRate() );
	}

	/**
	 * @throws InvalidPriceException
	 */
	public function subtract( RepresentsPrice $price ): static
	{
		$this->validatePrice( $price );

		return self::fromGrossAmount( $this->grossAmount->subtract( $price->getGrossAmount() ), $price->getVatRate() );
	}

	/**
	 * @return \Iterator<int,static>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		foreach ( $this->grossAmount->allocateToTargets( $numberOfTargets ) as $allocatedMoney )
		{
			yield static::fromGrossAmount( $allocatedMoney, $this->vatRate );
		}
	}

	/**
	 * @param array<int,int> $ratios
	 *
	 * @return \Iterator<int,static>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		foreach ( $this->grossAmount->allocateByRatios( $ratios ) as $allocatedMoney )
		{
			yield static::fromGrossAmount( $allocatedMoney, $this->vatRate );
		}
	}
}
