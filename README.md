# Prices

PHP types representing prices including gross amount, net amount, vat amount and vat rate A price include the gross, net and VAT amount, as well as the VAT rate. The missing values are automatically
calculated by the class depending on the instantiation method. 

## Requirements

* PHP >=7.4
* moneyphp/money

## Instantiation

You have the gross and net amount, as well as the VAT rate

````PHP
$net     = new Money( 1672, 'EUR' );
$gross   = new Money( 1990, 'EUR' );
$vatRate = new VatRate( 19 );

$price = new GrossBasedPrice( $net, $gross, $vatRate );
$price->getGrossAmount(); //new Money( 1990, 'EUR' )
$price->getNetAmount(); //new Money( 1672, 'EUR' )
$price->getVatAmount(); //new Money( 318, 'EUR' )
$price->getVatRate(); //new VatRate( 19 )
````

You have the gross amount and the VAT rate

````PHP
$gross   = new Money( 1990, 'EUR' );
$vatRate = new VatRate( 19 );

$price = GrossBasedPrice::fromGrossAmount( $gross, $vatRate );
$price->getGrossAmount(); //new Money( 1990, 'EUR' )
$price->getNetAmount(); //new Money( 1672, 'EUR' )
$price->getVatAmount(); //new Money( 318, 'EUR' )
$price->getVatRate(); //new VatRate( 19 )
````

You have the net amount and the VAT rate

````PHP
$net     = new Money( 1672, 'EUR' );
$vatRate = new VatRate( 19 );

$price = GrossBasedPrice::fromNetAmount( $net, $vatRate );
$price->getGrossAmount(); //new Money( 1990, 'EUR' )
$price->getNetAmount(); //new Money( 1672, 'EUR' )
$price->getVatAmount(); //new Money( 318, 'EUR' )
$price->getVatRate(); //new VatRate( 19 )
````

You want a new price type by another price type

````PHP
$grossBasedPrice = GrossBasedPrice::fromGrossAmount( new Money( 1990, 'EUR' ), new VatRate(19) );
$netBasedPrice   = NetBasedPrice::fromPrice( $price );

$netBasedPrice->getGrossAmount(); //new Money( 1990, 'EUR' )
$netBasedPrice->getNetAmount(); //new Money( 1672, 'EUR' )
$netBasedPrice->getVatAmount(); //new Money( 318, 'EUR' )
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

The method `fromNetAndGrossAmount`, which calculates VAT independently, does not exist because the calculation is not reliable. There are countries with VAT rates that have decimal places. If these are
taken into account, rounding can result in incorrect VAT rates.

Example:

* Gross: 9,99 EUR
* Net: 8,39 EUR
* Expected vat rate: 19,00 %
* Calculated vat rate, after rounding and with 2 decimal places: 19,07 %

### VatRate

You can instantiate the `VatRate` by a float value or by an integer value. The integer value must be the float value multiplied by 100. The following example generates the same VAT rate. The VAT rate
is 21,70 %.

````PHP
$vatRateByFloat = new VatRate( 21.7 );
$vatRateByInt = VatRate::fromInt( 2170 );
$vatRateByFloat->equals( $vatRateByInt ); //true
````

## Multiplication and division of prices

There are two ways to calculate VAT when multiplying by the quantity. This is the difference between `GrossBasedPrice` and `NetBasePrice`.

### `NetBasedPrice`

VAT is calculated after multiplying the unit price by the quantity.

Example: `90,82 € * 10 = 908,20 € * 1.19 = 1080,76 €`

````PHP
$unitPrice  = NetBasedPrice::fromGrossAmount( new Money( 10808, 'EUR' ) );
$totalPrice = $price->multiply( 10 );

$unitPrice->getGrossAmount(); //new Money( 10808, 'EUR' )
$unitPrice->getNetAmount(); //new Money( 9082, 'EUR' )
$totalPrice->getGrossAmount(); //new Money( 108076, 'EUR' )
$totalPrice->getNetAmount(); //new Money( 90820, 'EUR' )
````

### `GrossBasedPrice`

First, the VAT is calculated on the unit price and then multiplied by the quantity.

Example: `90,82 € * 1,19 = 108,08 € * 3 = 1080,80 €`

````PHP
$unitPrice  = GrossBasedPrice::fromNetAmount( new Money( 9082, 'EUR' ) );
$totalPrice = $price->multiply( 10 );

$unitPrice->getGrossAmount(); //new Money( 10808, 'EUR' )
$unitPrice->getNetAmount(); //new Money( 9082, 'EUR' )
$totalPrice->getGrossAmount(); //new Money( 108080, 'EUR' )
$totalPrice->getNetAmount(); //new Money( 90824, 'EUR' )
````

---
Division works like multiplication, except of course you divide instead of multiply.

## Addition and subtraction

````PHP
$price  = GrossBasedPrice::fromGrossAmount( new Money( 1000, 'EUR' ) );

$sum    = $price->add( GrossBasedPrice::fromGrossAmount( new Money( 1000, 'EUR' ) ) );
$sum->getGrossAmount(); //new Money( 2000, 'EUR' )

$difference = $price->subtract( GrossBasedPrice::fromGrossAmount( new Money( 1000, 'EUR' ) ) );
$difference->getGrossAmount(); //new Money( 0, 'EUR' )
````

## TotalPrice

While `RepresentsPrice` (or the implementation of it) is used primarily for the prices of order items, `RepresentsTotalPrice` (or the implementation of it) is used as the total price of an order.

In this case, the prices are not merely added together, but can be returned, grouped by VAT rate, for example.

````PHP
$prices = [
    GrossBasedPrice::fromGrossAmount( new Money( 100, 'EUR' ), new VatRate( 19 ) ),
    FakePriceImplementation::fromGrossAmount( new Money( 300, 'EUR' ), new VatRate( 7 ) ),
];
$additionalPrice = GrossBasedPrice::fromGrossAmount( new Money( 100,  'EUR' ), new VatRate( 16.5 ) );

$totalPrice = new TotalPrice( 'EUR', $prices );
$totalPrice->addPrice( $additionalPrice );

$anotherTotalPrice = new TotalPrice( 
  'EUR', 
  [ 
    NetBasedPrice::fromGrossAmount( new Money( 200,  'EUR'  ), new VatRate( 16.5 ) ),
    FakePriceImplementation::fromGrossAmount( new Money( 300,  'EUR' ), new VatRate( 16.5 ) ),
  ] 
);

$totalPrice->addTotalPrice( $anotherTotalPrice );

$totalPrice->getPrices(); // Array with prices from $prices, $additionalPrice and the prices from $anotherTotalPrice
$totalPrice->getTotalGrossAmount(); // new Money( 1000, 'EUR' ) (100 + 300 + 100 + 200 + 300)
$totalPrice->getTotalNetAmount(); // new Money( 794, 'EUR' ) (100/1,19 + 300/1,07 + (200 + 300)/1,165) 
$totalPrice->getTotalVatAmount(); // new Money( 206, 'EUR ) (1000 - 794)
````

Return by grouped vat rates.

````PHP
$prices = [
    GrossBasedPrice::fromGrossAmount( new Money( 100, 'EUR' ), new VatRate( 19 ) ),
    GrossBasedPrice::fromGrossAmount( new Money( 200, 'EUR' ), new VatRate( 19 ) ),
    FakePriceImplementation::fromGrossAmount( new Money( 300, 'EUR' ), new VatRate( 7 ) ),
];
$totalPrice = new TotalPrice( 'EUR', $prices );
$totalPrice->getPricesGroupedByVatRates(); 
/** 
  [ 
    1900 => [ 
      GrossBasedPrice::fromGrossAmount( new Money( 100, 'EUR' ), new VatRate( 19 ) ),
      GrossBasedPrice::fromGrossAmount( new Money( 200, 'EUR' ), new VatRate( 19 ) ),
    ],
    700 => [ FakePriceImplementation::fromGrossAmount( new Money( 300, 'EUR' ), new VatRate( 7 ) ) ]
  ]
**/
````

Json

````PHP
$prices = [
  GrossBasedPrice::fromGrossAmount( new Money( 100,  'EUR'  ), new VatRate( 19 ) ),
  FakePriceImplementation::fromGrossAmount( new Money( 300,  'EUR'  ), new VatRate( 19 ) ),
  NetBasedPrice::fromGrossAmount( new Money( 200,  'EUR'  ), new VatRate( 7 ) ),
];
$totalPrice = new TotalPrice( 'EUR', $prices );
json_encode( $totalPrice, JSON_PRETTY_PRINT ); 
/**
{
	"1900": [{
		"gross": {
			"amount": "100",
			"currency": "EUR"
		},
		"net": {
			"amount": "84",
			"currency": "EUR"
		},
		"vat": {
			"amount": "16",
			"currency": "EUR"
		}
	}, {
		"gross": {
			"amount": "300",
			"currency": "EUR"
		},
		"net": {
			"amount": "252",
			"currency": "EUR"
		},
		"vat": {
			"amount": "48",
			"currency": "EUR"
		}
	}],
	"700": [{
		"gross": {
			"amount": "200",
			"currency": "EUR"
		},
		"net": {
			"amount": "187",
			"currency": "EUR"
		},
		"vat": {
			"amount": "13",
			"currency": "EUR"
		}
	}]
}
**/
````
