# CHANGELOG

## [4.0.0] - 2026-10-14

### Changed
* [BC] Requires PHP 8.5, `compono-kit/money-interfaces` ^2.0 and `compono-kit/price-interfaces` ^2.0
* [BC] `multiply()` and `divide()` accept an optional `\RoundingMode`, the default is `\RoundingMode::HalfAwayFromZero`
* [BC] The constructors of `TotalPrice` and `VatRate` are `final`
* [BC] The promoted properties of `AbstractPrice` are `final`
* `VatRate` implements `\Stringable`
* `TotalPrice` sums the amounts with closures stored in class constants
* Uses PHPUnit 12 and the current PHPStan 2

### Removed
* Requirement of `ext-json`, which is always available since PHP 8.0

### Fixed
* `TotalPrice::fromTotalPrice()` called `getMoneyFactory()`, which was not part of `RepresentsTotalPrice`
* PHPStan errors on level max in sources and tests

---

## [3.0.0] - 2026-10-09

### Changed
* `VatRate` normalizes its value to two decimal places; `equals`, `compare` and the price calculation use the normalized value
* Adding or subtracting prices requires equal VAT rates and currencies; a zero amount is neutral on both sides
* `multiply`, `divide`, `add` and `subtract` return `static` and are implemented in `AbstractPrice`
* `AbstractPrice` requires `getBaseAmount()` and `fromBaseAmount()` in subclasses
* Allocation keeps net and gross totals
* JSON keys are camelCase (`currencyCode`, `grossAmount`, `netAmount`, `vatAmount`); `prices` is always present
* `TotalPrice` rejects prices with a currency different from its money factory

### Added
* `TotalPrice::subtractPrice()`
* `TotalPrice::getTotalsGroupedByVatRates()`

### Fixed
* `VatRate::toInt()` rounds instead of truncating (e.g. 0.29 → 29)

---

## [2.0.0] - 2025-10-19

### Changed
* Uses now PHP 8.3 

---

## [1.0.0] - 2025-10-19

### Added
* Initial release
