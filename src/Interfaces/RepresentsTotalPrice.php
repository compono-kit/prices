<?php declare(strict_types=1);

namespace Hansel23\Prices\Interfaces;

use Money\Currency;
use Money\Money;

interface RepresentsTotalPrice
{
	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): RepresentsTotalPrice;

	public function addPrice( RepresentsPrice $price ): RepresentsTotalPrice;

	public function getTotalGrossAmount(): Money;

	public function getTotalNetAmount(): Money;

	public function getTotalVatAmount(): Money;

	/**
	 * @return RepresentsVatRate[]
	 */
	public function getVatRates(): array;

	public function getCurrency(): Currency;

	/**
	 * @return RepresentsPrice[][] (int[] => RepresentsPrice)
	 */
	public function getPricesGroupedByVatRates(): array;

	/**
	 * @return RepresentsPrice[]
	 */
	public function getPrices(): array;
}
