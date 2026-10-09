<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Prices\Exceptions\InvalidVatRateException;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class VatRate implements RepresentsVatRate
{
	private readonly int $hundredthsOfPercent;

	public function __construct( float $value )
	{
		$this->validate( $value );

		$this->hundredthsOfPercent = (int)round( $value * 100 );
	}

	public static function fromInt( int $value ): static
	{
		return new static( $value / 100 );
	}

	public function toFloat(): float
	{
		return $this->hundredthsOfPercent / 100;
	}

	public function toInt(): int
	{
		return $this->hundredthsOfPercent;
	}

	public function __toString(): string
	{
		return (string)$this->toFloat();
	}

	public function equals( RepresentsVatRate $vatRate ): bool
	{
		return $this->hundredthsOfPercent === $vatRate->toInt();
	}

	public function compare( RepresentsVatRate $vatRate ): int
	{
		return $this->hundredthsOfPercent <=> $vatRate->toInt();
	}

	public function greaterThan( RepresentsVatRate $vatRate ): bool
	{
		return $this->compare( $vatRate ) > 0;
	}

	public function greaterThanOrEqual( RepresentsVatRate $vatRate ): bool
	{
		return $this->compare( $vatRate ) >= 0;
	}

	public function lessThan( RepresentsVatRate $vatRate ): bool
	{
		return $this->compare( $vatRate ) < 0;
	}

	public function lessThanOrEqual( RepresentsVatRate $vatRate ): bool
	{
		return $this->compare( $vatRate ) <= 0;
	}

	private function validate( float $value ): void
	{
		if ( $value < 0 )
		{
			throw new InvalidVatRateException( 'VAT rate must not be negative' );
		}
	}
}
