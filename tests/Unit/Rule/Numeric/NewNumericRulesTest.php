<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Numeric;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Numeric\DecimalRule;
use KaririCode\Validator\Rule\Numeric\NegativeOrZeroRule;
use KaririCode\Validator\Rule\Numeric\NegativeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DecimalRule::class)]
#[CoversClass(NegativeRule::class)]
#[CoversClass(NegativeOrZeroRule::class)]
final class NewNumericRulesTest extends TestCase
{
    private function ctx(array $params = []): ValidationContextImpl
    {
        $ctx = ValidationContextImpl::create([])->withField('test');

        return $params !== [] ? $ctx->withParameters($params) : $ctx;
    }

    public function testNegativeAcceptsNegative(): void
    {
        $rule = new NegativeRule();
        $this->assertTrue($rule->validate(-1, $this->ctx()));
        $this->assertTrue($rule->validate(-0.5, $this->ctx()));
        $this->assertTrue($rule->validate('-10', $this->ctx()));
        $this->assertFalse($rule->validate(0, $this->ctx()));
        $this->assertFalse($rule->validate(1, $this->ctx()));
        $this->assertFalse($rule->validate('abc', $this->ctx()));
    }

    public function testNegativeOrZero(): void
    {
        $rule = new NegativeOrZeroRule();
        $this->assertTrue($rule->validate(-1, $this->ctx()));
        $this->assertTrue($rule->validate(0, $this->ctx()));
        $this->assertTrue($rule->validate(0.0, $this->ctx()));
        $this->assertFalse($rule->validate(1, $this->ctx()));
        $this->assertFalse($rule->validate(0.001, $this->ctx()));
    }

    public function testDecimalDefault2Places(): void
    {
        $rule = new DecimalRule();
        $this->assertTrue($rule->validate(10, $this->ctx()));
        $this->assertTrue($rule->validate('10.50', $this->ctx()));
        $this->assertTrue($rule->validate('10.5', $this->ctx()));
        $this->assertFalse($rule->validate('10.555', $this->ctx()));
    }

    public function testDecimalCustomPlaces(): void
    {
        $rule = new DecimalRule();
        $ctx = $this->ctx(['places' => 4]);
        $this->assertTrue($rule->validate('3.1415', $ctx));
        $this->assertFalse($rule->validate('3.14159', $ctx));
    }

    public function testDecimalRejectsNonNumeric(): void
    {
        $this->assertFalse(new DecimalRule()->validate('abc', $this->ctx()));
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('numeric.negative', new NegativeRule()->getErrorCode());
        $this->assertSame('numeric.negative_or_zero', new NegativeOrZeroRule()->getErrorCode());
        $this->assertSame('numeric.decimal', new DecimalRule()->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} must be a negative number.', new NegativeRule()->getDefaultMessage());
        $this->assertSame('The {field} must be negative or zero.', new NegativeOrZeroRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a decimal number with at most {places} decimal places.', new DecimalRule()->getDefaultMessage());
    }
}
