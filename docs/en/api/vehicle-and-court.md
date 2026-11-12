# Vehicle Plate and CNJ Process Number

## `BrSpec::vehiclePlate(PlateKind $kind = PlateKind::Any): VehiclePlateSpecification`

A Brazilian vehicle plate:

- **Legacy:** three letters and four digits, `AAA9999` or `AAA-9999`.
- **Mercosur:** three letters, a digit, a letter and two digits, `AAA9A99`, without a hyphen.

Small letters are accepted. `PlateKind` is `Any` (default), `Legacy` or `Mercosur`. Whether the plate is registered is a question for the vehicle registry.

## `BrSpec::cnjProcessNumber(DocumentFormat $format = DocumentFormat::Any): CnjProcessNumberSpecification`

A unified process number of the Brazilian judiciary (CNJ Resolution 65/2008): twenty digits `NNNNNNN DD AAAA J TR OOOO` (sequence, check digits, year, segment of the judiciary, court, origin), masked `NNNNNNN-DD.AAAA.J.TR.OOOO` or unmasked, as the format allows.

- The segment `J` is 1 to 9.
- The check digits `DD` are 02 to 98, and the number rearranged as `NNNNNNN AAAA J TR OOOO DD` is congruent to 1 modulo 97 (ISO 7064 MOD 97-10).
- The court and the origin are not checked against a table.

<!-- tested: vehicle-and-court -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PlateKind;

BrSpec::vehiclePlate()->isSatisfiedBy('ABC-1234'); // true
BrSpec::vehiclePlate()->isSatisfiedBy('BRA2E19'); // true
BrSpec::vehiclePlate(PlateKind::Legacy)->isSatisfiedBy('BRA2E19'); // false (a Mercosur plate)
BrSpec::cnjProcessNumber()->isSatisfiedBy('1606504-41.2041.6.53.3394'); // true
BrSpec::cnjProcessNumber()->isSatisfiedBy('1606504-42.2041.6.53.3394'); // false (a wrong check digit)
```

## Serialized form

| Name | Fields |
| :--- | :--- |
| `brVehiclePlate` | `kind`: `any`, `legacy` or `mercosur` (default `any`) |
| `brCnjProcessNumber` | `format`: `any`, `masked` or `unmasked` (default `any`) |
