<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

abstract class AbstractPrice implements RepresentsPrice, \JsonSerializable
{
	final protected function __construct( protected readonly RepresentsMoney $netAmount, protected readonly RepresentsMoney $grossAmount, protected readonly RepresentsVatRate $vatRate )
	{
	}

	abstract protected function getBaseAmount(): RepresentsMoney;

	abstract protected static function fromBaseAmount( RepresentsMoney $baseAmount, RepresentsVatRate $vatRate ): static;

	public static function fromNetAmount( RepresentsMoney $netAmount, RepresentsVatRate $vatRate ): static
	{
		return new static(
			$netAmount,
			$netAmount->multiply( self::buildGrossMultiplier( $vatRate ) ),
			$vatRate
		);
	}

	public static function fromGrossAmount( RepresentsMoney $grossAmount, RepresentsVatRate $vatRate ): static
	{
		return new static(
			$grossAmount->divide( self::buildGrossMultiplier( $vatRate ) ),
			$grossAmount,
			$vatRate
		);
	}

	public static function fromPrice( RepresentsPrice $price ): static
	{
		return new static( $price->getNetAmount(), $price->getGrossAmount(), $price->getVatRate() );
	}

	public function getNetAmount(): RepresentsMoney
	{
		return $this->netAmount;
	}

	public function getGrossAmount(): RepresentsMoney
	{
		return $this->grossAmount;
	}

	public function getVatAmount(): RepresentsMoney
	{
		return $this->grossAmount->subtract( $this->netAmount );
	}

	public function getVatRate(): RepresentsVatRate
	{
		return $this->vatRate;
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->grossAmount->getCurrency();
	}

	public function multiply( float $quantity ): static
	{
		return static::fromBaseAmount( $this->getBaseAmount()->multiply( $quantity ), $this->vatRate );
	}

	public function divide( float $quantity ): static
	{
		return static::fromBaseAmount( $this->getBaseAmount()->divide( $quantity ), $this->vatRate );
	}

	/**
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): static
	{
		$this->validatePrice( $price );

		return static::fromBaseAmount(
			$this->getBaseAmount()->add( static::fromPrice( $price )->getBaseAmount() ),
			$this->resolveVatRate( $price )
		);
	}

	/**
	 * @throws InvalidPriceException
	 */
	public function subtract( RepresentsPrice $price ): static
	{
		$this->validatePrice( $price );

		return static::fromBaseAmount(
			$this->getBaseAmount()->subtract( static::fromPrice( $price )->getBaseAmount() ),
			$this->resolveVatRate( $price )
		);
	}

	/**
	 * @return \Iterator<int,static>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		return $this->combineAllocatedAmounts(
			$this->netAmount->allocateToTargets( $numberOfTargets ),
			$this->grossAmount->allocateToTargets( $numberOfTargets )
		);
	}

	/**
	 * @param array<int,int> $ratios
	 *
	 * @return \Iterator<int,static>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		return $this->combineAllocatedAmounts(
			$this->netAmount->allocateByRatios( $ratios ),
			$this->grossAmount->allocateByRatios( $ratios )
		);
	}

	/**
	 * @return array{currencyCode: string, netAmount: int, grossAmount: int, vatAmount: int, vatRate: int}
	 */
	public function jsonSerialize(): array
	{
		return [
			'currencyCode' => $this->getCurrency()->getIsoCode(),
			'netAmount'    => $this->netAmount->getAmount(),
			'grossAmount'  => $this->grossAmount->getAmount(),
			'vatAmount'    => $this->getVatAmount()->getAmount(),
			'vatRate'      => $this->vatRate->toInt(),
		];
	}

	/**
	 * @throws InvalidPriceException
	 */
	protected function validatePrice( RepresentsPrice $price ): void
	{
		if ( !$this->grossAmount->hasSameCurrency( $price->getGrossAmount() ) )
		{
			throw new InvalidPriceException(
				sprintf(
					"Currencies don't match (%s !== %s)",
					$this->getCurrency()->getIsoCode(),
					$price->getCurrency()->getIsoCode()
				)
			);
		}

		if ( $this->grossAmount->isZero() || $price->getGrossAmount()->isZero() )
		{
			return;
		}

		if ( !$this->vatRate->equals( $price->getVatRate() ) )
		{
			throw new InvalidPriceException(
				sprintf( "VAT rates don't match (%d !== %d)", $this->vatRate->toInt(), $price->getVatRate()->toInt() )
			);
		}
	}

	protected function resolveVatRate( RepresentsPrice $price ): RepresentsVatRate
	{
		return $this->grossAmount->isZero() ? $price->getVatRate() : $this->vatRate;
	}

	/**
	 * @param \Iterator<int,RepresentsMoney> $allocatedNetAmounts
	 * @param \Iterator<int,RepresentsMoney> $allocatedGrossAmounts
	 *
	 * @return \Iterator<int,static>
	 */
	private function combineAllocatedAmounts( \Iterator $allocatedNetAmounts, \Iterator $allocatedGrossAmounts ): \Iterator
	{
		$grossAmounts = iterator_to_array( $allocatedGrossAmounts, false );

		foreach ( iterator_to_array( $allocatedNetAmounts, false ) as $index => $netAmount )
		{
			yield new static( $netAmount, $grossAmounts[ $index ], $this->vatRate );
		}
	}

	private static function buildGrossMultiplier( RepresentsVatRate $vatRate ): float
	{
		return 1 + ($vatRate->toInt() / 10000);
	}
}
