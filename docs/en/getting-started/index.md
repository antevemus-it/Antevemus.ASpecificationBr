# Getting Started

## 📦 Installation

Install via Composer:

```bash
composer require antevemus/aspecification-br
```

Requirements: PHP 8.2 or later. Installed with it: [`antevemus/aspecification`](https://github.com/antevemus-it/Antevemus.ASpecification) `^2.13` (the core, from the same author). No PHP extension is required and nothing else is installed.

What it is for: checking that a value **is written as** a Brazilian document and that its check digits are right, inside the core's algebra. It does not ask any registry whether the document exists or is active: that is a question for the Receita Federal, the postal service, the vehicle registry or the court.

## 🚀 Quick Start

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

The first party has a CPF in the masked form; the second, an alphanumeric CNPJ and the postal code without the hyphen; the third, a CPF with a wrong check digit. Every value in this documentation is fictitious or the public example of an official specification.

Next: [Forms, check digits and secrecy](../concepts/forms-and-check-digits.md).
