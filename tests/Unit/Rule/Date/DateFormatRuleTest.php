<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Date;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Date\DateFormatRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateFormatRule::class)]
final class DateFormatRuleTest extends TestCase
{
    private DateFormatRule $rule;

    protected function setUp(): void
    {
        $this->rule = new DateFormatRule();
    }

    public function testDefaultFormatYmd(): void
    {
        $ctx = $this->ctx([]);
        $this->assertTrue($this->rule->validate('2025-02-28', $ctx));
    }

    public function testInvalidDefaultFormat(): void
    {
        $ctx = $this->ctx([]);
        $this->assertFalse($this->rule->validate('28/02/2025', $ctx));
    }

    public function testCustomFormat(): void
    {
        $ctx = $this->ctx(['format' => 'd/m/Y']);
        $this->assertTrue($this->rule->validate('28/02/2025', $ctx));
    }

    public function testDateTimeFormat(): void
    {
        $ctx = $this->ctx(['format' => 'Y-m-d H:i:s']);
        $this->assertTrue($this->rule->validate('2025-02-28 14:30:00', $ctx));
    }

    public function testStrictValidation(): void
    {
        $ctx = $this->ctx([]);
        $this->assertFalse($this->rule->validate('2025-02-30', $ctx));
    }

    public function testInvalidDateFormat(): void
    {
        $ctx = $this->ctx([]);
        $this->assertFalse($this->rule->validate('not-a-date', $ctx));
    }

    public function testNonStringReturnsFalse(): void
    {
        $ctx = $this->ctx([]);
        $this->assertFalse($this->rule->validate(20250228, $ctx));
        $this->assertFalse($this->rule->validate(null, $ctx));
    }

    public function testIso8601Format(): void
    {
        $ctx = $this->ctx(['format' => 'Y-m-d\TH:i:sP']);
        $this->assertTrue($this->rule->validate('2025-02-28T14:30:00+00:00', $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('date.format', $this->rule->getErrorCode());
    }

    public function testLeadingZerosRequired(): void
    {
        $ctx = $this->ctx([]);
        // '2025-2-28' re-formats to '2025-02-28' — strict check fails
        $this->assertFalse($this->rule->validate('2025-2-28', $ctx));
    }

    public function testEmptyStringFails(): void
    {
        $ctx = $this->ctx([]);
        $this->assertFalse($this->rule->validate('', $ctx));
    }

    public function testDefaultMessage(): void
    {
        $this->assertSame('The {field} must match the date format {format}.', $this->rule->getDefaultMessage());
    }

    private function ctx(array $params): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('f')->withParameters($params);
    }
}
