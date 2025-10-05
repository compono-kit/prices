<?php declare(strict_types=1);

namespace Componium\Prices\Interfaces;

interface RepresentsTotalPrice
{
	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): RepresentsTotalPrice;

	public function addPrice( RepresentsPrice $price ): RepresentsTotalPrice;

	public function getTotalGrossAmount(): RepresentsMoney;

	public function getTotalNetAmount(): RepresentsMoney;

	public function getTotalVatAmount(): RepresentsMoney;

	/**
	 * @return RepresentsVatRate[]
	 */
	public function getVatRates(): array;

	public function getCurrency(): RepresentsCurrency;

	/**
	 * @return RepresentsPrice[][] (int[] => RepresentsPrice)
	 */
	public function getPricesGroupedByVatRates(): array;

	/**
	 * @return RepresentsPrice[]
	 */
	public function getPrices(): array;
}
