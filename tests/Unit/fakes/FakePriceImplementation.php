<?php declare(strict_types=1);

namespace ComponoKit\Prices\Tests\Unit\fakes;

use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\AbstractPrice;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class FakePriceImplementation extends AbstractPrice
{
	protected function getBaseAmount(): RepresentsMoney
	{
		return $this->grossAmount;
	}

	protected static function fromBaseAmount( RepresentsMoney $baseAmount, RepresentsVatRate $vatRate ): static
	{
		return static::fromGrossAmount( $baseAmount, $vatRate );
	}
}
