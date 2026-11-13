# Tests

The suite runs with PHPUnit in two groups, `Unit` and `Property`, in the CI of every push on PHP 8.2, 8.3 and 8.4:

```bash
vendor/bin/phpunit
```

## What is tested

- **Fixed cases** of every specification: the forms accepted and refused by each `DocumentFormat`, `PhoneKind`, `PlateKind` and `PixKeyKind`, each check digit wrong, repetitions, the boundaries of the postal code range, every one of the 100 two-digit area codes (as a phone number and as a PIX key), values with a space or a newline at either end, integers, `null` and the empty string. For the rules added in 1.1.0, a case reaches each branch: the discount of the CNH (with the second remainder at 10 and in between, and the bases with a second remainder of 0 or 1, which have no completion: all 100 pairs of check digits refused), a remainder of 0 for SP, MG and the other states in both digits of the voter registration number, the state codes 00, 28, 29 and 99 with right check digits, the remainders 0 and 1 of the PIS/PASEP and of the RENAVAM, and the lengths 77 and 78 of an e-mail key, 64 and 65 of its local part, 63 and 64 of a label. Every value is fictitious or the public example of an official specification.
- **The copied routines answer as their origin:** the CPF and CNPJ check-digit routines were first written by the copyright holder for an internal product (see NOTICE.md); its cases are repeated here with the same expected answers.
- **Properties over generated documents** (fixed seeds, 400 iterations each): every generated CPF, CNPJ (numeric and alphanumeric), postal code, process number and PIS/PASEP is accepted in the forms its format allows and refused in the other, and every generated CNH, voter registration number and RENAVAM is accepted; changing one check digit to any other digit is always refused; `Any` answers exactly `Masked or Unmasked`, over noise strings and over documents with some punctuation removed; a voter registration number with a state code outside 01 to 28 is refused even with its check digits; every generated phone number is accepted in five writings and only for its own kind; every plate only for its own kind; every generated PIX key only for its own kind (and `Any`), and refused in capitals or with a mask. The generator computes the check digits by a route of its own (the three-step computation of the CNJ resolution, the "times ten, modulo eleven" form of the CPF rule, the fixed tables of weights of the CNPJ, complementary weights for the CNH (with the discount read from the first remainder), complementary weights for the PIS/PASEP and the voter registration number, the reversed writing of the RENAVAM), so the tests do not confirm the validator with itself. Before the 1.1.0 tests were written, the generator and `src/` were cross-checked over 50 000 generated values of each new routine (each one the only accepted completion of its base) and 250 000 generated PIX keys; for the CNH also over 200 000 random bases (3 315 without a completion, 196 685 with exactly one, none with more) and against the routine written out literally as a separate oracle.
- **A guard over a pool** that holds every concrete class of `src/Specifications` with every option (a new class left out fails it): every one has a name in `BrSpec::TYPES`, survives the round trip through the codec with its options, and never repeats the candidate in a failure message, a failure code or its serialized form.
- **The documentation:** every PHP block of `docs/en` and `docs/pt-BR`, and the Quick Start of each README, runs with the answers written in its comments; both languages carry the same code and the same files.

## Mutation testing

Infection runs over the whole `src/` in a scheduled workflow, with minimum scores one point below the last measure. Measured for 1.1.0: 587 mutants, 573 killed, 14 escaped, all equivalent (casts that PHP performs the same way without them, and repetition checks that read `$value[1]` where they read `$value[0]`, equal only when every character is the same); MSI 97.61%, mutation code coverage 100%. For 1.0.0 it was 328 mutants, 321 killed, 7 escaped; MSI 97.87%.

The suite has 527 tests (250 in 1.0.0).
