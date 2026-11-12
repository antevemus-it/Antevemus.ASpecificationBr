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
 * - area codes: the range 11..99 minus the codes the numbering plan of Anatel never assigned.
 *
 * Every value is drawn from mt_rand(), so a fixed seed (mt_srand) reproduces the sequence. Nothing here is a
 * real document; a generated value may coincide with a real one only by chance.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Support
 */
final class DocumentGenerator
{
    private const CNPJ_WEIGHTS_1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    private const CNPJ_WEIGHTS_2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    private const ALPHANUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
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

    /** A string of the given number of random digits. */
    public static function digits(int $count): string
    {
        $digits = '';
        for ($i = 0; $i < $count; $i++) {
            $digits .= (string) mt_rand(0, 9);
        }

        return $digits;
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
