<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Internal;

use Antevemus\ASpecificationBr\DocumentFormat;

/**
 * WrittenForm - Reads a candidate in the masked or the unmasked form of a document.
 *
 * @internal Not part of the public API; the specifications are.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Internal
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class WrittenForm
{
    /**
     * The characters of the document without the mask and in capital letters, when the candidate is a string
     * written exactly in one of the forms the format accepts; null otherwise.
     *
     * @param mixed          $candidate Value under evaluation
     * @param DocumentFormat $format    Forms accepted
     * @param string         $masked    Full pattern of the masked form
     * @param string         $unmasked  Full pattern of the unmasked form
     * @return string|null The bare characters, or null when the form is not accepted
     */
    public static function read(mixed $candidate, DocumentFormat $format, string $masked, string $unmasked): ?string
    {
        if (!is_string($candidate)) {
            return null;
        }

        $accepted = match ($format) {
            DocumentFormat::Masked => preg_match($masked, $candidate) === 1,
            DocumentFormat::Unmasked => preg_match($unmasked, $candidate) === 1,
            DocumentFormat::Any => preg_match($masked, $candidate) === 1 || preg_match($unmasked, $candidate) === 1,
        };

        if (!$accepted) {
            return null;
        }

        return strtoupper((string) preg_replace('/[^0-9A-Za-z]/', '', $candidate));
    }
}
