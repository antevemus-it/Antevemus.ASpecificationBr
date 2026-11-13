<?php

declare(strict_types=1);

namespace Antevemus\ASpecificationBr\Tests\Unit;

use Antevemus\ASpecificationBr\DocumentFormat;
use Antevemus\ASpecificationBr\Specifications\CnhSpecification;
use Antevemus\ASpecificationBr\Specifications\PisSpecification;
use Antevemus\ASpecificationBr\Specifications\RenavamSpecification;
use Antevemus\ASpecificationBr\Specifications\VoterIdSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * CNH, PIS/PASEP, voter registration number and RENAVAM: fixed cases, every one fictitious (drawn from
 * tests/Support/DocumentGenerator with a fixed seed, and chosen to reach each branch of the rules).
 *
 * @version    1.1.0
 * @package    Antevemus\ASpecificationBr\Tests
 * @subpackage Unit
 */
final class RegistrationNumberSpecificationsTest extends TestCase
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function cnhCases(): array
    {
        return [
            'plain' => ['46602254573', true],
            'first remainder 10 and second 10: the second digit is 0' => ['18682364800', true],
            'first remainder 10 and second 10, with the discount taken (8)' => ['18682364808', false],
            'first remainder 10 and second 5: the discount gives 3' => ['66742326403', true],
            'first remainder 10 and second 5, the discount forgotten (5)' => ['66742326405', false],
            'first remainder 10 and second 0: no completion (9 refused)' => ['08657511109', false],
            'first remainder 10 and second 0: no completion (0 refused)' => ['08657511100', false],
            'first remainder 10 and second 1: no completion (0 refused)' => ['15518794400', false],
            'second remainder 10 without discount, written 0' => ['33778190700', true],
            'first check digit wrong' => ['46602254583', false],
            'second check digit wrong' => ['46602254572', false],
            'a check digit 10 written as 1' => ['33778190701', false],
            'repetition of ones (right arithmetic)' => ['11111111111', false],
            'repetition of nines (right arithmetic)' => ['99999999999', false],
            'zeros' => ['00000000000', false],
            'ten digits' => ['4660225457', false],
            'twelve digits' => ['466022545730', false],
            'with a hyphen' => ['466022545-73', false],
            'with spaces' => ['466 022 545 73', false],
            'a space at the end' => ['46602254573 ', false],
            'a space at the start' => [' 46602254573', false],
            'a newline at the end' => ["46602254573\n", false],
            'a letter' => ['4660225457A', false],
            'an integer' => [46602254573, false],
            'null' => [null, false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('cnhCases')]
    public function testCnh(mixed $candidate, bool $expected): void
    {
        self::assertSame($expected, (new CnhSpecification())->isSatisfiedBy($candidate));
    }

    /**
     * Bases whose first remainder is 10 (first check digit 0, discount 2) and whose second remainder is 0 or 1:
     * the second digit would be negative, so no pair of check digits completes them.
     *
     * @return array<string, array{string}>
     */
    public static function cnhBasesWithoutCompletion(): array
    {
        return [
            'second remainder 0' => ['086575111'],
            'second remainder 1' => ['155187944'],
        ];
    }

    #[DataProvider('cnhBasesWithoutCompletion')]
    public function testACnhBaseWithANegativeSecondDigitHasNoCompletion(string $base): void
    {
        for ($pair = 0; $pair <= 99; $pair++) {
            $candidate = $base . sprintf('%02d', $pair);
            self::assertFalse((new CnhSpecification())->isSatisfiedBy($candidate), $candidate);
        }
    }

    public function testACnhBaseWithTheDiscountAndSecondRemainder10HasOnlyTheCompletion00(): void
    {
        $accepted = [];
        for ($pair = 0; $pair <= 99; $pair++) {
            if ((new CnhSpecification())->isSatisfiedBy('186823648' . sprintf('%02d', $pair))) {
                $accepted[] = sprintf('%02d', $pair);
            }
        }
        self::assertSame(['00'], $accepted);
    }

    /**
     * @return array<string, array{mixed, DocumentFormat, bool}>
     */
    public static function pisCases(): array
    {
        return [
            'masked, any' => ['824.33543.31-5', DocumentFormat::Any, true],
            'unmasked, any' => ['82433543315', DocumentFormat::Any, true],
            'masked, masked' => ['824.33543.31-5', DocumentFormat::Masked, true],
            'unmasked, masked' => ['82433543315', DocumentFormat::Masked, false],
            'unmasked, unmasked' => ['82433543315', DocumentFormat::Unmasked, true],
            'masked, unmasked' => ['824.33543.31-5', DocumentFormat::Unmasked, false],
            'remainder 0 gives 0' => ['448.52365.82-0', DocumentFormat::Any, true],
            'remainder 1 gives 0' => ['56087854690', DocumentFormat::Any, true],
            'remainder 0 written as 11 minus the remainder (1)' => ['44852365821', DocumentFormat::Any, false],
            'check digit wrong' => ['824.33543.31-6', DocumentFormat::Any, false],
            'another digit changed' => ['924.33543.31-5', DocumentFormat::Any, false],
            'zeros (right arithmetic)' => ['000.00000.00-0', DocumentFormat::Any, false],
            'zeros, unmasked' => ['00000000000', DocumentFormat::Unmasked, false],
            'repetition of ones' => ['11111111111', DocumentFormat::Any, false],
            'partial punctuation' => ['82433543.31-5', DocumentFormat::Any, false],
            'the CPF mask' => ['824.335.433-15', DocumentFormat::Any, false],
            'ten digits' => ['8243354331', DocumentFormat::Any, false],
            'twelve digits' => ['824335433150', DocumentFormat::Any, false],
            'a space at the end' => ['82433543315 ', DocumentFormat::Any, false],
            'a newline at the end' => ["824.33543.31-5\n", DocumentFormat::Any, false],
            'a space at the start' => [' 824.33543.31-5', DocumentFormat::Any, false],
            'an integer' => [82433543315, DocumentFormat::Any, false],
            'null' => [null, DocumentFormat::Any, false],
            'empty' => ['', DocumentFormat::Any, false],
        ];
    }

    #[DataProvider('pisCases')]
    public function testPis(mixed $candidate, DocumentFormat $format, bool $expected): void
    {
        self::assertSame($expected, (new PisSpecification($format))->isSatisfiedBy($candidate));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function voterIdCases(): array
    {
        return [
            'SP, plain' => ['309621560191', true],
            'SP, first remainder 0 gives 1' => ['847882780116', true],
            'SP, first remainder 0 written 0 (the rule of the other states)' => ['847882780108', false],
            'SP, first remainder 10 gives 0' => ['709144300108', true],
            'SP, second remainder 0 gives 1' => ['737168400141', true],
            'MG, plain' => ['802266810299', true],
            'MG, first remainder 0 gives 1' => ['743726200213', true],
            'MG, first remainder 10 gives 0' => ['424276780205', true],
            'MG, second remainder 0 gives 1' => ['375501110281', true],
            'RJ, plain' => ['073245830361', true],
            'RJ, first remainder 0 gives 0' => ['881251240302', true],
            'RJ, first remainder 0 written 1 (the rule of SP and MG)' => ['881251240311', false],
            'RJ, first remainder 10 gives 0' => ['099303230302', true],
            'RJ, second remainder 0 gives 0' => ['617175720310', true],
            'abroad (28), plain' => ['879711042836', true],
            'abroad (28), first remainder 0 gives 0' => ['430719592801', true],
            'abroad (28), first remainder 10 gives 0' => ['818666202801', true],
            'abroad (28), second remainder 0 gives 0' => ['846912372860', true],
            'state 00 with right arithmetic' => ['521637790019', false],
            'state 29 with right arithmetic' => ['818187202941', false],
            'state 99 with right arithmetic' => ['623860519911', false],
            'first check digit wrong' => ['309621560181', false],
            'second check digit wrong' => ['309621560192', false],
            'a sequence digit changed' => ['409621560191', false],
            'the state changed' => ['309621560291', false],
            'zeros' => ['000000000000', false],
            'repetition of ones' => ['111111111111', false],
            'repetition of twos' => ['222222222222', false],
            'the last digit dropped (read as another number)' => ['30962156019', false],
            'thirteen digits' => ['3096215601910', false],
            'thirteen digits with a leading zero' => ['0309621560191', false],
            'sequence with leading zeros, written' => ['000000932305', true],
            'sequence with leading zeros, dropped (as issued)' => ['932305', true],
            'sequence with leading zeros, some written' => ['0000932305', true],
            'leading zeros dropped, first check digit wrong' => ['932315', false],
            'leading zeros dropped, second check digit wrong' => ['932306', false],
            'SP, one sequence digit: the shortest form (five digits)' => ['10191', true],
            'SP, one sequence digit, zeros written' => ['000000010191', true],
            'four digits' => ['0191', false],
            'four digits of a state and check digits' => ['9323', false],
            'zero sequence with right arithmetic (state 05)' => ['000000000507', false],
            'zero sequence with right arithmetic, five digits' => ['00507', false],
            'zero sequence with right arithmetic (SP)' => ['000000000116', false],
            'zero sequence, three digits' => ['507', false],
            'groups of four' => ['3096 2156 0191', false],
            'a space at the end' => ['309621560191 ', false],
            'a newline at the end' => ["309621560191\n", false],
            'an integer' => [309621560191, false],
            'null' => [null, false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('voterIdCases')]
    public function testVoterId(mixed $candidate, bool $expected): void
    {
        self::assertSame($expected, (new VoterIdSpecification())->isSatisfiedBy($candidate));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function renavamCases(): array
    {
        return [
            'plain' => ['34797468689', true],
            'remainder 0 gives 0' => ['93215254090', true],
            'remainder 1 gives 0' => ['88073468910', true],
            'a nine-digit number written with two leading zeros' => ['00144546329', true],
            'the same nine digits without the zeros' => ['144546329', false],
            'check digit wrong' => ['34797468680', false],
            'another digit changed' => ['44797468689', false],
            'zeros (right arithmetic)' => ['00000000000', false],
            'repetition of ones' => ['11111111111', false],
            'ten digits' => ['3479746868', false],
            'twelve digits' => ['347974686890', false],
            'with a hyphen' => ['3479746868-9', false],
            'a space at the end' => ['34797468689 ', false],
            'a newline at the end' => ["34797468689\n", false],
            'an integer' => [34797468689, false],
            'null' => [null, false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('renavamCases')]
    public function testRenavam(mixed $candidate, bool $expected): void
    {
        self::assertSame($expected, (new RenavamSpecification())->isSatisfiedBy($candidate));
    }

    public function testPisAndRenavamShareTheirCheckDigit(): void
    {
        // The same rule: Denatran's (weights 2..9, 2, 3 from the right, ten times the sum) and the PIS one.
        foreach (['82433543315', '44852365820', '34797468689', '93215254090'] as $value) {
            self::assertTrue((new PisSpecification(DocumentFormat::Unmasked))->isSatisfiedBy($value), $value);
            self::assertTrue((new RenavamSpecification())->isSatisfiedBy($value), $value);
        }
    }
}
