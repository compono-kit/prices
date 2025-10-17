<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit\fakes;

use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Interfaces\RepresentsTotalPrice;
use Money\Currency;
use Money\Money;

class FakeTotalPrice implements RepresentsTotalPrice
{
	/** @var RepresentsPrice[] */
	private array    $prices;

	private Currency $currency;

	/**
	 * @param Currency          $currency
	 * @param RepresentsPrice[] $prices
	 */
	public function __construct( Currency $currency, array $prices = [] )
	{
		$this->currency = $currency;
		$this->prices   = $prices;
	}

	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): RepresentsTotalPrice
	{
		return $this;
	}

	public function addPrice( RepresentsPrice $price ): RepresentsTotalPrice
	{
		return $this;
	}

	public function getTotalGrossAmount(): Money
	{
		return new Money( 0, new Currency( 'EUR' ) );
	}

	public function getTotalNetAmount(): Money
	{
		return new Money( 0, new Currency( 'EUR' ) );
	}

	public function getTotalVatAmount(): Money
	{
		return new Money( 0, new Currency( 'EUR' ) );
	}

	public function getVatRates(): array
	{
		return [];
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function getPricesGroupedByVatRates(): array
	{
		return [];
	}

	public function getPrices(): array
	{
		return $this->prices;
	}
}
