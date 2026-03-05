<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Financial;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Financial\BicRule;
use KaririCode\Validator\Rule\Financial\IbanRule;
use KaririCode\Validator\Rule\Financial\LuhnRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LuhnRule::class)]
#[CoversClass(IbanRule::class)]
#[CoversClass(BicRule::class)]
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

    public function testIbanMod97Fallback(): void
    {
        // Covers IbanRule::mod97() private method via reflection.
        // This is the chunked-modulo fallback for systems without bcmath.
        $rule = new IbanRule();
        $reflect = new \ReflectionMethod(IbanRule::class, 'mod97');
        $reflect->setAccessible(true);

        // GB29NWBK60161331926819 rearranged and letter-encoded:
        // NWBK60161331926819GB29 -> 2332112060161331926819161129
        // mod97 = 1 (valid IBAN check)
        $result = $reflect->invoke($rule, '2332112060161331926819161129');
        $this->assertSame(1, $result);

        // Invalid: one digit changed, mod97 != 1
        $resultInvalid = $reflect->invoke($rule, '2332112060161331926819161128');
        $this->assertNotSame(1, $resultInvalid);
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('financial.luhn', (new LuhnRule())->getErrorCode());
        $this->assertSame('financial.iban', (new IbanRule())->getErrorCode());
        $this->assertSame('financial.bic', (new BicRule())->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} is not a valid card number.', (new LuhnRule())->getDefaultMessage());
        $this->assertSame('The {field} is not a valid IBAN.', (new IbanRule())->getDefaultMessage());
        $this->assertSame('The {field} is not a valid BIC/SWIFT code.', (new BicRule())->getDefaultMessage());
    }
}
