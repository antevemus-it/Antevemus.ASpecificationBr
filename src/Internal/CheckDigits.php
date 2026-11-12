<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Internal;

/**
 * CheckDigits - The check-digit routines of the Brazilian documents, on already normalized values.
 *
 * The CPF and CNPJ routines were first written by the copyright holder for an internal product and are
 * released here under the MIT License (see NOTICE.md). The CNPJ routine follows the alphanumeric CNPJ of the
 * Receita Federal: the twelve leading characters may be digits or capital letters, each worth its ASCII code
 * minus 48, and the two check digits stay numeric, so a numeric CNPJ gives the classic result. The CNJ routine
 * follows the ISO 7064 MOD 97-10 check of the unified process number (CNJ Resolution 65/2008).
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
final class CheckDigits
{
    /**
     * Whether eleven digits are a CPF: not a repetition of one digit, and both check digits right.
     *
     * @param string $value Eleven characters, already without the mask
     * @return bool True when the value is a CPF
     */
    public static function isCpf(string $value): bool
    {
        if (preg_match('/^[0-9]{11}$/D', $value) !== 1) {
            return false;
        }

        if ($value === str_repeat($value[0], 11)) {
            return false; // a repetition passes the arithmetic but is never a CPF
        }

        foreach ([9, 10] as $position) {
            $sum = 0;

            for ($i = 0; $i < $position; $i++) {
                $sum += ((int) $value[$i]) * ($position + 1 - $i);
            }

            if ((int) $value[$position] !== self::modulo11($sum)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether fourteen characters are a CNPJ, numeric or alphanumeric.
     *
     * @param string $value Fourteen characters, already without the mask and in capital letters
     * @return bool True when the form and both check digits are right
     */
    public static function isCnpj(string $value): bool
    {
        if (preg_match('/^[0-9A-Z]{12}[0-9]{2}$/D', $value) !== 1) {
            return false;
        }

        if ($value === str_repeat($value[0], 14)) {
            return false; // a repetition (always of a digit: the check digits are digits) is never a registration
        }

        foreach ([12, 13] as $position) {
            $weight = 2;
            $sum = 0;

            for ($i = $position - 1; $i >= 0; $i--) {
                $sum += (ord($value[$i]) - 48) * $weight;
                $weight = $weight === 9 ? 2 : $weight + 1;
            }

            if ((int) $value[$position] !== self::modulo11($sum)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether twenty digits are a unified process number of the CNJ.
     *
     * The digits are NNNNNNN DD AAAA J TR OOOO (sequence, check digits, year, segment of the judiciary,
     * court, origin). The segment is 1 to 9, the check digits are 02 to 98 (they are 98 minus a remainder
     * modulo 97), and the number rearranged as NNNNNNN AAAA J TR OOOO DD is congruent to 1 modulo 97.
     *
     * @param string $value Twenty digits, already without the mask
     * @return bool True when the segment and the check digits are right
     */
    public static function isCnjProcessNumber(string $value): bool
    {
        if (preg_match('/^[0-9]{20}$/D', $value) !== 1) {
            return false;
        }

        if ($value[13] === '0') {
            return false; // the segment of the judiciary is 1 to 9
        }

        $checkDigits = (int) substr($value, 7, 2);
        if ($checkDigits < 2 || $checkDigits > 98) {
            return false; // DD = 98 - (remainder modulo 97) is never 00, 01 or 99
        }

        $rearranged = substr($value, 0, 7) . substr($value, 9) . substr($value, 7, 2);
        $remainder = 0;

        foreach (str_split($rearranged) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $remainder === 1;
    }

    /**
     * The Brazilian modulo 11 rule on a weighted sum.
     *
     * @param int $sum Weighted sum of the characters
     * @return int Check digit from 0 to 9
     */
    private static function modulo11(int $sum): int
    {
        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
