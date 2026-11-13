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
 * The routines added in 1.1.0 (CNH, PIS/PASEP, voter registration number and RENAVAM) follow the public
 * descriptions named in the docblock of each one, and say there what could not be checked against the text of
 * an official norm (none of the four has its check digits published in one).
 *
 * @internal Not part of the public API; the specifications are.
 *
 * @version    1.1.0
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
     * Whether eleven digits are the national registration number of a driver's license (CNH).
     *
     * Contran Resolution 886/2021 (art. 4, I) defines the number only as nine characters and two security check
     * digits; no official text publishes how the check digits are computed. The routine here is the one
     * attributed to Denatran and used by the public validators, followed literally so that a number they
     * accept is accepted here:
     *
     *     dsc = 0
     *     v = sum of d[i] * (9 - i), i = 0..8;  dv1 = v % 11;  if dv1 >= 10 then dv1 = 0 and dsc = 2
     *     v = sum of d[i] * (1 + i), i = 0..8;  x = v % 11;   dv2 = x >= 10 ? 0 : x - dsc
     *
     * When the first remainder is 10 and the second is 0 or 1, x - dsc is negative: such a base has no valid
     * completion and is never a CNH. A repetition of one digit passes the arithmetic and is refused.
     *
     * @param string $value Eleven characters
     * @return bool True when both check digits are right
     */
    public static function isCnh(string $value): bool
    {
        if (preg_match('/^[0-9]{11}$/D', $value) !== 1 || $value === str_repeat($value[0], 11)) {
            return false;
        }

        $first = 0;
        $second = 0;
        for ($i = 0; $i < 9; $i++) {
            $first += ((int) $value[$i]) * (9 - $i);
            $second += ((int) $value[$i]) * ($i + 1);
        }

        $first %= 11;
        $discount = 0;
        if ($first === 10) {
            $first = 0;
            $discount = 2; // the adjustment the first digit passes on to the second
        }
        $second %= 11;
        $second = $second === 10 ? 0 : $second - $discount; // negative: no digit completes the base

        return (int) $value[9] === $first && (int) $value[10] === $second;
    }

    /**
     * Whether eleven digits are a PIS/PASEP number (also written NIS or NIT): ten digits and one check digit,
     * the ten weighted 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 under the modulo 11 rule (a remainder of 0 or 1 gives 0),
     * the routine of the Caixa as public sources describe it (no norm with its text was found). Of the
     * repetitions, only the zeros pass the arithmetic; they are refused.
     *
     * @param string $value Eleven characters, already without the mask
     * @return bool True when the check digit is right
     */
    public static function isPis(string $value): bool
    {
        return self::isElevenDigitsWithOneCheckDigit($value);
    }

    /**
     * Whether eleven digits are a RENAVAM (the national registry code of a vehicle): ten digits and one check
     * digit. The routine of Denatran, as public sources describe it (no norm with its text was found), weights
     * the ten digits 2, 3, 4, 5, 6, 7, 8, 9, 2, 3 from the right, takes ten times the sum modulo 11, and writes
     * 0 for 10: the same digit as the PIS rule, since its weights read from the left are 3, 2, 9, 8, 7, 6, 5,
     * 4, 3, 2 and ten times a sum is minus the sum modulo 11. A RENAVAM of nine digits, from before the
     * eleven-digit numbering, is written with two leading zeros. Of the repetitions, only the zeros pass the
     * arithmetic; they are refused.
     *
     * @param string $value Eleven characters
     * @return bool True when the check digit is right
     */
    public static function isRenavam(string $value): bool
    {
        return self::isElevenDigitsWithOneCheckDigit($value);
    }

    /**
     * Whether twelve digits are a voter registration number (título de eleitor): an eight-digit sequence, the
     * code of the state (01 to 28: 01 is SP, 02 is MG, 28 is abroad) and two check digits, the first over the
     * sequence weighted 2 to 9 and the second over the state code and the first check digit weighted 7, 8 and
     * 9. TSE Resolution 20.132/1998 sets what each digit is computed over (the sequence; the state code
     * followed by the first digit); the weights and the rule of the remainders are those of public
     * descriptions of the TSE routine: each digit is the remainder modulo 11, a remainder of 10 gives 0, and a
     * remainder of 0 gives 1 for SP and MG (0 elsewhere). The repetitions fail the state code or the
     * arithmetic.
     *
     * @param string $value Twelve characters
     * @return bool True when the state code and both check digits are right
     */
    public static function isVoterId(string $value): bool
    {
        if (preg_match('/^[0-9]{12}$/D', $value) !== 1) {
            return false;
        }

        $state = (int) substr($value, 8, 2);
        if ($state < 1 || $state > 28) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ((int) $value[$i]) * ($i + 2);
        }
        $first = self::voterIdDigit($sum, $state);
        $second = self::voterIdDigit(((int) $value[8]) * 7 + ((int) $value[9]) * 8 + $first * 9, $state);

        return (int) $value[10] === $first && (int) $value[11] === $second;
    }

    /**
     * Eleven digits whose last is the modulo 11 check digit of the ten before, weighted 3, 2, 9, 8, 7, 6, 5,
     * 4, 3, 2; the zeros are refused.
     *
     * @param string $value Eleven characters
     * @return bool True when the check digit is right
     */
    private static function isElevenDigitsWithOneCheckDigit(string $value): bool
    {
        if (preg_match('/^[0-9]{11}$/D', $value) !== 1 || $value === '00000000000') {
            return false;
        }

        $sum = 0;
        foreach ([3, 2, 9, 8, 7, 6, 5, 4, 3, 2] as $i => $weight) {
            $sum += ((int) $value[$i]) * $weight;
        }

        return (int) $value[10] === self::modulo11($sum);
    }

    /**
     * A check digit of the voter registration number.
     *
     * @param int $sum   Weighted sum
     * @param int $state Code of the state, 1 to 28
     * @return int Check digit from 0 to 9
     */
    private static function voterIdDigit(int $sum, int $state): int
    {
        $remainder = $sum % 11;

        return match (true) {
            $remainder === 10 => 0,
            $remainder === 0 && $state <= 2 => 1, // SP (01) and MG (02)
            default => $remainder,
        };
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
