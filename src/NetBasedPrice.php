<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class NetBasedPrice extends AbstractPrice
{
	protected function getBaseAmount(): RepresentsMoney
	{
		return $this->netAmount;
	}

	protected static function fromBaseAmount( RepresentsMoney $baseAmount, RepresentsVatRate $vatRate ): static
	{
		return static::fromNetAmount( $baseAmount, $vatRate );
	}
}
