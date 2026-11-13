# CNH, PIS/PASEP, Voter Registration and RENAVAM

Four registration numbers of up to twelve digits, each with its check digits. Only the PIS/PASEP has an official mask, so it is the only one of the four with a `DocumentFormat`; the other three accept their digits only, and nothing is trimmed.

## `BrSpec::cnh(): CnhSpecification`

The national registration number of a driver's license (CNH, *número do registro nacional*): eleven digits, nine and two security check digits (Contran Resolution 886/2021, which does not publish how they are computed). The number of the printed form (*espelho*) and the RENACH are other numbers.

- **First check digit:** the nine digits weighted 9 down to 1, the remainder modulo 11; a remainder of 10 gives 0 and sets a **discount of 2** on the second digit.
- **Second check digit:** the nine digits weighted 1 to 9, the remainder modulo 11; a remainder of 10 gives 0, any other gives the remainder minus the discount.
- When the first remainder is 10 and the second is 0 or 1, the second digit would be negative: such a base has **no valid completion** and is never a CNH (about 1.7% of the bases).
- A repetition of one digit (`11111111111`) passes the arithmetic and is refused.

No official text publishes this routine: it is the one attributed to Denatran and used by the public validators, followed literally so that a number they accept is accepted here.

## `BrSpec::pis(DocumentFormat $format = DocumentFormat::Any): PisSpecification`

A PIS/PASEP number, the same number the Caixa and the social security also write as NIS or NIT: eleven digits, masked `###.#####.##-#` or unmasked, as the format allows.

- The check digit: the ten digits weighted 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, the remainder modulo 11; a remainder of 0 or 1 gives 0, any other gives 11 minus the remainder.
- The zeros are refused (they pass the arithmetic); every other repetition fails it.

## `BrSpec::voterId(): VoterIdSpecification`

A voter registration number (*título de eleitor*). TSE Resolution 23.659/2021, art. 36, sole paragraph, sets it as **up to twelve digits**: an eight-digit sequence whose leading zeros are dropped at issuance, the code of the state of origin and two check digits by "Módulo 11", the first over the sequence and the last over the state code followed by the first check digit.

- **Written with or without the leading zeros:** a string of 5 to 12 digits (at least one digit of the sequence) is read left-padded with zeros to twelve, so `932305` and `000000932305` are the same number. A sequence of zeros is refused: no inscription is number zero. Thirteen digits or more, and four or fewer, are refused.
- **State code:** `01` to `28` (`01` is SP, `02` is MG, `28` is a voter abroad), the table of the resolution.
- **Weights and remainders:** the resolution does not give them; they are those of public descriptions of the TSE routine. The first check digit is over the sequence weighted 2 to 9, the second over the state code and the first check digit weighted 7, 8 and 9; each is the remainder modulo 11, a remainder of 10 gives 0, and a remainder of 0 gives **1 for SP and MG** (0 elsewhere).
- The card prints the number in groups, but no norm sets a mask: write the digits only.

## `BrSpec::renavam(): RenavamSpecification`

A RENAVAM, the national registry code of a vehicle: eleven digits, ten and one check digit.

- The check digit: the ten digits reversed and weighted 2, 3, 4, 5, 6, 7, 8, 9, 2, 3, ten times the sum modulo 11; 10 gives 0. It is the PIS/PASEP digit: read from the left the weights are 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, and ten times a sum is minus the sum modulo 11.
- A RENAVAM of nine digits, from before the eleven-digit numbering, is written with two leading zeros, as the registry writes it.
- The zeros are refused.

<!-- tested: registration-numbers -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

BrSpec::cnh()->isSatisfiedBy('46602254573'); // true
BrSpec::cnh()->isSatisfiedBy('66742326403'); // true (first remainder 10: the discount applies)
BrSpec::cnh()->isSatisfiedBy('11111111111'); // false (a repetition)
BrSpec::pis()->isSatisfiedBy('824.33543.31-5'); // true
BrSpec::pis(DocumentFormat::Masked)->isSatisfiedBy('82433543315'); // false
BrSpec::voterId()->isSatisfiedBy('847882780116'); // true (SP: a remainder of 0 gives 1)
BrSpec::voterId()->isSatisfiedBy('932305'); // true (the leading zeros of the sequence dropped, as issued)
BrSpec::voterId()->isSatisfiedBy('818187202941'); // false (no state 29)
BrSpec::renavam()->isSatisfiedBy('00144546329'); // true (a nine-digit RENAVAM with its leading zeros)
BrSpec::renavam()->isSatisfiedBy('144546329'); // false
```

Every number above is fictitious, drawn from the generator of the test suite.

## Serialized form

| Name | Fields |
| :--- | :--- |
| `brCnh` | none |
| `brPis` | `format`: `any`, `masked` or `unmasked` (default `any`) |
| `brVoterId` | none |
| `brRenavam` | none |

See [The codec](../concepts/forms-and-check-digits.md#the-codec).
