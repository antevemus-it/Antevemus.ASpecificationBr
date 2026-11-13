# CNH, PIS/PASEP, título de eleitor e RENAVAM

Quatro números de registro de até doze dígitos, cada um com os seus dígitos verificadores. Só o PIS/PASEP tem máscara oficial, então é o único dos quatro com `DocumentFormat`; os outros três aceitam só os dígitos, e nada é aparado.

## `BrSpec::cnh(): CnhSpecification`

O número do registro nacional da Carteira Nacional de Habilitação (CNH): onze dígitos, nove e dois dígitos verificadores de segurança (Resolução Contran 886/2021, que não publica como são calculados). O número do espelho e o RENACH são outros números.

- **Primeiro dígito verificador:** os nove dígitos com pesos de 9 a 1, o resto da divisão por 11; resto 10 dá 0 e cria um **desconto de 2** no segundo dígito.
- **Segundo dígito verificador:** os nove dígitos com pesos de 1 a 9, o resto da divisão por 11; resto 10 dá 0, qualquer outro dá o resto menos o desconto.
- Quando o primeiro resto é 10 e o segundo é 0 ou 1, o segundo dígito seria negativo: essa base **não tem complemento válido** e nunca é uma CNH (cerca de 1,7% das bases).
- Uma repetição de um dígito (`11111111111`) passa na aritmética e é recusada.

Nenhum texto oficial publica essa rotina: é a atribuída ao Denatran e usada pelos validadores públicos, seguida ao pé da letra para que um número que eles aceitam seja aceito aqui.

## `BrSpec::pis(DocumentFormat $format = DocumentFormat::Any): PisSpecification`

Um número do PIS/PASEP, o mesmo que a Caixa e a Previdência também escrevem como NIS ou NIT: onze dígitos, com a máscara `###.#####.##-#` ou sem máscara, conforme o formato.

- O dígito verificador: os dez dígitos com pesos 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, o resto da divisão por 11; resto 0 ou 1 dá 0, qualquer outro dá 11 menos o resto.
- Os zeros são recusados (passam na aritmética); toda outra repetição falha nela.

## `BrSpec::voterId(): VoterIdSpecification`

Um número de inscrição eleitoral (título de eleitor). A Resolução TSE 23.659/2021, art. 36, parágrafo único, o define com **até 12 algarismos**: um sequencial de oito algarismos cujos zeros à esquerda são desprezados na emissão, o código da unidade da Federação de origem e dois dígitos verificadores pelo "Módulo 11", o primeiro calculado sobre o número sequencial e o último sobre o código da UF seguido do primeiro dígito verificador.

- **Escrito com ou sem os zeros à esquerda:** uma string de 5 a 12 dígitos (pelo menos um dígito do sequencial) é lida completada com zeros à esquerda até doze, então `932305` e `000000932305` são o mesmo número. Um sequencial de zeros é recusado: nenhuma inscrição tem o número zero. Treze dígitos ou mais, e quatro ou menos, são recusados.
- **Código da UF:** `01` a `28` (`01` é SP, `02` é MG, `28` é o exterior), a tabela da resolução.
- **Pesos e restos:** a resolução não os dá; são os de descrições públicas da rotina do TSE. O primeiro dígito verificador é sobre o sequencial com pesos de 2 a 9, o segundo sobre o código da UF e o primeiro dígito verificador com pesos 7, 8 e 9; cada um é o resto da divisão por 11, resto 10 dá 0, e resto 0 dá **1 para SP e MG** (0 nas demais).
- O título imprime o número em grupos, mas nenhuma norma fixa uma máscara: escreva só os dígitos.

## `BrSpec::renavam(): RenavamSpecification`

Um RENAVAM, o código do registro nacional de um veículo: onze dígitos, dez e um dígito verificador.

- O dígito verificador: os dez dígitos invertidos e com pesos 2, 3, 4, 5, 6, 7, 8, 9, 2, 3, dez vezes a soma módulo 11; 10 dá 0. É o dígito do PIS/PASEP: lidos da esquerda, os pesos são 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, e dez vezes uma soma é menos a soma módulo 11.
- Um RENAVAM de nove dígitos, de antes da numeração de onze, é escrito com dois zeros à esquerda, como o registro o escreve.
- Os zeros são recusados.

<!-- tested: registration-numbers -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

BrSpec::cnh()->isSatisfiedBy('46602254573'); // true
BrSpec::cnh()->isSatisfiedBy('66742326403'); // true (primeiro resto 10: o desconto se aplica)
BrSpec::cnh()->isSatisfiedBy('11111111111'); // false (uma repetição)
BrSpec::pis()->isSatisfiedBy('824.33543.31-5'); // true
BrSpec::pis(DocumentFormat::Masked)->isSatisfiedBy('82433543315'); // false
BrSpec::voterId()->isSatisfiedBy('847882780116'); // true (SP: resto 0 dá 1)
BrSpec::voterId()->isSatisfiedBy('932305'); // true (os zeros à esquerda do sequencial desprezados, como na emissão)
BrSpec::voterId()->isSatisfiedBy('818187202941'); // false (não há UF 29)
BrSpec::renavam()->isSatisfiedBy('00144546329'); // true (um RENAVAM de nove dígitos com os zeros à esquerda)
BrSpec::renavam()->isSatisfiedBy('144546329'); // false
```

Todo número acima é fictício, tirado do gerador da suíte de testes.

## Forma serializada

| Nome | Campos |
| :--- | :--- |
| `brCnh` | nenhum |
| `brPis` | `format`: `any`, `masked` ou `unmasked` (padrão `any`) |
| `brVoterId` | nenhum |
| `brRenavam` | nenhum |

Veja [O codec](../concepts/forms-and-check-digits.md#o-codec).
