<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr;

/**
 * PixKeyKind - The kind of PIX key a PixKeySpecification accepts.
 *
 * - `Any`: a key of any of the five kinds below.
 * - `Cpf`: eleven digits, without punctuation.
 * - `Cnpj`: fourteen characters, numeric or alphanumeric, without punctuation.
 * - `Phone`: a mobile number in E.164, `+55` + area code + `9` + eight digits.
 * - `Email`: an e-mail address in small letters, at most 77 characters.
 * - `Random`: the random key (EVP), a UUID of RFC 4122 (version 1 to 5) in small letters.
 *
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
enum PixKeyKind: string
{
    case Any = 'any';
    case Cpf = 'cpf';
    case Cnpj = 'cnpj';
    case Phone = 'phone';
    case Email = 'email';
    case Random = 'random';
}
