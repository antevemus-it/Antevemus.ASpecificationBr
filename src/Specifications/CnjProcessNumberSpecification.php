<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\Internal\CheckDigits;
use Antevemus\ASpecificationBr\Internal\CodecOptions;
use Antevemus\ASpecificationBr\Internal\WrittenForm;

/**
 * CnjProcessNumberSpecification - Leaf specification satisfied by a string that is a unified process number
 * of the Brazilian judiciary (CNJ Resolution 65/2008).
 *
 * The candidate is a string in the masked form `NNNNNNN-DD.AAAA.J.TR.OOOO` or in the unmasked form of twenty
 * digits, as the format allows; the segment of the judiciary (J) is 1 to 9 and the check digits (DD) are
 * right (ISO 7064 MOD 97-10). The court and the origin are not checked against a table. The failure message
 * never repeats the candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class CnjProcessNumberSpecification extends AbstractSpecification implements ISerializableSpecification
{
    private const MASKED = '/^\d{7}-\d{2}\.\d{4}\.\d\.\d{2}\.\d{4}$/D';
    private const UNMASKED = '/^\d{20}$/D';

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

        return $value !== null && CheckDigits::isCnjProcessNumber($value);
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
        return 'The value is not a valid CNJ process number.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brCnjProcessNumber", "format": "any"}`.
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
