# CPF e CNPJ

## `BrSpec::cpf(DocumentFormat $format = DocumentFormat::Any): CpfSpecification`

Um CPF: onze dígitos, com a máscara `###.###.###-##` ou sem máscara, conforme o formato. Uma repetição de um dígito (`000.000.000-00`, `111.111.111-11`) passa na aritmética, mas é recusada. Os dois dígitos verificadores são calculados pela regra do módulo 11.

## `BrSpec::cnpj(DocumentFormat $format = DocumentFormat::Any, bool $alphanumeric = true): CnpjSpecification`

Um CNPJ: catorze caracteres, com a máscara `##.###.###/####-##` ou sem máscara, conforme o formato.

- **CNPJ alfanumérico.** No CNPJ da Receita Federal que admite letras, os oito caracteres da raiz e os quatro da ordem podem ser dígitos ou letras maiúsculas, e os dois dígitos verificadores continuam numéricos. Cada caractere vale o código ASCII menos 48, de modo que um CNPJ numérico dá o resultado clássico. Letras minúsculas são lidas como maiúsculas.
- **`alphanumeric: false`** aceita só dígitos, para o sistema que ainda guarda o CNPJ numa coluna só de números.
- Uma repetição numérica é recusada.

<!-- tested: taxpayer-ids -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

BrSpec::cpf()->isSatisfiedBy('529.982.247-25'); // true
BrSpec::cpf()->isSatisfiedBy('111.111.111-11'); // false (uma repetição)
BrSpec::cnpj()->isSatisfiedBy('11.222.333/0001-81'); // true
BrSpec::cnpj()->isSatisfiedBy('12.ABC.345/01DE-35'); // true (alfanumérico)
BrSpec::cnpj()->isSatisfiedBy('12abc34501de35'); // true (minúsculas, sem máscara)
BrSpec::cnpj(DocumentFormat::Any, false)->isSatisfiedBy('12.ABC.345/01DE-35'); // false (só dígitos)
BrSpec::cnpj()->isSatisfiedBy('11.222.333/0001-82'); // false (um dígito verificador errado)
```

## Forma serializada

| Nome | Campos |
| :--- | :--- |
| `brCpf` | `format`: `any`, `masked` ou `unmasked` (padrão `any`) |
| `brCnpj` | `format` como acima; `alphanumeric`: booleano (padrão `true`) |

Veja [O codec](../concepts/forms-and-check-digits.md#o-codec).
