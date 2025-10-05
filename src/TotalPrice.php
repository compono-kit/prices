<?php declare(strict_types=1);

namespace Componium\Prices;

use Componium\Prices\Interfaces\RepresentsCurrency;
use Componium\Prices\Interfaces\RepresentsMoney;
use Componium\Prices\Interfaces\RepresentsPrice;
use Componium\Prices\Interfaces\RepresentsTotalPrice;
use Componium\Prices\Interfaces\RepresentsVatRate;
use JetBrains\PhpStorm\Pure;
use Money\Currency;
use Money\Money;

class TotalPrice implements RepresentsTotalPrice, \JsonSerializable
{
	/**
	 * @param RepresentsCurrency          $currency
	 * @param array<int, RepresentsPrice> $prices
	 */
	public function __construct( private readonly RepresentsCurrency $currency, private readonly array $prices = [] )
	{
	}

	/**
	 * @param RepresentsTotalPrice $totalPrice
	 *
	 * @return static
	 */
	public static function fromTotalPrice( RepresentsTotalPrice $totalPrice ): RepresentsTotalPrice
	{
		return new static( $totalPrice->getCurrency(), $totalPrice->getPrices() );
	}

	/**
	 * @param RepresentsTotalPrice $totalPrice
	 *
	 * @return static
	 */
	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): RepresentsTotalPrice
	{
		$allPrices = $this->prices;

		foreach ( $totalPrice->getPrices() as $price )
		{
			$allPrices[] = $price;
		}

		return new static( $this->currency, $allPrices );
	}

	/**
	 * @param RepresentsPrice $price
	 *
	 * @return static
	 */
	#[Pure]
	public function addPrice( RepresentsPrice $price ): self
	{
		$allPrices   = $this->prices;
		$allPrices[] = $price;

		return new static( $this->currency, $allPrices );
	}

	public function getTotalGrossAmount(): RepresentsMoney
	{
		$totalGrossAmount = new Money( 0, $this->currency );
		foreach ( $this->prices as $price )
		{
			$totalGrossAmount = $totalGrossAmount->add( $price->getGrossAmount() );
		}

		return $totalGrossAmount;
	}

	public function getTotalNetAmount(): RepresentsMoney
	{
		$totalNetAmount = new Money( 0, $this->currency );
		foreach ( $this->prices as $price )
		{
			$totalNetAmount = $totalNetAmount->add( $price->getNetAmount() );
		}

		return $totalNetAmount;
	}

	public function getTotalVatAmount(): RepresentsMoney
	{
		$totalVatAmount = new Money( 0, new Currency( $this->currency->getIsoCode() ) );
		foreach ( $this->prices as $price )
		{
			$totalVatAmount = $totalVatAmount->add( $price->getVatAmount() );
		}

		return $totalVatAmount;
	}

	/**
	 * @return RepresentsVatRate[]
	 */
	public function getVatRates(): array
	{
		$vatRates = [];

		foreach ( $this->prices as $price )
		{
			$vatRate                       = $price->getVatRate();
			$vatRates[ $vatRate->toInt() ] = $vatRate;
		}

		return array_values( $vatRates );
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->currency;
	}

	/**
	 * @return RepresentsPrice[][] (int[] => RepresentsPrice)
	 */
	public function getPricesGroupedByVatRates(): array
	{
		$groupedPrices = [];

		foreach ( $this->prices as $price )
		{
			$groupedPrices[ $price->getVatRate()->toInt() ][] = $price;
		}

		return $groupedPrices;
	}

	/**
	 * @return RepresentsPrice[]
	 */
	public function getPrices(): array
	{
		return $this->prices;
	}

	public function jsonSerialize(): array
	{
		$data = [];
		foreach ( $this->getPricesGroupedByVatRates() as $vatRate => $prices )
		{
			foreach ( $prices as $price )
			{
				$data[ $vatRate ][] = [
					'gross' => $price->getGrossAmount(),
					'net'   => $price->getNetAmount(),
					'vat'   => $price->getVatAmount(),
				];
			}
		}

		return $data;
	}
}
