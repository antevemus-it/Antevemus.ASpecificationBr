<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Internal\CheckDigits;

/**
 * RenavamSpecification - Leaf specification satisfied by a string that is a RENAVAM, the national registry
 * code of a vehicle.
 *
 * The candidate is a string of eleven digits: ten and one check digit (Denatran's modulo 11 routine). The code
 * has no mask; a RENAVAM of nine digits, from before the eleven-digit numbering, is written with two leading
 * zeros, as the registry does. Nothing is trimmed, and the zeros are refused. Whether the vehicle is
 * registered is a question for the registry. The failure message never repeats the candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class RenavamSpecification extends AbstractSpecification implements ISerializableSpecification
{
    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return is_string($candidate) && CheckDigits::isRenavam($candidate);
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
        return 'The value is not a valid RENAVAM.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brRenavam"}`.
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data, SpecificationCodec $codec): static
    {
        $codec->expectKeys($data, []);

        return new static();
    }
}
