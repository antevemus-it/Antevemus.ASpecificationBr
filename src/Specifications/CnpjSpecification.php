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
 * CnpjSpecification - Leaf specification satisfied by a string that is a CNPJ, numeric or alphanumeric.
 *
 * The candidate is a string in the masked form `##.###.###/####-##` or in the unmasked form of fourteen
 * characters, as the format allows. In the alphanumeric CNPJ of the Receita Federal the twelve leading
 * characters may be letters (small letters are read as capitals) and the two check digits stay numeric;
 * with `alphanumeric: false` only digits are accepted. A numeric repetition is refused. The failure message
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
final class CnpjSpecification extends AbstractSpecification implements ISerializableSpecification
{
    private const MASKED_ALPHANUMERIC = '/^[0-9A-Za-z]{2}\.[0-9A-Za-z]{3}\.[0-9A-Za-z]{3}\/[0-9A-Za-z]{4}-\d{2}$/D';
    private const UNMASKED_ALPHANUMERIC = '/^[0-9A-Za-z]{12}\d{2}$/D';
    private const MASKED_NUMERIC = '/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/D';
    private const UNMASKED_NUMERIC = '/^\d{14}$/D';

    /**
     * @param DocumentFormat $format       Forms accepted (both, by default)
     * @param bool           $alphanumeric Whether letters are accepted in the twelve leading characters
     */
    public function __construct(
        private readonly DocumentFormat $format = DocumentFormat::Any,
        private readonly bool $alphanumeric = true
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        $value = $this->alphanumeric
            ? WrittenForm::read($candidate, $this->format, self::MASKED_ALPHANUMERIC, self::UNMASKED_ALPHANUMERIC)
            : WrittenForm::read($candidate, $this->format, self::MASKED_NUMERIC, self::UNMASKED_NUMERIC);

        return $value !== null && CheckDigits::isCnpj($value);
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
        return 'The value is not a valid CNPJ.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brCnpj", "format": "any", "alphanumeric": true}`.
     */
    public function toArray(): array
    {
        return ['format' => $this->format->value, 'alphanumeric' => $this->alphanumeric];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data, SpecificationCodec $codec): static
    {
        $codec->expectKeys($data, [], ['format', 'alphanumeric']);

        return new static(
            CodecOptions::enum($data, 'format', DocumentFormat::class, DocumentFormat::Any, $codec),
            CodecOptions::bool($data, 'alphanumeric', true, $codec)
        );
    }
}
