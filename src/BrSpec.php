<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr;

use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecificationBr\Specifications\CepSpecification;
use Antevemus\ASpecificationBr\Specifications\CnjProcessNumberSpecification;
use Antevemus\ASpecificationBr\Specifications\CnpjSpecification;
use Antevemus\ASpecificationBr\Specifications\CpfSpecification;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;
use Antevemus\ASpecificationBr\Specifications\VehiclePlateSpecification;

/**
 * BrSpec - Facade of the Brazilian document specifications, and their registration in a codec.
 *
 * Each method returns a leaf specification of the core's algebra: it composes with `and`, `or` and `not`,
 * goes under `Spec::property()`, takes `because()` and `withCode()`, and is evaluated in memory (a SQL
 * repository falls back to memory for a leaf it cannot translate). `register()` gives a codec the names this
 * package writes as `t`, so a rule stored as JSON keeps them.
 *
 * @version    1.0.0
 * @package    Antevemus\ASpecificationBr
 * @author     Heliton Junior (CTO) - <contato@antevemus.com.br>
 * @copyright  Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
 * @license    MIT
 */
final class BrSpec
{
    /** The name each specification of this package is written under in a codec document. */
    public const TYPES = [
        'brCpf' => CpfSpecification::class,
        'brCnpj' => CnpjSpecification::class,
        'brCep' => CepSpecification::class,
        'brPhone' => PhoneSpecification::class,
        'brVehiclePlate' => VehiclePlateSpecification::class,
        'brCnjProcessNumber' => CnjProcessNumberSpecification::class,
    ];

    /**
     * A CPF, masked (`###.###.###-##`) or unmasked (eleven digits) as the format allows.
     *
     * @param DocumentFormat $format Forms accepted
     * @return CpfSpecification The leaf
     */
    public static function cpf(DocumentFormat $format = DocumentFormat::Any): CpfSpecification
    {
        return new CpfSpecification($format);
    }

    /**
     * A CNPJ, masked (`##.###.###/####-##`) or unmasked (fourteen characters), alphanumeric by default.
     *
     * @param DocumentFormat $format       Forms accepted
     * @param bool           $alphanumeric Whether letters are accepted in the twelve leading characters
     * @return CnpjSpecification The leaf
     */
    public static function cnpj(DocumentFormat $format = DocumentFormat::Any, bool $alphanumeric = true): CnpjSpecification
    {
        return new CnpjSpecification($format, $alphanumeric);
    }

    /**
     * A postal code, masked (`#####-###`) or unmasked (eight digits), from `01000-000` to `99999-999`.
     *
     * @param DocumentFormat $format Forms accepted
     * @return CepSpecification The leaf
     */
    public static function cep(DocumentFormat $format = DocumentFormat::Any): CepSpecification
    {
        return new CepSpecification($format);
    }

    /**
     * A phone number with a valid area code, mobile or landline, with or without the country code `55`.
     *
     * @param PhoneKind $kind Kind of number accepted
     * @return PhoneSpecification The leaf
     */
    public static function phone(PhoneKind $kind = PhoneKind::Any): PhoneSpecification
    {
        return new PhoneSpecification($kind);
    }

    /**
     * A vehicle plate, legacy (`AAA9999`, `AAA-9999`) or Mercosur (`AAA9A99`).
     *
     * @param PlateKind $kind Kind of plate accepted
     * @return VehiclePlateSpecification The leaf
     */
    public static function vehiclePlate(PlateKind $kind = PlateKind::Any): VehiclePlateSpecification
    {
        return new VehiclePlateSpecification($kind);
    }

    /**
     * A unified process number of the CNJ, masked (`NNNNNNN-DD.AAAA.J.TR.OOOO`) or unmasked (twenty digits).
     *
     * @param DocumentFormat $format Forms accepted
     * @return CnjProcessNumberSpecification The leaf
     */
    public static function cnjProcessNumber(DocumentFormat $format = DocumentFormat::Any): CnjProcessNumberSpecification
    {
        return new CnjProcessNumberSpecification($format);
    }

    /**
     * Registers every specification of this package in a codec, under the names of TYPES.
     *
     * @param SpecificationCodec $codec Codec to extend
     * @return SpecificationCodec The same codec
     */
    public static function register(SpecificationCodec $codec): SpecificationCodec
    {
        foreach (self::TYPES as $type => $class) {
            $codec->register($type, $class);
        }

        return $codec;
    }
}
