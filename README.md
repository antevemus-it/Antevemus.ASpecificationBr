# Antevemus ASpecification BR

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://github.com/antevemus-it/Antevemus.ASpecificationBr/actions/workflows/tests.yml/badge.svg)](https://github.com/antevemus-it/Antevemus.ASpecificationBr/actions/workflows/tests.yml)

[Português (Brasil)](README.pt-BR.md)

**The Brazilian documents satellite of [ASpecification](https://github.com/antevemus-it/Antevemus.ASpecification) for PHP 8.2+.** Leaf specifications for CPF, CNPJ (numeric and alphanumeric), CEP, phone number, vehicle plate (legacy and Mercosur), CNJ court process number, CNH, PIS/PASEP, voter registration number, RENAVAM and PIX key: check digits, the exact masked or unmasked form, a name for each one in the codec, and failure messages that never repeat the value under evaluation. They compose with the core's `and`, `or`, `not` and `Spec::property()`.

Install with `composer require antevemus/aspecification-br`.

## Quick Start

<!-- tested: quick-start -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecification\Spec;
use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PhoneKind;

// A party: a CPF or a CNPJ, a postal code and a mobile phone number.
$party = Spec::property('document', BrSpec::cpf()->or(BrSpec::cnpj()))
    ->and(Spec::property('zip', BrSpec::cep()))
    ->and(Spec::property('phone', BrSpec::phone(PhoneKind::Mobile)));

$party->isSatisfiedBy((object) ['document' => '529.982.247-25', 'zip' => '01310-100', 'phone' => '(11) 98765-4321']); // true
$party->isSatisfiedBy((object) ['document' => '12.ABC.345/01DE-35', 'zip' => '01310100', 'phone' => '+55 11 98765-4321']); // true
$party->isSatisfiedBy((object) ['document' => '529.982.247-26', 'zip' => '01310-100', 'phone' => '(11) 98765-4321']); // false
```

## Documentation

The official documentation lives in [docs/en](docs/en/index.md) (and in Portuguese, in [docs/pt-BR](docs/pt-BR/index.md)); every PHP block of it runs in the test suite.

| Page | What it covers |
| :--- | :--- |
| [Installation and quick start](docs/en/getting-started/index.md) | Composer, the requirements and the Quick Start. |
| [Forms, check digits and secrecy](docs/en/concepts/forms-and-check-digits.md) | The candidate, the masked and unmasked forms, what each check proves, the failure messages and the codec. |
| [CPF and CNPJ](docs/en/api/taxpayer-ids.md) | `BrSpec::cpf()`, `BrSpec::cnpj()` and the alphanumeric CNPJ. |
| [CEP and phone number](docs/en/api/address-and-phone.md) | `BrSpec::cep()`, `BrSpec::phone()` and the area codes. |
| [Vehicle plate and CNJ process number](docs/en/api/vehicle-and-court.md) | `BrSpec::vehiclePlate()` and `BrSpec::cnjProcessNumber()`. |
| [CNH, PIS/PASEP, voter registration and RENAVAM](docs/en/api/registration-numbers.md) | `BrSpec::cnh()`, `BrSpec::pis()`, `BrSpec::voterId()` and `BrSpec::renavam()`. |
| [PIX keys](docs/en/api/pix-keys.md) | `BrSpec::pixKey()` and the kinds of key of the DICT. |
| [Tests](docs/en/quality/tests.md) | The suite, the properties and the mutation testing. |
| [Roadmap](docs/en/project/roadmap.md) | What shipped and what may come. |

## License

MIT, see [LICENSE](LICENSE). Provenance in [NOTICE.md](NOTICE.md); changes in [CHANGELOG.md](CHANGELOG.md).
