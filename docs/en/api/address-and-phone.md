# CEP and Phone Number

## `BrSpec::cep(DocumentFormat $format = DocumentFormat::Any): CepSpecification`

A postal code: eight digits, masked `#####-###` or unmasked, as the format allows, from `01000-000` to `99999-999` (no postal code starts below `01000`). Whether the code is assigned to an address is a question for the postal service's base.

## `BrSpec::phone(PhoneKind $kind = PhoneKind::Any): PhoneSpecification`

A Brazilian phone number. The value may carry spaces, parentheses, hyphens, dots and one leading `+`; once they are removed, only digits may remain:

| Digits | Reading |
| :--- | :--- |
| 10 | Landline: area code + `2` to `5` + 7 digits. |
| 11 | Mobile: area code + `9` + 8 digits. |
| 12 or 13 | The same, preceded by the country code `55`. A leading `+` requires it. |

- **Area code:** one of the 67 of the numbering plan of Anatel, listed in `PhoneSpecification::AREA_CODES`. Codes never assigned, such as `20` or `23`, are refused.
- **`PhoneKind`:** `Any` (default), `Mobile` or `Landline`.
- **Not phone numbers here:** a carrier prefix (`0xx`), non-geographic numbers (`0800`, `0300`, `4004`), and an extension.

<!-- tested: address-and-phone -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PhoneKind;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;

BrSpec::cep()->isSatisfiedBy('01310-100'); // true
BrSpec::cep()->isSatisfiedBy('00999-999'); // false (below the range)
BrSpec::phone()->isSatisfiedBy('(11) 98765-4321'); // true
BrSpec::phone()->isSatisfiedBy('+55 21 3456-7890'); // true
BrSpec::phone(PhoneKind::Mobile)->isSatisfiedBy('(21) 3456-7890'); // false (a landline)
BrSpec::phone()->isSatisfiedBy('(20) 98765-4321'); // false (no area code 20)
BrSpec::phone()->isSatisfiedBy('0800 123 4567'); // false
count(PhoneSpecification::AREA_CODES); // 67
```

## Serialized form

| Name | Fields |
| :--- | :--- |
| `brCep` | `format`: `any`, `masked` or `unmasked` (default `any`) |
| `brPhone` | `kind`: `any`, `mobile` or `landline` (default `any`) |
