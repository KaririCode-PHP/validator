<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\String;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\String\LengthRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LengthRule::class)]
final class LengthRuleTest extends TestCase
{
    private LengthRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LengthRule();
    }

    public function testWithinBounds(): void
    {
        $ctx = $this->ctx(['min' => 2, 'max' => 10]);
        $this->assertTrue($this->rule->validate('hello', $ctx));
    }

    public function testExactMinBoundary(): void
    {
        $ctx = $this->ctx(['min' => 5]);
        $this->assertTrue($this->rule->validate('hello', $ctx));
    }

    public function testBelowMinBoundary(): void
    {
        $ctx = $this->ctx(['min' => 5]);
        $this->assertFalse($this->rule->validate('hi', $ctx));
    }

    public function testExactMaxBoundary(): void
    {
        $ctx = $this->ctx(['max' => 5]);
        $this->assertTrue($this->rule->validate('hello', $ctx));
    }

    public function testAboveMaxBoundary(): void
    {
        $ctx = $this->ctx(['max' => 3]);
        $this->assertFalse($this->rule->validate('hello', $ctx));
    }

    public function testNoMaxAllowsAnyLength(): void
    {
        $ctx = $this->ctx(['min' => 0]);
        $this->assertTrue($this->rule->validate(str_repeat('a', 10000), $ctx));
    }

    public function testMultibyteSupport(): void
    {
        $ctx = $this->ctx(['min' => 3, 'max' => 5]);
        $this->assertTrue($this->rule->validate('café', $ctx));  // 4 chars
        $this->assertTrue($this->rule->validate('日本語', $ctx));  // 3 chars
    }

    public function testEmptyStringWithMinZero(): void
    {
        $ctx = $this->ctx(['min' => 0, 'max' => 100]);
        $this->assertTrue($this->rule->validate('', $ctx));
    }

    public function testNonStringReturnsFalse(): void
    {
        $ctx = $this->ctx(['min' => 0]);
        $this->assertFalse($this->rule->validate(42, $ctx));
        $this->assertFalse($this->rule->validate(null, $ctx));
    }

    public function testDefaultMinIsZero(): void
    {
        $ctx = $this->ctx(['max' => 100]);
        $this->assertTrue($this->rule->validate('', $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('string.length', $this->rule->getErrorCode());
    }

    private function ctx(array $params): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('f')->withParameters($params);
    }
}
