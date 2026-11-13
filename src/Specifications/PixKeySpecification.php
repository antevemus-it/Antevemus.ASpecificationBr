<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Internal\CheckDigits;
use Antevemus\ASpecificationBr\Internal\CodecOptions;
use Antevemus\ASpecificationBr\PixKeyKind;

/**
 * PixKeySpecification - Leaf specification satisfied by a string that is a PIX key, written as the DICT of the
 * Banco Central stores it.
 *
 * The forms follow section 1 of the DICT operational manual (Manual Operacional do DICT, version 8.5) and the
 * patterns of the DICT API specification, which the manual points to:
 *
 * - CPF: eleven digits without punctuation, with the CPF check (a repetition is refused).
 * - CNPJ: fourteen characters without punctuation, the alphanumeric CNPJ included (the manual adopted it in
 *   version 8.4, from 2026-07-01): the twelve leading may be digits or capital letters, the check digits are
 *   numeric, with the CNPJ check. Small letters are refused: the DICT pattern has none.
 * - Phone: the mobile number ("número de telefone celular") in E.164, `+55`, an area code of
 *   PhoneSpecification::AREA_CODES, `9` and eight digits, with nothing else.
 * - E-mail: at most 77 characters, in small letters, as the DICT requires. The check is conservative, a
 *   subset of the DICT pattern: the local part takes letters, digits and ``!#$'*+/=?^_`{|}~-``, with dots only
 *   between them, and at most 64 characters (RFC 5321); the domain takes at least two labels of letters,
 *   digits and inner hyphens, each of at most 63 characters.
 * - Random key (EVP): a UUID of RFC 4122 in small letters, `xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx`, as the
 *   manual and the 8-4-4-4-12 pattern of the API specify it, with the version `M` from 1 to 5 and the variant
 *   `N` of RFC 4122 (8, 9, a or b). The version is not narrowed to 4: the example of the DICT API
 *   specification (2.13.0) is a version 1 UUID. The nil UUID, capital letters and other writings are refused;
 *   lower the case before checking if a form may receive capitals.
 *
 * Nothing is trimmed and no punctuation is removed: a key is compared with the DICT as written. Whether the
 * key is registered is a question for the DICT. The failure message never repeats the candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class PixKeySpecification extends AbstractSpecification implements ISerializableSpecification
{
    /** The maximum length of an e-mail key in the DICT. */
    public const EMAIL_MAX_LENGTH = 77;

    private const PHONE = '/^\+55([0-9]{2})9[0-9]{8}$/D';
    private const EMAIL = '/^(?=[^@]{1,64}@)[a-z0-9!#$\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$\'*+\/=?^_`{|}~-]+)*'
        . '@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/D';
    private const RANDOM = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D';

    /**
     * @param PixKeyKind $kind Kind of key accepted (any of the five, by default)
     */
    public function __construct(private readonly PixKeyKind $kind = PixKeyKind::Any)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!is_string($candidate)) {
            return false;
        }

        if ($this->kind !== PixKeyKind::Any) {
            return self::isKey($this->kind, $candidate);
        }

        foreach (PixKeyKind::cases() as $kind) {
            if ($kind !== PixKeyKind::Any && self::isKey($kind, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a string is a key of one kind other than Any.
     *
     * @param PixKeyKind $kind      Kind of key, other than Any
     * @param string     $candidate Value under evaluation
     * @return bool True when the value is a key of that kind
     */
    private static function isKey(PixKeyKind $kind, string $candidate): bool
    {
        return match ($kind) {
            PixKeyKind::Cpf => CheckDigits::isCpf($candidate),
            PixKeyKind::Cnpj => CheckDigits::isCnpj($candidate),
            PixKeyKind::Phone => preg_match(self::PHONE, $candidate, $match) === 1
                && in_array($match[1], PhoneSpecification::AREA_CODES, true),
            PixKeyKind::Email => strlen($candidate) <= self::EMAIL_MAX_LENGTH && preg_match(self::EMAIL, $candidate) === 1,
            PixKeyKind::Random => preg_match(self::RANDOM, $candidate) === 1,
        };
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
        return 'The value is not a valid PIX key.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brPixKey", "kind": "any"}`.
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

        return new static(CodecOptions::enum($data, 'kind', PixKeyKind::class, PixKeyKind::Any, $codec));
    }
}
