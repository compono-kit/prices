<?php declare(strict_types=1);

namespace Componium\Prices;

use Componium\Prices\Exceptions\InvalidVatRateException;
use Componium\Prices\Interfaces\RepresentsVatRate;

class VatRate implements RepresentsVatRate
{
	private float $value;

	public function __construct( float $value )
	{
		$this->validate( $value );

		$this->value = $value;
	}

	public function toFloat(): float
	{
		return $this->value;
	}

	public function __toString(): string
	{
		return (string)$this->value;
	}

	/**
	 * @param int $value
	 *
	 * @return static
	 */
	public static function fromInt( int $value ): self
	{
		return new static( $value / 100 );
	}

	public function toInt(): int
	{
		return (int)($this->value * 100);
	}

	public function equals( RepresentsVatRate $vatRate ): bool
	{
		return $this->value === $vatRate->toFloat();
	}

	public function compare( RepresentsVatRate $vatRate ): int
	{
		return $this->value <=> $vatRate->toFloat();
	}

	public function greaterThan( RepresentsVatRate $vatRate ): bool
	{
		return $this->value > $vatRate->toFloat();
	}

	public function greaterThanOrEqual( RepresentsVatRate $vatRate ): bool
	{
		return $this->value >= $vatRate->toFloat();
	}

	public function lessThan( RepresentsVatRate $vatRate ): bool
	{
		return $this->value < $vatRate->toFloat();
	}

	public function lessThanOrEqual( RepresentsVatRate $vatRate ): bool
	{
		return $this->value <= $vatRate->toFloat();
	}

	private function validate( float $value ): void
	{
		if ( $value < 0 )
		{
			throw new InvalidVatRateException( 'VAT rate must not be negative' );
		}
	}
}
