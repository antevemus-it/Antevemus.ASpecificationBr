<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Internal\CheckDigits;

/**
 * CnhSpecification - Leaf specification satisfied by a string that is the national registration number of a
 * Brazilian driver's license (CNH, "número do registro nacional").
 *
 * The candidate is a string of eleven digits: nine and two security check digits (Contran Resolution
 * 886/2021, art. 4, I, which does not publish how they are computed). The number has no official mask, so no
 * other writing is accepted, and nothing is trimmed. A repetition of one digit is refused. The check digits
 * follow literally the modulo 11 routine attributed to Denatran and used by the public validators, with its
 * adjustment: when the first remainder is 10, the first digit is 0 and the second is lowered by 2, and a base
 * whose second digit would be negative has no valid completion; see CheckDigits::isCnh(). The number of the form ("espelho") and the RENACH are other numbers. Whether the
 * license was issued or is valid is a question for the registry. The failure message never repeats the
 * candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class CnhSpecification extends AbstractSpecification implements ISerializableSpecification
{
    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return is_string($candidate) && CheckDigits::isCnh($candidate);
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
        return 'The value is not a valid CNH number.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brCnh"}`.
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
