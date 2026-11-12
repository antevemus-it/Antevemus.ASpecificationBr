<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecificationBr\Internal\CheckDigits;
use Antevemus\ASpecificationBr\Specifications\CnjProcessNumberSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The check-digit routines on their own, with input the specifications never pass them (a prefix, a suffix,
 * a trailing newline): the routines do not lean on the caller having checked the form.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class CheckDigitsTest extends TestCase
{
    /**
     * @return array<string, array{string, string, bool}> routine, value, expected
     */
    public static function cases(): array
    {
        $cpf = '52998224725';
        $cnpj = '12ABC34501DE35';
        $cnj = '16065044120416533394';

        return [
            'CPF' => ['isCpf', $cpf, true],
            'CPF with a prefix' => ['isCpf', 'x' . $cpf, false],
            'CPF with a digit before' => ['isCpf', '0' . $cpf, false],
            'CPF with a suffix' => ['isCpf', $cpf . 'x', false],
            'CPF with a digit after' => ['isCpf', $cpf . '0', false],
            'CPF with a newline' => ['isCpf', $cpf . "\n", false],
            'CPF repetition' => ['isCpf', '22222222222', false],
            'CNPJ' => ['isCnpj', $cnpj, true],
            'CNPJ with a prefix' => ['isCnpj', 'X' . $cnpj, false],
            'CNPJ with a digit before' => ['isCnpj', '0' . $cnpj, false],
            'CNPJ with a suffix' => ['isCnpj', $cnpj . 'X', false],
            'CNPJ with a digit after' => ['isCnpj', $cnpj . '0', false],
            'CNPJ with a newline' => ['isCnpj', $cnpj . "\n", false],
            'CNPJ in small letters' => ['isCnpj', strtolower($cnpj), false],
            'CNPJ repetition' => ['isCnpj', '99999999999999', false],
            'CNJ' => ['isCnjProcessNumber', $cnj, true],
            'CNJ with a prefix' => ['isCnjProcessNumber', 'x' . $cnj, false],
            'CNJ with a digit before' => ['isCnjProcessNumber', '0' . $cnj, false],
            'CNJ with a suffix' => ['isCnjProcessNumber', $cnj . 'x', false],
            'CNJ with a digit after' => ['isCnjProcessNumber', $cnj . '0', false],
            'CNJ with a newline' => ['isCnjProcessNumber', $cnj . "\n", false],
        ];
    }

    #[DataProvider('cases')]
    public function testRoutine(string $routine, string $value, bool $expected): void
    {
        self::assertSame($expected, CheckDigits::$routine($value));
    }

    public function testACpfRepetitionWithRightArithmeticIsRefused(): void
    {
        // 000.000.000-00 has right check digits arithmetically; only the repetition rule refuses it.
        self::assertFalse(CheckDigits::isCpf('00000000000'));
        self::assertFalse(CheckDigits::isCnpj('00000000000000'));
    }

    public function testSegmentZeroIsRefusedEvenWithRightCheckDigits(): void
    {
        // NNNNNNN AAAA J TR OOOO with J = 0, and the check digits computed for it: only the segment rule refuses it.
        $sequence = '1606504';
        $tail = '2041' . '0' . '53' . '3394';
        $remainder = 0;
        foreach (str_split($sequence . $tail . '00') as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }
        $checkDigits = sprintf('%02d', 98 - $remainder);
        $number = $sequence . $checkDigits . $tail;

        self::assertSame(1, self::mod97($sequence . $tail . $checkDigits), 'the arithmetic is right');
        self::assertGreaterThanOrEqual(2, (int) $checkDigits, 'the check digits are in their range');
        self::assertFalse(CheckDigits::isCnjProcessNumber($number));
        self::assertFalse((new CnjProcessNumberSpecification())->isSatisfiedBy($number));
    }

    /**
     * The form check of the CNJ routine is what refuses a 21-character value whose reading would pass the
     * arithmetic: one digit after, one digit before, or a trailing newline. Each value is searched over
     * generated numbers so that, without the form check, the routine would accept it.
     */
    public function testTheCnjFormCheckRefusesValuesTheArithmeticWouldAccept(): void
    {
        mt_srand(2026);
        $found = ['after' => null, 'before' => null, 'newline' => null];
        for ($i = 0; $i < 200000 && in_array(null, $found, true); $i++) {
            $cnj = \Antevemus\ASpecificationBr\Tests\Support\DocumentGenerator::cnjProcessNumber();
            $digit = (string) mt_rand(0, 9);
            foreach (['after' => $cnj . $digit, 'before' => $digit . $cnj, 'newline' => $cnj . "\n"] as $kind => $value) {
                if ($found[$kind] === null && self::passesWithoutTheFormCheck($value)) {
                    $found[$kind] = $value;
                }
            }
        }
        foreach ($found as $kind => $value) {
            self::assertNotNull($value, "a value of kind {$kind} exists");
            self::assertFalse(CheckDigits::isCnjProcessNumber($value), $kind);
        }
    }

    /** The CNJ routine after its form check, for the search above. */
    private static function passesWithoutTheFormCheck(string $value): bool
    {
        $checkDigits = (int) substr($value, 7, 2);
        if ($value[13] === '0' || $checkDigits < 2 || $checkDigits > 98) {
            return false;
        }

        return self::mod97(substr($value, 0, 7) . substr($value, 9) . substr($value, 7, 2)) === 1;
    }

    private static function mod97(string $digits): int
    {
        $remainder = 0;
        foreach (str_split($digits) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $remainder;
    }
}
