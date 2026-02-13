<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Prices\Exceptions\InvalidVatRateException;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class VatRate implements RepresentsVatRate
{
	public function __construct( private readonly float $value )
	{
		$this->validate( $value );
	}

	public function toFloat(): float
	{
		return $this->value;
	}

	public function __toString(): string
	{
		return (string)$this->value;
	}

	public static function fromInt( int $value ): static
	{
		return new static( $value / 100 );
	}

	public function toInt(): int
	{
		return (int)(round( $this->value * 100 ));
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
