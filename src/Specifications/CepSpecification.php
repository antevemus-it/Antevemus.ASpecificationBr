<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\Internal\CodecOptions;
use Antevemus\ASpecificationBr\Internal\WrittenForm;

/**
 * CepSpecification - Leaf specification satisfied by a string that is a Brazilian postal code (CEP).
 *
 * The candidate is a string in the masked form `#####-###` or in the unmasked form of eight digits, as the
 * format allows, within the range of the postal codes, `01000-000` to `99999-999`. Whether the code is
 * assigned to an address is a question for the postal service's base, not for this specification. The
 * failure message never repeats the candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class CepSpecification extends AbstractSpecification implements ISerializableSpecification
{
    private const MASKED = '/^\d{5}-\d{3}$/D';
    private const UNMASKED = '/^\d{8}$/D';

    /** The lowest five-digit prefix of a postal code. */
    private const LOWEST_PREFIX = 1000;

    /**
     * @param DocumentFormat $format Forms accepted (both, by default)
     */
    public function __construct(private readonly DocumentFormat $format = DocumentFormat::Any)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        $value = WrittenForm::read($candidate, $this->format, self::MASKED, self::UNMASKED);

        return $value !== null && (int) substr($value, 0, 5) >= self::LOWEST_PREFIX;
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
        return 'The value is not a valid CEP.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brCep", "format": "any"}`.
     */
    public function toArray(): array
    {
        return ['format' => $this->format->value];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data, SpecificationCodec $codec): static
    {
        $codec->expectKeys($data, [], ['format']);

        return new static(CodecOptions::enum($data, 'format', DocumentFormat::class, DocumentFormat::Any, $codec));
    }
}
