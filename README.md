# Prices

PHP types representing prices. A price includes the gross, net and VAT amount as well as the VAT rate. Missing values are calculated depending on the instantiation method.

## Contents

* [Requirements](#requirements)
* [Installation](#installation)
* [Instantiation](#instantiation)
* [VatRate](#vatrate)
* [Multiplication and division of prices](#multiplication-and-division-of-prices)
* [Addition and subtraction](#addition-and-subtraction)
* [Allocation](#allocation)
* [TotalPrice](#totalprice)
* [Json](#json)
* [Exceptions](#exceptions)
* [Upgrade from 2.x](#upgrade-from-2x)

## Requirements

* PHP >= 8.3
* compono-kit/money-interfaces
* compono-kit/price-interfaces

## Installation 📦

```bash
composer require compono-kit/prices
```

## Instantiation

Prices are created by named constructors only. The constructor is not public.

You have the gross amount and the VAT rate

````PHP
$gross   = new Money( 1990, new EUR() );
$vatRate = new VatRate( 19 );

$price = GrossBasedPrice::fromGrossAmount( $gross, $vatRate );
$price->getGrossAmount(); //new Money( 1990, new EUR() )
$price->getNetAmount(); //new Money( 1672, new EUR() )
$price->getVatAmount(); //new Money( 318, new EUR() )
$price->getVatRate(); //new VatRate( 19 )
````

You have the net amount and the VAT rate

````PHP
$net     = new Money( 1672, new EUR() );
$vatRate = new VatRate( 19 );

$price = GrossBasedPrice::fromNetAmount( $net, $vatRate );
$price->getGrossAmount(); //new Money( 1990, new EUR() )
$price->getNetAmount(); //new Money( 1672, new EUR() )
$price->getVatAmount(); //new Money( 318, new EUR() )
$price->getVatRate(); //new VatRate( 19 )
````

You want a new price type by another price type

````PHP
$grossBasedPrice = GrossBasedPrice::fromGrossAmount( new Money( 1990, new EUR() ), new VatRate( 19 ) );
$netBasedPrice   = NetBasedPrice::fromPrice( $grossBasedPrice );

$netBasedPrice->getGrossAmount(); //new Money( 1990, new EUR() )
$netBasedPrice->getNetAmount(); //new Money( 1672, new EUR() )
$netBasedPrice->getVatAmount(); //new Money( 318, new EUR() )
$netBasedPrice->getVatRate(); //new VatRate( 19 )
````

----
In some circumstances, it may matter whether the price is generated from the net or gross amount.

Example:

* VAT rate: 19 %
* Gross amount: 9,99 EUR
    * Calculated net amount: 8,39 EUR (9,99 / 1,19 = rounded 8,39)
* Net amount: 8,39 EUR
    * Calculated gross amount: 9,98 EUR (8,39 * 1,19 = rounded 9,98)

A method `fromNetAndGrossAmount`, which calculates the VAT rate, does not exist because the calculation is not reliable. There are countries with VAT rates that have decimal places. If these are
taken into account, rounding can result in incorrect VAT rates.

Example:

* Gross: 9,99 EUR
* Net: 8,39 EUR
* Expected vat rate: 19,00 %
* Calculated vat rate, after rounding and with 2 decimal places: 19,07 %

## VatRate

You can instantiate the `VatRate` by a float value or by an integer value. The integer value must be the float value multiplied by 100. The following example generates the same VAT rate. The VAT rate
is 21,70 %.

````PHP
$vatRateByFloat = new VatRate( 21.7 );
$vatRateByInt   = VatRate::fromInt( 2170 );
$vatRateByFloat->equals( $vatRateByInt ); //true
````

The value is normalized to two decimal places. Comparison and price calculation always use the normalized value.

````PHP
$vatRate = new VatRate( 22.3589 );
$vatRate->toFloat(); //22.36
$vatRate->toInt(); //2236
$vatRate->equals( VatRate::fromInt( 2236 ) ); //true
````

## Multiplication and division of prices

There are two ways to calculate VAT when multiplying by the quantity. This is the difference between `GrossBasedPrice` and `NetBasedPrice`.

### `NetBasedPrice`

VAT is calculated after multiplying the unit price by the quantity.

Example: `90,82 € * 10 = 908,20 € * 1,19 = 1080,76 €`

````PHP
$unitPrice  = NetBasedPrice::fromGrossAmount( new Money( 10808, new EUR() ), new VatRate( 19 ) );
$totalPrice = $unitPrice->multiply( 10 );

$unitPrice->getGrossAmount(); //new Money( 10808, new EUR() )
$unitPrice->getNetAmount(); //new Money( 9082, new EUR() )
$totalPrice->getGrossAmount(); //new Money( 108076, new EUR() )
$totalPrice->getNetAmount(); //new Money( 90820, new EUR() )
````

### `GrossBasedPrice`

First, the VAT is calculated on the unit price and then multiplied by the quantity.

Example: `90,82 € * 1,19 = 108,08 € * 10 = 1080,80 €`

````PHP
$unitPrice  = GrossBasedPrice::fromNetAmount( new Money( 9082, new EUR() ), new VatRate( 19 ) );
$totalPrice = $unitPrice->multiply( 10 );

$unitPrice->getGrossAmount(); //new Money( 10808, new EUR() )
$unitPrice->getNetAmount(); //new Money( 9082, new EUR() )
$totalPrice->getGrossAmount(); //new Money( 108080, new EUR() )
$totalPrice->getNetAmount(); //new Money( 90824, new EUR() )
````

---
Division works like multiplication, except of course you divide instead of multiply. Both methods return an instance of the same class.

## Addition and subtraction

A single price has exactly one VAT rate. Therefore only prices with the same VAT rate and the same currency can be added or subtracted. A `GrossBasedPrice` adds the gross amounts, a `NetBasedPrice` adds the net amounts.

````PHP
$price = GrossBasedPrice::fromGrossAmount( new Money( 1000, new EUR() ), new VatRate( 19 ) );

$sum = $price->add( GrossBasedPrice::fromGrossAmount( new Money( 1000, new EUR() ), new VatRate( 19 ) ) );
$sum->getGrossAmount(); //new Money( 2000, new EUR() )

$difference = $price->subtract( GrossBasedPrice::fromGrossAmount( new Money( 1000, new EUR() ), new VatRate( 19 ) ) );
$difference->getGrossAmount(); //new Money( 0, new EUR() )
````

A price with an amount of zero is neutral, regardless of its VAT rate. The result has the VAT rate of the price that is not zero.

````PHP
$zero  = GrossBasedPrice::fromGrossAmount( new Money( 0, new EUR() ), new VatRate( 0 ) );
$price = GrossBasedPrice::fromGrossAmount( new Money( 119, new EUR() ), new VatRate( 19 ) );

$zero->add( $price )->getVatRate(); //new VatRate( 19 )
$price->add( $zero )->getVatRate(); //new VatRate( 19 )
````

To sum up prices with different VAT rates, e.g. a shopping cart, use [TotalPrice](#totalprice).

## Allocation

`allocateToTargets` and `allocateByRatios` allocate the net and the gross amount with the same ratios. The sums of the allocated net and gross amounts are always equal to the original amounts. A single
part may therefore differ by one minor unit from the exact VAT rate.

````PHP
$price = GrossBasedPrice::fromGrossAmount( new Money( 100, new EUR() ), new VatRate( 19 ) );

foreach ( $price->allocateToTargets( 3 ) as $allocatedPrice )
{
    $allocatedPrice->getGrossAmount(); //34, 33, 33
    $allocatedPrice->getNetAmount(); //28, 28, 28
}
````

## TotalPrice

While `RepresentsPrice` (or the implementation of it) is used primarily for the prices of order items, `RepresentsTotalPrice` (or the implementation of it) is used as the total price of an order or a shopping
cart. It may contain prices with different VAT rates. All prices must have the currency of the money factory.

`TotalPrice` is immutable. Every adding method returns a new instance.

````PHP
$totalPrice = new TotalPrice(
    new MoneyFactory( new EUR() ),
    [
        GrossBasedPrice::fromGrossAmount( new Money( 100, new EUR() ), new VatRate( 19 ) ),
        NetBasedPrice::fromGrossAmount( new Money( 300, new EUR() ), new VatRate( 7 ) ),
    ]
);
$totalPrice = $totalPrice->addPrice( GrossBasedPrice::fromGrossAmount( new Money( 100, new EUR() ), new VatRate( 16.5 ) ) );

$anotherTotalPrice = new TotalPrice(
    new MoneyFactory( new EUR() ),
    [
        NetBasedPrice::fromGrossAmount( new Money( 200, new EUR() ), new VatRate( 16.5 ) ),
        GrossBasedPrice::fromGrossAmount( new Money( 300, new EUR() ), new VatRate( 16.5 ) ),
    ]
);
$totalPrice = $totalPrice->addTotalPrice( $anotherTotalPrice );

$totalPrice->getPrices(); //All five prices
$totalPrice->getTotalGrossAmount(); //new Money( 1000, new EUR() ) (100 + 300 + 100 + 200 + 300)
$totalPrice->getTotalNetAmount(); //new Money( 880, new EUR() ) (84 + 280 + 86 + 172 + 258)
$totalPrice->getTotalVatAmount(); //new Money( 120, new EUR() ) (1000 - 880)
````

Prices can be subtracted, e.g. for a discount or a credit. The negated price is added to the list.

````PHP
$totalPrice = (new TotalPrice( new MoneyFactory( new EUR() ) ))
    ->addPrice( GrossBasedPrice::fromGrossAmount( new Money( 119, new EUR() ), new VatRate( 19 ) ) )
    ->addPrice( GrossBasedPrice::fromGrossAmount( new Money( 107, new EUR() ), new VatRate( 7 ) ) )
    ->subtractPrice( GrossBasedPrice::fromGrossAmount( new Money( 119, new EUR() ), new VatRate( 19 ) ) );

$totalPrice->getTotalGrossAmount(); //new Money( 107, new EUR() )
$totalPrice->getTotalNetAmount(); //new Money( 100, new EUR() )
$totalPrice->getTotalVatAmount(); //new Money( 7, new EUR() )
````

### Grouped by VAT rates

````PHP
$totalPrice = new TotalPrice(
    new MoneyFactory( new EUR() ),
    [
        GrossBasedPrice::fromGrossAmount( new Money( 100, new EUR() ), new VatRate( 19 ) ),
        GrossBasedPrice::fromGrossAmount( new Money( 200, new EUR() ), new VatRate( 19 ) ),
        NetBasedPrice::fromGrossAmount( new Money( 300, new EUR() ), new VatRate( 7 ) ),
    ]
);
$totalPrice->getPricesGroupedByVatRates();
/**
  [
    1900 => [
      GrossBasedPrice::fromGrossAmount( new Money( 100, new EUR() ), new VatRate( 19 ) ),
      GrossBasedPrice::fromGrossAmount( new Money( 200, new EUR() ), new VatRate( 19 ) ),
    ],
    700 => [ NetBasedPrice::fromGrossAmount( new Money( 300, new EUR() ), new VatRate( 7 ) ) ]
  ]
**/
````

### Totals for invoices

`getTotalNetAmount()` and `getTotalVatAmount()` sum up the rounded amounts of every single price. Invoices usually calculate the VAT once per VAT rate on the sum of the group. Use
`getTotalsGroupedByVatRates()` for that. The given class defines whether the gross amounts (`GrossBasedPrice`, usually B2C) or the net amounts (`NetBasedPrice`, usually B2B) are summed up.

````PHP
$totalPrice = new TotalPrice(
    new MoneyFactory( new EUR() ),
    [
        GrossBasedPrice::fromGrossAmount( new Money( 10, new EUR() ), new VatRate( 19 ) ),
        GrossBasedPrice::fromGrossAmount( new Money( 10, new EUR() ), new VatRate( 19 ) ),
        GrossBasedPrice::fromGrossAmount( new Money( 10, new EUR() ), new VatRate( 19 ) ),
    ]
);

$totalPrice->getTotalNetAmount(); //new Money( 24, new EUR() ) (8 + 8 + 8)

$totals = $totalPrice->getTotalsGroupedByVatRates( GrossBasedPrice::class );
$totals[1900]->getGrossAmount(); //new Money( 30, new EUR() )
$totals[1900]->getNetAmount(); //new Money( 25, new EUR() ) (30 / 1,19)

$totals = $totalPrice->getTotalsGroupedByVatRates( NetBasedPrice::class );
$totals[1900]->getNetAmount(); //new Money( 24, new EUR() )
$totals[1900]->getGrossAmount(); //new Money( 29, new EUR() ) (24 * 1,19)
````

## Json

````PHP
json_encode( GrossBasedPrice::fromNetAmount( new Money( 100, new EUR() ), new VatRate( 19 ) ) );
````

````JSON
{
    "currencyCode": "EUR",
    "netAmount": 100,
    "grossAmount": 119,
    "vatAmount": 19,
    "vatRate": 1900
}
````

````PHP
$prices = [
  GrossBasedPrice::fromGrossAmount( new Money( 100, new EUR() ), new VatRate( 19 ) ),
  GrossBasedPrice::fromGrossAmount( new Money( 300, new EUR() ), new VatRate( 19 ) ),
  NetBasedPrice::fromGrossAmount( new Money( 200, new EUR() ), new VatRate( 7 ) ),
];
$totalPrice = new TotalPrice( new MoneyFactory( new EUR() ), $prices );
json_encode( $totalPrice, JSON_PRETTY_PRINT );
````

````JSON
{
    "currencyCode": "EUR",
    "prices": {
        "1900": [
            {
                "grossAmount": 100,
                "netAmount": 84,
                "vatAmount": 16
            },
            {
                "grossAmount": 300,
                "netAmount": 252,
                "vatAmount": 48
            }
        ],
        "700": [
            {
                "grossAmount": 200,
                "netAmount": 187,
                "vatAmount": 13
            }
        ]
    }
}
````

An empty `TotalPrice` is serialized as `{"currencyCode":"EUR","prices":{}}`.

## Exceptions

### InvalidPriceException

* If prices with different VAT rates are added or subtracted and none of them has an amount of zero
* If prices with different currencies are added or subtracted
* If a `TotalPrice` gets a price with a currency different from its money factory

### InvalidVatRateException

If `VatRate` is instantiated with a value less than zero `InvalidVatRateException` will be thrown

## Upgrade from 2.x

* `VatRate` is normalized to two decimal places. `new VatRate( 22.3589 )->toFloat()` returns `22.36`.
* Adding a price with a VAT rate of 0 % to a price with another VAT rate throws an `InvalidPriceException` unless one of the amounts is zero. Use `TotalPrice` for different VAT rates.
* Adding or subtracting prices with different currencies throws an `InvalidPriceException`.
* Own subclasses of `AbstractPrice` must implement `getBaseAmount()` and `fromBaseAmount()`. `multiply`, `divide`, `add`, `subtract`, `allocateToTargets` and `allocateByRatios` are inherited and
  return `static`.
* Allocation keeps the net and gross totals.
* JSON keys changed: `currency-code` → `currencyCode`, `gross` → `grossAmount`, `net` → `netAmount`, `vat` → `vatAmount`. `prices` is always present.
