<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Comparison;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Comparison\BetweenRule;
use KaririCode\Validator\Rule\Comparison\EqualToRule;
use KaririCode\Validator\Rule\Comparison\GreaterThanOrEqualRule;
use KaririCode\Validator\Rule\Comparison\GreaterThanRule;
use KaririCode\Validator\Rule\Comparison\LessThanOrEqualRule;
use KaririCode\Validator\Rule\Comparison\LessThanRule;
use KaririCode\Validator\Rule\Comparison\NotEqualToRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EqualToRule::class)]
#[CoversClass(NotEqualToRule::class)]
#[CoversClass(GreaterThanRule::class)]
#[CoversClass(GreaterThanOrEqualRule::class)]
#[CoversClass(LessThanRule::class)]
#[CoversClass(LessThanOrEqualRule::class)]
#[CoversClass(BetweenRule::class)]
final class ComparisonRulesTest extends TestCase
{
    private function ctx(array $params = []): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('test')->withParameters($params);
    }

    public function testEqualTo(): void
    {
        $rule = new EqualToRule();
        $this->assertTrue($rule->validate(42, $this->ctx(['expected' => 42])));
        $this->assertTrue($rule->validate('test', $this->ctx(['expected' => 'test'])));
        $this->assertFalse($rule->validate(42, $this->ctx(['expected' => '42']))); // strict
        $this->assertFalse($rule->validate(41, $this->ctx(['expected' => 42])));
    }

    public function testNotEqualTo(): void
    {
        $rule = new NotEqualToRule();
        $this->assertTrue($rule->validate(41, $this->ctx(['expected' => 42])));
        $this->assertFalse($rule->validate(42, $this->ctx(['expected' => 42])));
    }

    public function testGreaterThan(): void
    {
        $rule = new GreaterThanRule();
        $this->assertTrue($rule->validate(11, $this->ctx(['threshold' => 10])));
        $this->assertFalse($rule->validate(10, $this->ctx(['threshold' => 10])));
        $this->assertFalse($rule->validate(9, $this->ctx(['threshold' => 10])));
        $this->assertFalse($rule->validate('abc', $this->ctx(['threshold' => 10])));
    }

    public function testGreaterThanOrEqual(): void
    {
        $rule = new GreaterThanOrEqualRule();
        $this->assertTrue($rule->validate(10, $this->ctx(['threshold' => 10])));
        $this->assertTrue($rule->validate(11, $this->ctx(['threshold' => 10])));
        $this->assertFalse($rule->validate(9, $this->ctx(['threshold' => 10])));
    }

    public function testLessThan(): void
    {
        $rule = new LessThanRule();
        $this->assertTrue($rule->validate(9, $this->ctx(['threshold' => 10])));
        $this->assertFalse($rule->validate(10, $this->ctx(['threshold' => 10])));
    }

    public function testLessThanOrEqual(): void
    {
        $rule = new LessThanOrEqualRule();
        $this->assertTrue($rule->validate(10, $this->ctx(['threshold' => 10])));
        $this->assertTrue($rule->validate(9, $this->ctx(['threshold' => 10])));
        $this->assertFalse($rule->validate(11, $this->ctx(['threshold' => 10])));
    }

    public function testBetweenExclusive(): void
    {
        $rule = new BetweenRule();
        $ctx = $this->ctx(['min' => 1, 'max' => 10]);
        $this->assertTrue($rule->validate(5, $ctx));
        $this->assertFalse($rule->validate(1, $ctx)); // exclusive
        $this->assertFalse($rule->validate(10, $ctx)); // exclusive
        $this->assertFalse($rule->validate(0, $ctx));
        $this->assertFalse($rule->validate(11, $ctx));
    }

    public function testBetweenRequiresBothParams(): void
    {
        $rule = new BetweenRule();
        $this->assertFalse($rule->validate(5, $this->ctx(['min' => 1])));
        $this->assertFalse($rule->validate(5, $this->ctx(['max' => 10])));
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('comparison.equal_to', new EqualToRule()->getErrorCode());
        $this->assertSame('comparison.not_equal_to', new NotEqualToRule()->getErrorCode());
        $this->assertSame('comparison.greater_than', new GreaterThanRule()->getErrorCode());
        $this->assertSame('comparison.less_than', new LessThanRule()->getErrorCode());
        $this->assertSame('comparison.between', new BetweenRule()->getErrorCode());
        $this->assertSame('comparison.greater_than_or_equal', new GreaterThanOrEqualRule()->getErrorCode());
        $this->assertSame('comparison.less_than_or_equal', new LessThanOrEqualRule()->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} must be equal to {expected}.', new EqualToRule()->getDefaultMessage());
        $this->assertSame('The {field} must not be equal to {expected}.', new NotEqualToRule()->getDefaultMessage());
        $this->assertSame('The {field} must be greater than {threshold}.', new GreaterThanRule()->getDefaultMessage());
        $this->assertSame('The {field} must be less than {threshold}.', new LessThanRule()->getDefaultMessage());
        $this->assertSame('The {field} must be between {min} and {max}.', new BetweenRule()->getDefaultMessage());
        $this->assertSame('The {field} must be greater than or equal to {threshold}.', new GreaterThanOrEqualRule()->getDefaultMessage());
        $this->assertSame('The {field} must be less than or equal to {threshold}.', new LessThanOrEqualRule()->getDefaultMessage());
    }

    public function testNonNumericReturnsFalse(): void
    {
        $ctx = $this->ctx(['threshold' => 10]);
        $this->assertFalse(new LessThanRule()->validate('abc', $ctx));
        $this->assertFalse(new GreaterThanOrEqualRule()->validate('abc', $ctx));
        $this->assertFalse(new LessThanOrEqualRule()->validate('abc', $ctx));
        $this->assertFalse(new BetweenRule()->validate('abc', $this->ctx(['min' => 1, 'max' => 10])));
    }

    public function testGreaterThanOrEqualNullThreshold(): void
    {
        $this->assertFalse(new GreaterThanOrEqualRule()->validate(5, $this->ctx([])));
    }

    public function testLessThanOrEqualNullThreshold(): void
    {
        $this->assertFalse(new LessThanOrEqualRule()->validate(5, $this->ctx([])));
    }
}
