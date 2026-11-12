# Testes

A suíte roda com o PHPUnit em dois grupos, `Unit` e `Property`, no CI de todo push, no PHP 8.2, 8.3 e 8.4:

```bash
vendor/bin/phpunit
```

## O que é testado

- **Casos fixos** de toda especificação: as formas aceitas e recusadas por cada `DocumentFormat`, `PhoneKind` e `PlateKind`, cada dígito verificador errado, repetições, os limites da faixa do CEP, cada um dos 100 DDDs de dois dígitos, valores com espaço ou quebra de linha numa das pontas, inteiros, `null` e a string vazia. Todo valor é fictício ou é o exemplo público de uma especificação oficial.
- **As rotinas copiadas respondem como a origem:** as rotinas de dígito verificador do CPF e do CNPJ foram escritas primeiro pelo titular para um produto interno (veja o NOTICE.md); os casos dela se repetem aqui com as mesmas respostas esperadas.
- **Propriedades sobre documentos gerados** (sementes fixas, 400 iterações cada): todo CPF, CNPJ (numérico e alfanumérico), CEP e número de processo gerado é aceito nas formas que o formato permite e recusado na outra; trocar um dígito verificador por qualquer outro dígito é sempre recusado; o `Any` responde exatamente `Masked ou Unmasked`, sobre textos aleatórios e sobre documentos com parte da pontuação removida; todo telefone gerado é aceito em cinco escritas e só para o próprio tipo; toda placa, só para o próprio tipo. O gerador calcula os dígitos verificadores por um caminho próprio (o cálculo em três etapas da resolução do CNJ, a forma "vezes dez, módulo onze" da regra do CPF, as tabelas fixas de pesos do CNPJ), para os testes não confirmarem o validador com ele mesmo.
- **Uma guarda sobre um conjunto** que tem toda classe concreta de `src/Specifications` com toda opção (classe nova deixada de fora reprova): toda uma tem nome em `BrSpec::TYPES`, sobrevive à ida e volta pelo codec com as suas opções, e nunca repete o candidato numa mensagem de falha, num código de falha nem na forma serializada.
- **A documentação:** todo bloco PHP de `docs/en` e `docs/pt-BR`, e o Quick Start de cada README, roda com as respostas escritas nos comentários; as duas línguas têm o mesmo código e os mesmos arquivos.

## Teste de mutação

O Infection roda sobre todo o `src/` num workflow agendado, com pisos um ponto abaixo da última medida. Medido para a 1.0.0: 328 mutantes, 321 mortos, 7 escaparam, todos equivalentes (conversões que o PHP faz do mesmo jeito sem elas, e uma checagem de repetição que lê `$value[1]` onde lê `$value[0]`, igual só quando todos os caracteres são iguais); MSI 97,87 %, cobertura de mutação 100 %.
