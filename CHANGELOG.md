# CHANGELOG

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
* PHPStan, Composer scripts `test`, `analyse` and `check`

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
