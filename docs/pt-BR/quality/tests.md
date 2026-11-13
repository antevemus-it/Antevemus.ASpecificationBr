# Testes

A suíte roda com o PHPUnit em dois grupos, `Unit` e `Property`, no CI de todo push, no PHP 8.2, 8.3 e 8.4:

```bash
vendor/bin/phpunit
```

## O que é testado

- **Casos fixos** de toda especificação: as formas aceitas e recusadas por cada `DocumentFormat`, `PhoneKind`, `PlateKind` e `PixKeyKind`, cada dígito verificador errado, repetições, os limites da faixa do CEP, cada um dos 100 DDDs de dois dígitos (como telefone e como chave PIX), valores com espaço ou quebra de linha numa das pontas, inteiros, `null` e a string vazia. Para as regras acrescentadas na 1.1.0, um caso alcança cada ramo: o desconto da CNH (com o segundo resto em 10 e no meio, e as bases com segundo resto 0 ou 1, que não têm complemento: os 100 pares de dígitos verificadores recusados), um resto 0 para SP, MG e as demais UFs nos dois dígitos do título de eleitor, os códigos de UF 00, 28, 29 e 99 com os dígitos verificadores certos, os restos 0 e 1 do PIS/PASEP e do RENAVAM, e os comprimentos 77 e 78 da chave e-mail, 64 e 65 da parte local, 63 e 64 de um rótulo. Todo valor é fictício ou é o exemplo público de uma especificação oficial.
- **As rotinas copiadas respondem como a origem:** as rotinas de dígito verificador do CPF e do CNPJ foram escritas primeiro pelo titular para um produto interno (veja o NOTICE.md); os casos dela se repetem aqui com as mesmas respostas esperadas.
- **Propriedades sobre documentos gerados** (sementes fixas, 400 iterações cada): todo CPF, CNPJ (numérico e alfanumérico), CEP, número de processo e PIS/PASEP gerado é aceito nas formas que o formato permite e recusado na outra, e toda CNH, título de eleitor e RENAVAM gerado é aceito; trocar um dígito verificador por qualquer outro dígito é sempre recusado; o `Any` responde exatamente `Masked ou Unmasked`, sobre textos aleatórios e sobre documentos com parte da pontuação removida; um título de eleitor com código de UF fora de 01 a 28 é recusado mesmo com os seus dígitos verificadores; todo telefone gerado é aceito em cinco escritas e só para o próprio tipo; toda placa, só para o próprio tipo; toda chave PIX gerada, só para o próprio tipo (e o `Any`), e recusada em maiúsculas ou com máscara. O gerador calcula os dígitos verificadores por um caminho próprio (o cálculo em três etapas da resolução do CNJ, a forma "vezes dez, módulo onze" da regra do CPF, as tabelas fixas de pesos do CNPJ, pesos complementares para a CNH (com o desconto lido do primeiro resto), pesos complementares para o PIS/PASEP e o título de eleitor, a escrita invertida do RENAVAM), para os testes não confirmarem o validador com ele mesmo. Antes de os testes da 1.1.0 serem escritos, o gerador e o `src/` foram conferidos um contra o outro sobre 50 000 valores gerados de cada rotina nova (cada um o único complemento aceito da sua base) e 250 000 chaves PIX geradas; para a CNH também sobre 200 000 bases aleatórias (3 315 sem complemento, 196 685 com exatamente um, nenhuma com mais) e contra a rotina escrita ao pé da letra como oráculo separado.
- **Uma guarda sobre um conjunto** que tem toda classe concreta de `src/Specifications` com toda opção (classe nova deixada de fora reprova): toda uma tem nome em `BrSpec::TYPES`, sobrevive à ida e volta pelo codec com as suas opções, e nunca repete o candidato numa mensagem de falha, num código de falha nem na forma serializada.
- **A documentação:** todo bloco PHP de `docs/en` e `docs/pt-BR`, e o Quick Start de cada README, roda com as respostas escritas nos comentários; as duas línguas têm o mesmo código e os mesmos arquivos.

## Teste de mutação

O Infection roda sobre todo o `src/` num workflow agendado, com pisos um ponto abaixo da última medida. Medido para a 1.1.0: 587 mutantes, 573 mortos, 14 escaparam, todos equivalentes (conversões que o PHP faz do mesmo jeito sem elas, e checagens de repetição que leem `$value[1]` onde leem `$value[0]`, iguais só quando todos os caracteres são iguais); MSI 97,61 %, cobertura de mutação 100 %. Na 1.0.0 foram 328 mutantes, 321 mortos, 7 escaparam; MSI 97,87 %.

A suíte tem 527 testes (250 na 1.0.0).
