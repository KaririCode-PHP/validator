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
        $this->assertFalse((new DateBeforeRule())->validate('2025-01-01', $this->ctx()));
        $this->assertFalse((new DateAfterRule())->validate('2025-01-01', $this->ctx()));
        $this->assertFalse((new DateBetweenRule())->validate('2025-01-01', $this->ctx()));
    }
}
