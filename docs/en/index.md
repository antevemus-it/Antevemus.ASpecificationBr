# Antevemus ASpecification BR

The official documentation of the package, one page per topic. The same pages exist in Portuguese (Brazil), in `docs/pt-BR`, and every PHP block of these pages is run by the test suite with the answers written in its comments.

> **The Brazilian documents satellite of [ASpecification](https://github.com/antevemus-it/Antevemus.ASpecification) for PHP 8.2+**  
> Leaf specifications for CPF, CNPJ (numeric and alphanumeric), CEP, phone number, vehicle plate (legacy and Mercosur) and CNJ court process number: check digits, the exact masked or unmasked form, a name for each one in the codec, and failure messages that never repeat the value under evaluation. They compose with the core's `and`, `or`, `not` and `Spec::property()`.

## Documentation Map

### Getting started

- [Installation and quick start](getting-started/index.md): Composer, the requirements and the Quick Start.

### Concepts

- [Forms, check digits and secrecy](concepts/forms-and-check-digits.md): What a candidate is, the masked and unmasked forms, what each check proves and what it does not, the failure messages, and the codec.

### API reference

- [CPF and CNPJ](api/taxpayer-ids.md): `BrSpec::cpf()`, `BrSpec::cnpj()` and the alphanumeric CNPJ.
- [CEP and phone number](api/address-and-phone.md): `BrSpec::cep()`, `BrSpec::phone()`, the kinds of number and the area codes.
- [Vehicle plate and CNJ process number](api/vehicle-and-court.md): `BrSpec::vehiclePlate()` and `BrSpec::cnjProcessNumber()`.

### Quality

- [Tests](quality/tests.md): The PHPUnit report, the properties over generated documents and the mutation testing.

### Project

- [Roadmap](project/roadmap.md): The release shipped and what may come next.

## Where Is Each Thing

| What | Where |
| :--- | :--- |
| Overview and the Quick Start | [README.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/README.md) |
| What changed in each version | [CHANGELOG.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/CHANGELOG.md) |
| Milestones shipped and planned | [ROADMAP.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/ROADMAP.md) |
| License and provenance | [LICENSE](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/LICENSE), [NOTICE.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/NOTICE.md) |
