# Chaves PIX

## `BrSpec::pixKey(PixKeyKind $kind = PixKeyKind::Any): PixKeySpecification`

Uma chave PIX **como o DICT do Banco Central a guarda**, conforme a seção 1 do Manual Operacional do DICT e os padrões da especificação da API do DICT. Uma chave é comparada com o DICT exatamente como escrita, então nada é aparado e nenhuma pontuação é removida.

| `PixKeyKind` | Aceita |
| :--- | :--- |
| `Any` (padrão) | Uma chave de qualquer um dos cinco tipos abaixo. |
| `Cpf` | Onze dígitos sem pontuação, com a verificação do CPF (uma repetição é recusada). |
| `Cnpj` | Catorze caracteres sem pontuação, numérico ou **alfanumérico** (o DICT adotou o CNPJ alfanumérico a partir de 2026-07-01), com a verificação do CNPJ. Só letras maiúsculas, como no padrão do DICT. |
| `Phone` | Um celular em E.164: `+55`, um DDD de `PhoneSpecification::AREA_CODES`, `9` e oito dígitos, sem mais nada. O DICT só guarda celulares. |
| `Email` | Um endereço em **letras minúsculas** de no máximo 77 caracteres (`PixKeySpecification::EMAIL_MAX_LENGTH`), como o DICT exige. |
| `Random` | A chave aleatória (EVP): um **UUID da RFC 4122 em minúsculas**, `xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx` com a versão `M` de `1` a `5` e a variante `N` uma de `8`, `9`, `a`, `b`. |

- **A verificação do e-mail é conservadora**, um subconjunto do padrão do DICT: a parte local aceita letras minúsculas, dígitos e ``!#$'*+/=?^_`{|}~-``, com pontos só entre eles, e no máximo 64 caracteres; o domínio tem pelo menos dois rótulos de letras minúsculas, dígitos e hífens internos, cada um de no máximo 63 caracteres. Parte local entre aspas, IP literal e caracteres fora desse conjunto são recusados.
- **Caixa das letras.** O DICT escreve o e-mail e a chave aleatória em minúsculas, e os padrões dele não têm maiúscula: os dois são recusados em maiúsculas. Converta para minúsculas antes de verificar quando um formulário puder receber maiúsculas.
- **A chave aleatória** é gerada pelo DICT como um UUID da RFC 4122. A versão não é restrita à 4: o exemplo da especificação da API do DICT (2.13.0), `123e4567-e89b-12d3-a456-426655440000`, é um UUID versão 1, e é aceito. O UUID nulo, uma versão fora de 1 a 5, outra variante e letras maiúsculas são recusados.

<!-- tested: pix-keys -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PixKeyKind;

BrSpec::pixKey()->isSatisfiedBy('52998224725'); // true (um CPF)
BrSpec::pixKey()->isSatisfiedBy('529.982.247-25'); // false (chave não tem máscara)
BrSpec::pixKey()->isSatisfiedBy('12ABC34501DE35'); // true (um CNPJ alfanumérico)
BrSpec::pixKey()->isSatisfiedBy('+5511987654321'); // true (um celular)
BrSpec::pixKey()->isSatisfiedBy('+552134567890'); // false (um fixo)
BrSpec::pixKey()->isSatisfiedBy('pix@bcb.gov.br'); // true
BrSpec::pixKey()->isSatisfiedBy('Pix@bcb.gov.br'); // false (letras maiúsculas)
BrSpec::pixKey()->isSatisfiedBy('123e4567-e89b-12d3-a456-426655440000'); // true (uma chave aleatória: o exemplo da API do DICT)
BrSpec::pixKey(PixKeyKind::Email)->isSatisfiedBy('52998224725'); // false (um CPF, não um e-mail)
```

O e-mail e a chave aleatória são os exemplos da especificação da API do DICT; os outros valores são fictícios ou os exemplos públicos das páginas de CPF e CNPJ.

## Forma serializada

| Nome | Campos |
| :--- | :--- |
| `brPixKey` | `kind`: `any`, `cpf`, `cnpj`, `phone`, `email` ou `random` (padrão `any`) |

Veja [O codec](../concepts/forms-and-check-digits.md#o-codec).
