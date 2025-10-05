<?php declare(strict_types=1);

namespace Componium\Prices;

use Componium\Prices\Exceptions\InvalidCurrencyException;
use Componium\Prices\Interfaces\RepresentsCurrency;

class Currency implements RepresentsCurrency
{
	private readonly string $isoCode;

	public function __construct( string $isoCode, private readonly string $symbol, private readonly int $minorUnitFactor )
	{
		if ( !preg_match( '/^[A-Z]{3}$/', strtoupper( $isoCode ) ) )
		{
			throw new InvalidCurrencyException( 'ISO code must be exactly 3 letters (A–Z)' );
		}

		if ( $this->minorUnitFactor <= 0 )
		{
			throw new InvalidCurrencyException( 'Minor unit factor must be greater than 0' );
		}

		$this->isoCode = strtoupper( $isoCode );
	}

	public function getIsoCode(): string
	{
		return $this->isoCode;
	}

	public function getSymbol(): string
	{
		return $this->symbol;
	}

	public function getMinorUnitFactor(): int
	{
		return $this->minorUnitFactor;
	}

	public function equals( Currency $other ): bool
	{
		return $this->isoCode === $other->isoCode;
	}
}
