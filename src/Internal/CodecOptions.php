<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Internal;

use Antevemus\ASpecification\Serialization\SpecificationCodec;
use BackedEnum;

/**
 * CodecOptions - Reads the options of a specification of this package from its serialized form.
 *
 * @internal Not part of the public API; the specifications are.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @subpackage Internal
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class CodecOptions
{
    /**
     * An optional enum option, written as the value of its case.
     *
     * Every option of this package is a string-backed enum.
     *
     * @template E of BackedEnum
     * @param array<string, mixed> $data     Serialized fields
     * @param string               $key      Name of the field
     * @param class-string<E>      $enum     Backed enum of the option
     * @param E                    $default  Case used when the field is absent
     * @param SpecificationCodec   $codec    Codec decoding the document
     * @return E The case read
     */
    public static function enum(array $data, string $key, string $enum, BackedEnum $default, SpecificationCodec $codec): BackedEnum
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        $case = is_string($data[$key]) ? $enum::tryFrom($data[$key]) : null;
        if ($case === null) {
            $allowed = implode(', ', array_map(static fn(BackedEnum $c): string => $c->value, $enum::cases()));
            throw $codec->invalid(sprintf('Key "%s" must be one of: %s.', $key, $allowed));
        }

        return $case;
    }

    /**
     * An optional boolean option.
     *
     * @param array<string, mixed> $data    Serialized fields
     * @param string               $key     Name of the field
     * @param bool                 $default Value used when the field is absent
     * @param SpecificationCodec   $codec   Codec decoding the document
     * @return bool The value read
     */
    public static function bool(array $data, string $key, bool $default, SpecificationCodec $codec): bool
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        if (!is_bool($data[$key])) {
            throw $codec->invalid(sprintf('Key "%s" must be a boolean.', $key));
        }

        return $data[$key];
    }
}
