# Placa de veículo e número de processo do CNJ

## `BrSpec::vehiclePlate(PlateKind $kind = PlateKind::Any): VehiclePlateSpecification`

Uma placa de veículo brasileira:

- **Antiga:** três letras e quatro dígitos, `AAA9999` ou `AAA-9999`.
- **Mercosul:** três letras, um dígito, uma letra e dois dígitos, `AAA9A99`, sem hífen.

Letras minúsculas são aceitas. O `PlateKind` é `Any` (padrão), `Legacy` ou `Mercosur`. Se a placa está registrada é pergunta para o Detran.

## `BrSpec::cnjProcessNumber(DocumentFormat $format = DocumentFormat::Any): CnjProcessNumberSpecification`

Um número único de processo do Judiciário brasileiro (Resolução CNJ 65/2008): vinte dígitos `NNNNNNN DD AAAA J TR OOOO` (sequencial, dígitos verificadores, ano, segmento do Judiciário, tribunal, origem), com a máscara `NNNNNNN-DD.AAAA.J.TR.OOOO` ou sem máscara, conforme o formato.

- O segmento `J` vai de 1 a 9.
- Os dígitos verificadores `DD` vão de 02 a 98, e o número reordenado como `NNNNNNN AAAA J TR OOOO DD` dá resto 1 na divisão por 97 (ISO 7064 MOD 97-10).
- O tribunal e a origem não são conferidos contra uma tabela.

<!-- tested: vehicle-and-court -->
```php
<?php

declare(strict_types=1);

use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\PlateKind;

BrSpec::vehiclePlate()->isSatisfiedBy('ABC-1234'); // true
BrSpec::vehiclePlate()->isSatisfiedBy('BRA2E19'); // true
BrSpec::vehiclePlate(PlateKind::Legacy)->isSatisfiedBy('BRA2E19'); // false (uma placa Mercosul)
BrSpec::cnjProcessNumber()->isSatisfiedBy('1606504-41.2041.6.53.3394'); // true
BrSpec::cnjProcessNumber()->isSatisfiedBy('1606504-42.2041.6.53.3394'); // false (um dígito verificador errado)
```

## Forma serializada

| Nome | Campos |
| :--- | :--- |
| `brVehiclePlate` | `kind`: `any`, `legacy` ou `mercosur` (padrão `any`) |
| `brCnjProcessNumber` | `format`: `any`, `masked` ou `unmasked` (padrão `any`) |
