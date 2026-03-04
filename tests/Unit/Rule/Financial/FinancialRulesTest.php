<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Financial;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Financial\BicRule;
use KaririCode\Validator\Rule\Financial\IbanRule;
use KaririCode\Validator\Rule\Financial\LuhnRule;
use PHPUnit\Framework\TestCase;

final class FinancialRulesTest extends TestCase
{
    private function ctx(): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('test');
    }

    // ── Luhn ──
    public function testLuhnValidCards(): void
    {
        $rule = new LuhnRule();
        $this->assertTrue($rule->validate('4111111111111111', $this->ctx())); // Visa test
        $this->assertTrue($rule->validate('5500000000000004', $this->ctx())); // MC test
        $this->assertTrue($rule->validate('4111 1111 1111 1111', $this->ctx())); // with spaces
        $this->assertTrue($rule->validate('4111-1111-1111-1111', $this->ctx())); // with hyphens
    }

    public function testLuhnInvalidCards(): void
    {
        $rule = new LuhnRule();
        $this->assertFalse($rule->validate('4111111111111112', $this->ctx())); // bad check digit
        $this->assertFalse($rule->validate('1234567', $this->ctx())); // too short
        $this->assertFalse($rule->validate('abcdefgh12345678', $this->ctx())); // not digits
        $this->assertFalse($rule->validate('', $this->ctx()));
        $this->assertFalse($rule->validate(null, $this->ctx()));
    }

    // ── IBAN ──
    public function testIbanValid(): void
    {
        $rule = new IbanRule();
        $this->assertTrue($rule->validate('GB29 NWBK 6016 1331 9268 19', $this->ctx())); // UK
        $this->assertTrue($rule->validate('DE89370400440532013000', $this->ctx())); // Germany
        $this->assertTrue($rule->validate('FR7630006000011234567890189', $this->ctx())); // France
    }

    public function testIbanInvalid(): void
    {
        $rule = new IbanRule();
        $this->assertFalse($rule->validate('GB29NWBK60161331926800', $this->ctx())); // bad check
        $this->assertFalse($rule->validate('INVALIDIBAN', $this->ctx()));
        $this->assertFalse($rule->validate('', $this->ctx()));
        $this->assertFalse($rule->validate(123, $this->ctx()));
    }

    // ── BIC ──
    public function testBicValid(): void
    {
        $rule = new BicRule();
        $this->assertTrue($rule->validate('DEUTDEFF', $this->ctx())); // Deutsche Bank 8-char
        $this->assertTrue($rule->validate('DEUTDEFF500', $this->ctx())); // 11-char with branch
        $this->assertTrue($rule->validate('BNPAFRPP', $this->ctx())); // BNP Paribas
    }

    public function testBicInvalid(): void
    {
        $rule = new BicRule();
        $this->assertFalse($rule->validate('DEUT', $this->ctx())); // too short
        $this->assertFalse($rule->validate('1234DEFF', $this->ctx())); // numbers in bank code
        $this->assertFalse($rule->validate('', $this->ctx()));
        $this->assertFalse($rule->validate(123, $this->ctx()));
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('financial.luhn', (new LuhnRule())->getErrorCode());
        $this->assertSame('financial.iban', (new IbanRule())->getErrorCode());
        $this->assertSame('financial.bic', (new BicRule())->getErrorCode());
    }
}
