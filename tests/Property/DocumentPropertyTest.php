<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Property;

use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\PhoneKind;
use Antevemus\ASpecificationBr\PlateKind;
use Antevemus\ASpecificationBr\Specifications\CepSpecification;
use Antevemus\ASpecificationBr\Specifications\CnjProcessNumberSpecification;
use Antevemus\ASpecificationBr\Specifications\CnpjSpecification;
use Antevemus\ASpecificationBr\Specifications\CpfSpecification;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;
use Antevemus\ASpecificationBr\Specifications\VehiclePlateSpecification;
use Antevemus\ASpecificationBr\Tests\Support\DocumentGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Properties of the specifications over generated documents, with fixed seeds (a failure names its seed and
 * iteration, and `mt_srand(<seed>)` reproduces it).
 *
 * 1. Every generated document is accepted, masked and unmasked, in the forms the format allows, and refused in
 *    the form it does not.
 * 2. Changing one check digit to any other digit is always refused.
 * 3. For the four specifications with a format, `Any` answers exactly `Masked or Unmasked`, over noise strings
 *    and over generated documents with random punctuation removed.
 * 4. Every generated phone number is accepted in five writings, and only for its own kind.
 * 5. Every generated plate is accepted for its own kind and in small letters, and refused for the other kind.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Property
 */
final class DocumentPropertyTest extends TestCase
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
    public function testGeneratedDocumentsAreAcceptedInTheirFormsOnly(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $cases = [
                [CpfSpecification::class, [], $cpf = DocumentGenerator::cpf(), DocumentGenerator::maskCpf($cpf)],
                [CnpjSpecification::class, [], $cnpj = DocumentGenerator::cnpj(true), DocumentGenerator::maskCnpj($cnpj)],
                [CnpjSpecification::class, [true], $numeric = DocumentGenerator::cnpj(false), DocumentGenerator::maskCnpj($numeric)],
                [CepSpecification::class, [], $cep = DocumentGenerator::cep(), DocumentGenerator::maskCep($cep)],
                [CnjProcessNumberSpecification::class, [], $cnj = DocumentGenerator::cnjProcessNumber(), DocumentGenerator::maskCnj($cnj)],
            ];
            foreach ($cases as [$class, $extra, $unmasked, $masked]) {
                $where = "{$class}, seed {$seed}, iteration {$i}";
                self::assertTrue((new $class(DocumentFormat::Any, ...$extra))->isSatisfiedBy($unmasked), "{$where}: unmasked, any");
                self::assertTrue((new $class(DocumentFormat::Any, ...$extra))->isSatisfiedBy($masked), "{$where}: masked, any");
                self::assertTrue((new $class(DocumentFormat::Unmasked, ...$extra))->isSatisfiedBy($unmasked), "{$where}: unmasked, unmasked");
                self::assertTrue((new $class(DocumentFormat::Masked, ...$extra))->isSatisfiedBy($masked), "{$where}: masked, masked");
                self::assertFalse((new $class(DocumentFormat::Masked, ...$extra))->isSatisfiedBy($unmasked), "{$where}: unmasked, masked");
                self::assertFalse((new $class(DocumentFormat::Unmasked, ...$extra))->isSatisfiedBy($masked), "{$where}: masked, unmasked");
            }
            self::assertSame(preg_match('/[A-Z]/', $cnpj) !== 1, (new CnpjSpecification(DocumentFormat::Any, false))->isSatisfiedBy($cnpj), "numeric-only CNPJ, seed {$seed}, iteration {$i}");
            self::assertTrue((new CnpjSpecification(DocumentFormat::Any, false))->isSatisfiedBy($numeric), "numeric CNPJ, numeric only, seed {$seed}, iteration {$i}");
        }
    }

    #[DataProvider('seeds')]
    public function testChangingOneCheckDigitIsAlwaysRefused(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // [specification, document, positions of the check digits]
            $cases = [
                [new CpfSpecification(), DocumentGenerator::cpf(), [9, 10]],
                [new CnpjSpecification(), DocumentGenerator::cnpj(true), [12, 13]],
                [new CnjProcessNumberSpecification(), DocumentGenerator::cnjProcessNumber(), [7, 8]],
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
        $classes = [CpfSpecification::class, CnpjSpecification::class, CepSpecification::class, CnjProcessNumberSpecification::class];
        $alphabet = '0123456789.-/ ABCxyz';
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $documents = [
                DocumentGenerator::maskCpf(DocumentGenerator::cpf()),
                DocumentGenerator::maskCnpj(DocumentGenerator::cnpj(true)),
                DocumentGenerator::maskCep(DocumentGenerator::cep()),
                DocumentGenerator::maskCnj(DocumentGenerator::cnjProcessNumber()),
            ];
            $candidates = [];
            foreach ($documents as $document) {
                $candidates[] = self::dropSomePunctuation($document);
            }
            $noise = '';
            for ($c = mt_rand(0, 26); $c > 0; $c--) {
                $noise .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
            $candidates[] = $noise;
            foreach ($classes as $class) {
                foreach ($candidates as $candidate) {
                    $any = (new $class(DocumentFormat::Any))->isSatisfiedBy($candidate);
                    $either = (new $class(DocumentFormat::Masked))->isSatisfiedBy($candidate) || (new $class(DocumentFormat::Unmasked))->isSatisfiedBy($candidate);
                    self::assertSame($either, $any, "{$class}, seed {$seed}, iteration {$i}");
                }
            }
        }
    }

    #[DataProvider('seeds')]
    public function testGeneratedPhonesAreAcceptedInEveryWritingForTheirKindOnly(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $mobile = mt_rand(0, 1) === 1;
            $digits = DocumentGenerator::phone($mobile);
            $area = substr($digits, 0, 2);
            $number = substr($digits, 2);
            $split = strlen($number) - 4;
            $writings = [
                $digits,
                "({$area}) " . substr($number, 0, $split) . '-' . substr($number, $split),
                "+55 {$area} " . substr($number, 0, $split) . ' ' . substr($number, $split),
                '55' . $digits,
                "{$area}." . substr($number, 0, $split) . '.' . substr($number, $split),
            ];
            $own = $mobile ? PhoneKind::Mobile : PhoneKind::Landline;
            $other = $mobile ? PhoneKind::Landline : PhoneKind::Mobile;
            foreach ($writings as $writing) {
                $where = "seed {$seed}, iteration {$i}";
                self::assertTrue((new PhoneSpecification())->isSatisfiedBy($writing), "{$where}: any");
                self::assertTrue((new PhoneSpecification($own))->isSatisfiedBy($writing), "{$where}: own kind");
                self::assertFalse((new PhoneSpecification($other))->isSatisfiedBy($writing), "{$where}: other kind");
            }
        }
    }

    #[DataProvider('seeds')]
    public function testGeneratedPlatesAreAcceptedForTheirKindOnly(int $seed): void
    {
        mt_srand($seed);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $mercosur = mt_rand(0, 1) === 1;
            $plate = DocumentGenerator::plate($mercosur);
            $own = $mercosur ? PlateKind::Mercosur : PlateKind::Legacy;
            $other = $mercosur ? PlateKind::Legacy : PlateKind::Mercosur;
            $where = "seed {$seed}, iteration {$i}";
            foreach ([$plate, strtolower($plate)] as $written) {
                self::assertTrue((new VehiclePlateSpecification())->isSatisfiedBy($written), "{$where}: any");
                self::assertTrue((new VehiclePlateSpecification($own))->isSatisfiedBy($written), "{$where}: own kind");
                self::assertFalse((new VehiclePlateSpecification($other))->isSatisfiedBy($written), "{$where}: other kind");
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
