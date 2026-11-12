<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr;

/**
 * PlateKind - The kind of Brazilian vehicle plate a VehiclePlateSpecification accepts.
 *
 * - `Any`: a legacy plate or a Mercosur plate.
 * - `Legacy`: three letters and four digits, `AAA9999` or `AAA-9999`.
 * - `Mercosur`: three letters, a digit, a letter and two digits, `AAA9A99`.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
enum PlateKind: string
{
    case Any = 'any';
    case Legacy = 'legacy';
    case Mercosur = 'mercosur';
}
