<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule;

use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Rule\Brazilian\CepRule;
use KaririCode\Validator\Rule\Brazilian\CnpjRule;
use KaririCode\Validator\Rule\Brazilian\CpfRule;
use KaririCode\Validator\Rule\Brazilian\PisRule;
use KaririCode\Validator\Rule\Financial\BicRule;
use KaririCode\Validator\Rule\Financial\IbanRule;
use KaririCode\Validator\Rule\Financial\LuhnRule;
use KaririCode\Validator\Tests\Support\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

// ── Unit: DocumentRulesTest ──────────────────────────────────────────
// Check digits: card numbers, IBAN, BIC, CPF, CNPJ (alphanumeric too), CEP, PIS.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(LuhnRule::class)]
#[CoversClass(IbanRule::class)]
#[CoversClass(BicRule::class)]
#[CoversClass(CpfRule::class)]
#[CoversClass(CnpjRule::class)]
#[CoversClass(CepRule::class)]
#[CoversClass(PisRule::class)]
final class DocumentRulesTest extends RuleTestCase
{
    /**
     * @return iterable<string, array{bool, ValidationRule, mixed}>
     */
    public static function cases(): iterable
    {
        yield 'luhn: card with spaces' => [true, new LuhnRule(), '4111 1111 1111 1111'];
        yield 'luhn: card with hyphens' => [true, new LuhnRule(), '4111-1111-1111-1111'];
        yield 'luhn: integer' => [true, new LuhnRule(), 4111111111111111];
        yield 'luhn: odd length' => [true, new LuhnRule(), '79927398713'];
        yield 'luhn: odd length, wrong digit' => [false, new LuhnRule(), '79927398710'];
        yield 'luhn: wrong digit' => [false, new LuhnRule(), '4111111111111112'];
        yield 'luhn: eight digits' => [true, new LuhnRule(), '00000000'];
        yield 'luhn: seven digits' => [false, new LuhnRule(), '0000000'];
        yield 'luhn: letters' => [false, new LuhnRule(), '4111x11111111111'];
        yield 'luhn: tab' => [false, new LuhnRule(), "4111\t1111 1111 1111"];
        yield 'luhn: array' => [false, new LuhnRule(), [4111111111111111]];
        yield 'luhn: float' => [false, new LuhnRule(), 4111.0];

        yield 'iban: grouped' => [true, new IbanRule(), 'DE89 3704 0044 0532 0130 00'];
        yield 'iban: compact' => [true, new IbanRule(), 'GB82WEST12345698765432'];
        yield 'iban: lower case' => [true, new IbanRule(), 'gb82west12345698765432'];
        yield 'iban: shortest country (15)' => [true, new IbanRule(), 'NO9386011117947'];
        yield 'iban: 15 characters, computed' => [true, new IbanRule(), self::iban('XX', str_repeat('7', 11))];
        yield 'iban: 34 characters, computed' => [true, new IbanRule(), self::iban('XX', str_repeat('A1', 15))];
        yield 'iban: 14 characters, computed' => [false, new IbanRule(), self::iban('XX', str_repeat('7', 10))];
        yield 'iban: 35 characters, computed' => [false, new IbanRule(), self::iban('XX', str_repeat('A1', 15) . '1')];
        yield 'iban: wrong check' => [false, new IbanRule(), 'DE89370400440532013001'];
        yield 'iban: digit in country' => [false, new IbanRule(), 'D189370400440532013000'];
        yield 'iban: integer' => [false, new IbanRule(), 89370400440532013000];

        yield 'bic: 8 characters' => [true, new BicRule(), 'DEUTDEFF'];
        yield 'bic: with branch' => [true, new BicRule(), 'DEUTDEFF500'];
        yield 'bic: lower case, padded' => [true, new BicRule(), ' deutdeff '];
        yield 'bic: 7 characters' => [false, new BicRule(), 'DEUTDEF'];
        yield 'bic: 9 characters' => [false, new BicRule(), 'DEUTDEFF5'];
        yield 'bic: digit in party prefix' => [false, new BicRule(), 'DEU1DEFF'];
        yield 'bic: digit in country' => [false, new BicRule(), 'DEUTD1FF'];
        yield 'bic: integer' => [false, new BicRule(), 12345678];

        yield 'cpf: formatted' => [true, new CpfRule(), '529.982.247-25'];
        yield 'cpf: bare' => [true, new CpfRule(), '52998224725'];
        yield 'cpf: partly formatted' => [true, new CpfRule(), '529982247-25'];
        yield 'cpf: another' => [true, new CpfRule(), '111.444.777-35'];
        yield 'cpf: first check digit zero (remainder 10)' => [true, new CpfRule(), '10000000108'];
        yield 'cpf: wrong second digit' => [false, new CpfRule(), '529.982.247-26'];
        yield 'cpf: wrong first digit' => [false, new CpfRule(), '529.982.247-35'];
        yield 'cpf: repeated digit' => [false, new CpfRule(), '111.111.111-11'];
        yield 'cpf: zeros' => [false, new CpfRule(), '00000000000'];
        yield 'cpf: leading letter' => [false, new CpfRule(), 'a529.982.247-25'];
        yield 'cpf: trailing space' => [false, new CpfRule(), '529.982.247-25 '];
        yield 'cpf: ten digits' => [false, new CpfRule(), '5299822472'];
        yield 'cpf: integer' => [false, new CpfRule(), 52998224725];

        yield 'cnpj: numeric, formatted' => [true, new CnpjRule(), '11.222.333/0001-81'];
        yield 'cnpj: numeric, bare' => [true, new CnpjRule(), '11222333000181'];
        yield 'cnpj: alphanumeric, RFB example' => [true, new CnpjRule(), '12.ABC.345/01DE-35'];
        yield 'cnpj: alphanumeric, bare' => [true, new CnpjRule(), '12ABC34501DE35'];
        yield 'cnpj: letter in the order' => [true, new CnpjRule(), '12.345.678/000A-08'];
        yield 'cnpj: letters I, O and Q' => [true, new CnpjRule(), self::cnpj('IOQ123456789')];
        yield 'cnpj: first check digit zero' => [true, new CnpjRule(), '11000000000108'];
        yield 'cnpj: second check digit zero' => [true, new CnpjRule(), '00011000000430'];
        yield 'cnpj: check digit 9 (remainder 2)' => [true, new CnpjRule(), self::cnpjWithRemainderTwo()];
        yield 'cnpj: wrong second digit' => [false, new CnpjRule(), '11.222.333/0001-82'];
        yield 'cnpj: wrong first digit' => [false, new CnpjRule(), '11.222.333/0001-91'];
        yield 'cnpj: alphanumeric, wrong digit' => [false, new CnpjRule(), '12.ABC.345/01DE-36'];
        yield 'cnpj: lower-case letters' => [false, new CnpjRule(), '12.abc.345/01de-35'];
        yield 'cnpj: letter as check digit' => [false, new CnpjRule(), '11.222.333/0001-8A'];
        yield 'cnpj: zeros' => [false, new CnpjRule(), '00000000000000'];
        yield 'cnpj: trailing space' => [false, new CnpjRule(), '11.222.333/0001-81 '];
        yield 'cnpj: thirteen characters' => [false, new CnpjRule(), '1122233300018'];
        yield 'cnpj: integer' => [false, new CnpjRule(), 11222333000181];

        yield 'cep: formatted' => [true, new CepRule(), '63100-000'];
        yield 'cep: bare' => [true, new CepRule(), '01001000'];
        yield 'cep: zeros, formatted' => [false, new CepRule(), '00000-000'];
        yield 'cep: zeros, bare' => [false, new CepRule(), '00000000'];
        yield 'cep: hyphen misplaced' => [false, new CepRule(), '6310-0000'];
        yield 'cep: nine digits' => [false, new CepRule(), '63100-0000'];
        yield 'cep: leading space' => [false, new CepRule(), ' 63100-000'];
        yield 'cep: integer' => [false, new CepRule(), 63100000];

        yield 'pis: formatted' => [true, new PisRule(), '123.45678.90-0'];
        yield 'pis: bare' => [true, new PisRule(), '12345678900'];
        yield 'pis: check digit zero (remainder 0)' => [true, new PisRule(), '10000000040'];
        yield 'pis: check digit zero (remainder 1)' => [true, new PisRule(), self::pisWithRemainder(1)];
        yield 'pis: check digit 9 (remainder 2)' => [true, new PisRule(), self::pisWithRemainder(2)];
        yield 'pis: zeros' => [false, new PisRule(), '00000000000'];
        yield 'pis: check digit from 11 minus the remainder' => [true, new PisRule(), '12056412545'];
        yield 'pis: wrong digit' => [false, new PisRule(), '12345678906'];
        yield 'pis: repeated digit' => [false, new PisRule(), '11111111111'];
        yield 'pis: ten digits' => [false, new PisRule(), '1234567890'];
        yield 'pis: integer' => [false, new PisRule(), 12345678900];
    }

    #[Test]
    #[DataProvider('cases')]
    public function rulesAcceptAndRejectValues(bool $expected, ValidationRule $rule, mixed $value): void
    {
        self::assertRule($expected, $rule, $value);
    }

    #[Test]
    public function violationsNameTheirCodes(): void
    {
        self::assertViolation('financial.luhn', new LuhnRule());
        self::assertViolation('financial.iban', new IbanRule());
        self::assertViolation('financial.bic', new BicRule());
        self::assertViolation('brazilian.cpf', new CpfRule());
        self::assertViolation('brazilian.cnpj', new CnpjRule());
        self::assertViolation('brazilian.cep', new CepRule());
        self::assertViolation('brazilian.pis', new PisRule());
    }

    /** An IBAN with correct ISO 7064 MOD 97-10 check digits, whatever its length. */
    private static function iban(string $country, string $bban): string
    {
        $digits = '';

        foreach (str_split($bban . $country . '00') as $character) {
            $digits .= ctype_alpha($character) ? (string) (ord($character) - 55) : $character;
        }

        $remainder = 0;

        foreach (str_split($digits) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $country . str_pad((string) (98 - $remainder), 2, '0', STR_PAD_LEFT) . $bban;
    }

    /** A CNPJ with correct check digits for a twelve-character base. */
    private static function cnpj(string $base): string
    {
        foreach ([12, 13] as $length) {
            $sum = 0;

            foreach (str_split($base) as $index => $character) {
                $sum += (ord($character) - 48) * [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2][$index + 13 - $length];
            }

            $base .= (string) ($sum % 11 < 2 ? 0 : 11 - $sum % 11);
        }

        return $base;
    }

    /** The first PIS from 10000000000 up whose weighted sum leaves the remainder given. */
    private static function pisWithRemainder(int $remainder): string
    {
        for ($base = 1000000000; ; ++$base) {
            $digits = (string) $base;
            $sum = 0;

            foreach ([3, 2, 9, 8, 7, 6, 5, 4, 3, 2] as $index => $weight) {
                $sum += (int) $digits[$index] * $weight;
            }

            if ($sum % 11 === $remainder) {
                return $digits . ($remainder < 2 ? '0' : (string) (11 - $remainder));
            }
        }
    }

    /** The first numeric CNPJ from 100000000000 up whose first check digit comes from remainder 2. */
    private static function cnpjWithRemainderTwo(): string
    {
        for ($base = 100000000000; ; ++$base) {
            $sum = 0;

            foreach (str_split((string) $base) as $index => $digit) {
                $sum += (int) $digit * [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2][$index];
            }

            if ($sum % 11 === 2) {
                return self::cnpj((string) $base);
            }
        }
    }
}
