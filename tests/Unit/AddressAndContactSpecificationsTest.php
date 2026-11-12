<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\PhoneKind;
use Antevemus\ASpecificationBr\Specifications\CepSpecification;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;
use Antevemus\ASpecificationBr\Tests\Support\DocumentGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * CEP and phone number: fixed cases, every one fictitious.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class AddressAndContactSpecificationsTest extends TestCase
{
    /**
     * @return array<string, array{mixed, DocumentFormat, bool}>
     */
    public static function cepCases(): array
    {
        return [
            'masked, any' => ['01310-100', DocumentFormat::Any, true],
            'unmasked, any' => ['01310100', DocumentFormat::Any, true],
            'masked, masked' => ['01310-100', DocumentFormat::Masked, true],
            'unmasked, masked' => ['01310100', DocumentFormat::Masked, false],
            'unmasked, unmasked' => ['01310100', DocumentFormat::Unmasked, true],
            'masked, unmasked' => ['01310-100', DocumentFormat::Unmasked, false],
            'the lowest' => ['01000-000', DocumentFormat::Any, true],
            'just below the lowest' => ['00999-999', DocumentFormat::Any, false],
            'zeros' => ['00000-000', DocumentFormat::Any, false],
            'the highest' => ['99999-999', DocumentFormat::Any, true],
            'a dot in the mask' => ['01.310-100', DocumentFormat::Any, false],
            'seven digits' => ['1310100', DocumentFormat::Any, false],
            'nine digits' => ['013101000', DocumentFormat::Any, false],
            'an integer' => [1310100, DocumentFormat::Any, false],
            'a space at the start' => [' 01310-100', DocumentFormat::Any, false],
        ];
    }

    #[DataProvider('cepCases')]
    public function testCep(mixed $candidate, DocumentFormat $format, bool $expected): void
    {
        self::assertSame($expected, (new CepSpecification($format))->isSatisfiedBy($candidate));
    }

    /**
     * @return array<string, array{mixed, PhoneKind, bool}>
     */
    public static function phoneCases(): array
    {
        return [
            'mobile, written' => ['(11) 98765-4321', PhoneKind::Any, true],
            'mobile, digits' => ['11987654321', PhoneKind::Any, true],
            'mobile, with country code and plus' => ['+55 11 98765-4321', PhoneKind::Any, true],
            'mobile, with country code, no plus' => ['5511987654321', PhoneKind::Any, true],
            'mobile, dots' => ['11.98765.4321', PhoneKind::Any, true],
            'landline, written' => ['(21) 3456-7890', PhoneKind::Any, true],
            'landline, digits' => ['2134567890', PhoneKind::Any, true],
            'landline, with country code' => ['+55 21 3456-7890', PhoneKind::Any, true],
            'landline starting with 2' => ['2122345678', PhoneKind::Landline, true],
            'landline starting with 5' => ['2152345678', PhoneKind::Landline, true],
            'landline starting with 6' => ['2162345678', PhoneKind::Any, false],
            'landline starting with 1' => ['2112345678', PhoneKind::Any, false],
            'eleven digits not starting with 9' => ['11887654321', PhoneKind::Any, false],
            'area code 55 (Rio Grande do Sul), mobile' => ['55 99876-5432', PhoneKind::Any, true],
            'area code 55 with country code' => ['+55 55 99876-5432', PhoneKind::Any, true],
            'mobile when only landlines' => ['11987654321', PhoneKind::Landline, false],
            'landline when only mobiles' => ['2134567890', PhoneKind::Mobile, false],
            'mobile when only mobiles' => ['11987654321', PhoneKind::Mobile, true],
            'area code never assigned (20)' => ['20987654321', PhoneKind::Any, false],
            'area code never assigned (23)' => ['2334567890', PhoneKind::Any, false],
            'area code 10' => ['10987654321', PhoneKind::Any, false],
            'another country code with plus' => ['+1 11 98765-4321', PhoneKind::Any, false],
            'twelve digits not starting with 55' => ['541134567890', PhoneKind::Any, false],
            'plus without country code' => ['+11987654321', PhoneKind::Any, false],
            'plus in the middle' => ['11+987654321', PhoneKind::Any, false],
            'nine digits' => ['987654321', PhoneKind::Any, false],
            'carrier prefix' => ['0 21 11 98765-4321', PhoneKind::Any, false],
            'toll-free' => ['0800 123 4567', PhoneKind::Any, false],
            'a letter' => ['(11) 9876A-4321', PhoneKind::Any, false],
            'a slash' => ['11/98765-4321', PhoneKind::Any, false],
            'a tab' => ["11\t98765-4321", PhoneKind::Any, false],
            'only punctuation' => ['() -', PhoneKind::Any, false],
            'an integer' => [11987654321, PhoneKind::Any, false],
            'empty' => ['', PhoneKind::Any, false],
        ];
    }

    #[DataProvider('phoneCases')]
    public function testPhone(mixed $candidate, PhoneKind $kind, bool $expected): void
    {
        self::assertSame($expected, (new PhoneSpecification($kind))->isSatisfiedBy($candidate));
    }

    public function testTheAreaCodesAreTheSixtySevenOfTheNumberingPlan(): void
    {
        self::assertCount(67, PhoneSpecification::AREA_CODES);
        self::assertSame(DocumentGenerator::areaCodes(), PhoneSpecification::AREA_CODES, 'the list in src matches the one derived in the tests by another route');
    }

    public function testEveryAreaCodeIsAcceptedAndEveryOtherIsRefused(): void
    {
        for ($code = 0; $code <= 99; $code++) {
            $area = sprintf('%02d', $code);
            $expected = in_array($area, PhoneSpecification::AREA_CODES, true);
            self::assertSame($expected, (new PhoneSpecification())->isSatisfiedBy($area . '987654321'), "mobile, area code {$area}");
            self::assertSame($expected, (new PhoneSpecification())->isSatisfiedBy($area . '34567890'), "landline, area code {$area}");
        }
    }
}
