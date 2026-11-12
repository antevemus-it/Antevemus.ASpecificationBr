# CEP e telefone

## `BrSpec::cep(DocumentFormat $format = DocumentFormat::Any): CepSpecification`

Um CEP: oito dígitos, com a máscara `#####-###` ou sem máscara, conforme o formato, de `01000-000` a `99999-999` (nenhum CEP começa abaixo de `01000`). Se o CEP está atribuído a um endereço é pergunta para a base dos Correios.

## `BrSpec::phone(PhoneKind $kind = PhoneKind::Any): PhoneSpecification`

Um telefone brasileiro. O valor pode trazer espaços, parênteses, hífens, pontos e um `+` no início; tirados esses, só podem sobrar dígitos:

| Dígitos | Leitura |
| :--- | :--- |
| 10 | Fixo: DDD + `2` a `5` + 7 dígitos. |
| 11 | Celular: DDD + `9` + 8 dígitos. |
| 12 ou 13 | O mesmo, precedido do código do país `55`. Um `+` no início o exige. |

- **DDD:** um dos 67 do plano de numeração da Anatel, listados em `PhoneSpecification::AREA_CODES`. Códigos nunca atribuídos, como `20` ou `23`, são recusados.
- **`PhoneKind`:** `Any` (padrão), `Mobile` ou `Landline`.
- **Não são telefone aqui:** prefixo de operadora (`0xx`), números não geográficos (`0800`, `0300`, `4004`) e ramal.

<!-- tested: address-and-phone -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PhoneKind;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;

BrSpec::cep()->isSatisfiedBy('01310-100'); // true
BrSpec::cep()->isSatisfiedBy('00999-999'); // false (abaixo da faixa)
BrSpec::phone()->isSatisfiedBy('(11) 98765-4321'); // true
BrSpec::phone()->isSatisfiedBy('+55 21 3456-7890'); // true
BrSpec::phone(PhoneKind::Mobile)->isSatisfiedBy('(21) 3456-7890'); // false (um fixo)
BrSpec::phone()->isSatisfiedBy('(20) 98765-4321'); // false (não existe DDD 20)
BrSpec::phone()->isSatisfiedBy('0800 123 4567'); // false
count(PhoneSpecification::AREA_CODES); // 67
```

## Forma serializada

| Nome | Campos |
| :--- | :--- |
| `brCep` | `format`: `any`, `masked` ou `unmasked` (padrão `any`) |
| `brPhone` | `kind`: `any`, `mobile` ou `landline` (padrão `any`) |
