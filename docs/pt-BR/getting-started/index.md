# Primeiros passos

## 📦 Instalação

Instale pelo Composer:

```bash
composer require antevemus/aspecification-br
```

Requisitos: PHP 8.2 ou mais novo. Instalado junto: [`antevemus/aspecification`](https://github.com/antevemus-it/Antevemus.ASpecification) `^2.13` (o núcleo, do mesmo autor). Nenhuma extensão do PHP é exigida e nada mais é instalado.

Para que serve: conferir que um valor **está escrito como** um documento brasileiro e que os dígitos verificadores estão certos, dentro da álgebra do núcleo. Ele não pergunta a nenhum cadastro se o documento existe ou está ativo: isso é com a Receita Federal, os Correios, o Detran ou o tribunal.

## 🚀 Início rápido

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

A primeira parte tem um CPF com máscara; a segunda, um CNPJ alfanumérico e o CEP sem hífen; a terceira, um CPF com um dígito verificador errado. Todo valor desta documentação é fictício ou é o exemplo público de uma especificação oficial.

A seguir: [Formas, dígitos verificadores e sigilo](../concepts/forms-and-check-digits.md).
