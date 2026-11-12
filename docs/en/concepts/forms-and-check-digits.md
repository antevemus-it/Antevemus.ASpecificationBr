# Forms, Check Digits and Secrecy

Every specification of this package is a **leaf** of the core's algebra: it answers `isSatisfiedBy()` and `evaluate()`, composes with `and`, `or` and `not`, goes under `Spec::property()`, and takes `because()` and `withCode()`. A SQL repository of the core falls back to memory for a leaf it cannot translate, and these leaves are always evaluated in memory.

## The candidate

- **Only a string.** An integer is refused: a CPF or a postal code that starts with zero would lose it.
- **Nothing is trimmed.** A space or a newline at either end refuses the value, so the form accepted is exactly the one received.
- **The empty string and `null`** are refused.

## The forms

CPF, CNPJ, CEP and the CNJ process number have an official mask. `DocumentFormat` chooses the forms accepted:

| `DocumentFormat` | Accepts |
| :--- | :--- |
| `Any` (default) | The masked form or the unmasked form, exactly. Never partial punctuation. |
| `Masked` | Only the official mask, such as `###.###.###-##`. |
| `Unmasked` | Only the bare characters. |

The phone number has no single official writing: its specification accepts spaces, parentheses, hyphens, dots and one leading `+`, and reads the digits. The vehicle plate has its own two kinds (`PlateKind`).

<!-- tested: forms -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;

BrSpec::cpf()->isSatisfiedBy('529.982.247-25'); // true
BrSpec::cpf()->isSatisfiedBy('52998224725'); // true
BrSpec::cpf()->isSatisfiedBy('529982247-25'); // false (partial punctuation)
BrSpec::cpf(DocumentFormat::Masked)->isSatisfiedBy('52998224725'); // false
BrSpec::cpf()->isSatisfiedBy(52998224725); // false (an integer)
BrSpec::cpf()->isSatisfiedBy(' 529.982.247-25'); // false (a leading space)
```

## What each check proves

| Specification | Checks | Does not check |
| :--- | :--- | :--- |
| CPF | Eleven digits, not a repetition of one digit, both check digits (modulo 11). | Whether the CPF was issued or is regular. |
| CNPJ | Fourteen characters; in the alphanumeric CNPJ the twelve leading ones may be letters; not a numeric repetition; both check digits. | Whether the company exists or is active. |
| CEP | Eight digits from `01000-000` to `99999-999`. | Whether the code is assigned to an address. |
| Phone number | An area code of the numbering plan; mobile (`9` + 8 digits) or landline (`2` to `5` + 7 digits); the country code `55` when written. | Whether the line exists. |
| Vehicle plate | The legacy or the Mercosur pattern. | Whether the plate is registered. |
| CNJ process number | Twenty digits, the segment of the judiciary from 1 to 9, the check digits (ISO 7064 MOD 97-10). | The court and the origin against a table. |

A check digit catches a mistyped digit; it does not prove the document belongs to anyone.

## Secrecy of the failures

A document is personal data. No failure message, failure code, exception or serialized form of this package repeats the value under evaluation; the default message names only the kind of document, and `because()` and `withCode()` replace it as anywhere in the core.

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

## The codec

A rule stored as JSON (the core's `SpecificationCodec`, the `spec_json` of the rule catalog) needs the names of this package's specifications. `BrSpec::register()` gives them to a codec: `brCpf`, `brCnpj`, `brCep`, `brPhone`, `brVehiclePlate` and `brCnjProcessNumber`, each with its options only.

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

A codec without `register()` refuses these names, as it refuses any name it does not know.
