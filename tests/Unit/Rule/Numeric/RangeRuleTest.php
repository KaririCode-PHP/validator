<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Numeric;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Numeric\RangeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RangeRule::class)]
final class RangeRuleTest extends TestCase
{
    private RangeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RangeRule();
    }

    public function testWithinRange(): void
    {
        $ctx = $this->ctx(['min' => 0, 'max' => 100]);
        $this->assertTrue($this->rule->validate(50, $ctx));
    }

    public function testExactMinBoundary(): void
    {
        $ctx = $this->ctx(['min' => 10, 'max' => 100]);
        $this->assertTrue($this->rule->validate(10, $ctx));
    }

    public function testExactMaxBoundary(): void
    {
        $ctx = $this->ctx(['min' => 0, 'max' => 100]);
        $this->assertTrue($this->rule->validate(100, $ctx));
    }

    public function testBelowMinFails(): void
    {
        $ctx = $this->ctx(['min' => 10]);
        $this->assertFalse($this->rule->validate(5, $ctx));
    }

    public function testAboveMaxFails(): void
    {
        $ctx = $this->ctx(['max' => 100]);
        $this->assertFalse($this->rule->validate(150, $ctx));
    }

    public function testFloatValues(): void
    {
        $ctx = $this->ctx(['min' => 0.5, 'max' => 9.9]);
        $this->assertTrue($this->rule->validate(5.5, $ctx));
        $this->assertFalse($this->rule->validate(0.1, $ctx));
    }

    public function testNumericString(): void
    {
        $ctx = $this->ctx(['min' => 0, 'max' => 100]);
        $this->assertTrue($this->rule->validate('50', $ctx));
    }

    public function testNonNumericFails(): void
    {
        $ctx = $this->ctx(['min' => 0, 'max' => 100]);
        $this->assertFalse($this->rule->validate('abc', $ctx));
        $this->assertFalse($this->rule->validate(null, $ctx));
    }

    public function testNegativeRange(): void
    {
        $ctx = $this->ctx(['min' => -100, 'max' => -1]);
        $this->assertTrue($this->rule->validate(-50, $ctx));
        $this->assertFalse($this->rule->validate(0, $ctx));
    }

    public function testOnlyMinBound(): void
    {
        $ctx = $this->ctx(['min' => 0]);
        $this->assertTrue($this->rule->validate(PHP_INT_MAX, $ctx));
        $this->assertFalse($this->rule->validate(-1, $ctx));
    }

    public function testOnlyMaxBound(): void
    {
        $ctx = $this->ctx(['max' => 100]);
        $this->assertTrue($this->rule->validate(-9999, $ctx));
        $this->assertFalse($this->rule->validate(101, $ctx));
    }

    public function testNoBoundsAlwaysPasses(): void
    {
        $ctx = $this->ctx([]);
        $this->assertTrue($this->rule->validate(99999, $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('numeric.range', $this->rule->getErrorCode());
    }

    private function ctx(array $params): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('f')->withParameters($params);
    }
}
