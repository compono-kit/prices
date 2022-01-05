# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

# [2.0.0] - 2021-11-04

## Changed

* Upgrade to PHP 8

---

# [1.2.0] - 2021-11-04

## Changed

* Override vat rates in methods `add` and `subtract` (if initial vat rate is 0)

---

# [1.1.0] - 2021-10-29

## Added

* new Method `fromTotalPrice` in TotalPrice
* new Methods in `RepresentsPrice`:
  * `allocateToTargets`
  * `allocateByRatios`
* Make AbstractPrice json serializable

## Changed

* Validation of added or subtracted price
  * Must have same VAT rate
  * If not `InvalidPriceException` will be thrown

---

# [1.0.0] - 2021-10-29

### First Release

---

[2.0.0]: https://github.com/Hansel23/prices/compare/1.1.0...2.0.0
[1.2.0]: https://github.com/Hansel23/prices/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/Hansel23/prices/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/Hansel23/prices/compare/0.0.0...1.0.0
