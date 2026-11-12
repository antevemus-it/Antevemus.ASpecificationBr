# Tests

The suite runs with PHPUnit in two groups, `Unit` and `Property`, in the CI of every push on PHP 8.2, 8.3 and 8.4:

```bash
vendor/bin/phpunit
```

## What is tested

- **Fixed cases** of every specification: the forms accepted and refused by each `DocumentFormat`, `PhoneKind` and `PlateKind`, each check digit wrong, repetitions, the boundaries of the postal code range, every one of the 100 two-digit area codes, values with a space or a newline at either end, integers, `null` and the empty string. Every value is fictitious or the public example of an official specification.
- **The copied routines answer as their origin:** the CPF and CNPJ check-digit routines were first written by the copyright holder for an internal product (see NOTICE.md); its cases are repeated here with the same expected answers.
- **Properties over generated documents** (fixed seeds, 400 iterations each): every generated CPF, CNPJ (numeric and alphanumeric), postal code and process number is accepted in the forms its format allows and refused in the other; changing one check digit to any other digit is always refused; `Any` answers exactly `Masked or Unmasked`, over noise strings and over documents with some punctuation removed; every generated phone number is accepted in five writings and only for its own kind; every plate only for its own kind. The generator computes the check digits by a route of its own (the three-step computation of the CNJ resolution, the "times ten, modulo eleven" form of the CPF rule, the fixed tables of weights of the CNPJ), so the tests do not confirm the validator with itself.
- **A guard over a pool** that holds every concrete class of `src/Specifications` with every option (a new class left out fails it): every one has a name in `BrSpec::TYPES`, survives the round trip through the codec with its options, and never repeats the candidate in a failure message, a failure code or its serialized form.
- **The documentation:** every PHP block of `docs/en` and `docs/pt-BR`, and the Quick Start of each README, runs with the answers written in its comments; both languages carry the same code and the same files.

## Mutation testing

Infection runs over the whole `src/` in a scheduled workflow, with minimum scores one point below the last measure. Measured for 1.0.0: 328 mutants, 321 killed, 7 escaped, all equivalent (casts that PHP performs the same way without them, and a repetition check that reads `$value[1]` where it reads `$value[0]`, equal only when every character is the same); MSI 97.87%, mutation code coverage 100%.
