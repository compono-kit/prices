<?php declare(strict_types=1);

namespace Componium\Prices\Interfaces;

use Componium\Prices\Exceptions\InvalidPriceException;

interface RepresentsPrice
{
	public function getNetAmount(): RepresentsMoney;

	public function getGrossAmount(): RepresentsMoney;

	public function getVatAmount(): RepresentsMoney;

	public function getVatRate(): RepresentsVatRate;

	public function getCurrency(): RepresentsCurrency;

	public function multiply( float $quantity ): RepresentsPrice;

	public function divide( float $quantity ): RepresentsPrice;

	/**
	 * @param RepresentsPrice $price
	 *
	 * @return RepresentsPrice
	 *
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): RepresentsPrice;

	/**
	 * @param RepresentsPrice $price
	 *
	 * @return RepresentsPrice
	 *
	 * @throws InvalidPriceException
	 */
	public function subtract( RepresentsPrice $price ): RepresentsPrice;

	/**
	 * @param int $targetCount
	 *
	 * @return \Iterator|RepresentsPrice[]
	 */
	public function allocateToTargets( int $targetCount ): \Iterator;

	/**
	 * @param array|int[] $ratios
	 *
	 * @return \Iterator|RepresentsPrice[]
	 */
	public function allocateByRatios( array $ratios ): \Iterator;
}
