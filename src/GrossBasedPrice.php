<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class GrossBasedPrice extends AbstractPrice
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
