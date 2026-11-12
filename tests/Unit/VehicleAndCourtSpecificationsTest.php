<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\PlateKind;
use Antevemus\ASpecificationBr\Specifications\CnjProcessNumberSpecification;
use Antevemus\ASpecificationBr\Specifications\VehiclePlateSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Vehicle plate and CNJ process number: fixed cases, every one fictitious.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class VehicleAndCourtSpecificationsTest extends TestCase
{
    /**
     * @return array<string, array{mixed, PlateKind, bool}>
     */
    public static function plateCases(): array
    {
        return [
            'legacy, no hyphen' => ['ABC1234', PlateKind::Any, true],
            'legacy, hyphen' => ['ABC-1234', PlateKind::Any, true],
            'Mercosur' => ['BRA2E19', PlateKind::Any, true],
            'Mercosur in small letters' => ['bra2e19', PlateKind::Any, true],
            'legacy when only legacy' => ['ABC-1234', PlateKind::Legacy, true],
            'Mercosur when only legacy' => ['BRA2E19', PlateKind::Legacy, false],
            'Mercosur when only Mercosur' => ['BRA2E19', PlateKind::Mercosur, true],
            'legacy when only Mercosur' => ['ABC1234', PlateKind::Mercosur, false],
            'Mercosur with a hyphen' => ['BRA-2E19', PlateKind::Any, false],
            'two letters' => ['AB1234', PlateKind::Any, false],
            'four letters' => ['ABCD123', PlateKind::Any, false],
            'Mercosur with two letters in the middle' => ['BRA2EE9', PlateKind::Any, false],
            'a space' => ['ABC 1234', PlateKind::Any, false],
            'a space at the end' => ['ABC1234 ', PlateKind::Any, false],
            'an accented letter' => ['ÁBC1234', PlateKind::Any, false],
            'an integer' => [1234567, PlateKind::Any, false],
            'empty' => ['', PlateKind::Any, false],
        ];
    }

    #[DataProvider('plateCases')]
    public function testVehiclePlate(mixed $candidate, PlateKind $kind, bool $expected): void
    {
        self::assertSame($expected, (new VehiclePlateSpecification($kind))->isSatisfiedBy($candidate));
    }

    /**
     * @return array<string, array{mixed, DocumentFormat, bool}>
     */
    public static function cnjCases(): array
    {
        return [
            'masked, any' => ['1606504-41.2041.6.53.3394', DocumentFormat::Any, true],
            'unmasked, any' => ['16065044120416533394', DocumentFormat::Any, true],
            'masked, masked' => ['1606504-41.2041.6.53.3394', DocumentFormat::Masked, true],
            'unmasked, masked' => ['16065044120416533394', DocumentFormat::Masked, false],
            'unmasked, unmasked' => ['16065044120416533394', DocumentFormat::Unmasked, true],
            'masked, unmasked' => ['1606504-41.2041.6.53.3394', DocumentFormat::Unmasked, false],
            'check digits wrong' => ['1606504-42.2041.6.53.3394', DocumentFormat::Any, false],
            'segment changed' => ['1606504-41.2041.5.53.3394', DocumentFormat::Any, false],
            'segment zero' => ['0000000-00.0000.0.00.0000', DocumentFormat::Any, false],
            'partial punctuation' => ['1606504-41.2041.6.533394', DocumentFormat::Any, false],
            'nineteen digits' => ['1606504412041653339', DocumentFormat::Any, false],
            'an integer' => [1606504, DocumentFormat::Any, false],
            'a space at the end' => ['16065044120416533394 ', DocumentFormat::Any, false],
        ];
    }

    #[DataProvider('cnjCases')]
    public function testCnjProcessNumber(mixed $candidate, DocumentFormat $format, bool $expected): void
    {
        self::assertSame($expected, (new CnjProcessNumberSpecification($format))->isSatisfiedBy($candidate));
    }

    public function testCnjCheckDigitsOutsideTheirRangeAreRefusedEvenWhenTheRemainderMatches(): void
    {
        // A number whose valid check digits are 02 also gives remainder 1 with 99 (02 + 97); 99 is never
        // written by the rule (DD = 98 - remainder), so it is refused. Built by searching the sequence.
        $found = null;
        for ($sequence = 0; $sequence < 100000 && $found === null; $sequence++) {
            $body = sprintf('%07d', $sequence) . '%s' . '2041653' . '3394';
            if ((new CnjProcessNumberSpecification())->isSatisfiedBy(sprintf($body, '02'))) {
                $found = $body;
            }
        }
        self::assertNotNull($found, 'a sequence with check digits 02 exists');
        self::assertFalse((new CnjProcessNumberSpecification())->isSatisfiedBy(sprintf($found, '99')));
    }
}
