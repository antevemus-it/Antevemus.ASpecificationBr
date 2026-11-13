# Roadmap

## Entregue

- **1.0.0:** CPF, CNPJ (numérico e alfanumérico), CEP, telefone, placa de veículo (antiga e Mercosul) e número de processo do CNJ, com `DocumentFormat`, `PhoneKind` e `PlateKind`, a fachada `BrSpec` e os nomes no codec.
- **1.1.0:** CNH, PIS/PASEP (NIS/NIT), título de eleitor e RENAVAM, com os dígitos verificadores; chaves PIX como o DICT as guarda (um CPF, um CNPJ, um celular, um e-mail ou a chave aleatória), com o `PixKeyKind`.

## Sob demanda

Não planejados; cada um vem quando um consumidor precisar:

- [ ] Uma leitura das partes do documento (a raiz e a filial do CNPJ, o tribunal do número de processo) para os consumidores que as guardam separadas.

A visão por marcos está no [ROADMAP.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/ROADMAP.md).
