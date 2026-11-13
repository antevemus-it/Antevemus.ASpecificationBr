<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecification\Contracts\ISerializableSpecification;
use Antevemus\ASpecification\Serialization\Exceptions\SpecificationCodecException;
use Antevemus\ASpecification\Serialization\SpecificationCodec;
use Antevemus\ASpecification\Spec;
use Antevemus\ASpecificationBr\BrSpec;
use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\PhoneKind;
use Antevemus\ASpecificationBr\PixKeyKind;
use Antevemus\ASpecificationBr\PlateKind;
use Antevemus\ASpecificationBr\Specifications\CepSpecification;
use Antevemus\ASpecificationBr\Specifications\CnhSpecification;
use Antevemus\ASpecificationBr\Specifications\CnjProcessNumberSpecification;
use Antevemus\ASpecificationBr\Specifications\CnpjSpecification;
use Antevemus\ASpecificationBr\Specifications\CpfSpecification;
use Antevemus\ASpecificationBr\Specifications\PhoneSpecification;
use Antevemus\ASpecificationBr\Specifications\PisSpecification;
use Antevemus\ASpecificationBr\Specifications\PixKeySpecification;
use Antevemus\ASpecificationBr\Specifications\RenavamSpecification;
use Antevemus\ASpecificationBr\Specifications\VehiclePlateSpecification;
use Antevemus\ASpecificationBr\Specifications\VoterIdSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The facade, the codec and the secrecy of the failures, over a pool that holds every specification of
 * src/Specifications with every option: a class added there and left out of the pool fails the guard.
 *
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class BrSpecAndCodecTest extends TestCase
{
    /**
     * Every specification with every option, and a candidate it refuses that looks like a real document.
     *
     * @return list<array{ISerializableSpecification, string}>
     */
    public static function pool(): array
    {
        $pool = [];
        foreach (DocumentFormat::cases() as $format) {
            $pool[] = [new CpfSpecification($format), '876.047.238-38'];
            $pool[] = [new CepSpecification($format), '00999-999'];
            $pool[] = [new CnjProcessNumberSpecification($format), '1606504-42.2041.6.53.3394'];
            $pool[] = [new PisSpecification($format), '824.33543.31-6'];
            foreach ([true, false] as $alphanumeric) {
                $pool[] = [new CnpjSpecification($format, $alphanumeric), 'RN.0HY.F3C/2TQZ-91'];
            }
        }
        foreach (PhoneKind::cases() as $kind) {
            $pool[] = [new PhoneSpecification($kind), '(20) 98765-4321'];
        }
        foreach (PlateKind::cases() as $kind) {
            $pool[] = [new VehiclePlateSpecification($kind), 'BRA-2E19X'];
        }
        $pool[] = [new CnhSpecification(), '46602254572'];
        $pool[] = [new VoterIdSpecification(), '309621560192'];
        $pool[] = [new RenavamSpecification(), '34797468680'];
        foreach (PixKeyKind::cases() as $kind) {
            $pool[] = [new PixKeySpecification($kind), '+5520987654321'];
        }

        return $pool;
    }

    /**
     * @return array<string, array{ISerializableSpecification, string}>
     */
    public static function poolCases(): array
    {
        $cases = [];
        foreach (self::pool() as [$specification, $candidate]) {
            $cases[(new ReflectionClass($specification))->getShortName() . ' ' . json_encode($specification->toArray())] = [$specification, $candidate];
        }

        return $cases;
    }

    public function testThePoolCoversEverySpecificationOfTheSourceAndTheFacadeNamesThemAll(): void
    {
        $classes = [];
        foreach (glob(dirname(__DIR__, 2) . '/src/Specifications/*.php') ?: [] as $file) {
            $class = 'Antevemus\\ASpecificationBr\\Specifications\\' . basename($file, '.php');
            if (!(new ReflectionClass($class))->isAbstract()) {
                $classes[] = $class;
            }
        }
        sort($classes);
        $inPool = array_values(array_unique(array_map(static fn(array $entry): string => $entry[0]::class, self::pool())));
        sort($inPool);
        $named = array_values(BrSpec::TYPES);
        sort($named);

        self::assertNotEmpty($classes);
        self::assertSame($classes, $inPool, 'every concrete class of src/Specifications is in the pool');
        self::assertSame($classes, $named, 'every concrete class of src/Specifications has a name in BrSpec::TYPES');
    }

    #[DataProvider('poolCases')]
    public function testEverySpecificationRoundTripsThroughTheCodec(ISerializableSpecification $specification, string $candidate): void
    {
        $codec = BrSpec::register(new SpecificationCodec());
        $json = $codec->toJson($specification);
        $decoded = $codec->fromJson($json);

        self::assertInstanceOf($specification::class, $decoded);
        self::assertEquals($specification, $decoded, 'every option survives the round trip');
        self::assertSame($specification->toArray(), $decoded->toArray());
        self::assertSame($json, $codec->toJson($decoded), 'the second encoding is byte by byte the first');
        self::assertSame('string', $decoded->getType());
    }

    public function testTheSerializedFormOfEachSpecification(): void
    {
        $codec = BrSpec::register(new SpecificationCodec());
        $expected = [
            '{"t":"brCpf","format":"masked"}' => BrSpec::cpf(DocumentFormat::Masked),
            '{"t":"brCnpj","format":"unmasked","alphanumeric":false}' => BrSpec::cnpj(DocumentFormat::Unmasked, false),
            '{"t":"brCep","format":"masked"}' => BrSpec::cep(DocumentFormat::Masked),
            '{"t":"brPhone","kind":"landline"}' => BrSpec::phone(PhoneKind::Landline),
            '{"t":"brVehiclePlate","kind":"mercosur"}' => BrSpec::vehiclePlate(PlateKind::Mercosur),
            '{"t":"brCnjProcessNumber","format":"unmasked"}' => BrSpec::cnjProcessNumber(DocumentFormat::Unmasked),
            '{"t":"brCnh"}' => BrSpec::cnh(),
            '{"t":"brPis","format":"masked"}' => BrSpec::pis(DocumentFormat::Masked),
            '{"t":"brVoterId"}' => BrSpec::voterId(),
            '{"t":"brRenavam"}' => BrSpec::renavam(),
            '{"t":"brPixKey","kind":"random"}' => BrSpec::pixKey(PixKeyKind::Random),
            '{"t":"brPixKey","kind":"any"}' => BrSpec::pixKey(),
            '{"t":"brPis","format":"any"}' => BrSpec::pis(),
        ];
        foreach ($expected as $spec => $specification) {
            self::assertSame('{"v":1,"spec":' . $spec . '}', $codec->toJson($specification));
        }
    }

    public function testAnUnknownKeyIsRefusedForEveryName(): void
    {
        $codec = BrSpec::register(new SpecificationCodec());
        foreach (array_keys(BrSpec::TYPES) as $type) {
            try {
                $codec->decode(['v' => 1, 'spec' => ['t' => $type, 'extra' => 'any']]);
                self::fail("{$type}: an unknown key is refused");
            } catch (SpecificationCodecException $e) {
                self::assertStringContainsString('Unknown key "extra"', $e->getMessage(), $type);
            }
        }
    }

    #[DataProvider('poolCases')]
    public function testNoFailureNorSerializationRepeatsTheCandidate(ISerializableSpecification $specification, string $candidate): void
    {
        $result = $specification->evaluate($candidate);
        self::assertFalse($result->isSatisfied, 'the pool candidate is refused');

        $written = json_encode([$result->getReasons(), $result->getCodes(), $specification->toArray(), (string) $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        self::assertIsString($written);
        $digits = (string) preg_replace('/[^0-9A-Za-z]/', '', $candidate);
        self::assertStringNotContainsString($candidate, $written);
        self::assertStringNotContainsString($digits, (string) preg_replace('/[^0-9A-Za-z]/', '', $written));
    }

    public function testTheFacadeBuildsEachSpecificationWithItsOptions(): void
    {
        self::assertEquals(new CpfSpecification(DocumentFormat::Masked), BrSpec::cpf(DocumentFormat::Masked));
        self::assertEquals(new CnpjSpecification(DocumentFormat::Unmasked, false), BrSpec::cnpj(DocumentFormat::Unmasked, false));
        self::assertEquals(new CepSpecification(DocumentFormat::Masked), BrSpec::cep(DocumentFormat::Masked));
        self::assertEquals(new PhoneSpecification(PhoneKind::Mobile), BrSpec::phone(PhoneKind::Mobile));
        self::assertEquals(new VehiclePlateSpecification(PlateKind::Mercosur), BrSpec::vehiclePlate(PlateKind::Mercosur));
        self::assertEquals(new CnjProcessNumberSpecification(DocumentFormat::Masked), BrSpec::cnjProcessNumber(DocumentFormat::Masked));
        self::assertEquals(new CnpjSpecification(), BrSpec::cnpj());
        self::assertEquals(new CnhSpecification(), BrSpec::cnh());
        self::assertEquals(new PisSpecification(DocumentFormat::Unmasked), BrSpec::pis(DocumentFormat::Unmasked));
        self::assertEquals(new PisSpecification(), BrSpec::pis());
        self::assertEquals(new VoterIdSpecification(), BrSpec::voterId());
        self::assertEquals(new RenavamSpecification(), BrSpec::renavam());
        self::assertEquals(new PixKeySpecification(PixKeyKind::Email), BrSpec::pixKey(PixKeyKind::Email));
        self::assertEquals(new PixKeySpecification(), BrSpec::pixKey());
    }

    public function testTheDocumentsWithoutOptionsTakeTheDefaults(): void
    {
        $codec = BrSpec::register(new SpecificationCodec());
        foreach (array_keys(BrSpec::TYPES) as $type) {
            $decoded = $codec->decode(['v' => 1, 'spec' => ['t' => $type]]);
            self::assertInstanceOf(BrSpec::TYPES[$type], $decoded);
            self::assertEquals(new (BrSpec::TYPES[$type])(), $decoded, $type);
        }
    }

    public function testTheCodecWithoutRegistrationDoesNotKnowTheNames(): void
    {
        $this->expectException(SpecificationCodecException::class);
        (new SpecificationCodec())->decode(['v' => 1, 'spec' => ['t' => 'brCpf']]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidDocuments(): array
    {
        return [
            'unknown key' => [['t' => 'brCpf', 'mask' => 'any'], 'Unknown key "mask"'],
            'format not a case' => [['t' => 'brCep', 'format' => 'pretty'], 'Key "format" must be one of: any, masked, unmasked.'],
            'format not a string' => [['t' => 'brCnjProcessNumber', 'format' => 1], 'Key "format" must be one of: any, masked, unmasked.'],
            'kind not a case' => [['t' => 'brPhone', 'kind' => 'fax'], 'Key "kind" must be one of: any, mobile, landline.'],
            'plate kind not a case' => [['t' => 'brVehiclePlate', 'kind' => 'old'], 'Key "kind" must be one of: any, legacy, mercosur.'],
            'alphanumeric not a boolean' => [['t' => 'brCnpj', 'alphanumeric' => 'yes'], 'Key "alphanumeric" must be a boolean.'],
            'alphanumeric as an integer' => [['t' => 'brCnpj', 'alphanumeric' => 1], 'Key "alphanumeric" must be a boolean.'],
            'PIS format not a case' => [['t' => 'brPis', 'format' => 'pretty'], 'Key "format" must be one of: any, masked, unmasked.'],
            'PIX kind not a case' => [['t' => 'brPixKey', 'kind' => 'evp'], 'Key "kind" must be one of: any, cpf, cnpj, phone, email, random.'],
            'PIX kind not a string' => [['t' => 'brPixKey', 'kind' => null], 'Key "kind" must be one of: any, cpf, cnpj, phone, email, random.'],
            'CNH takes no option' => [['t' => 'brCnh', 'format' => 'any'], 'Unknown key "format"'],
            'voter registration number takes no option' => [['t' => 'brVoterId', 'kind' => 'any'], 'Unknown key "kind"'],
            'RENAVAM takes no option' => [['t' => 'brRenavam', 'format' => 'unmasked'], 'Unknown key "format"'],
            'PIX key takes no format' => [['t' => 'brPixKey', 'format' => 'any'], 'Unknown key "format"'],
        ];
    }

    /**
     * @param array<string, mixed> $spec
     */
    #[DataProvider('invalidDocuments')]
    public function testAnInvalidDocumentIsRefusedWithItsReason(array $spec, string $reason): void
    {
        $codec = BrSpec::register(new SpecificationCodec());
        try {
            $codec->decode(['v' => 1, 'spec' => $spec]);
            self::fail('refused');
        } catch (SpecificationCodecException $e) {
            self::assertStringContainsString($reason, $e->getMessage());
        }
    }

    public function testRegisterReturnsTheSameCodecAndRegistersEveryName(): void
    {
        $codec = new SpecificationCodec();
        self::assertSame($codec, BrSpec::register($codec));
        foreach (BrSpec::TYPES as $type => $class) {
            self::assertSame($class, $codec->types()[$type] ?? null, $type);
        }
    }

    public function testTheLeavesComposeWithTheCoreAlgebra(): void
    {
        $party = Spec::property('document', BrSpec::cpf()->or(BrSpec::cnpj()))
            ->and(Spec::property('zip', BrSpec::cep()))
            ->and(Spec::property('phone', BrSpec::phone(PhoneKind::Mobile)));

        self::assertTrue($party->isSatisfiedBy((object) ['document' => '11.222.333/0001-81', 'zip' => '01310-100', 'phone' => '(11) 98765-4321']));
        self::assertTrue($party->isSatisfiedBy((object) ['document' => '529.982.247-25', 'zip' => '01310100', 'phone' => '+55 11 98765-4321']));
        self::assertFalse($party->isSatisfiedBy((object) ['document' => '529.982.247-25', 'zip' => '01310100', 'phone' => '(21) 3456-7890']));
        self::assertFalse(BrSpec::vehiclePlate()->not()->isSatisfiedBy('BRA2E19'));

        $driver = Spec::property('license', BrSpec::cnh())->and(Spec::property('vehicle', BrSpec::renavam()))->and(Spec::property('payout', BrSpec::pixKey()));
        self::assertTrue($driver->isSatisfiedBy((object) ['license' => '46602254573', 'vehicle' => '34797468689', 'payout' => 'pix@bcb.gov.br']));
        self::assertFalse($driver->isSatisfiedBy((object) ['license' => '46602254573', 'vehicle' => '34797468689', 'payout' => 'Pix@bcb.gov.br']));

        $result = Spec::property('document', BrSpec::cpf()->because('Informe um CPF válido.')->withCode('PARTY_CPF'))->evaluate((object) ['document' => '529.982.247-26']);
        self::assertSame(['PARTY_CPF'], $result->getCodes());
        self::assertSame(['Informe um CPF válido.'], array_values(array_unique($result->getReasons())));
    }

    public function testTheDefaultFailureMessages(): void
    {
        $messages = [
            'The value is not a valid CPF.' => BrSpec::cpf(),
            'The value is not a valid CNPJ.' => BrSpec::cnpj(),
            'The value is not a valid CEP.' => BrSpec::cep(),
            'The value is not a valid Brazilian phone number.' => BrSpec::phone(),
            'The value is not a valid Brazilian vehicle plate.' => BrSpec::vehiclePlate(),
            'The value is not a valid CNJ process number.' => BrSpec::cnjProcessNumber(),
            'The value is not a valid CNH number.' => BrSpec::cnh(),
            'The value is not a valid PIS/PASEP number.' => BrSpec::pis(),
            'The value is not a valid voter registration number.' => BrSpec::voterId(),
            'The value is not a valid RENAVAM.' => BrSpec::renavam(),
            'The value is not a valid PIX key.' => BrSpec::pixKey(),
        ];
        foreach ($messages as $message => $specification) {
            self::assertSame([$message], $specification->evaluate('x')->getReasons());
        }
    }
}
