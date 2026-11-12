<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr;

/**
 * PhoneKind - The kind of Brazilian phone number a PhoneSpecification accepts.
 *
 * - `Any`: a mobile or a landline number.
 * - `Mobile`: 11 national digits, area code + `9` + 8 digits.
 * - `Landline`: 10 national digits, area code + `2` to `5` + 7 digits.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
enum PhoneKind: string
{
    case Any = 'any';
    case Mobile = 'mobile';
    case Landline = 'landline';
}
