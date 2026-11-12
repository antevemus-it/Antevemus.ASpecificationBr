<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Specifications;

use Antevemus\ASpecification\AbstractSpecification;
use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Internal\CodecOptions;
use Antevemus\ASpecificationBr\PlateKind;

/**
 * VehiclePlateSpecification - Leaf specification satisfied by a string that is a Brazilian vehicle plate.
 *
 * A legacy plate is three letters and four digits, `AAA9999` or `AAA-9999`; a Mercosur plate is three
 * letters, a digit, a letter and two digits, `AAA9A99`. Small letters are accepted. Nothing is trimmed.
 * Whether the plate is registered is a question for the vehicle registry. The failure message never repeats
 * the candidate.
 *
 * @extends AbstractSpecification<string>
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Specifications
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class VehiclePlateSpecification extends AbstractSpecification implements ISerializableSpecification
{
    private const LEGACY = '/^[A-Za-z]{3}-?\d{4}$/D';
    private const MERCOSUR = '/^[A-Za-z]{3}\d[A-Za-z]\d{2}$/D';

    /**
     * @param PlateKind $kind Kind of plate accepted (legacy or Mercosur, by default)
     */
    public function __construct(private readonly PlateKind $kind = PlateKind::Any)
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

        return match ($this->kind) {
            PlateKind::Legacy => preg_match(self::LEGACY, $candidate) === 1,
            PlateKind::Mercosur => preg_match(self::MERCOSUR, $candidate) === 1,
            PlateKind::Any => preg_match(self::LEGACY, $candidate) === 1 || preg_match(self::MERCOSUR, $candidate) === 1,
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
        return 'The value is not a valid Brazilian vehicle plate.';
    }

    /**
     * {@inheritdoc}
     *
     * Serialized as `{"t": "brVehiclePlate", "kind": "any"}`.
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

        return new static(CodecOptions::enum($data, 'kind', PlateKind::class, PlateKind::Any, $codec));
    }
}
