<?php declare(strict_types=1);

namespace ComponoKit\Prices;

use ComponoKit\Money\Interfaces\BuildsMoneys;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Prices\Exceptions\InvalidPriceException;
use ComponoKit\Prices\Interfaces\RepresentsPrice;
use ComponoKit\Prices\Interfaces\RepresentsTotalPrice;
use ComponoKit\Prices\Interfaces\RepresentsVatRate;

class TotalPrice implements RepresentsTotalPrice, \JsonSerializable
{
	private readonly RepresentsMoney $initialMoney;

	/**
	 * @param array<int, RepresentsPrice> $prices
	 *
	 * @throws InvalidPriceException
	 */
	public function __construct( private readonly BuildsMoneys $moneyFactory, private readonly array $prices = [] )
	{
		$this->initialMoney = $this->moneyFactory->build( 0 );

		foreach ( $this->prices as $price )
		{
			$this->validateCurrency( $price );
		}
	}

	public static function fromTotalPrice( RepresentsTotalPrice $totalPrice ): static
	{
		return new static( $totalPrice->getMoneyFactory(), $totalPrice->getPrices() );
	}

	public function addTotalPrice( RepresentsTotalPrice $totalPrice ): static
	{
		$allPrices = $this->prices;

		foreach ( $totalPrice->getPrices() as $price )
		{
			$allPrices[] = $price;
		}

		return new static( $this->moneyFactory, $allPrices );
	}

	public function addPrice( RepresentsPrice $price ): static
	{
		$allPrices   = $this->prices;
		$allPrices[] = $price;

		return new static( $this->moneyFactory, $allPrices );
	}

	public function subtractPrice( RepresentsPrice $price ): static
	{
		return $this->addPrice( $price->multiply( -1 ) );
	}

	public function getTotalGrossAmount(): RepresentsMoney
	{
		return $this->sumAmounts( fn( RepresentsPrice $price ): RepresentsMoney => $price->getGrossAmount() );
	}

	public function getTotalNetAmount(): RepresentsMoney
	{
		return $this->sumAmounts( fn( RepresentsPrice $price ): RepresentsMoney => $price->getNetAmount() );
	}

	public function getTotalVatAmount(): RepresentsMoney
	{
		return $this->sumAmounts( fn( RepresentsPrice $price ): RepresentsMoney => $price->getVatAmount() );
	}

	/**
	 * @return RepresentsVatRate[]
	 */
	public function getVatRates(): array
	{
		$vatRates = [];

		foreach ( $this->prices as $price )
		{
			$vatRate                       = $price->getVatRate();
			$vatRates[ $vatRate->toInt() ] = $vatRate;
		}

		return array_values( $vatRates );
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->initialMoney->getCurrency();
	}

	/**
	 * @return array<int, RepresentsPrice[]> An array where keys are VAT rates (as integers)
	 *                                        and values are arrays of RepresentsPrice objects
	 */
	public function getPricesGroupedByVatRates(): array
	{
		$groupedPrices = [];

		foreach ( $this->prices as $price )
		{
			$groupedPrices[ $price->getVatRate()->toInt() ][] = $price;
		}

		return $groupedPrices;
	}

	/**
	 * @template TPrice of AbstractPrice
	 *
	 * @param class-string<TPrice> $priceClass
	 *
	 * @return array<int, TPrice>
	 */
	public function getTotalsGroupedByVatRates( string $priceClass ): array
	{
		if ( !is_subclass_of( $priceClass, AbstractPrice::class ) )
		{
			throw new \InvalidArgumentException(
				sprintf( '%s must extend %s', $priceClass, AbstractPrice::class )
			);
		}

		$totals = [];

		foreach ( $this->getPricesGroupedByVatRates() as $vatRate => $prices )
		{
			$total = $priceClass::fromPrice( array_shift( $prices ) );

			foreach ( $prices as $price )
			{
				$total = $total->add( $price );
			}

			$totals[ $vatRate ] = $total;
		}

		return $totals;
	}

	/**
	 * @return array<int, RepresentsPrice>
	 */
	public function getPrices(): array
	{
		return $this->prices;
	}

	public function getMoneyFactory(): BuildsMoneys
	{
		return $this->moneyFactory;
	}

	/**
	 * @return array{currencyCode: string, prices: object}
	 */
	public function jsonSerialize(): array
	{
		$pricesGroupedByVatRates = [];

		foreach ( $this->getPricesGroupedByVatRates() as $vatRate => $prices )
		{
			foreach ( $prices as $price )
			{
				$pricesGroupedByVatRates[ $vatRate ][] = [
					'grossAmount' => $price->getGrossAmount()->getAmount(),
					'netAmount'   => $price->getNetAmount()->getAmount(),
					'vatAmount'   => $price->getVatAmount()->getAmount(),
				];
			}
		}

		return [
			'currencyCode' => $this->getCurrency()->getIsoCode(),
			'prices'       => (object)$pricesGroupedByVatRates,
		];
	}

	/**
	 * @param callable(RepresentsPrice): RepresentsMoney $selectAmount
	 */
	private function sumAmounts( callable $selectAmount ): RepresentsMoney
	{
		$totalAmount = $this->initialMoney;

		foreach ( $this->prices as $price )
		{
			$totalAmount = $totalAmount->add( $selectAmount( $price ) );
		}

		return $totalAmount;
	}

	private function validateCurrency( RepresentsPrice $price ): void
	{
		if ( !$this->initialMoney->hasSameCurrency( $price->getGrossAmount() ) )
		{
			throw new InvalidPriceException(
				sprintf(
					'Price currency %s does not match total price currency %s',
					$price->getCurrency()->getIsoCode(),
					$this->getCurrency()->getIsoCode()
				)
			);
		}
	}
}
