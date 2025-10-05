<?php declare(strict_types=1);

namespace ComponoKit\Prices\Interfaces;

use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\Exceptions\InvalidPriceException;

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
	 * @throws InvalidPriceException
	 */
	public function add( RepresentsPrice $price ): RepresentsPrice;

	/**
	 * @throws InvalidPriceException
	 */
	public function subtract( RepresentsPrice $price ): RepresentsPrice;

	/**
	 * @return \Iterator<int,RepresentsPrice>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator;

	/**
	 * @param array<int,int> $ratios
	 *
	 * @return \Iterator<int,RepresentsPrice>
	 */
	public function allocateByRatios( array $ratios ): \Iterator;
}
