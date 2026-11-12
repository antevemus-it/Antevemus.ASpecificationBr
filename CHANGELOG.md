# Changelog

All notable changes to this package are recorded here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package follows [Semantic Versioning](https://semver.org/).

## [Unreleased]

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
