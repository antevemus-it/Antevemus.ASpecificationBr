<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Internal\CheckDigits;

/**
 * VoterIdSpecification - Leaf specification satisfied by a string that is a Brazilian voter registration
 * number (título de eleitor).
 *
 * The candidate is a string of twelve digits: an eight-digit sequence, the code of the state (01 to 28) and
 * two check digits, the second over the state code and the first (TSE Resolution 20.132/1998). The number has
 * no mask set by a norm (the card prints it in three groups of four), so only the twelve digits are accepted,
 * and nothing is trimmed. The thirteen-digit numbers of some old registrations of SP and MG are not accepted.
 * Whether the registration exists or is regular is a question for the electoral court. The failure message
 * never repeats the candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class VoterIdSpecification extends AbstractSpecification implements ISerializableSpecification
{
    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return is_string($candidate) && CheckDigits::isVoterId($candidate);
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
        return 'The value is not a valid voter registration number.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brVoterId"}`.
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
