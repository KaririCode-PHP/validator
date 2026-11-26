<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\String;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\String\NotEmptyRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotEmptyRule::class)]
final class NotEmptyRuleTest extends TestCase
{
    private NotEmptyRule $rule;
    private ValidationContextImpl $ctx;

    protected function setUp(): void
    {
        $this->rule = new NotEmptyRule();
        $this->ctx = ValidationContextImpl::create([])->withField('f');
    }

    public function testNonEmptyStringPasses(): void
    {
        $this->assertTrue($this->rule->validate('hello', $this->ctx));
    }

    public function testEmptyStringFails(): void
    {
        $this->assertFalse($this->rule->validate('', $this->ctx));
    }

    public function testWhitespaceOnlyFails(): void
    {
        $this->assertFalse($this->rule->validate('   ', $this->ctx));
    }

    public function testZeroStringPasses(): void
    {
        $this->assertTrue($this->rule->validate('0', $this->ctx));
    }

    public function testNonStringReturnsFalse(): void
    {
        $this->assertFalse($this->rule->validate(null, $this->ctx));
        $this->assertFalse($this->rule->validate(42, $this->ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('string.not_empty', $this->rule->getErrorCode());
    }

    public function testDefaultMessage(): void
    {
        $this->assertSame('The {field} must not be empty.', $this->rule->getDefaultMessage());
    }
}
