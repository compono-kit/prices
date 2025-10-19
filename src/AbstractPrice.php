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

	public static function fromNetAmount( RepresentsMoney $netAmount, RepresentsVatRate $vatRate ): static
	{
		return new static(
			$netAmount,
			$netAmount->multiply( 1 + ($vatRate->toFloat() / 100) ),
			$vatRate
		);
	}

	public static function fromGrossAmount( RepresentsMoney $grossAmount, RepresentsVatRate $vatRate ): static
	{
		return new static(
			$grossAmount->divide( 1 + ($vatRate->toFloat() / 100) ),
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
		return $this->grossAmount->subtract( $this->getNetAmount() );
	}

	public function getVatRate(): RepresentsVatRate
	{
		return $this->vatRate;
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->grossAmount->getCurrency();
	}

	public function jsonSerialize(): array
	{
		return [
			'currency-code'    => $this->getGrossAmount()->getCurrency()->getIsoCode(),
			'netAmount'   => $this->getNetAmount()->getAmount(),
			'grossAmount' => $this->getGrossAmount()->getAmount(),
			'vatAmount'   => $this->getVatAmount()->getAmount(),
			'vatRate'     => $this->getVatRate()->toInt(),
		];
	}

	protected function validatePrice( RepresentsPrice $price ): void
	{
		$vatRate = $this->vatRate->toInt();
		if ( $vatRate > 0 && $price->getVatRate()->toInt() !== $vatRate )
		{
			throw new InvalidPriceException(
				sprintf( "Vat rates doesn't match (%d !== %d)", $this->vatRate->toInt(), $price->getVatRate()->toInt() )
			);
		}
	}
}
