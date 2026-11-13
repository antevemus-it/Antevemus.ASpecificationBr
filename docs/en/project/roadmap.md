# Roadmap

## Shipped

- **1.0.0:** CPF, CNPJ (numeric and alphanumeric), CEP, phone number, vehicle plate (legacy and Mercosur) and CNJ process number, with `DocumentFormat`, `PhoneKind` and `PlateKind`, the `BrSpec` facade and the codec names.
- **1.1.0:** CNH (driver's license number), PIS/PASEP (NIS/NIT), voter registration number (título de eleitor) and RENAVAM, with their check digits; PIX keys as the DICT stores them (a CPF, a CNPJ, a mobile number, an e-mail or the random key), with `PixKeyKind`.

## On demand

Not planned; each one comes when a consumer needs it:

- [ ] A reading of a document's parts (the root and the branch of a CNPJ, the court of a process number) for the consumers that store them apart.

The milestone view is in [ROADMAP.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/ROADMAP.md).
