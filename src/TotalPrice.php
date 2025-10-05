<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Money;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Interfaces\RepresentsTotalPrice;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;
use ComponoKit\Money\Interfaces\RepresentsMoney;

class TotalPrice implements RepresentsTotalPrice, \JsonSerializable
{
	/**
	 * @param array<int, RepresentsPrice> $prices
	 */
	public function __construct( private readonly RepresentsCurrency $currency, private readonly array $prices = [] )
	{
	}

	public static function fromTotalPrice( RepresentsTotalPrice $totalPrice ): static
	{
		return new static( $totalPrice->getCurrency(), $totalPrice->getPrices() );
	}

	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): static
	{
		$allPrices = $this->prices;

		foreach ( $totalPrice->getPrices() as $price )
		{
			$allPrices[] = $price;
		}

		return new static( $this->currency, $allPrices );
	}

	public function addPrice( RepresentsPrice $price ): static
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
		$totalVatAmount = new Money( 0, $this->currency );
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
	 * @return array<int, array<int, RepresentsPrice>>
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
	 * @return array<int, RepresentsPrice>
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
