# Roadmap

The Brazilian documents satellite of ASpecification. The core stays free of anything specific to a country; this package carries the Brazilian documents as leaf specifications.

## Milestones

- [x] **1.0.0:** CPF, CNPJ (numeric and alphanumeric), CEP, phone number, vehicle plate (legacy and Mercosur), CNJ process number; `DocumentFormat`, `PhoneKind`, `PlateKind`; the `BrSpec` facade and the codec names.
- [x] **1.1.0:** CNH, PIS/PASEP (NIS/NIT), voter registration number (título de eleitor) and RENAVAM, with their check digits; PIX keys as the DICT stores them (`PixKeyKind`).

## On demand

Not planned; each one comes when a consumer needs it:

- [ ] A reading of a document's parts (the root and the branch of a CNPJ, the court of a process number).

Finding these documents inside free text, masking them and recording what was masked is a different job, for a package that would depend on this one.
