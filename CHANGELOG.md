# Changelog

All notable changes to this package are recorded here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package follows [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.1.0] - 2026-11-12

### Added

- `CnhSpecification` (`BrSpec::cnh()`, codec name `brCnh`): the national registration number of a driver's license, eleven digits, not a repetition, both check digits by the modulo 11 routine attributed to Denatran and used by the public validators, followed literally: a first remainder of 10 writes 0 and lowers the second digit by 2, a second remainder of 10 writes 0, and a base whose second digit would be negative (first remainder 10, second 0 or 1) has no valid completion. No mask: only the eleven digits.
- `PisSpecification` (`BrSpec::pis()`, codec name `brPis`): a PIS/PASEP number (also NIS or NIT), masked `###.#####.##-#` or unmasked as `DocumentFormat` allows, the check digit by the weights 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 modulo 11; the zeros are refused.
- `VoterIdSpecification` (`BrSpec::voterId()`, codec name `brVoterId`): a voter registration number (título de eleitor), twelve digits, the state code 01 to 28, both check digits modulo 11, a remainder of 0 giving 1 for SP and MG. No mask: only the twelve digits.
- `RenavamSpecification` (`BrSpec::renavam()`, codec name `brRenavam`): the national registry code of a vehicle, eleven digits (a nine-digit one with two leading zeros), the check digit modulo 11; the zeros are refused.
- `PixKeySpecification` (`BrSpec::pixKey()`, codec name `brPixKey`) and `PixKeyKind` (`Any`, `Cpf`, `Cnpj`, `Phone`, `Email`, `Random`): a PIX key as the DICT of the Banco Central stores it. A CPF or a CNPJ without punctuation, with their checks (the alphanumeric CNPJ included, in capital letters, as the DICT adopted it from 2026-07-01); a mobile number `+55` + area code + `9` + eight digits; an e-mail in small letters of at most 77 characters (`PixKeySpecification::EMAIL_MAX_LENGTH`), by a conservative subset of the DICT pattern; a random key as a UUID of RFC 4122 in small letters (version 1 to 5, variant 8, 9, a or b; the example of the DICT API specification 2.13.0 is a version 1 UUID, accepted).
- `BrSpec::TYPES` gains `brCnh`, `brPis`, `brVoterId`, `brRenavam` and `brPixKey`; `BrSpec::register()` registers them.
- Documentation pages "CNH, PIS/PASEP, voter registration and RENAVAM" and "PIX keys", in `docs/en` and `docs/pt-BR`, every PHP block run by the test suite.

### Changed

- The `composer.json` description is shorter than 255 characters, the length Packagist shows, and names the new documents.
- `DocumentFormat` is also used by `PisSpecification`. Nothing of 1.0.0 changes: the classes, the signatures, the enum cases, the codec names and the serialized forms are the same.

### Notes

- The check-digit routines follow the public descriptions named in `src/Internal/CheckDigits.php`; where no official text with the algorithm was found (the CNH routine, which Contran Resolution 886/2021 does not publish, the PIS/PASEP and RENAVAM weights, the voter registration weights and the rule of SP and MG), the docblock says so. The PIX key forms follow section 1 of the DICT operational manual (version 8.5) and the patterns of the DICT API specification.
- The PIS/PASEP and the RENAVAM have the same check digit: Denatran's writing (weights 2..9, 2, 3 from the right, ten times the sum modulo 11) is the PIS rule read from the other end.
- The test generator computes every new check digit by a route of its own (complementary weights for the CNH, with the discount read from the first remainder, and for the PIS/PASEP and the voter registration number, Denatran's reversed writing for the RENAVAM); before the tests were written it was cross-checked against `src/` over 50 000 generated values of each routine (each one the only accepted completion of its base) and 250 000 generated PIX keys; for the CNH also over 200 000 random bases (3 315 without a completion, 196 685 with exactly one, none with more) and against the routine written out literally as a separate oracle.
- Suite: 527 tests (Unit and Property). Mutation testing over the whole `src/`: 587 mutants, 573 killed, 14 escaped, all equivalent (listed in `infection.json5`); MSI 97.61%.

## [1.0.0] - 2026-11-12

### Added

- `CpfSpecification`: eleven digits, masked `###.###.###-##` or unmasked, not a repetition, both check digits (modulo 11).
- `CnpjSpecification`: fourteen characters, masked `##.###.###/####-##` or unmasked; the alphanumeric CNPJ of the Receita Federal (letters in the twelve leading characters, each worth its ASCII code minus 48; small letters read as capitals), or digits only with `alphanumeric: false`; not a numeric repetition; both check digits.
- `CepSpecification`: eight digits, masked `#####-###` or unmasked, from `01000-000` to `99999-999`.
- `PhoneSpecification`: a Brazilian phone number with spaces, parentheses, hyphens, dots and one leading `+` allowed; 10 digits (landline, `2` to `5`) or 11 (mobile, `9`), optionally preceded by the country code `55`; one of the 67 area codes of the numbering plan (`PhoneSpecification::AREA_CODES`).
- `VehiclePlateSpecification`: legacy `AAA9999`/`AAA-9999` or Mercosur `AAA9A99`, small letters accepted.
- `CnjProcessNumberSpecification`: twenty digits, masked `NNNNNNN-DD.AAAA.J.TR.OOOO` or unmasked; segment 1 to 9; check digits 02 to 98 and ISO 7064 MOD 97-10.
- `DocumentFormat` (`Any`, `Masked`, `Unmasked`), `PhoneKind` (`Any`, `Mobile`, `Landline`) and `PlateKind` (`Any`, `Legacy`, `Mercosur`).
- `BrSpec`: the facade (`cpf()`, `cnpj()`, `cep()`, `phone()`, `vehiclePlate()`, `cnjProcessNumber()`), the codec names (`BrSpec::TYPES`: `brCpf`, `brCnpj`, `brCep`, `brPhone`, `brVehiclePlate`, `brCnjProcessNumber`) and `BrSpec::register()` for a `SpecificationCodec`.
- Only strings are candidates (an integer would lose a leading zero) and nothing is trimmed; no failure message, failure code or serialized form repeats the candidate.
- Documentation in `docs/en` and `docs/pt-BR` (Docs Kit format), every PHP block run by the test suite.

### Notes

- Requires `antevemus/aspecification` `^2.13`; uses only its public API (`AbstractSpecification`, `ISerializableSpecification`, `SpecificationCodec::register()`), and the core is not changed.
- The CPF and CNPJ check-digit routines are first-party code of the copyright holder, relicensed under MIT (see NOTICE.md).
- Suite: 250 tests (Unit and Property). Mutation testing over the whole `src/`: 328 mutants, 321 killed, 7 escaped, all equivalent; MSI 97.87%.
