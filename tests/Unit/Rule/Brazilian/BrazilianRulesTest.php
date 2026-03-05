<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Brazilian;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Brazilian\CepRule;
use KaririCode\Validator\Rule\Brazilian\CnpjRule;
use KaririCode\Validator\Rule\Brazilian\CpfRule;
use KaririCode\Validator\Rule\Brazilian\PisRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CpfRule::class)]
#[CoversClass(CnpjRule::class)]
#[CoversClass(CepRule::class)]
#[CoversClass(PisRule::class)]
final class BrazilianRulesTest extends TestCase
{
    private function ctx(): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('test');
    }

    // ── CPF ──
    public function testCpfValidFormatted(): void
    {
        $rule = new CpfRule();
        $this->assertTrue($rule->validate('529.982.247-25', $this->ctx()));
    }

    public function testCpfValidUnformatted(): void
    {
        $rule = new CpfRule();
        $this->assertTrue($rule->validate('52998224725', $this->ctx()));
    }

    public function testCpfValidKnownNumbers(): void
    {
        $rule = new CpfRule();
        // Additional known valid CPFs
        $this->assertTrue($rule->validate('111.444.777-35', $this->ctx()));
        $this->assertTrue($rule->validate('86454327060', $this->ctx()));
    }

    public function testCpfRejectsAllSameDigits(): void
    {
        $rule = new CpfRule();
        $this->assertFalse($rule->validate('111.111.111-11', $this->ctx()));
        $this->assertFalse($rule->validate('00000000000', $this->ctx()));
        $this->assertFalse($rule->validate('99999999999', $this->ctx()));
    }

    public function testCpfRejectsInvalidCheckDigits(): void
    {
        $rule = new CpfRule();
        $this->assertFalse($rule->validate('529.982.247-26', $this->ctx())); // bad 2nd check
        $this->assertFalse($rule->validate('12345678901', $this->ctx()));
    }

    public function testCpfRejectsWrongLength(): void
    {
        $rule = new CpfRule();
        $this->assertFalse($rule->validate('1234567890', $this->ctx())); // 10 digits
        $this->assertFalse($rule->validate('123456789012', $this->ctx())); // 12 digits
        $this->assertFalse($rule->validate('', $this->ctx()));
        $this->assertFalse($rule->validate(123, $this->ctx()));
    }

    // ── CNPJ ──
    public function testCnpjValidFormatted(): void
    {
        $rule = new CnpjRule();
        $this->assertTrue($rule->validate('11.222.333/0001-81', $this->ctx()));
    }

    public function testCnpjValidUnformatted(): void
    {
        $rule = new CnpjRule();
        $this->assertTrue($rule->validate('11222333000181', $this->ctx()));
    }

    public function testCnpjRejectsAllSameDigits(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('11111111111111', $this->ctx()));
        $this->assertFalse($rule->validate('00000000000000', $this->ctx()));
    }

    public function testCnpjRejectsInvalidCheckDigits(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('11.222.333/0001-82', $this->ctx()));
        $this->assertFalse($rule->validate('12345678000100', $this->ctx()));
    }

    public function testCnpjRejectsWrongLength(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('1234567890123', $this->ctx())); // 13 digits
        $this->assertFalse($rule->validate('', $this->ctx()));
    }

    // ── CEP ──
    public function testCepValidFormatted(): void
    {
        $rule = new CepRule();
        $this->assertTrue($rule->validate('01001-000', $this->ctx()));
        $this->assertTrue($rule->validate('63100-000', $this->ctx())); // Crato, CE
    }

    public function testCepValidUnformatted(): void
    {
        $rule = new CepRule();
        $this->assertTrue($rule->validate('01001000', $this->ctx()));
    }

    public function testCepRejectsInvalid(): void
    {
        $rule = new CepRule();
        $this->assertFalse($rule->validate('00000000', $this->ctx())); // all zeros
        $this->assertFalse($rule->validate('0100100', $this->ctx())); // 7 digits
        $this->assertFalse($rule->validate('', $this->ctx()));
        $this->assertFalse($rule->validate(12345678, $this->ctx()));
    }

    // ── PIS ──
    public function testPisValid(): void
    {
        $rule = new PisRule();
        $this->assertTrue($rule->validate('123.45678.90-0', $this->ctx()));
        $this->assertTrue($rule->validate('12345678900', $this->ctx()));
    }

    public function testPisRejectsAllSameDigits(): void
    {
        $rule = new PisRule();
        $this->assertFalse($rule->validate('11111111111', $this->ctx()));
        $this->assertFalse($rule->validate('00000000000', $this->ctx()));
    }

    public function testPisRejectsInvalidCheck(): void
    {
        $rule = new PisRule();
        $this->assertFalse($rule->validate('12345678906', $this->ctx()));
    }

    public function testPisRejectsWrongLength(): void
    {
        $rule = new PisRule();
        $this->assertFalse($rule->validate('1234567890', $this->ctx()));
        $this->assertFalse($rule->validate('', $this->ctx()));
    }

    public function testCnpjNonStringReturnsFalse(): void
    {
        // Covers CnpjRule.validate() line 34: !is_string branch
        $this->assertFalse((new CnpjRule())->validate(12345678000181, $this->ctx()));
        $this->assertFalse((new CnpjRule())->validate(null, $this->ctx()));
    }

    public function testPisNonStringReturnsFalse(): void
    {
        // Covers PisRule.validate() line 27: !is_string branch
        $this->assertFalse((new PisRule())->validate(12345678900, $this->ctx()));
        $this->assertFalse((new PisRule())->validate(null, $this->ctx()));
    }

    public function testCpfWrongFirstCheckDigit(): void
    {
        // CPF 52998224735: digits[9]=3 but firstCheck=2 → L58 return false path
        $this->assertFalse((new CpfRule())->validate('52998224735', $this->ctx()));
    }

    public function testCpfCheckDigitZero(): void
    {
        // 10000000108: firstCheck=0 (remainder===10), secondCheck=8
        $this->assertFalse((new CpfRule())->validate('10000000100', $this->ctx())); // wrong second (should be 8)
        $this->assertTrue((new CpfRule())->validate('10000000108', $this->ctx()));  // firstCheck=0 path
        // 10000002810: r2=10 → secondCheck=0 path
        $this->assertTrue((new CpfRule())->validate('10000002810', $this->ctx()));
    }

    public function testCnpjCheckDigitZero(): void
    {
        // 11000000000108: r1=0 → firstCheck=0 (< 2 path)
        $this->assertTrue((new CnpjRule())->validate('11000000000108', $this->ctx()));
        // 00011000000430: r2=1 → secondCheck=0 (< 2 path)
        $this->assertTrue((new CnpjRule())->validate('00011000000430', $this->ctx()));
    }

    public function testPisCheckDigitZero(): void
    {
        // 10000000040: r=0 → check=0 (< 2 path)
        $this->assertTrue((new PisRule())->validate('10000000040', $this->ctx()));
    }

    // ── Error Codes ──
    public function testErrorCodes(): void
    {
        $this->assertSame('brazilian.cpf', (new CpfRule())->getErrorCode());
        $this->assertSame('brazilian.cnpj', (new CnpjRule())->getErrorCode());
        $this->assertSame('brazilian.cep', (new CepRule())->getErrorCode());
        $this->assertSame('brazilian.pis', (new PisRule())->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} is not a valid CPF.', (new CpfRule())->getDefaultMessage());
        $this->assertSame('The {field} is not a valid CNPJ.', (new CnpjRule())->getDefaultMessage());
        $this->assertSame('The {field} is not a valid CEP.', (new CepRule())->getDefaultMessage());
        $this->assertSame('The {field} is not a valid PIS/PASEP.', (new PisRule())->getDefaultMessage());
    }
}
