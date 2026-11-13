<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Property;

use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\PixKeyKind;
use Antevemus\ASpecificationBr\Specifications\CnhSpecification;
use Antevemus\ASpecificationBr\Specifications\PisSpecification;
use Antevemus\ASpecificationBr\Specifications\PixKeySpecification;
use Antevemus\ASpecificationBr\Specifications\RenavamSpecification;
use Antevemus\ASpecificationBr\Specifications\VoterIdSpecification;
use Antevemus\ASpecificationBr\Tests\Support\DocumentGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Properties of the specifications added in 1.1.0 over generated documents, with fixed seeds (a failure names
 * its seed and iteration, and `mt_srand(<seed>)` reproduces it).
 *
 * 1. Every generated CNH, voter registration number and RENAVAM is accepted, the voter registration number
 *    also with the leading zeros of its sequence dropped; every generated PIS/PASEP is accepted, masked and
 *    unmasked, in the forms the format allows, and refused in the form it does not.
 * 2. Changing one check digit to any other digit is always refused (the voter registration number in both
 *    of its forms).
 * 3. For the PIS/PASEP, `Any` answers exactly `Masked or Unmasked`, over noise strings and over generated
 *    numbers with random punctuation removed.
 * 4. A voter registration number with a state code outside 01 to 28 is refused, even with the check digits
 *    computed for it.
 * 5. Every generated PIX key is accepted for its own kind and for `Any` and refused for every other kind; in
 *    capital letters, an e-mail or a random key is refused; a CPF or a CNPJ is refused with its mask.
 *
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Property
 */
final class RegistrationAndPixPropertyTest extends TestCase
{
    private const ITERATIONS = 400;

    /**
     * @return array<string, array{int}>
     */
    public static function seeds(): array
    {
        return ['seed 20261112' => [20261112], 'seed 61' => [61], 'seed 97' => [97]];
    }

    #[DataProvider('seeds')]
    public function testGeneratedNumbersAreAcceptedInTheirFormsOnly(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $where = "seed {$seed}, iteration {$i}";
            self::assertTrue((new CnhSpecification())->isSatisfiedBy(DocumentGenerator::cnh()), "{$where}: CNH");
            $voterId = DocumentGenerator::voterId();
            self::assertTrue((new VoterIdSpecification())->isSatisfiedBy($voterId), "{$where}: voter registration number");
            self::assertTrue((new VoterIdSpecification())->isSatisfiedBy(ltrim($voterId, '0')), "{$where}: voter registration number without leading zeros");
            self::assertTrue((new RenavamSpecification())->isSatisfiedBy(DocumentGenerator::renavam()), "{$where}: RENAVAM");

            $unmasked = DocumentGenerator::pis();
            $masked = DocumentGenerator::maskPis($unmasked);
            self::assertTrue((new PisSpecification(DocumentFormat::Any))->isSatisfiedBy($unmasked), "{$where}: PIS unmasked, any");
            self::assertTrue((new PisSpecification(DocumentFormat::Any))->isSatisfiedBy($masked), "{$where}: PIS masked, any");
            self::assertTrue((new PisSpecification(DocumentFormat::Unmasked))->isSatisfiedBy($unmasked), "{$where}: PIS unmasked, unmasked");
            self::assertTrue((new PisSpecification(DocumentFormat::Masked))->isSatisfiedBy($masked), "{$where}: PIS masked, masked");
            self::assertFalse((new PisSpecification(DocumentFormat::Masked))->isSatisfiedBy($unmasked), "{$where}: PIS unmasked, masked");
            self::assertFalse((new PisSpecification(DocumentFormat::Unmasked))->isSatisfiedBy($masked), "{$where}: PIS masked, unmasked");
        }
    }

    #[DataProvider('seeds')]
    public function testChangingOneCheckDigitIsAlwaysRefused(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // [specification, document, positions of the check digits]
            $cases = [
                [new CnhSpecification(), DocumentGenerator::cnh(), [9, 10]],
                [new PisSpecification(), DocumentGenerator::pis(), [10]],
                [new PisSpecification(), DocumentGenerator::maskPis(DocumentGenerator::pis()), [13]],
                [new VoterIdSpecification(), $voterId = DocumentGenerator::voterId(), [10, 11]],
                [new VoterIdSpecification(), $issued = ltrim($voterId, '0'), [strlen($issued) - 2, strlen($issued) - 1]],
                [new RenavamSpecification(), DocumentGenerator::renavam(), [10]],
            ];
            foreach ($cases as [$specification, $document, $positions]) {
                foreach ($positions as $position) {
                    for ($digit = 0; $digit <= 9; $digit++) {
                        if ((string) $digit === $document[$position]) {
                            continue;
                        }
                        $changed = substr_replace($document, (string) $digit, $position, 1);
                        self::assertFalse($specification->isSatisfiedBy($changed), $specification::class . ", seed {$seed}, iteration {$i}, position {$position}, digit {$digit}");
                    }
                }
            }
        }
    }

    #[DataProvider('seeds')]
    public function testAnyIsExactlyMaskedOrUnmasked(int $seed): void
    {
        mt_srand($seed);
        $alphabet = '0123456789.-/ ABCxyz';
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $candidates = [self::dropSomePunctuation(DocumentGenerator::maskPis(DocumentGenerator::pis()))];
            $noise = '';
            for ($c = mt_rand(0, 16); $c > 0; $c--) {
                $noise .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
            $candidates[] = $noise;
            foreach ($candidates as $candidate) {
                $any = (new PisSpecification(DocumentFormat::Any))->isSatisfiedBy($candidate);
                $either = (new PisSpecification(DocumentFormat::Masked))->isSatisfiedBy($candidate) || (new PisSpecification(DocumentFormat::Unmasked))->isSatisfiedBy($candidate);
                self::assertSame($either, $any, "seed {$seed}, iteration {$i}");
            }
        }
    }

    #[DataProvider('seeds')]
    public function testAStateCodeOutsideTheTableIsRefusedEvenWithItsCheckDigits(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $state = mt_rand(0, 1) === 1 ? 0 : mt_rand(29, 99);
            self::assertFalse((new VoterIdSpecification())->isSatisfiedBy(DocumentGenerator::voterId($state)), "seed {$seed}, iteration {$i}, state {$state}");
        }
    }

    #[DataProvider('seeds')]
    public function testGeneratedPixKeysAreAcceptedForTheirKindOnly(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $cpf = DocumentGenerator::cpf();
            $cnpj = DocumentGenerator::cnpj(true);
            $email = DocumentGenerator::pixEmail();
            $random = DocumentGenerator::pixRandomKey();
            $keys = [
                [$cpf, PixKeyKind::Cpf],
                [DocumentGenerator::cnpj(false), PixKeyKind::Cnpj],
                [$cnpj, PixKeyKind::Cnpj],
                [DocumentGenerator::pixPhone(), PixKeyKind::Phone],
                [$email, PixKeyKind::Email],
                [$random, PixKeyKind::Random],
            ];
            $where = "seed {$seed}, iteration {$i}";
            foreach ($keys as [$key, $kind]) {
                foreach (PixKeyKind::cases() as $case) {
                    $expected = $case === PixKeyKind::Any || $case === $kind;
                    self::assertSame($expected, (new PixKeySpecification($case))->isSatisfiedBy($key), "{$where}: {$kind->value} key, {$case->value}");
                }
            }
            $refused = [
                DocumentGenerator::maskCpf($cpf),
                DocumentGenerator::maskCnpj($cnpj),
                strtoupper($email), // the top-level domain is made of letters
                $random . "\n",
                ' ' . $email,
            ];
            if (preg_match('/[a-f]/', $random) === 1) {
                $refused[] = strtoupper($random);
            }
            foreach ($refused as $candidate) {
                self::assertFalse((new PixKeySpecification())->isSatisfiedBy($candidate), "{$where}: refused");
            }
        }
    }

    /** Removes each punctuation character with probability one half (sometimes none, sometimes all). */
    private static function dropSomePunctuation(string $masked): string
    {
        $out = '';
        foreach (str_split($masked) as $character) {
            if (ctype_alnum($character) || mt_rand(0, 1) === 0) {
                $out .= $character;
            }
        }

        return $out;
    }
}
