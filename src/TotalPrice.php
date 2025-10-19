<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Money\Interfaces\BuildsMoneys;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Interfaces\RepresentsTotalPrice;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class TotalPrice implements RepresentsTotalPrice, \JsonSerializable
{
	private readonly RepresentsMoney $initialMoney;

	/**
	 * @param array<int, RepresentsPrice> $prices
	 */
	public function __construct( private readonly BuildsMoneys $moneyFactory, private readonly array $prices = [] )
	{
		$this->initialMoney = $this->moneyFactory->build( 0 );
	}

	public static function fromTotalPrice( RepresentsTotalPrice $totalPrice ): static
	{
		return new static( $totalPrice->getMoneyFactory(), $totalPrice->getPrices() );
	}

	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): static
	{
		$allPrices = $this->prices;

		foreach ( $totalPrice->getPrices() as $price )
		{
			$allPrices[] = $price;
		}

		return new static( $this->moneyFactory, $allPrices );
	}

	public function addPrice( RepresentsPrice $price ): static
	{
		$allPrices   = $this->prices;
		$allPrices[] = $price;

		return new static( $this->moneyFactory, $allPrices );
	}

	public function getTotalGrossAmount(): RepresentsMoney
	{
		$totalGrossAmount = $this->moneyFactory->build( 0 );
		foreach ( $this->prices as $price )
		{
			$totalGrossAmount = $totalGrossAmount->add( $price->getGrossAmount() );
		}

		return $totalGrossAmount;
	}

	public function getTotalNetAmount(): RepresentsMoney
	{
		$totalNetAmount = $this->initialMoney;
		foreach ( $this->prices as $price )
		{
			$totalNetAmount = $totalNetAmount->add( $price->getNetAmount() );
		}

		return $totalNetAmount;
	}

	public function getTotalVatAmount(): RepresentsMoney
	{
		$totalVatAmount = $this->initialMoney;
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
		return $this->initialMoney->getCurrency();
	}

	/**
	 * @return array<int, RepresentsPrice[]> An array where keys are VAT rates (as integers)
	 *                                        and values are arrays of RepresentsPrice objects
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

	public function getMoneyFactory(): BuildsMoneys
	{
		return $this->moneyFactory;
	}

	public function jsonSerialize(): array
	{
		$data = [ 'currency-code' => $this->initialMoney->getCurrency()->getIsoCode() ];

		foreach ( $this->getPricesGroupedByVatRates() as $vatRate => $prices )
		{
			foreach ( $prices as $price )
			{
				$data['prices'][ $vatRate ][] = [
					'gross' => $price->getGrossAmount()->getAmount(),
					'net'   => $price->getNetAmount()->getAmount(),
					'vat'   => $price->getVatAmount()->getAmount(),
				];
			}
		}

		return $data;
	}
}
