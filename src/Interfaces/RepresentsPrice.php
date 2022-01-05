<?php declare(strict_types=1);

namespace Hansel23\Prices\Interfaces;

use Hansel23\Prices\Exceptions\InvalidPriceException;
use Money\Currency;
use Money\Money;

interface RepresentsPrice
{
	public function getNetAmount(): Money;

	public function getGrossAmount(): Money;

	public function getVatAmount(): Money;

	public function getVatRate(): RepresentsVatRate;

	public function getCurrency(): Currency;

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
