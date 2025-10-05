<?php declare(strict_types=1);

namespace ComponoKit\Prices\Interfaces;

interface RepresentsVatRate
{
	public function toFloat(): float;

	public function toInt(): int;

	public function equals( RepresentsVatRate $vatRate ): bool;

	public function compare( RepresentsVatRate $vatRate ): int;

	public function greaterThan( RepresentsVatRate $vatRate ): bool;

	public function greaterThanOrEqual( RepresentsVatRate $vatRate ): bool;

	public function lessThan( RepresentsVatRate $vatRate ): bool;

	public function lessThanOrEqual( RepresentsVatRate $vatRate ): bool;
}
