<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Support;

/**
 * DocumentGenerator - Fictitious Brazilian documents with right check digits, for the tests.
 *
 * Written apart from src/Internal/CheckDigits on purpose, by another route, so the tests do not confirm the
 * validator with itself:
 * - CPF: the "times ten, modulo eleven" form of the rule (a remainder of 10 gives 0);
 * - CNPJ: the two fixed tables of weights of the Receita Federal, the value of a character being its ASCII
 *   code minus 48;
 * - CNJ: the three-step computation of Annex VIII of CNJ Resolution 65/2008 (R1, R2, R3, DD = 98 - R3);
 * - area codes: the range 11..99 minus the codes the numbering plan of Anatel never assigned;
 * - CNH: the complementary weights (2 to 10 for the first digit, 10 down to 2 for the second, eleven minus each
 *   weight of the routine), whose sums are minus the sums of the routine modulo 11, and the discount read from
 *   the first remainder being 1; a base whose second digit would be negative is drawn again;
 * - PIS/PASEP: the complementary weights 8, 9, 2, 3, 4, 5, 6, 7, 8, 9 (eleven minus each weight of the rule),
 *   whose sum is minus the sum of the rule modulo 11, so the digit is that remainder (10 gives 0);
 * - voter registration number: the complementary weights (9 down to 2 over the sequence, 4, 3 and 2 over the
 *   state code and the first digit) and a table of the special remainders;
 * - RENAVAM: Denatran's own writing, the ten digits reversed and weighted 2..9, 2, 3, ten times the sum
 *   modulo 11;
 * - PIX keys: the CPF and CNPJ above, a mobile number of the area codes above after "+55", an e-mail of the
 *   characters of the DICT pattern, and a UUID of version 1 to 5 and of the RFC 4122 variant, built from its
 *   sixteen bytes.
 *
 * Every value is drawn from mt_rand(), so a fixed seed (mt_srand) reproduces the sequence. Nothing here is a
 * real document; a generated value may coincide with a real one only by chance.
 *
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Support
 */
final class DocumentGenerator
{
    private const CNPJ_WEIGHTS_1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    private const CNPJ_WEIGHTS_2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    private const ALPHANUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const PIS_COMPLEMENT_WEIGHTS = [8, 9, 2, 3, 4, 5, 6, 7, 8, 9];
    private const RENAVAM_WEIGHTS_FROM_THE_RIGHT = [2, 3, 4, 5, 6, 7, 8, 9, 2, 3];
    private const EMAIL_LOCAL = 'abcdefghijklmnopqrstuvwxyz0123456789!#$\'*+/=?^_`{|}~-';
    private const EMAIL_DOMAIN = 'abcdefghijklmnopqrstuvwxyz0123456789-';
    private const UNASSIGNED_AREA_CODES = [20, 23, 25, 26, 29, 30, 36, 39, 40, 50, 52, 56, 57, 58, 59, 60, 70, 72, 76, 78, 80, 90];

    /** Eleven digits of a CPF, not a repetition. */
    public static function cpf(): string
    {
        do {
            $base = self::digits(9);
        } while (preg_match('/^(\d)\1{8}$/', $base) === 1);

        $first = self::cpfDigit($base, 10);
        $second = self::cpfDigit($base . $first, 11);

        return $base . $first . $second;
    }

    /** The CPF mask over eleven digits. */
    public static function maskCpf(string $digits): string
    {
        return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
    }

    /** Fourteen characters of a CNPJ, with letters in the twelve leading ones when asked, not a repetition. */
    public static function cnpj(bool $alphanumeric): string
    {
        do {
            $base = '';
            for ($i = 0; $i < 12; $i++) {
                $base .= $alphanumeric ? self::ALPHANUMERIC[mt_rand(0, 35)] : (string) mt_rand(0, 9);
            }
        } while (preg_match('/^(\d)\1{11}$/', $base) === 1);

        $first = self::cnpjDigit($base, self::CNPJ_WEIGHTS_1);
        $second = self::cnpjDigit($base . $first, self::CNPJ_WEIGHTS_2);

        return $base . $first . $second;
    }

    /** The CNPJ mask over fourteen characters. */
    public static function maskCnpj(string $characters): string
    {
        return substr($characters, 0, 2) . '.' . substr($characters, 2, 3) . '.' . substr($characters, 5, 3) . '/'
            . substr($characters, 8, 4) . '-' . substr($characters, 12, 2);
    }

    /** Eight digits of a postal code, 01000000 to 99999999. */
    public static function cep(): string
    {
        return sprintf('%05d', mt_rand(1000, 99999)) . self::digits(3);
    }

    /** The postal code mask over eight digits. */
    public static function maskCep(string $digits): string
    {
        return substr($digits, 0, 5) . '-' . substr($digits, 5, 3);
    }

    /** Twenty digits of a unified process number of the CNJ (NNNNNNN DD AAAA J TR OOOO). */
    public static function cnjProcessNumber(): string
    {
        $sequence = self::digits(7);
        $year = (string) mt_rand(1990, 2099);
        $segment = (string) mt_rand(1, 9);
        $court = self::digits(2);
        $origin = self::digits(4);

        $r1 = ((int) $sequence) % 97;
        $r2 = ((int) ($r1 . $year . $segment . $court)) % 97;
        $r3 = ((int) ($r2 . $origin . '00')) % 97;
        $checkDigits = sprintf('%02d', 98 - $r3);

        return $sequence . $checkDigits . $year . $segment . $court . $origin;
    }

    /** The CNJ mask over twenty digits. */
    public static function maskCnj(string $digits): string
    {
        return substr($digits, 0, 7) . '-' . substr($digits, 7, 2) . '.' . substr($digits, 9, 4) . '.'
            . substr($digits, 13, 1) . '.' . substr($digits, 14, 2) . '.' . substr($digits, 16, 4);
    }

    /**
     * The area codes of the numbering plan.
     *
     * @return list<string>
     */
    public static function areaCodes(): array
    {
        $codes = [];
        for ($code = 11; $code <= 99; $code++) {
            if ($code % 10 !== 0 && !in_array($code, self::UNASSIGNED_AREA_CODES, true)) {
                $codes[] = (string) $code;
            }
        }

        return $codes;
    }

    /** National digits of a mobile number (11) or of a landline (10). */
    public static function phone(bool $mobile): string
    {
        $areaCodes = self::areaCodes();
        $area = $areaCodes[mt_rand(0, count($areaCodes) - 1)];

        return $mobile ? $area . '9' . self::digits(8) : $area . (string) mt_rand(2, 5) . self::digits(7);
    }

    /** A legacy plate (AAA9999) or a Mercosur plate (AAA9A99), in capital letters. */
    public static function plate(bool $mercosur): string
    {
        $letters = self::letters(3);

        return $mercosur ? $letters . mt_rand(0, 9) . self::letters(1) . self::digits(2) : $letters . self::digits(4);
    }

    /** Eleven digits of a CNH registration number, not a repetition, of a base that has a completion. */
    public static function cnh(): string
    {
        while (true) {
            $base = self::digits(9);
            if (preg_match('/^(\d)\1{8}$/', $base) === 1) {
                continue;
            }

            // Weighted 2..10 the sum is minus the routine's sum (weights 9..1) modulo 11: a remainder of 1 here
            // is the routine's remainder 10, which writes 0 and lowers the second digit by 2.
            $complement = self::weighted($base, [2, 3, 4, 5, 6, 7, 8, 9, 10]) % 11;
            $first = $complement < 2 ? 0 : 11 - $complement;
            $discount = $complement === 1 ? 2 : 0;

            // Weighted 10..2 the sum is minus the routine's sum (weights 1..9) modulo 11.
            $remainder = (11 - self::weighted($base, [10, 9, 8, 7, 6, 5, 4, 3, 2]) % 11) % 11;
            $second = $remainder === 10 ? 0 : $remainder - $discount;
            if ($second >= 0) {
                return $base . $first . $second;
            }
        }
    }

    /** Eleven digits of a PIS/PASEP number, not zeros. */
    public static function pis(): string
    {
        do {
            $base = self::digits(10);
        } while ($base === '0000000000');

        $digit = self::weighted($base, self::PIS_COMPLEMENT_WEIGHTS) % 11;

        return $base . ($digit === 10 ? 0 : $digit);
    }

    /** The PIS/PASEP mask (###.#####.##-#) over eleven digits. */
    public static function maskPis(string $digits): string
    {
        return substr($digits, 0, 3) . '.' . substr($digits, 3, 5) . '.' . substr($digits, 8, 2) . '-' . substr($digits, 10, 1);
    }

    /** Twelve digits of a voter registration number of the given state (1 to 28), or of any state. */
    public static function voterId(?int $state = null): string
    {
        $sequence = self::digits(8);
        $code = sprintf('%02d', $state ?? mt_rand(1, 28));
        $special = in_array($code, ['01', '02'], true);

        $first = self::voterIdDigit(self::weighted($sequence, [9, 8, 7, 6, 5, 4, 3, 2]), $special);
        $second = self::voterIdDigit(self::weighted($code . $first, [4, 3, 2]), $special);

        return $sequence . $code . $first . $second;
    }

    /** Eleven digits of a RENAVAM, not zeros. */
    public static function renavam(): string
    {
        do {
            $base = self::digits(10);
        } while ($base === '0000000000');

        $digit = (self::weighted(strrev($base), self::RENAVAM_WEIGHTS_FROM_THE_RIGHT) * 10) % 11;

        return $base . ($digit === 10 ? 0 : $digit);
    }

    /** A mobile number as a PIX key: "+55", an area code, "9" and eight digits. */
    public static function pixPhone(): string
    {
        return '+55' . self::phone(true);
    }

    /** A lowercase e-mail address of at most 77 characters, as a PIX key. */
    public static function pixEmail(): string
    {
        $local = self::from(self::EMAIL_LOCAL, mt_rand(1, 20));
        if (mt_rand(0, 1) === 1) {
            $local .= '.' . self::from(self::EMAIL_LOCAL, mt_rand(1, 10));
        }
        $domain = self::from('abcdefghijklmnopqrstuvwxyz0123456789', 1) . self::from(self::EMAIL_DOMAIN, mt_rand(0, 12)) . 'x';

        return $local . '@' . $domain . '.' . self::from('abcdefghijklmnopqrstuvwxyz', mt_rand(2, 6));
    }

    /** A random key (EVP) of the DICT: a UUID of version 1 to 5 and of the RFC 4122 variant, in small letters. */
    public static function pixRandomKey(): string
    {
        $bytes = [];
        for ($i = 0; $i < 16; $i++) {
            $bytes[] = mt_rand(0, 255);
        }
        $bytes[6] = (mt_rand(1, 5) << 4) | ($bytes[6] & 0x0F); // version 1 to 5
        $bytes[8] = 0x80 | ($bytes[8] & 0x3F); // variant of RFC 4122
        $hex = '';
        foreach ($bytes as $byte) {
            $hex .= sprintf('%02x', $byte);
        }

        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }

    /** A string of the given number of random digits. */
    public static function digits(int $count): string
    {
        $digits = '';
        for ($i = 0; $i < $count; $i++) {
            $digits .= (string) mt_rand(0, 9);
        }

        return $digits;
    }

    /**
     * @param list<int> $weights
     */
    private static function weighted(string $digits, array $weights): int
    {
        $sum = 0;
        foreach ($weights as $i => $weight) {
            $sum += (int) $digits[$i] * $weight;
        }

        return $sum;
    }

    /** The digit of a sum of complementary weights: minus that sum modulo 11, read through the table of the rule. */
    private static function voterIdDigit(int $complementSum, bool $special): int
    {
        $remainder = (11 - $complementSum % 11) % 11;

        return [0 => $special ? 1 : 0, 10 => 0][$remainder] ?? $remainder;
    }

    private static function from(string $alphabet, int $count): string
    {
        $out = '';
        for ($i = 0; $i < $count; $i++) {
            $out .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
        }

        return $out;
    }

    private static function letters(int $count): string
    {
        $letters = '';
        for ($i = 0; $i < $count; $i++) {
            $letters .= chr(mt_rand(65, 90));
        }

        return $letters;
    }

    private static function cpfDigit(string $digits, int $firstWeight): int
    {
        $sum = 0;
        foreach (str_split($digits) as $i => $digit) {
            $sum += (int) $digit * ($firstWeight - $i);
        }
        $digit = ($sum * 10) % 11;

        return $digit === 10 ? 0 : $digit;
    }

    /**
     * @param list<int> $weights
     */
    private static function cnpjDigit(string $characters, array $weights): int
    {
        $sum = 0;
        foreach ($weights as $i => $weight) {
            $sum += (ord($characters[$i]) - 48) * $weight;
        }
        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
