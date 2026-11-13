# Formas, dígitos verificadores e sigilo

Toda especificação deste pacote é uma **folha** da álgebra do núcleo: responde `isSatisfiedBy()` e `evaluate()`, compõe com `and`, `or` e `not`, entra em `Spec::property()` e aceita `because()` e `withCode()`. Um repositório SQL do núcleo avalia em memória a folha que não sabe traduzir, e estas folhas são sempre avaliadas em memória.

## O candidato

- **Só string.** Inteiro é recusado: um CPF ou um CEP que começa com zero perderia o zero.
- **Nada é aparado.** Um espaço ou uma quebra de linha numa das pontas recusa o valor, para a forma aceita ser exatamente a recebida.
- **A string vazia e o `null`** são recusados.

## As formas

CPF, CNPJ, CEP, o número de processo do CNJ e o PIS/PASEP têm máscara oficial. O `DocumentFormat` escolhe as formas aceitas:

| `DocumentFormat` | Aceita |
| :--- | :--- |
| `Any` (padrão) | A forma com máscara ou a forma sem máscara, exatamente. Nunca pontuação pela metade. |
| `Masked` | Só a máscara oficial, como `###.###.###-##`. |
| `Unmasked` | Só os caracteres, sem pontuação. |

O telefone não tem uma escrita oficial única: a especificação dele aceita espaços, parênteses, hífens, pontos e um `+` no início, e lê os dígitos. A placa de veículo tem os seus dois tipos (`PlateKind`). A CNH, o título de eleitor e o RENAVAM não têm máscara oficial e são aceitos só como os seus dígitos; uma chave PIX é escrita como o DICT a guarda, sem máscara, e os tipos dela são o `PixKeyKind`.

<!-- tested: forms -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

BrSpec::cpf()->isSatisfiedBy('529.982.247-25'); // true
BrSpec::cpf()->isSatisfiedBy('52998224725'); // true
BrSpec::cpf()->isSatisfiedBy('529982247-25'); // false (pontuação pela metade)
BrSpec::cpf(DocumentFormat::Masked)->isSatisfiedBy('52998224725'); // false
BrSpec::cpf()->isSatisfiedBy(52998224725); // false (um inteiro)
BrSpec::cpf()->isSatisfiedBy(' 529.982.247-25'); // false (um espaço no início)
```

## O que cada verificação prova

| Especificação | Confere | Não confere |
| :--- | :--- | :--- |
| CPF | Onze dígitos, não uma repetição de um dígito, os dois dígitos verificadores (módulo 11). | Se o CPF foi emitido ou está regular. |
| CNPJ | Catorze caracteres; no CNPJ alfanumérico os doze primeiros podem ser letras; não uma repetição numérica; os dois dígitos verificadores. | Se a empresa existe ou está ativa. |
| CEP | Oito dígitos de `01000-000` a `99999-999`. | Se o CEP está atribuído a um endereço. |
| Telefone | Um DDD do plano de numeração; celular (`9` + 8 dígitos) ou fixo (`2` a `5` + 7 dígitos); o código do país `55` quando escrito. | Se a linha existe. |
| Placa de veículo | O padrão antigo ou o Mercosul. | Se a placa está registrada. |
| Número de processo do CNJ | Vinte dígitos, o segmento do Judiciário de 1 a 9, os dígitos verificadores (ISO 7064 MOD 97-10). | O tribunal e a origem contra uma tabela. |
| CNH | Onze dígitos, não uma repetição de um dígito, os dois dígitos verificadores (módulo 11, com o desconto do primeiro sobre o segundo; uma base cujo segundo dígito seria negativo não tem complemento). | Se a habilitação foi emitida ou está válida. |
| PIS/PASEP | Onze dígitos, não zeros, o dígito verificador (módulo 11, pesos 3, 2, 9, 8, 7, 6, 5, 4, 3, 2). | Se o número foi atribuído. |
| Título de eleitor | Doze dígitos, o código da UF de 01 a 28, os dois dígitos verificadores (módulo 11, com a regra de SP e MG). | Se a inscrição existe ou está regular. |
| RENAVAM | Onze dígitos, não zeros, o dígito verificador (módulo 11). | Se o veículo está registrado. |
| Chave PIX | A forma que o DICT guarda para o tipo: um CPF ou um CNPJ (o alfanumérico incluído) com as suas verificações, um celular `+55` com um DDD do plano de numeração, um e-mail em minúsculas de no máximo 77 caracteres, um UUID da RFC 4122 (versão 1 a 5) em minúsculas. | Se a chave está registrada no DICT, ou de quem é. |

Um dígito verificador pega um dígito digitado errado; não prova que o documento é de alguém.

## Sigilo das falhas

Documento é dado pessoal. Nenhuma mensagem de falha, código de falha, exceção ou forma serializada deste pacote repete o valor avaliado; a mensagem padrão só nomeia o tipo de documento, e `because()` e `withCode()` a substituem como em qualquer lugar do núcleo.

<!-- tested: secrecy -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;

$result = BrSpec::cpf()->evaluate('529.982.247-26');
$result->isSatisfied; // false
$result->getReasons(); // ['The value is not a valid CPF.']
BrSpec::cpf()->because('Informe um CPF válido.')->withCode('PARTY_CPF')->evaluate('529.982.247-26')->getCodes(); // ['PARTY_CPF']
```

## O codec

Uma regra gravada em JSON (o `SpecificationCodec` do núcleo, o `spec_json` do catálogo de regras) precisa dos nomes das especificações deste pacote. O `BrSpec::register()` os entrega a um codec: `brCpf`, `brCnpj`, `brCep`, `brPhone`, `brVehiclePlate`, `brCnjProcessNumber`, `brCnh`, `brPis`, `brVoterId`, `brRenavam` e `brPixKey`, cada um só com as suas opções.

<!-- tested: codec -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

$codec = BrSpec::register(new SpecificationCodec());
$json = $codec->toJson(BrSpec::cpf(DocumentFormat::Masked));
$json; // '{"v":1,"spec":{"t":"brCpf","format":"masked"}}'
$codec->fromJson($json)->isSatisfiedBy('529.982.247-25'); // true
```

Um codec sem o `register()` recusa esses nomes, como recusa qualquer nome que não conhece.
