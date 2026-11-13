# Antevemus ASpecification BR

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://github.com/antevemus-it/Antevemus.ASpecificationBr/actions/workflows/tests.yml/badge.svg)](https://github.com/antevemus-it/Antevemus.ASpecificationBr/actions/workflows/tests.yml)

[English](README.md)

**O satélite de documentos brasileiros da [ASpecification](https://github.com/antevemus-it/Antevemus.ASpecification) para PHP 8.2+.** Especificações-folha para CPF, CNPJ (numérico e alfanumérico), CEP, telefone, placa de veículo (antiga e Mercosul), número de processo do CNJ, CNH, PIS/PASEP, título de eleitor, RENAVAM e chave PIX: dígitos verificadores, a forma exata com máscara ou sem máscara, um nome para cada uma no codec e mensagens de falha que nunca repetem o valor avaliado. Elas se compõem com o `and`, o `or`, o `not` e o `Spec::property()` do núcleo.

Instale com `composer require antevemus/aspecification-br`.

## Início rápido

<!-- tested: quick-start -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecification\Spec;
use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PhoneKind;

// Uma parte: um CPF ou um CNPJ, um CEP e um celular.
$party = Spec::property('document', BrSpec::cpf()->or(BrSpec::cnpj()))
    ->and(Spec::property('zip', BrSpec::cep()))
    ->and(Spec::property('phone', BrSpec::phone(PhoneKind::Mobile)));

$party->isSatisfiedBy((object) ['document' => '529.982.247-25', 'zip' => '01310-100', 'phone' => '(11) 98765-4321']); // true
$party->isSatisfiedBy((object) ['document' => '12.ABC.345/01DE-35', 'zip' => '01310100', 'phone' => '+55 11 98765-4321']); // true
$party->isSatisfiedBy((object) ['document' => '529.982.247-26', 'zip' => '01310-100', 'phone' => '(11) 98765-4321']); // false
```

## Documentação

A documentação oficial fica em [docs/pt-BR](docs/pt-BR/index.md) (e em inglês, em [docs/en](docs/en/index.md)); todo bloco PHP dela roda na suíte de testes.

| Página | O que cobre |
| :--- | :--- |
| [Instalação e início rápido](docs/pt-BR/getting-started/index.md) | O Composer, os requisitos e o Quick Start. |
| [Formas, dígitos verificadores e sigilo](docs/pt-BR/concepts/forms-and-check-digits.md) | O candidato, as formas com e sem máscara, o que cada verificação prova, as mensagens de falha e o codec. |
| [CPF e CNPJ](docs/pt-BR/api/taxpayer-ids.md) | `BrSpec::cpf()`, `BrSpec::cnpj()` e o CNPJ alfanumérico. |
| [CEP e telefone](docs/pt-BR/api/address-and-phone.md) | `BrSpec::cep()`, `BrSpec::phone()` e os DDDs. |
| [Placa de veículo e número de processo do CNJ](docs/pt-BR/api/vehicle-and-court.md) | `BrSpec::vehiclePlate()` e `BrSpec::cnjProcessNumber()`. |
| [CNH, PIS/PASEP, título de eleitor e RENAVAM](docs/pt-BR/api/registration-numbers.md) | `BrSpec::cnh()`, `BrSpec::pis()`, `BrSpec::voterId()` e `BrSpec::renavam()`. |
| [Chaves PIX](docs/pt-BR/api/pix-keys.md) | `BrSpec::pixKey()` e os tipos de chave do DICT. |
| [Testes](docs/pt-BR/quality/tests.md) | A suíte, as propriedades e o teste de mutação. |
| [Roadmap](docs/pt-BR/project/roadmap.md) | O que foi entregue e o que pode vir. |

## Licença

MIT, veja o [LICENSE](LICENSE). Procedência no [NOTICE.md](NOTICE.md); mudanças no [CHANGELOG.md](CHANGELOG.md).
