<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecificationBr\PixKeyKind;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;
use Antevemus\ASpecificationBr\Specifications\PixKeySpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PIX keys: fixed cases, every one fictitious or the public example of an official specification (the CPF and
 * CNPJ examples of the other tests, and the e-mail `pix@bcb.gov.br` and the random key
 * `123e4567-e89b-12d3-a456-426655440000` given as examples by the DICT API specification of the Banco Central,
 * version 2.13.0).
 *
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class PixKeySpecificationTest extends TestCase
{
    private const CPF = '52998224725';
    private const CNPJ = '11222333000181';
    private const ALPHANUMERIC_CNPJ = '12ABC34501DE35';
    private const PHONE = '+5511987654321';
    private const EMAIL = 'pix@bcb.gov.br';
    private const RANDOM = '01861ab8-4d4d-4d74-9cb3-ffc5cc1c82b6';

    /**
     * Each kind of key, accepted for its own kind and for Any, refused for every other kind.
     *
     * @return array<string, array{string, PixKeyKind}>
     */
    public static function keys(): array
    {
        return [
            'CPF' => [self::CPF, PixKeyKind::Cpf],
            'numeric CNPJ' => [self::CNPJ, PixKeyKind::Cnpj],
            'alphanumeric CNPJ' => [self::ALPHANUMERIC_CNPJ, PixKeyKind::Cnpj],
            'phone' => [self::PHONE, PixKeyKind::Phone],
            'e-mail' => [self::EMAIL, PixKeyKind::Email],
            'random key' => [self::RANDOM, PixKeyKind::Random],
            'random key of the DICT API example (version 1)' => ['123e4567-e89b-12d3-a456-426655440000', PixKeyKind::Random],
        ];
    }

    #[DataProvider('keys')]
    public function testEachKeyIsAcceptedForItsKindAndForAnyOnly(string $key, PixKeyKind $kind): void
    {
        foreach (PixKeyKind::cases() as $case) {
            $expected = $case === PixKeyKind::Any || $case === $kind;
            self::assertSame($expected, (new PixKeySpecification($case))->isSatisfiedBy($key), $case->value);
        }
    }

    /**
     * @return array<string, array{mixed, PixKeyKind}>
     */
    public static function refused(): array
    {
        return [
            // CPF
            'masked CPF' => ['529.982.247-25', PixKeyKind::Cpf],
            'CPF with a wrong check digit' => ['52998224726', PixKeyKind::Cpf],
            'CPF repetition' => ['11111111111', PixKeyKind::Cpf],
            'CPF with a space at the end' => [self::CPF . ' ', PixKeyKind::Cpf],
            'CPF as an integer' => [52998224725, PixKeyKind::Cpf],
            // CNPJ
            'masked CNPJ' => ['11.222.333/0001-81', PixKeyKind::Cnpj],
            'alphanumeric CNPJ in small letters' => ['12abc34501de35', PixKeyKind::Cnpj],
            'CNPJ with a wrong check digit' => ['11222333000182', PixKeyKind::Cnpj],
            'CNPJ repetition' => ['00000000000000', PixKeyKind::Cnpj],
            'CNPJ with a newline' => [self::CNPJ . "\n", PixKeyKind::Cnpj],
            // phone
            'phone without the plus' => ['5511987654321', PixKeyKind::Phone],
            'phone without the country code' => ['+11987654321', PixKeyKind::Phone],
            'phone without the plus nor the country code' => ['11987654321', PixKeyKind::Phone],
            'phone of another country' => ['+14155552671', PixKeyKind::Phone],
            'phone with spaces' => ['+55 11 98765-4321', PixKeyKind::Phone],
            'phone with a hyphen' => ['+5511 98765-4321', PixKeyKind::Phone],
            'landline' => ['+552134567890', PixKeyKind::Phone],
            'mobile without the 9' => ['+5511887654321', PixKeyKind::Phone],
            'area code never assigned (20)' => ['+5520987654321', PixKeyKind::Phone],
            'area code 10 (the example of the DICT API)' => ['+5510998765432', PixKeyKind::Phone],
            'phone with one digit more' => ['+55119876543210', PixKeyKind::Phone],
            'phone with one digit less' => ['+551198765432', PixKeyKind::Phone],
            'phone with a space at the end' => [self::PHONE . ' ', PixKeyKind::Phone],
            'phone with a newline' => [self::PHONE . "\n", PixKeyKind::Phone],
            // e-mail
            'e-mail in capital letters' => ['Pix@bcb.gov.br', PixKeyKind::Email],
            'e-mail with a capital letter in the domain' => ['pix@BCB.gov.br', PixKeyKind::Email],
            'e-mail without the at sign' => ['pix.bcb.gov.br', PixKeyKind::Email],
            'e-mail with two at signs' => ['pix@pix@bcb.gov.br', PixKeyKind::Email],
            'e-mail without a local part' => ['@bcb.gov.br', PixKeyKind::Email],
            'e-mail without a domain' => ['pix@', PixKeyKind::Email],
            'e-mail with a domain of one label' => ['pix@localhost', PixKeyKind::Email],
            'e-mail with a leading dot' => ['.pix@bcb.gov.br', PixKeyKind::Email],
            'e-mail with a dot before the at sign' => ['pix.@bcb.gov.br', PixKeyKind::Email],
            'e-mail with two dots in a row' => ['pi..x@bcb.gov.br', PixKeyKind::Email],
            'e-mail with two dots in the domain' => ['pix@bcb..gov.br', PixKeyKind::Email],
            'e-mail with a domain ending in a dot' => ['pix@bcb.gov.br.', PixKeyKind::Email],
            'e-mail with a label starting with a hyphen' => ['pix@-bcb.gov.br', PixKeyKind::Email],
            'e-mail with a label ending with a hyphen' => ['pix@bcb-.gov.br', PixKeyKind::Email],
            'e-mail with an underscore in the domain' => ['pix@bcb_x.gov.br', PixKeyKind::Email],
            'e-mail with an ampersand (outside the conservative set)' => ['p&x@bcb.gov.br', PixKeyKind::Email],
            'e-mail with a percent sign' => ['p%x@bcb.gov.br', PixKeyKind::Email],
            'e-mail with a space' => ['p x@bcb.gov.br', PixKeyKind::Email],
            'e-mail with an accented letter' => ['píx@bcb.gov.br', PixKeyKind::Email],
            'e-mail in quotes' => ['"pix"@bcb.gov.br', PixKeyKind::Email],
            'e-mail with an IP literal' => ['pix@[10.0.0.1]', PixKeyKind::Email],
            'e-mail with a space at the end' => [self::EMAIL . ' ', PixKeyKind::Email],
            'e-mail with a newline' => [self::EMAIL . "\n", PixKeyKind::Email],
            'e-mail with a space at the start' => [' ' . self::EMAIL, PixKeyKind::Email],
            // random key
            'random key in capital letters' => ['01861AB8-4D4D-4D74-9CB3-FFC5CC1C82B6', PixKeyKind::Random],
            'random key with one capital letter' => ['01861ab8-4d4d-4d74-9cb3-ffc5cc1c82B6', PixKeyKind::Random],
            'random key without hyphens' => ['01861ab84d4d4d749cb3ffc5cc1c82b6', PixKeyKind::Random],
            'random key in braces' => ['{01861ab8-4d4d-4d74-9cb3-ffc5cc1c82b6}', PixKeyKind::Random],
            'random key of the DICT API example in capital letters' => ['123E4567-E89B-12D3-A456-426655440000', PixKeyKind::Random],
            'random key of version 0' => ['01861ab8-4d4d-0d74-9cb3-ffc5cc1c82b6', PixKeyKind::Random],
            'random key of version 6' => ['01861ab8-4d4d-6d74-9cb3-ffc5cc1c82b6', PixKeyKind::Random],
            'random key of version f' => ['01861ab8-4d4d-fd74-9cb3-ffc5cc1c82b6', PixKeyKind::Random],
            'random key of the variant 0' => ['01861ab8-4d4d-4d74-7cb3-ffc5cc1c82b6', PixKeyKind::Random],
            'random key of the variant c' => ['01861ab8-4d4d-4d74-ccb3-ffc5cc1c82b6', PixKeyKind::Random],
            'random key with a letter beyond f' => ['01861ab8-4d4d-4d74-9cb3-ffc5cc1c82g6', PixKeyKind::Random],
            'random key one character short' => ['01861ab8-4d4d-4d74-9cb3-ffc5cc1c82b', PixKeyKind::Random],
            'random key one character long' => ['01861ab8-4d4d-4d74-9cb3-ffc5cc1c82b60', PixKeyKind::Random],
            'random key with a newline' => [self::RANDOM . "\n", PixKeyKind::Random],
            'nil UUID' => ['00000000-0000-0000-0000-000000000000', PixKeyKind::Random],
            // any
            'null' => [null, PixKeyKind::Any],
            'empty' => ['', PixKeyKind::Any],
            'an integer' => [11987654321, PixKeyKind::Any],
            'a word' => ['pix', PixKeyKind::Any],
        ];
    }

    #[DataProvider('refused')]
    public function testRefused(mixed $candidate, PixKeyKind $kind): void
    {
        self::assertFalse((new PixKeySpecification($kind))->isSatisfiedBy($candidate), $kind->value);
        self::assertFalse((new PixKeySpecification())->isSatisfiedBy($candidate), 'any');
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function emailBoundaries(): array
    {
        $domain = '@' . str_repeat('d', 61) . '.br'; // 65 characters with the at sign

        return [
            '77 characters' => [str_repeat('a', 12) . $domain, true],
            '78 characters' => [str_repeat('a', 13) . $domain, false],
            'local part of 64 characters' => [str_repeat('a', 64) . '@b.br', true],
            'local part of 65 characters' => [str_repeat('a', 65) . '@b.br', false],
            'local part of 65 characters with a dot' => [str_repeat('a', 32) . '.' . str_repeat('a', 32) . '@b.br', false],
            'label of 63 characters' => ['a@' . str_repeat('b', 63) . '.br', true],
            'label of 64 characters' => ['a@' . str_repeat('b', 64) . '.br', false],
            'one-character local part and labels' => ['a@b.c', true],
            'every character of the conservative set' => ['!#$\'*+/=?^_`{|}~-@b.br', true],
            'dots between the characters' => ['a.b.c@b.br', true],
            'a hyphen inside a label' => ['a@b-c.br', true],
            'a digit as the label' => ['a@1.br', true],
            'three labels' => ['pix@bcb.gov.br', true],
        ];
    }

    #[DataProvider('emailBoundaries')]
    public function testEmailBoundaries(string $candidate, bool $expected): void
    {
        self::assertSame($expected, (new PixKeySpecification(PixKeyKind::Email))->isSatisfiedBy($candidate), (string) strlen($candidate));
        self::assertSame($expected, (new PixKeySpecification())->isSatisfiedBy($candidate), 'any');
    }

    public function testTheMaximumLengthOfAnEmailIsTheOneOfTheDict(): void
    {
        self::assertSame(77, PixKeySpecification::EMAIL_MAX_LENGTH);
    }

    public function testEveryAreaCodeIsAcceptedAndEveryOtherIsRefused(): void
    {
        for ($code = 0; $code <= 99; $code++) {
            $area = sprintf('%02d', $code);
            $expected = in_array($area, PhoneSpecification::AREA_CODES, true);
            self::assertSame($expected, (new PixKeySpecification(PixKeyKind::Phone))->isSatisfiedBy("+55{$area}987654321"), "area code {$area}");
        }
    }

    public function testTheVariantsOfARandomKey(): void
    {
        foreach (['8', '9', 'a', 'b'] as $variant) {
            $key = '01861ab8-4d4d-4d74-' . $variant . 'cb3-ffc5cc1c82b6';
            self::assertTrue((new PixKeySpecification(PixKeyKind::Random))->isSatisfiedBy($key), $variant);
        }
    }

    public function testTheVersionsOfARandomKey(): void
    {
        for ($version = 0; $version <= 15; $version++) {
            $key = '01861ab8-4d4d-' . dechex($version) . 'd74-9cb3-ffc5cc1c82b6';
            self::assertSame($version >= 1 && $version <= 5, (new PixKeySpecification(PixKeyKind::Random))->isSatisfiedBy($key), (string) $version);
        }
    }
}
