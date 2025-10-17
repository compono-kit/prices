<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit\fakes;

use ComponoKit\Prices\AbstractPrice;
use ComponoKit\Prices\GrossBasedPrice;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\VatRate;
use Money\Currency;
use Money\Money;

class FakePriceImplementation extends AbstractPrice
{
	public function multiply( float $quantity ): RepresentsPrice
	{
		return GrossBasedPrice::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 0 ) );
	}

	public function divide( float $quantity ): RepresentsPrice
	{
		return GrossBasedPrice::fromGrossAmount( new Money( 0, new Currency( 'EUR' ) ), new VatRate( 0 ) );
	}

	public function add( RepresentsPrice $price ): RepresentsPrice
	{
		return $price;
	}

	public function subtract( RepresentsPrice $price ): RepresentsPrice
	{
		return $price;
	}

	public function allocateToTargets( int $targetCount ): \Iterator|array
	{
		yield;
	}

	public function allocateByRatios( array $ratios ): \Iterator
	{
		yield;
	}
}
