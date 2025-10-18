<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit\fakes;

use ComponoKit\Prices\AbstractPrice;
use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\VatRate;

class FakePriceImplementation extends AbstractPrice
{
	use BuildingFakeMoneys;

	public function multiply( float $quantity ): RepresentsPrice
	{
		return GrossBasedPrice::fromGrossAmount(
			$this->buildMoney(
				(int)($this->grossAmount->getAmount() * $quantity),
				$this->getCurrency()->getIsoCode()
			),
			new VatRate( 0 )
		);
	}

	public function divide( float $quantity ): RepresentsPrice
	{
		return GrossBasedPrice::fromGrossAmount(
			$this->buildMoney(
				(int)round( $this->grossAmount->getAmount() / $quantity, 0, PHP_ROUND_HALF_UP ),
				$this->getCurrency()->getIsoCode()
			),
			new VatRate( 0 )
		);
	}

	public function add( RepresentsPrice $price ): RepresentsPrice
	{
		return $price;
	}

	public function subtract( RepresentsPrice $price ): RepresentsPrice
	{
		return $price;
	}

	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		yield;
	}

	public function allocateByRatios( array $ratios ): \Iterator
	{
		yield;
	}
}
