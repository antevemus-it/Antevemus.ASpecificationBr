# CPF and CNPJ

## `BrSpec::cpf(DocumentFormat $format = DocumentFormat::Any): CpfSpecification`

A CPF: eleven digits, masked `###.###.###-##` or unmasked, as the format allows. A repetition of one digit (`000.000.000-00`, `111.111.111-11`) passes the arithmetic but is refused. Both check digits are computed by the modulo 11 rule.

## `BrSpec::cnpj(DocumentFormat $format = DocumentFormat::Any, bool $alphanumeric = true): CnpjSpecification`

A CNPJ: fourteen characters, masked `##.###.###/####-##` or unmasked, as the format allows.

- **Alphanumeric CNPJ.** In the CNPJ of the Receita Federal that admits letters, the eight characters of the root and the four of the order may be digits or capital letters, and the two check digits stay numeric. Each character is worth its ASCII code minus 48, so a numeric CNPJ gives the classic result. Small letters are read as capitals.
- **`alphanumeric: false`** accepts only digits, for a system that still stores the CNPJ as a number-only column.
- A numeric repetition is refused.

<!-- tested: taxpayer-ids -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

BrSpec::cpf()->isSatisfiedBy('529.982.247-25'); // true
BrSpec::cpf()->isSatisfiedBy('111.111.111-11'); // false (a repetition)
BrSpec::cnpj()->isSatisfiedBy('11.222.333/0001-81'); // true
BrSpec::cnpj()->isSatisfiedBy('12.ABC.345/01DE-35'); // true (alphanumeric)
BrSpec::cnpj()->isSatisfiedBy('12abc34501de35'); // true (small letters, unmasked)
BrSpec::cnpj(DocumentFormat::Any, false)->isSatisfiedBy('12.ABC.345/01DE-35'); // false (digits only)
BrSpec::cnpj()->isSatisfiedBy('11.222.333/0001-82'); // false (a wrong check digit)
```

## Serialized form

| Name | Fields |
| :--- | :--- |
| `brCpf` | `format`: `any`, `masked` or `unmasked` (default `any`) |
| `brCnpj` | `format` as above; `alphanumeric`: boolean (default `true`) |

See [The codec](../concepts/forms-and-check-digits.md#the-codec).
