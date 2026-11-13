# Antevemus ASpecification BR

A documentação oficial do pacote, uma página por assunto. As mesmas páginas existem em inglês, em `docs/en`, e todo bloco PHP destas páginas roda na suíte de testes com as respostas escritas nos comentários.

> **O satélite de documentos brasileiros da [ASpecification](https://github.com/antevemus-it/Antevemus.ASpecification) para PHP 8.2+**  
> Especificações-folha para CPF, CNPJ (numérico e alfanumérico), CEP, telefone, placa de veículo (antiga e Mercosul), número de processo do CNJ, CNH, PIS/PASEP, título de eleitor, RENAVAM e chave PIX: dígitos verificadores, a forma exata com máscara ou sem máscara, um nome para cada uma no codec e mensagens de falha que nunca repetem o valor avaliado. Elas se compõem com o `and`, o `or`, o `not` e o `Spec::property()` do núcleo.

## Mapa da documentação

### Primeiros passos

- [Instalação e início rápido](getting-started/index.md): o Composer, os requisitos e o Quick Start.

### Conceitos

- [Formas, dígitos verificadores e sigilo](concepts/forms-and-check-digits.md): o que é um candidato, as formas com e sem máscara, o que cada verificação prova e o que não prova, as mensagens de falha e o codec.

### Referência da API

- [CPF e CNPJ](api/taxpayer-ids.md): `BrSpec::cpf()`, `BrSpec::cnpj()` e o CNPJ alfanumérico.
- [CEP e telefone](api/address-and-phone.md): `BrSpec::cep()`, `BrSpec::phone()`, os tipos de número e os DDDs.
- [Placa de veículo e número de processo do CNJ](api/vehicle-and-court.md): `BrSpec::vehiclePlate()` e `BrSpec::cnjProcessNumber()`.
- [CNH, PIS/PASEP, título de eleitor e RENAVAM](api/registration-numbers.md): `BrSpec::cnh()`, `BrSpec::pis()`, `BrSpec::voterId()` e `BrSpec::renavam()`.
- [Chaves PIX](api/pix-keys.md): `BrSpec::pixKey()` e os tipos de chave do DICT.

### Qualidade

- [Testes](quality/tests.md): o relatório do PHPUnit, as propriedades sobre documentos gerados e o teste de mutação.

### Projeto

- [Roadmap](project/roadmap.md): a versão publicada e o que pode vir depois.

## Onde fica cada coisa

| O quê | Onde |
| :--- | :--- |
| Visão geral e o Quick Start | [README.pt-BR.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/README.pt-BR.md) |
| O que mudou em cada versão | [CHANGELOG.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/CHANGELOG.md) |
| Marcos entregues e planejados | [ROADMAP.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/ROADMAP.md) |
| Licença e procedência | [LICENSE](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/LICENSE), [NOTICE.md](https://github.com/antevemus-it/Antevemus.ASpecificationBr/blob/main/NOTICE.md) |
