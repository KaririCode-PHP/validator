<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Collection;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Collection\CountRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CountRule::class)]
final class CountRuleTest extends TestCase
{
    private CountRule $rule;

    protected function setUp(): void
    {
        $this->rule = new CountRule();
    }

    public function testWithinBounds(): void
    {
        $ctx = $this->ctx(['min' => 1, 'max' => 5]);
        $this->assertTrue($this->rule->validate([1, 2, 3], $ctx));
    }

    public function testBelowMinFails(): void
    {
        $ctx = $this->ctx(['min' => 3]);
        $this->assertFalse($this->rule->validate([1], $ctx));
    }

    public function testAboveMaxFails(): void
    {
        $ctx = $this->ctx(['max' => 2]);
        $this->assertFalse($this->rule->validate([1, 2, 3], $ctx));
    }

    public function testExactMinBoundary(): void
    {
        $ctx = $this->ctx(['min' => 3]);
        $this->assertTrue($this->rule->validate([1, 2, 3], $ctx));
    }

    public function testExactMaxBoundary(): void
    {
        $ctx = $this->ctx(['max' => 3]);
        $this->assertTrue($this->rule->validate([1, 2, 3], $ctx));
    }

    public function testEmptyArrayWithMinZero(): void
    {
        $ctx = $this->ctx(['min' => 0, 'max' => 10]);
        $this->assertTrue($this->rule->validate([], $ctx));
    }

    public function testCountableObject(): void
    {
        $ctx = $this->ctx(['min' => 1, 'max' => 5]);
        $collection = new \ArrayObject([1, 2, 3]);
        $this->assertTrue($this->rule->validate($collection, $ctx));
    }

    public function testNonCountableReturnsFalse(): void
    {
        $ctx = $this->ctx(['min' => 0]);
        $this->assertFalse($this->rule->validate('string', $ctx));
        $this->assertFalse($this->rule->validate(42, $ctx));
        $this->assertFalse($this->rule->validate(null, $ctx));
    }

    public function testNoBoundsDefaultPasses(): void
    {
        $ctx = $this->ctx([]);
        $this->assertTrue($this->rule->validate([1, 2, 3], $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('collection.count', $this->rule->getErrorCode());
    }

    private function ctx(array $params): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('f')->withParameters($params);
    }
}
