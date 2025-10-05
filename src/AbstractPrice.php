<?php declare(strict_types=1);

namespace Componium\Prices;

use Componium\Prices\Exceptions\InvalidPriceException;
use Componium\Prices\Interfaces\RepresentsMoney;
use Componium\Prices\Interfaces\RepresentsPrice;
use Componium\Prices\Interfaces\RepresentsVatRate;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Money\Currency;
use Money\Money;

abstract class AbstractPrice implements RepresentsPrice, \JsonSerializable
{
	protected RepresentsMoney   $netAmount;

	protected RepresentsMoney   $grossAmount;

	protected RepresentsVatRate $vatRate;

	final protected function __construct( RepresentsMoney $netAmount, RepresentsMoney $grossAmount, RepresentsVatRate $vatRate )
	{
		$this->netAmount   = $netAmount;
		$this->grossAmount = $grossAmount;
		$this->vatRate     = $vatRate;
	}

	/**
	 * @param RepresentsMoney   $netAmount
	 * @param RepresentsVatRate $vatRate
	 *
	 * @return static
	 */
	public static function fromNetAmount( RepresentsMoney $netAmount, RepresentsVatRate $vatRate ): self
	{
		return new static(
			$netAmount,
			$netAmount->multiply( 1 + ($vatRate->toFloat() / 100) ),
			$vatRate
		);
	}

	/**
	 * @param Money   $grossAmount
	 * @param RepresentsVatRate $vatRate
	 *
	 * @return static
	 */
	public static function fromGrossAmount( Money $grossAmount, RepresentsVatRate $vatRate ): self
	{
		return new static(
			$grossAmount->divide( 1 + ($vatRate->toFloat() / 100) ),
			$grossAmount,
			$vatRate
		);
	}

	/**
	 * @param RepresentsPrice $price
	 *
	 * @return static
	 */
	public static function fromPrice( RepresentsPrice $price ): self
	{
		return new static( $price->getNetAmount(), $price->getGrossAmount(), $price->getVatRate() );
	}

	public function getNetAmount(): Money
	{
		return $this->netAmount;
	}

	public function getGrossAmount(): Money
	{
		return $this->grossAmount;
	}

	public function getVatAmount(): Money
	{
		return $this->grossAmount->subtract( $this->getNetAmount() );
	}

	public function getVatRate(): RepresentsVatRate
	{
		return $this->vatRate;
	}

	#[Pure] public function getCurrency(): Currency
	{
		return $this->grossAmount->getCurrency();
	}

	#[ArrayShape([ 'currency' => "string", 'netAmount' => "string", 'grossAmount' => "string", 'vatAmount' => "string", 'vatRate' => "float" ])] public function jsonSerialize(): array
	{
		return [
			'currency'    => $this->getGrossAmount()->getCurrency()->getCode(),
			'netAmount'   => $this->getNetAmount()->getAmount(),
			'grossAmount' => $this->getGrossAmount()->getAmount(),
			'vatAmount'   => $this->getVatAmount()->getAmount(),
			'vatRate'     => $this->getVatRate()->toFloat(),
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
