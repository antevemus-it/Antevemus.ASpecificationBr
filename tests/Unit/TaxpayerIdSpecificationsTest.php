<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\Specifications\CnpjSpecification;
use Antevemus\ASpecificationBr\Specifications\CpfSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * CPF and CNPJ: fixed cases, every one fictitious or the public example of an official specification.
 *
 * The first block repeats the cases of the routine this package copied (the same expected answers), so the
 * copy is known to behave as its origin.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class TaxpayerIdSpecificationsTest extends TestCase
{
    /**
     * The cases of the origin of the copy, with its answers.
     *
     * @return array<string, array{string, bool, bool}> candidate, is a CPF, is a CNPJ
     */
    public static function originCases(): array
    {
        return [
            'masked CPF' => ['529.982.247-25', true, false],
            'masked numeric CNPJ' => ['11.222.333/0001-81', false, true],
            'masked alphanumeric CNPJ' => ['12.ABC.345/01DE-35', false, true],
            'alphanumeric CNPJ in small letters' => ['12abc34501de35', false, true],
            'CNPJ with a wrong check digit' => ['11.222.333/0001-82', false, false],
            'CPF that is a repetition' => ['11111111111', false, false],
            'CNPJ with letters in the check digits' => ['12ABC34501DEAB', false, false],
            'too short' => ['123', false, false],
        ];
    }

    #[DataProvider('originCases')]
    public function testTheCopyAnswersAsItsOrigin(string $candidate, bool $isCpf, bool $isCnpj): void
    {
        self::assertSame($isCpf, (new CpfSpecification())->isSatisfiedBy($candidate));
        self::assertSame($isCnpj, (new CnpjSpecification())->isSatisfiedBy($candidate));
    }

    /**
     * @return array<string, array{mixed, DocumentFormat, bool}>
     */
    public static function cpfCases(): array
    {
        return [
            'masked, any' => ['876.047.238-37', DocumentFormat::Any, true],
            'unmasked, any' => ['87604723837', DocumentFormat::Any, true],
            'masked, masked' => ['876.047.238-37', DocumentFormat::Masked, true],
            'unmasked, masked' => ['87604723837', DocumentFormat::Masked, false],
            'unmasked, unmasked' => ['87604723837', DocumentFormat::Unmasked, true],
            'masked, unmasked' => ['876.047.238-37', DocumentFormat::Unmasked, false],
            'partial punctuation' => ['876047238-37', DocumentFormat::Any, false],
            'other punctuation' => ['876-047-238.37', DocumentFormat::Any, false],
            'first check digit wrong' => ['876.047.238-47', DocumentFormat::Any, false],
            'second check digit wrong' => ['876.047.238-38', DocumentFormat::Any, false],
            'a space at the end' => ['87604723837 ', DocumentFormat::Any, false],
            'a newline at the end' => ["87604723837\n", DocumentFormat::Any, false],
            'twelve digits' => ['876047238370', DocumentFormat::Any, false],
            'ten digits' => ['8760472383', DocumentFormat::Any, false],
            'zeros' => ['000.000.000-00', DocumentFormat::Any, false],
            'an integer' => [87604723837, DocumentFormat::Any, false],
            'null' => [null, DocumentFormat::Any, false],
            'empty' => ['', DocumentFormat::Any, false],
            'a letter in place of a digit' => ['876.047.23A-37', DocumentFormat::Any, false],
            'full-width digits' => ['８７６０４７２３８３７', DocumentFormat::Any, false],
        ];
    }

    #[DataProvider('cpfCases')]
    public function testCpf(mixed $candidate, DocumentFormat $format, bool $expected): void
    {
        self::assertSame($expected, (new CpfSpecification($format))->isSatisfiedBy($candidate));
    }

    /**
     * @return array<string, array{mixed, DocumentFormat, bool, bool}>
     */
    public static function cnpjCases(): array
    {
        return [
            'numeric masked, any' => ['11.222.333/0001-81', DocumentFormat::Any, true, true],
            'numeric unmasked, any' => ['11222333000181', DocumentFormat::Any, true, true],
            'numeric masked, numeric only' => ['11.222.333/0001-81', DocumentFormat::Any, false, true],
            'alphanumeric masked' => ['RN.0HY.F3C/2TQZ-90', DocumentFormat::Masked, true, true],
            'alphanumeric unmasked' => ['RN0HYF3C2TQZ90', DocumentFormat::Unmasked, true, true],
            'alphanumeric in small letters' => ['rn.0hy.f3c/2tqz-90', DocumentFormat::Any, true, true],
            'alphanumeric refused when numeric only' => ['RN.0HY.F3C/2TQZ-90', DocumentFormat::Any, false, false],
            'masked refused when unmasked only' => ['11.222.333/0001-81', DocumentFormat::Unmasked, true, false],
            'unmasked refused when masked only' => ['11222333000181', DocumentFormat::Masked, true, false],
            'partial punctuation' => ['11222333/0001-81', DocumentFormat::Any, true, false],
            'first check digit wrong' => ['11.222.333/0001-91', DocumentFormat::Any, true, false],
            'numeric repetition' => ['00.000.000/0000-00', DocumentFormat::Any, true, false],
            'repetition of ones' => ['11111111111111', DocumentFormat::Any, true, false],
            'a letter in a check digit' => ['RN0HYF3C2TQZ9A', DocumentFormat::Any, true, false],
            'a symbol' => ['RN0HYF3C2TQ#90', DocumentFormat::Any, true, false],
            'an integer' => [11222333000181, DocumentFormat::Any, true, false],
            'a space at the end' => ['11222333000181 ', DocumentFormat::Any, true, false],
        ];
    }

    #[DataProvider('cnpjCases')]
    public function testCnpj(mixed $candidate, DocumentFormat $format, bool $alphanumeric, bool $expected): void
    {
        self::assertSame($expected, (new CnpjSpecification($format, $alphanumeric))->isSatisfiedBy($candidate));
    }

    public function testCpfAndCnpjNeverAcceptTheSameValue(): void
    {
        foreach (['529.982.247-25', '52998224725', '11.222.333/0001-81', '11222333000181'] as $value) {
            self::assertFalse((new CpfSpecification())->isSatisfiedBy($value) && (new CnpjSpecification())->isSatisfiedBy($value), $value);
        }
    }
}
