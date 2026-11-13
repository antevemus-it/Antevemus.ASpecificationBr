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
 * The candidate is a string of up to twelve digits (TSE Resolution 23.659/2021, art. 36, sole paragraph): an
 * eight-digit sequence whose leading zeros are dropped at issuance, the code of the state (01 to 28) and two
 * check digits by "Módulo 11", the first over the sequence and the last over the state code followed by the
 * first. A string of 5 to 12 digits is read left-padded with zeros to twelve, so the number is accepted with
 * or without its leading zeros; a sequence of zeros is refused. The weights and the rule of the remainders
 * (10 gives 0; 0 gives 1 for SP and MG) are not in the resolution and come from public descriptions of the
 * TSE routine; see CheckDigits::isVoterId(). The number has no mask set by a norm (the card prints it in
 * groups), so only digits are accepted, and nothing is trimmed.
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
