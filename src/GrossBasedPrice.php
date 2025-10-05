<?php declare(strict_types=1);

namespace Componium\Prices;

use Componium\Prices\Exceptions\InvalidPriceException;
use Componium\Prices\Interfaces\RepresentsPrice;

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
	 * @param RepresentsPrice $price
	 *
	 * @return RepresentsPrice|static
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): static|RepresentsPrice
	{
		$this->validatePrice( $price );

		return self::fromGrossAmount( $this->grossAmount->add( $price->getGrossAmount() ), $price->getVatRate() );
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

		return self::fromGrossAmount( $this->grossAmount->subtract( $price->getGrossAmount() ), $price->getVatRate() );
	}

	/**
	 * @param int $targetCount
	 *
	 * @return \Iterator|static[]
	 */
	public function allocateToTargets( int $targetCount ): \Iterator|array
	{
		foreach ( $this->grossAmount->allocateTo( $targetCount ) as $allocatedMoney )
		{
			yield static::fromGrossAmount( $allocatedMoney, $this->vatRate );
		}
	}

	/**
	 * @param array|int[] $ratios
	 *
	 * @return \Iterator|static[]
	 */
	public function allocateByRatios( array $ratios ): array|\Iterator
	{
		foreach ( $this->grossAmount->allocate( $ratios ) as $allocatedMoney )
		{
			yield static::fromGrossAmount( $allocatedMoney, $this->vatRate );
		}
	}
}
