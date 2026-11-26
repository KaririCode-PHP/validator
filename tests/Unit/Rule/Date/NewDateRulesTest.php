<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Date;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Date\DateAfterRule;
use KaririCode\Validator\Rule\Date\DateBeforeRule;
use KaririCode\Validator\Rule\Date\DateBetweenRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateAfterRule::class)]
#[CoversClass(DateBeforeRule::class)]
#[CoversClass(DateBetweenRule::class)]
final class NewDateRulesTest extends TestCase
{
    private function ctx(array $params = []): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('test')->withParameters($params);
    }

    public function testDateBefore(): void
    {
        $rule = new DateBeforeRule();
        $ctx = $this->ctx(['before' => '2025-12-31']);
        $this->assertTrue($rule->validate('2025-01-01', $ctx));
        $this->assertFalse($rule->validate('2026-01-01', $ctx));
        $this->assertFalse($rule->validate('2025-12-31', $ctx)); // not strictly before
    }

    public function testDateAfter(): void
    {
        $rule = new DateAfterRule();
        $ctx = $this->ctx(['after' => '2025-01-01']);
        $this->assertTrue($rule->validate('2025-06-15', $ctx));
        $this->assertFalse($rule->validate('2024-12-31', $ctx));
        $this->assertFalse($rule->validate('2025-01-01', $ctx)); // not strictly after
    }

    public function testDateBetween(): void
    {
        $rule = new DateBetweenRule();
        $ctx = $this->ctx(['after' => '2025-01-01', 'before' => '2025-12-31']);
        $this->assertTrue($rule->validate('2025-06-15', $ctx));
        $this->assertFalse($rule->validate('2025-01-01', $ctx)); // exclusive
        $this->assertFalse($rule->validate('2025-12-31', $ctx)); // exclusive
        $this->assertFalse($rule->validate('2024-06-15', $ctx));
    }

    public function testCustomFormat(): void
    {
        $rule = new DateBeforeRule();
        $ctx = $this->ctx(['before' => '31/12/2025', 'format' => 'd/m/Y']);
        $this->assertTrue($rule->validate('01/01/2025', $ctx));
        $this->assertFalse($rule->validate('01/01/2026', $ctx));
    }

    public function testInvalidDateFormat(): void
    {
        $rule = new DateBeforeRule();
        $ctx = $this->ctx(['before' => '2025-12-31']);
        $this->assertFalse($rule->validate('not-a-date', $ctx));
        $this->assertFalse($rule->validate(123, $ctx));
    }

    public function testMissingParameter(): void
    {
        $this->assertFalse(new DateBeforeRule()->validate('2025-01-01', $this->ctx()));
        $this->assertFalse(new DateAfterRule()->validate('2025-01-01', $this->ctx()));
        $this->assertFalse(new DateBetweenRule()->validate('2025-01-01', $this->ctx()));
    }

    public function testNonStringReturnsFalse(): void
    {
        $ctx = $this->ctx(['after' => '2025-01-01']);
        $this->assertFalse(new DateAfterRule()->validate(42, $ctx));
        $this->assertFalse(new DateAfterRule()->validate(null, $ctx));
        $this->assertFalse(new DateBetweenRule()->validate(42, $this->ctx(['after' => '2025-01-01', 'before' => '2025-12-31'])));
    }

    public function testInvalidDateStringReturnsFalse(): void
    {
        $ctx = $this->ctx(['after' => '2025-01-01']);
        $this->assertFalse(new DateAfterRule()->validate('not-a-date', $ctx));
        $this->assertFalse(new DateAfterRule()->validate(123, $ctx));
    }

    public function testDateAfterWithNonStringAfterParam(): void
    {
        // Covers DateAfterRule.validate() L37: !is_string($afterStr) returns false
        $ctx = $this->ctx(['after' => 42]);
        $this->assertFalse(new DateAfterRule()->validate('2025-06-01', $ctx));
    }

    public function testDateBetweenMissingBefore(): void
    {
        // Only after param — before is null, should return false
        $this->assertFalse(new DateBetweenRule()->validate('2025-06-01', $this->ctx(['after' => '2025-01-01'])));
    }

    public function testDateBetweenInvalidDateString(): void
    {
        // Covers DateBetweenRule.validate() L40: createFromFormat returns false
        $ctx = $this->ctx(['after' => '2025-01-01', 'before' => '2025-12-31']);
        $this->assertFalse(new DateBetweenRule()->validate('not-a-date', $ctx));
        $this->assertFalse(new DateAfterRule()->validate('not-a-date', $this->ctx(['after' => '2025-01-01'])));
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('date.before', new DateBeforeRule()->getErrorCode());
        $this->assertSame('date.after', new DateAfterRule()->getErrorCode());
        $this->assertSame('date.between', new DateBetweenRule()->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} must be a date before {before}.', new DateBeforeRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a date after {after}.', new DateAfterRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a date between {after} and {before}.', new DateBetweenRule()->getDefaultMessage());
    }
}
