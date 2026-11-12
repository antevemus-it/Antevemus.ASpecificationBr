<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Internal\CodecOptions;
use Antevemus\ASpecificationBr\PhoneKind;

/**
 * PhoneSpecification - Leaf specification satisfied by a string that is a Brazilian phone number.
 *
 * The candidate may carry spaces, parentheses, hyphens, dots and one leading `+`; once they are removed, only
 * digits may remain: 10 (landline: area code + `2` to `5` + 7 digits) or 11 (mobile: area code + `9` + 8
 * digits), or the same preceded by the country code `55` (12 or 13 digits; a leading `+` requires it). The
 * area code is one of the 67 of the numbering plan of Anatel. Carrier prefixes (`0xx`), non-geographic numbers
 * (`0800`, `0300`, `4004`) and extensions are not phone numbers here. The failure message never repeats the
 * candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class PhoneSpecification extends AbstractSpecification implements ISerializableSpecification
{
    /** The 67 area codes (DDD) of the Brazilian numbering plan. */
    public const AREA_CODES = [
        '11', '12', '13', '14', '15', '16', '17', '18', '19',
        '21', '22', '24', '27', '28',
        '31', '32', '33', '34', '35', '37', '38',
        '41', '42', '43', '44', '45', '46', '47', '48', '49',
        '51', '53', '54', '55',
        '61', '62', '63', '64', '65', '66', '67', '68', '69',
        '71', '73', '74', '75', '77', '79',
        '81', '82', '83', '84', '85', '86', '87', '88', '89',
        '91', '92', '93', '94', '95', '96', '97', '98', '99',
    ];

    private const WRITTEN = '/^\+?[0-9 ().-]+$/D';
    private const COUNTRY_CODE = '55';

    /**
     * @param PhoneKind $kind Kind of number accepted (mobile or landline, by default)
     */
    public function __construct(private readonly PhoneKind $kind = PhoneKind::Any)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!is_string($candidate) || preg_match(self::WRITTEN, $candidate) !== 1) {
            return false;
        }

        $digits = (string) preg_replace('/\D/', '', $candidate);
        $length = strlen($digits);

        if ($length === 12 || $length === 13) {
            if (!str_starts_with($digits, self::COUNTRY_CODE)) {
                return false;
            }
            $digits = substr($digits, 2);
        } elseif (str_starts_with($candidate, '+')) {
            return false;
        }

        if (!in_array(substr($digits, 0, 2), self::AREA_CODES, true)) {
            return false;
        }

        $kind = match (true) {
            strlen($digits) === 11 && $digits[2] === '9' => PhoneKind::Mobile,
            strlen($digits) === 10 && $digits[2] >= '2' && $digits[2] <= '5' => PhoneKind::Landline,
            default => null,
        };

        return $kind !== null && ($this->kind === PhoneKind::Any || $this->kind === $kind);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFailureMessage(mixed $candidate): string
    {
        return 'The value is not a valid Brazilian phone number.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brPhone", "kind": "any"}`.
     */
    public function toArray(): array
    {
        return ['kind' => $this->kind->value];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data, SpecificationCodec $codec): static
    {
        $codec->expectKeys($data, [], ['kind']);

        return new static(CodecOptions::enum($data, 'kind', PhoneKind::class, PhoneKind::Any, $codec));
    }
}
