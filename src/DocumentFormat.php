<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr;

/**
 * DocumentFormat - The written form a document specification accepts.
 *
 * - `Any`: the masked form or the unmasked form, exactly; never partial punctuation.
 * - `Masked`: only the official mask (`###.###.###-##` for a CPF, for example).
 * - `Unmasked`: only the bare characters, without any punctuation.
 *
 * Used by CpfSpecification, CnpjSpecification, CepSpecification and CnjProcessNumberSpecification.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
enum DocumentFormat: string
{
    case Any = 'any';
    case Masked = 'masked';
    case Unmasked = 'unmasked';
}
